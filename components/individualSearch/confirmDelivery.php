<?php
// Activar error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Asegurarse de que no haya salida antes
ob_start();

// Iniciar sesión
session_start();

// Incluir la conexión a la DB
include '../../controller/conexion.php';

// Configurar zona horaria Bogotá
date_default_timezone_set('America/Bogota');

// Configurar headers antes de cualquier salida
header('Content-Type: application/json');

// Incluir PHPMailer
require __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Función para log de errores
function logError($message)
{
    $logFile = __DIR__ . '/confirm_delivery_errors.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

// Función para responder con error
function respondWithError($userMessage, $technicalError = null)
{
    if ($technicalError) {
        logError($technicalError);
    }
    ob_clean();
    echo json_encode(['success' => false, 'message' => $userMessage]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_number_id = (int)$_POST['user_number_id'];
    $recipient_number_id = (int)$_POST['recipient_number_id'];
    $recipient_name = trim($_POST['recipient_name']);
    $signature = $_POST['signature']; // Base64 PNG - SIEMPRE ES LA FIRMA DE RECEPCIÓN
    $use_existing_auth = isset($_POST['use_existing_auth']) ? (bool)$_POST['use_existing_auth'] : false;
    $authorization_id = isset($_POST['authorization_id']) ? (int)$_POST['authorization_id'] : null;

    // Para casos sin autorización previa (misma persona o nueva autorización)
    $authorization_letter = isset($_POST['authorization_letter']) ? $_POST['authorization_letter'] : null;
    $id_photo = isset($_FILES['id_photo']) ? $_FILES['id_photo'] : null;

    $sede = $_SESSION['sede'] ?? '';
    $delivered_by = $_SESSION['username'];

    // Validar campos obligatorios básicos
    if (empty($user_number_id)) {
        respondWithError('ID de usuario faltante.');
    }
    if (empty($recipient_number_id)) {
        respondWithError('ID del receptor faltante.');
    }
    if (empty($recipient_name)) {
        respondWithError('Nombre del receptor faltante.');
    }
    if (empty($signature)) {
        respondWithError('Firma de recepción faltante.');
    }
    if (empty($delivered_by)) {
        respondWithError('Usuario entregador faltante.');
    }

    // Validar que recipient_name no sea 'undefined'
    if ($recipient_name === 'undefined') {
        respondWithError('Nombre del receptor inválido.');
    }

    // MODIFICADO: Obtener tipo de entrega desde la BD (gift_category + gift_gender)
    $stmt_gift = $conn->prepare("SELECT gift_category, gift_gender FROM gf_users WHERE number_id = ?");
    if (!$stmt_gift) {
        respondWithError('Error interno del servidor', 'Failed to prepare gift query: ' . $conn->error);
    }

    $stmt_gift->bind_param("i", $user_number_id);

    if (!$stmt_gift->execute()) {
        $stmt_gift->close();
        respondWithError('Error interno del servidor', 'Failed to execute gift query: ' . $stmt_gift->error);
    }

    // Usar bind_result en lugar de get_result
    $stmt_gift->bind_result($gift_category, $gift_gender);

    if ($stmt_gift->fetch()) {
        $gift_category = $gift_category ?? '';
        $gift_gender = $gift_gender ?? '';

        // Concatenar gift_category + gift_gender para formar el tipo_entrega
        $tipo_entrega = trim($gift_category . ' ' . $gift_gender);

        // Validar que el tipo de entrega no esté vacío
        if (empty($tipo_entrega) || $tipo_entrega === ' ') {
            $stmt_gift->close();
            respondWithError('El usuario no tiene categoría de regalo asignada.');
        }
    } else {
        $stmt_gift->close();
        respondWithError('Usuario no encontrado.');
    }
    $stmt_gift->close();

    // Verificar que no haya entrega previa del mismo tipo este año
    $stmt_check_type = $conn->prepare("SELECT COUNT(*) FROM gf_gift_deliveries WHERE user_number_id = ? AND tipo_entrega = ? AND YEAR(reception_date) = YEAR(CURDATE())");
    if (!$stmt_check_type) {
        respondWithError('Error interno del servidor', 'Failed to prepare type check query: ' . $conn->error);
    }

    $stmt_check_type->bind_param("is", $user_number_id, $tipo_entrega);

    if (!$stmt_check_type->execute()) {
        $stmt_check_type->close();
        respondWithError('Error interno del servidor', 'Failed to execute type check: ' . $stmt_check_type->error);
    }

    $stmt_check_type->bind_result($type_count);
    $stmt_check_type->fetch();
    $stmt_check_type->close();

    if ($type_count > 0) {
        respondWithError("Ya existe una entrega de tipo '$tipo_entrega' para esta persona en el año actual.");
    }

    // Verificar que el usuario existe - verificación adicional
    $stmt_check = $conn->prepare("SELECT updated_by_username FROM gf_users WHERE number_id = ?");
    if (!$stmt_check) {
        respondWithError('Error interno del servidor', 'Failed to prepare user check query: ' . $conn->error);
    }

    $stmt_check->bind_param("i", $user_number_id);

    if (!$stmt_check->execute()) {
        $stmt_check->close();
        respondWithError('Error interno del servidor', 'Failed to execute user check: ' . $stmt_check->error);
    }

    // Usar bind_result para la verificación de usuario
    $stmt_check->bind_result($updated_by_username);

    if (!$stmt_check->fetch()) {
        $stmt_check->close();
        respondWithError('Usuario no encontrado.');
    }
    $stmt_check->close();

    // Manejar firma de recepción: SIEMPRE SE GUARDA NUEVA
    $receptionSignaturePath = '';
    if (!empty($signature)) {
        $signatureData = str_replace('data:image/png;base64,', '', $signature);
        $signatureData = base64_decode($signatureData);
        $date = date('YmdHis');
        $receptionSignatureFileName = "firma_recepcion_{$user_number_id}_{$tipo_entrega}_{$date}.png";

        $upload_dir = "../../img/firmasRegalos/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if (!file_put_contents($upload_dir . $receptionSignatureFileName, $signatureData)) {
            respondWithError('Error al guardar la firma', 'Failed to save signature file');
        }
        $receptionSignaturePath = $receptionSignatureFileName;
    }

    // Variables para autorización
    $authLetterPath = 'N/A';
    $authIdPhotoPath = '';

    if ($use_existing_auth && $authorization_id) {
        // USAR AUTORIZACIÓN EXISTENTE - Solo obtener la carta, NO la foto
        $stmt_auth = $conn->prepare("SELECT authorization_letter FROM gf_authorizations WHERE id = ? AND status = 'active'");
        if (!$stmt_auth) {
            respondWithError('Error interno del servidor', 'Failed to prepare auth query: ' . $conn->error);
        }

        $stmt_auth->bind_param("i", $authorization_id);

        if (!$stmt_auth->execute()) {
            $stmt_auth->close();
            respondWithError('Error interno del servidor', 'Failed to execute auth query: ' . $stmt_auth->error);
        }

        // Usar bind_result para autorización - Solo la carta
        $stmt_auth->bind_result($auth_letter);

        if ($stmt_auth->fetch()) {
            $authLetterPath = $auth_letter;
        } else {
            $stmt_auth->close();
            respondWithError('Autorización no encontrada o inactiva.');
        }
        $stmt_auth->close();

        // PROCESAR NUEVA FOTO DE IDENTIFICACIÓN - SIEMPRE REQUERIDA
        if (!$id_photo || $id_photo['error'] !== UPLOAD_ERR_OK) {
            respondWithError('Error con la foto de identificación.');
        }

        $uploadDir = '../../uploads/idPhotos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $date = date('YmdHis');
        $photoFileName = "id_{$user_number_id}_{$tipo_entrega}_{$date}." . pathinfo($id_photo['name'], PATHINFO_EXTENSION);

        if (!move_uploaded_file($id_photo['tmp_name'], "../../uploads/idPhotos/{$photoFileName}")) {
            respondWithError('Error al subir la foto de identificación.');
        }
        $authIdPhotoPath = $photoFileName;
    } else {
        // PROCESAR NUEVA AUTORIZACIÓN (para casos sin autorización previa)
        if ($recipient_number_id != $user_number_id) {
            // Validar archivos para nueva autorización
            if (!$id_photo || $id_photo['error'] !== UPLOAD_ERR_OK) {
                respondWithError('Error con la foto de identificación.');
            }

            // Manejar carta de autorización
            if ($authorization_letter !== 'N/A' && isset($_FILES['authorization_letter'])) {
                $file = $_FILES['authorization_letter'];
                if ($file['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = '../../uploads/cartasAutorizacion/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $date = date('YmdHis');
                    $originalExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $letterFileName = "carta_{$user_number_id}_{$tipo_entrega}_{$date}.{$originalExtension}";

                    if (!move_uploaded_file($file['tmp_name'], "../../uploads/cartasAutorizacion/{$letterFileName}")) {
                        respondWithError('Error al subir el archivo de autorización.');
                    }
                    $authLetterPath = $letterFileName;
                } else {
                    respondWithError('Error al subir el archivo de autorización.');
                }
            }

            // Manejar foto de identificación
            if ($id_photo && $id_photo['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../../uploads/idPhotos/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $date = date('YmdHis');
                $photoFileName = "id_{$user_number_id}_{$tipo_entrega}_{$date}." . pathinfo($id_photo['name'], PATHINFO_EXTENSION);

                if (!move_uploaded_file($id_photo['tmp_name'], "../../uploads/idPhotos/{$photoFileName}")) {
                    respondWithError('Error al subir la foto de identificación.');
                }
                $authIdPhotoPath = $photoFileName;
            }
        } else {
            // Mismo persona - requiere foto de identificación
            if (!$id_photo || $id_photo['error'] !== UPLOAD_ERR_OK) {
                respondWithError('Error con la foto de identificación.');
            }

            $uploadDir = '../../uploads/idPhotos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $date = date('YmdHis');
            $photoFileName = "id_{$user_number_id}_{$tipo_entrega}_{$date}." . pathinfo($id_photo['name'], PATHINFO_EXTENSION);

            if (!move_uploaded_file($id_photo['tmp_name'], "../../uploads/idPhotos/{$photoFileName}")) {
                respondWithError('Error al subir la foto de identificación.');
            }
            $authIdPhotoPath = $photoFileName;
        }
    }

    // Insertar en DB - SIEMPRE CON LA NUEVA FIRMA DE RECEPCIÓN
    $stmt = $conn->prepare("INSERT INTO gf_gift_deliveries (user_number_id, recipient_number_id, recipient_name, signature, authorization_letter, sede, tipo_entrega, delivered_by, id_photo, reception_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    if (!$stmt) {
        respondWithError('Error interno del servidor', 'Failed to prepare insert query: ' . $conn->error);
    }

    $stmt->bind_param("iisssssss", $user_number_id, $recipient_number_id, $recipient_name, $receptionSignaturePath, $authLetterPath, $sede, $tipo_entrega, $delivered_by, $authIdPhotoPath);

    if ($stmt->execute()) {
        logError("Insert successful for user $user_number_id, tipo: $tipo_entrega");

        $emailSent = false;
        $emailError = '';

        // Obtener el email del usuario registrado
        $emailQuery = $conn->prepare("SELECT email, name FROM gf_users WHERE number_id = ?");
        if ($emailQuery) {
            $emailQuery->bind_param("i", $user_number_id);
            if ($emailQuery->execute()) {
                // Usar bind_result para email query
                $emailQuery->bind_result($userEmail, $userName);

                if ($emailQuery->fetch()) {
                    $emailQuery->close();

                    // Obtener el nombre del asesor
                    $asesorQuery = $conn->prepare("SELECT nombre FROM users WHERE username = ?");
                    $asesorName = '';
                    if ($asesorQuery) {
                        $asesorQuery->bind_param("s", $delivered_by);
                        if ($asesorQuery->execute()) {
                            $asesorQuery->bind_result($asesor_nombre);
                            if ($asesorQuery->fetch()) {
                                $asesorName = $asesor_nombre;
                            }
                        }
                        $asesorQuery->close();
                    }

                    $currentDateTime = date('Y-m-d H:i:s');

                    // Solo intentar enviar correo si hay email válido
                    if (!empty($userEmail) && filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
                        $subject = "🎁 Regalo Entregado - Beneficios Metrofem";
                        $isSamePerson = ($recipient_number_id == $user_number_id);
                        $entregadoA = $isSamePerson ? 'usted mismo' : $recipient_name;

                        $message = "
                        <html>
                        <head>
                            <style>
                                body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
                                .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
                                h1 { color: #333; text-align: center; }
                                p { color: #555; line-height: 1.6; }
                                .footer { margin-top: 20px; font-size: 12px; color: #999; text-align: center; }
                                .gift-icon { font-size: 48px; text-align: center; margin: 20px 0; }
                                .info-box { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0; }
                            </style>
                        </head>
                        <body>
                            <div class='container'>
                                <div class='gift-icon'>🎁</div>
                                <h1>¡Felicidades, {$userName}!</h1>
                                <p>¡Grandes noticias!</p>
                                <p>Queremos confirmarte que ya hemos hecho efectiva la entrega de tu beneficio: {$tipo_entrega} .</p>
                                <p>Dicho Beneficio fue entregado a {$entregadoA} en la sede {$sede}, el día {$currentDateTime} por el(la) asesor(a) {$asesorName}.</p>
                                <p>Este es un pequeño gesto para agradecerte por ser parte fundamental de nuestro fondo de empleados metrofem. Tu esfuerzo y dedicación son los que hacen posible el éxito de nuestro Fondo.</p>
                                <p>Esperamos que este detalle sea de tu agrado.</p>
                                <div style='text-align: center; margin-top: 20px;'>
                                    <img src='https://www.metrofem.com/images/logo/logo-escritorio.png' alt='Logo metrofem' style='width: 200px; height: auto;'>
                                </div>";

                        if (!$isSamePerson) {
                            $authText = $use_existing_auth ? "(Usando autorización previamente registrada)" : "";
                            $message .= "
                                <p>Esta persona ha sido autorizada por usted para recibir el regalo. {$authText}</p>
                                <p>{$recipient_name} con identificación No. {$recipient_number_id}</p>";
                        }

                        $message .= "
                                <p>Esperamos que disfrute su obsequio.</p>
                                <div class='footer'>
                                    <p>Este es un mensaje automático, por favor no responda.</p>
                                    <p>© SYGNIA - Made by <span class='eagle-span'>Eagle Software</span></p>
                                </div>
                            </div>
                        </body>
                        </html>
                        ";

                        // Envío de correo usando PHPMailer con alternancia de SMTP
                        try {
                            // Función para obtener el SMTP disponible
                            function getAvailableSmtp($conn) {
                                $today = date('Y-m-d');
                                $maxEmailsPerDay = 300;

                                // Obtener todos los SMTPs disponibles
                                $smtpListQuery = $conn->prepare("SELECT id FROM smtpconfig ORDER BY id");
                                if (!$smtpListQuery) {
                                    return 1; // Fallback al SMTP ID 1
                                }

                                if (!$smtpListQuery->execute()) {
                                    $smtpListQuery->close();
                                    return 1;
                                }

                                $smtpIds = [];
                                $smtp_id = null; // Inicializar variable
                                $smtpListQuery->bind_result($smtp_id);
                                while ($smtpListQuery->fetch()) {
                                    $smtpIds[] = $smtp_id;
                                }
                                $smtpListQuery->close();

                                // Si no hay SMTPs configurados, usar ID 1
                                if (empty($smtpIds)) {
                                    return 1;
                                }

                                // Contar correos enviados hoy para cada SMTP
                                foreach ($smtpIds as $smtpId) {
                                    $countQuery = $conn->prepare("SELECT COUNT(*) FROM mail_history WHERE smtp_id = ? AND DATE(sent_at) = ? AND status = 'sent'");
                                    if ($countQuery) {
                                        $countQuery->bind_param("is", $smtpId, $today);
                                        if ($countQuery->execute()) {
                                            $count = 0; // Inicializar variable
                                            $countQuery->bind_result($count);
                                            $countQuery->fetch();
                                            $countQuery->close();

                                            // Si este SMTP no ha alcanzado el límite, usarlo
                                            if ($count < $maxEmailsPerDay) {
                                                return $smtpId;
                                            }
                                        } else {
                                            $countQuery->close();
                                        }
                                    }
                                }

                                // Si todos los SMTPs han alcanzado el límite, usar el primero
                                return $smtpIds[0];
                            }

                            // Función para registrar en el historial
                            function registerMailHistory($conn, $smtpId, $email, $subject, $status) {
                                $historyQuery = $conn->prepare("INSERT INTO mail_history (smtp_id, recipient_email, subject, status) VALUES (?, ?, ?, ?)");
                                if ($historyQuery) {
                                    $historyQuery->bind_param("isss", $smtpId, $email, $subject, $status);
                                    $historyQuery->execute();
                                    $historyQuery->close();
                                }
                            }

                            // Obtener el SMTP disponible
                            $selectedSmtpId = getAvailableSmtp($conn);

                            // Obtener configuración del SMTP seleccionado
                            $smtpQuery = $conn->prepare("SELECT host, email, password, port FROM smtpconfig WHERE id = ?");
                            if ($smtpQuery) {
                                $smtpQuery->bind_param("i", $selectedSmtpId);

                                if ($smtpQuery->execute()) {
                                    // Inicializar variables
                                    $smtp_host = '';
                                    $smtp_email = '';
                                    $smtp_password = '';
                                    $smtp_port = 0;
                                    
                                    $smtpQuery->bind_result($smtp_host, $smtp_email, $smtp_password, $smtp_port);

                                    if ($smtpQuery->fetch()) {
                                        $smtpQuery->close();

                                        $mail = new PHPMailer(true);
                                        $mail->SMTPDebug = 0;
                                        $mail->isSMTP();
                                        $mail->Host = $smtp_host;
                                        $mail->SMTPAuth = true;
                                        $mail->Username = $smtp_email;
                                        $mail->Password = $smtp_password;
                                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                                        $mail->Port = $smtp_port;
                                        $mail->Timeout = 60;
                                        $mail->SMTPKeepAlive = true;
                                        $mail->SMTPOptions = [
                                            'ssl' => [
                                                'verify_peer' => false,
                                                'verify_peer_name' => false,
                                                'allow_self_signed' => true
                                            ]
                                        ];

                                        $mail->setFrom($smtp_email, 'Metrofem - Plataforma Beneficios');
                                        $mail->CharSet = 'UTF-8';
                                        $mail->addAddress($userEmail);
                                        $mail->isHTML(true);
                                        $mail->Subject = $subject;
                                        $mail->Body = $message;
                                        $mail->AltBody = strip_tags($message);

                                        $mail->send();
                                        $emailSent = true;

                                        // Registrar envío exitoso en el historial
                                        registerMailHistory($conn, $selectedSmtpId, $userEmail, $subject, 'sent');

                                        logError("Correo enviado exitosamente a $userEmail para $tipo_entrega usando SMTP ID: $selectedSmtpId");
                                    } else {
                                        $emailError = "Configuración SMTP no encontrada para ID: $selectedSmtpId";
                                        logError($emailError);

                                        // Registrar fallo en el historial
                                        registerMailHistory($conn, $selectedSmtpId, $userEmail, $subject, 'failed');

                                        $smtpQuery->close();
                                    }
                                } else {
                                    $emailError = "Error al ejecutar consulta SMTP para ID: $selectedSmtpId";
                                    logError($emailError);

                                    // Registrar fallo en el historial
                                    registerMailHistory($conn, $selectedSmtpId, $userEmail, $subject, 'failed');

                                    $smtpQuery->close();
                                }
                            } else {
                                $emailError = "Error al preparar consulta SMTP para ID: $selectedSmtpId";
                                logError($emailError);

                                // Registrar fallo en el historial
                                registerMailHistory($conn, $selectedSmtpId, $userEmail, $subject, 'failed');
                            }
                        } catch (Exception $e) {
                            $emailError = "Error al enviar correo: " . $e->getMessage();
                            logError($emailError);

                            // Registrar fallo en el historial
                            if (isset($selectedSmtpId)) {
                                registerMailHistory($conn, $selectedSmtpId, $userEmail, $subject, 'failed');
                            }
                        } catch (Error $e) {
                            $emailError = "Error fatal al enviar correo: " . $e->getMessage();
                            logError($emailError);

                            // Registrar fallo en el historial
                            if (isset($selectedSmtpId)) {
                                registerMailHistory($conn, $selectedSmtpId, $userEmail, $subject, 'failed');
                            }
                        } catch (Exception $e) {
                            $emailError = "Error al enviar correo: " . $e->getMessage();
                            logError($emailError);

                            // Registrar fallo en el historial
                            registerMailHistory($conn, $selectedSmtpId ?? 1, $userEmail, $subject, 'failed');
                        } catch (Error $e) {
                            $emailError = "Error fatal al enviar correo: " . $e->getMessage();
                            logError($emailError);

                            // Registrar fallo en el historial
                            registerMailHistory($conn, $selectedSmtpId ?? 1, $userEmail, $subject, 'failed');
                        }
                    } else {
                        $emailError = "Email no válido o vacío: $userEmail";
                        logError($emailError);
                    }
                } else {
                    $emailError = "No se encontraron datos del usuario";
                    logError($emailError);
                    $emailQuery->close();
                }
            } else {
                $emailError = "Error al ejecutar consulta de email";
                logError($emailError);
                $emailQuery->close();
            }
        } else {
            $emailError = "Error al preparar consulta de email";
            logError($emailError);
        }

        ob_clean();
        if ($emailSent) {
            echo json_encode(['success' => true, 'message' => 'Entrega confirmada y correo enviado exitosamente.']);
        } else {
            echo json_encode(['success' => true, 'message' => 'Entrega confirmada correctamente. ' . ($emailError ? 'Error en correo: ' . $emailError : 'No se pudo enviar el correo.')]);
        }
    } else {
        logError("Insert failed: " . $stmt->error);
        $stmt->close();
        respondWithError('Error al confirmar la entrega', 'Insert failed: ' . $stmt->error);
    }
    $stmt->close();
} else {
    respondWithError('Solicitud inválida.');
}

exit();
