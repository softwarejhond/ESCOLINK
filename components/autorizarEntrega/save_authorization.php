<?php
header('Content-Type: application/json');

// Incluir conexión a la base de datos y generador de carta
include '../../controller/conexion.php';
include 'generate_authorization_letter.php';

/**
 * Función para escribir logs de error personalizados
 */
function writeErrorLog($message, $context = []) {
    $logFile = __DIR__ . '/save_authorization_error.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
    $logEntry = "[{$timestamp}] {$message}{$contextStr}" . PHP_EOL;
    
    if (!file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX)) {
        error_log("[SAVE_AUTH_ERROR] {$message}{$contextStr}");
    }
}

/**
 * Función para responder con error sin exponer detalles
 */
function respondWithError($userMessage, $technicalError = null, $context = []) {
    if ($technicalError) {
        writeErrorLog($technicalError, $context);
    }
    echo json_encode(['success' => false, 'message' => $userMessage]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar datos requeridos
        $beneficiary_number_id = $_POST['beneficiary_number_id'] ?? '';
        $beneficiary_name = $_POST['beneficiary_name'] ?? '';
        $beneficiary_gift_category = $_POST['beneficiary_gift_category'] ?? '';
        $receiver_number_id = $_POST['receiver_number_id'] ?? '';
        $receiver_name = $_POST['receiver_name'] ?? '';
        
        if (empty($beneficiary_number_id) || empty($receiver_number_id) || empty($beneficiary_gift_category)) {
            respondWithError('Datos incompletos', 'Missing required fields', [
                'beneficiary_number_id' => $beneficiary_number_id,
                'receiver_number_id' => $receiver_number_id,
                'beneficiary_gift_category' => $beneficiary_gift_category
            ]);
        }

        // Verificación 1: el beneficiario no puede tener múltiples autorizaciones activas
        $stmt = $conn->prepare("
            SELECT id, receiver_number_id, receiver_name, created_at 
            FROM gf_authorizations 
            WHERE beneficiary_number_id = ? AND status = 'active'
        ");
        
        if (!$stmt) {
            respondWithError('Error interno del servidor', 'Failed to prepare beneficiary check query: ' . $conn->error);
        }
        
        $stmt->bind_param("s", $beneficiary_number_id);
        
        if (!$stmt->execute()) {
            $stmt->close();
            respondWithError('Error interno del servidor', 'Failed to execute beneficiary check: ' . $stmt->error);
        }
        
        // Usar bind_result en lugar de get_result
        $stmt->bind_result($existing_id, $existing_receiver_id, $existing_receiver_name, $existing_created_at);
        
        if ($stmt->fetch()) {
            $message = 'Ya existe una autorización activa para este beneficiario. ';
            $message .= 'Persona autorizada: ' . $existing_receiver_name;
            $message .= ' (ID: ' . $existing_receiver_id . ') ';
            $message .= 'desde el ' . date('d/m/Y', strtotime($existing_created_at));
            
            $stmt->close();
            echo json_encode([
                'success' => false, 
                'message' => $message,
                'existing_authorization' => [
                    'id' => $existing_id,
                    'receiver_number_id' => $existing_receiver_id,
                    'receiver_name' => $existing_receiver_name,
                    'created_at' => $existing_created_at
                ]
            ]);
            exit;
        }
        $stmt->close();

        // Verificación 2: el receptor no puede estar autorizado para múltiples beneficiarios
        $stmt = $conn->prepare("
            SELECT id, beneficiary_number_id, beneficiary_name, created_at 
            FROM gf_authorizations 
            WHERE receiver_number_id = ? AND status = 'active'
        ");
        
        if (!$stmt) {
            respondWithError('Error interno del servidor', 'Failed to prepare receiver check query: ' . $conn->error);
        }
        
        $stmt->bind_param("s", $receiver_number_id);
        
        if (!$stmt->execute()) {
            $stmt->close();
            respondWithError('Error interno del servidor', 'Failed to execute receiver check: ' . $stmt->error);
        }
        
        // Usar bind_result para la segunda verificación
        $stmt->bind_result($existing_id2, $existing_beneficiary_id, $existing_beneficiary_name, $existing_created_at2);
        
        if ($stmt->fetch()) {
            $message = 'Esta persona ya está autorizada para reclamar el regalo de otro beneficiario. ';
            $message .= 'Beneficiario actual: ' . $existing_beneficiary_name;
            $message .= ' (ID: ' . $existing_beneficiary_id . ') ';
            $message .= 'desde el ' . date('d/m/Y', strtotime($existing_created_at2)) . '. ';
            $message .= 'Una persona solo puede estar autorizada para reclamar un regalo a la vez.';
            
            $stmt->close();
            echo json_encode([
                'success' => false, 
                'message' => $message,
                'existing_receiver_authorization' => [
                    'id' => $existing_id2,
                    'beneficiary_number_id' => $existing_beneficiary_id,
                    'beneficiary_name' => $existing_beneficiary_name,
                    'created_at' => $existing_created_at2
                ]
            ]);
            exit;
        }
        $stmt->close();

        // Verificación 3: el receptor no puede ser el mismo beneficiario
        if ($beneficiary_number_id === $receiver_number_id) {
            echo json_encode([
                'success' => false, 
                'message' => 'Error: Una persona no puede autorizarse a sí misma para recibir su propio regalo'
            ]);
            exit;
        }

        // Procesar archivos (solo firma ahora)
        $upload_dir = '../../uploads/authorizations/';
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                respondWithError('Error interno del servidor', 'Failed to create upload directory', ['upload_dir' => $upload_dir]);
            }
        }

        // Firma digital
        $signature_name = '';
        $signature_data_url = '';
        if (!empty($_POST['signature'])) {
            $signature_data_url = $_POST['signature'];
            $signature_data = str_replace('data:image/png;base64,', '', $signature_data_url);
            $signature_data = str_replace(' ', '+', $signature_data);
            $signature_name = uniqid() . '_' . time() . '_signature.png';
            
            if (!file_put_contents($upload_dir . $signature_name, base64_decode($signature_data))) {
                respondWithError('Error al guardar la firma', 'Failed to save signature file', ['signature_name' => $signature_name]);
            }
        }

        // Generar carta de autorización automáticamente
        $auth_letter_name = '';
        if (!empty($signature_data_url)) {
            try {
                $auth_letter_name = generateAuthorizationLetter(
                    $beneficiary_name,
                    $beneficiary_number_id,
                    $receiver_name,
                    $receiver_number_id,
                    $beneficiary_gift_category,
                    $signature_data_url
                );
            } catch (Exception $e) {
                respondWithError('Error al generar la carta de autorización', 'Letter generation failed: ' . $e->getMessage());
            }
        }

        // Insertar en base de datos con verificación final (sin id_photo)
        $stmt = $conn->prepare("
            INSERT INTO gf_authorizations 
            (beneficiary_number_id, beneficiary_name, receiver_number_id, receiver_name, 
             authorization_letter, signature, created_at, status) 
            VALUES (?, ?, ?, ?, ?, ?, NOW(), 'active')
        ");
        
        if (!$stmt) {
            respondWithError('Error interno del servidor', 'Failed to prepare insert query: ' . $conn->error);
        }
        
        $stmt->bind_param("ssssss", 
            $beneficiary_number_id, $beneficiary_name, 
            $receiver_number_id, $receiver_name,
            $auth_letter_name, $signature_name
        );

        if ($stmt->execute()) {
            $authorization_id = $stmt->insert_id;
            $stmt->close();
            
            writeErrorLog('Authorization saved successfully', [
                'authorization_id' => $authorization_id,
                'beneficiary_id' => $beneficiary_number_id,
                'receiver_id' => $receiver_number_id
            ]);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Autorización registrada exitosamente. Cada persona solo puede autorizar o estar autorizada para un regalo a la vez.',
                'authorization_id' => $authorization_id,
                'authorization_letter' => $auth_letter_name
            ]);
        } else {
            $stmt->close();
            respondWithError('Error al guardar en base de datos', 'Insert failed: ' . $stmt->error);
        }

    } catch (Exception $e) {
        respondWithError('Error interno del servidor', 'Exception: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine());
    } catch (Error $e) {
        respondWithError('Error interno del servidor', 'Fatal Error: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine());
    }
} else {
    respondWithError('Método no permitido', 'Invalid request method: ' . $_SERVER['REQUEST_METHOD']);
}
?>