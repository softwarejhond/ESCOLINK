<?php
session_start();
require_once '../../controller/conexion.php';

/**
 * Función para escribir logs de error personalizados
 */
function writeErrorLog($message, $context = []) {
    $logFile = __DIR__ . '/matriz_error.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
    $logEntry = "[{$timestamp}] {$message}{$contextStr}" . PHP_EOL;
    
    if (!file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX)) {
        error_log("[MATRIZ_ERROR] {$message}{$contextStr}");
    }
}

/**
 * Función para responder con error sin exponer detalles
 */
function respondWithError($userMessage, $technicalError = null, $context = []) {
    if ($technicalError) {
        writeErrorLog($technicalError, $context);
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $userMessage]);
    exit;
}

try {
    // Verificar conexión
    if (!$conn) {
        respondWithError('Error de conexión', 'Database connection not available');
    }

    writeErrorLog('Starting matrix generation');

    // PRIMERO: Obtener todos los usuarios y almacenar en array
    $stmt_users = $conn->prepare("SELECT number_id, name, company_name, cell_phone, email, address, city, registration_date, gender, sede, gift_category, gift_gender, birthdate, update_date FROM gf_users ORDER BY number_id ASC");
    if (!$stmt_users) {
        respondWithError('Error interno del servidor', 'Failed to prepare users query: ' . $conn->error);
    }

    if (!$stmt_users->execute()) {
        $stmt_users->close();
        respondWithError('Error interno del servidor', 'Failed to execute users query: ' . $stmt_users->error);
    }

    // Usar bind_result para obtener todos los usuarios en un array
    $stmt_users->bind_result(
        $user_number_id, $user_name, $user_company_name, $user_cell_phone, 
        $user_email, $user_address, $user_city, $user_registration_date, 
        $user_gender, $user_sede, $user_gift_category, $user_gift_gender, 
        $user_birthdate, $user_update_date
    );

    $users = [];
    while ($stmt_users->fetch()) {
        $users[] = [
            'number_id' => $user_number_id,
            'name' => $user_name,
            'company_name' => $user_company_name,
            'cell_phone' => $user_cell_phone,
            'email' => $user_email,
            'address' => $user_address,
            'city' => $user_city,
            'registration_date' => $user_registration_date,
            'gender' => $user_gender,
            'sede' => $user_sede,
            'gift_category' => $user_gift_category,
            'gift_gender' => $user_gift_gender,
            'birthdate' => $user_birthdate,
            'update_date' => $user_update_date
        ];
    }
    $stmt_users->close();

    if (empty($users)) {
        writeErrorLog('No users found in database');
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => []]);
        exit;
    }

    writeErrorLog('Retrieved users count: ' . count($users));

    // SEGUNDO: Obtener todas las entregas de una vez
    $stmt_all_deliveries = $conn->prepare("SELECT id, user_number_id, recipient_number_id, recipient_name, signature, authorization_letter, sede, tipo_entrega, delivered_by, id_photo, reception_date FROM gf_gift_deliveries ORDER BY user_number_id ASC, reception_date ASC");
    if (!$stmt_all_deliveries) {
        respondWithError('Error interno del servidor', 'Failed to prepare deliveries query: ' . $conn->error);
    }

    if (!$stmt_all_deliveries->execute()) {
        $stmt_all_deliveries->close();
        respondWithError('Error interno del servidor', 'Failed to execute deliveries query: ' . $stmt_all_deliveries->error);
    }

    $stmt_all_deliveries->bind_result(
        $delivery_id, $delivery_user_number_id, $delivery_recipient_number_id, 
        $delivery_recipient_name, $delivery_signature, $delivery_authorization_letter, 
        $delivery_sede, $delivery_tipo_entrega, $delivery_delivered_by, 
        $delivery_id_photo, $delivery_reception_date
    );

    // Organizar entregas por usuario
    $deliveries_by_user = [];
    while ($stmt_all_deliveries->fetch()) {
        $deliveries_by_user[$delivery_user_number_id][] = [
            'id' => $delivery_id,
            'user_number_id' => $delivery_user_number_id,
            'recipient_number_id' => $delivery_recipient_number_id,
            'recipient_name' => $delivery_recipient_name,
            'signature' => $delivery_signature,
            'authorization_letter' => $delivery_authorization_letter,
            'sede' => $delivery_sede,
            'tipo_entrega' => $delivery_tipo_entrega,
            'delivered_by' => $delivery_delivered_by,
            'id_photo' => $delivery_id_photo,
            'reception_date' => $delivery_reception_date
        ];
    }
    $stmt_all_deliveries->close();

    // TERCERO: Obtener todos los nombres de entregadores
    $stmt_all_users_names = $conn->prepare("SELECT username, nombre FROM users");
    if (!$stmt_all_users_names) {
        respondWithError('Error interno del servidor', 'Failed to prepare users names query: ' . $conn->error);
    }

    if (!$stmt_all_users_names->execute()) {
        $stmt_all_users_names->close();
        respondWithError('Error interno del servidor', 'Failed to execute users names query: ' . $stmt_all_users_names->error);
    }

    $stmt_all_users_names->bind_result($username, $nombre);
    $users_names = [];
    while ($stmt_all_users_names->fetch()) {
        $users_names[$username] = $nombre;
    }
    $stmt_all_users_names->close();

    $data = [];
    $dominio = "https://beneficiosmetrofem.agenciaeaglesoftware.com/";

    // PROCESAR CADA USUARIO
    foreach ($users as $user) {
        $user_number_id = $user['number_id'];
        
        // Datos base del usuario
        $baseRow = [
            'Número ID' => $user_number_id,
            'Nombre' => $user['name'] ?? '',
            'Empresa' => $user['company_name'] ?? '',
            'Celular' => $user['cell_phone'] ?? '',
            'Email' => $user['email'] ?? '',
            'Dirección' => $user['address'] ?? '',
            'Ciudad' => $user['city'] ?? '',
            'Fecha Registro' => $user['registration_date'] ?? '',
            'Género' => $user['gender'] ?? '',
            'Fecha Nacimiento' => $user['birthdate'] ?? '',
            'Categoría Regalo' => $user['gift_category'] ?? '',
            'Género Regalo' => $user['gift_gender'] ?? '',
            'Última Actualización' => $user['update_date'] ?? 'No actualizado',
            'Sede Usuario' => $user['sede'] ?? '',
        ];

        // Obtener entregas de este usuario del array organizado
        $user_deliveries = $deliveries_by_user[$user_number_id] ?? [];
        $hasDeliveries = !empty($user_deliveries);
        
        // Procesar cada entrega
        foreach ($user_deliveries as $delivery) {
            $row = $baseRow; // Copiar datos base
            
            $row['Tiene Entrega'] = 'SI';
            $row['Fecha Entrega'] = $delivery['reception_date'] ?? '';
            $row['Año Entrega'] = $delivery['reception_date'] ? date('Y', strtotime($delivery['reception_date'])) : '';
            $row['Receptor ID'] = $delivery['recipient_number_id'] ?? '';
            $row['Receptor Nombre'] = $delivery['recipient_name'] ?? '';
            $row['Sede Entrega'] = $delivery['sede'] ?? '';
            $row['Tipo Entrega'] = $delivery['tipo_entrega'] ?? '';
            $row['Entregado Por'] = $delivery['delivered_by'] ?? '';

            // Obtener nombre entregador del array precargado
            $row['Nombre Entregado Por'] = $users_names[$delivery['delivered_by']] ?? '';

            // URLs
            $row['URL Firma'] = !empty($delivery['signature']) ? $dominio . 'img/firmasRegalos/' . $delivery['signature'] : '';
            $row['URL Foto ID'] = !empty($delivery['id_photo']) ? $dominio . 'uploads/idPhotos/' . $delivery['id_photo'] : '';
            $row['URL Carta Autorización'] = (!empty($delivery['authorization_letter']) && $delivery['authorization_letter'] != 'N/A') ? $dominio . 'uploads/cartasAutorizacion/' . $delivery['authorization_letter'] : 'N/A';

            // Información del receptor - buscar en el array de usuarios precargado
            if ($delivery['recipient_number_id'] != $user_number_id) {
                $recipient_found = false;
                foreach ($users as $potential_recipient) {
                    if ($potential_recipient['number_id'] == $delivery['recipient_number_id']) {
                        $row['Receptor es Usuario'] = 'SI';
                        $row['Empresa Receptor'] = $potential_recipient['company_name'] ?? '';
                        $row['Ciudad Receptor'] = $potential_recipient['city'] ?? '';
                        $recipient_found = true;
                        break;
                    }
                }
                if (!$recipient_found) {
                    $row['Receptor es Usuario'] = 'NO';
                    $row['Empresa Receptor'] = '';
                    $row['Ciudad Receptor'] = '';
                }
            } else {
                $row['Receptor es Usuario'] = 'MISMA PERSONA';
                $row['Empresa Receptor'] = $user['company_name'] ?? '';
                $row['Ciudad Receptor'] = $user['city'] ?? '';
            }

            $data[] = $row;
        }

        // Si no hay entregas, crear una fila con datos vacíos de entrega
        if (!$hasDeliveries) {
            $row = $baseRow; // Copiar datos base
            
            $row['Tiene Entrega'] = 'NO';
            $row['Fecha Entrega'] = '';
            $row['Año Entrega'] = '';
            $row['Receptor ID'] = '';
            $row['Receptor Nombre'] = '';
            $row['Sede Entrega'] = '';
            $row['Tipo Entrega'] = '';
            $row['Entregado Por'] = '';
            $row['Nombre Entregado Por'] = '';
            $row['URL Firma'] = '';
            $row['URL Foto ID'] = '';
            $row['URL Carta Autorización'] = '';
            $row['Receptor es Usuario'] = '';
            $row['Empresa Receptor'] = '';
            $row['Ciudad Receptor'] = '';

            $data[] = $row;
        }
    }

    writeErrorLog('Matrix data generated successfully', ['total_rows' => count($data)]);

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    respondWithError('Error interno del servidor', 'Exception: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine());
} catch (Error $e) {
    respondWithError('Error interno del servidor', 'Fatal Error: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine());
}

if ($conn) {
    mysqli_close($conn);
}
?>