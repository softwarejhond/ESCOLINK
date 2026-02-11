<?php
header('Content-Type: application/json');
session_start();

include '../../controller/conexion.php';

/**
 * Función para escribir logs de error personalizados
 */
function writeErrorLog($message, $context = []) {
    $logFile = __DIR__ . '/search_authorization_error.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
    $logEntry = "[{$timestamp}] {$message}{$contextStr}" . PHP_EOL;
    
    if (!file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX)) {
        error_log("[SEARCH_AUTH_ERROR] {$message}{$contextStr}");
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
    $beneficiary_number_id = $_POST['beneficiary_number_id'] ?? '';
    
    if (empty($beneficiary_number_id)) {
        echo json_encode(['success' => false, 'message' => 'ID de beneficiario requerido']);
        exit;
    }

    try {
        // Buscar autorización activa para el beneficiario
        $stmt = $conn->prepare("
            SELECT a.id, a.receiver_number_id, a.receiver_name, a.authorization_letter, 
                   a.id_photo, a.signature, a.created_at,
                   u.name as receiver_full_name, u.company_name as receiver_company 
            FROM gf_authorizations a 
            LEFT JOIN gf_users u ON a.receiver_number_id = u.number_id 
            WHERE a.beneficiary_number_id = ? AND a.status = 'active' 
            ORDER BY a.created_at DESC 
            LIMIT 1
        ");
        
        if (!$stmt) {
            respondWithError('Error interno del servidor', 'Failed to prepare authorization search query: ' . $conn->error, ['beneficiary_id' => $beneficiary_number_id]);
        }
        
        $stmt->bind_param("s", $beneficiary_number_id);
        
        if (!$stmt->execute()) {
            $stmt->close();
            respondWithError('Error interno del servidor', 'Failed to execute authorization search: ' . $stmt->error, ['beneficiary_id' => $beneficiary_number_id]);
        }
        
        // Usar bind_result en lugar de get_result
        $stmt->bind_result(
            $auth_id, 
            $receiver_number_id, 
            $receiver_name, 
            $authorization_letter, 
            $id_photo, 
            $signature, 
            $created_at,
            $receiver_full_name, 
            $receiver_company
        );
        
        if ($stmt->fetch()) {
            // Se encontró una autorización
            $stmt->close();
            
            writeErrorLog('Authorization found successfully', [
                'beneficiary_id' => $beneficiary_number_id,
                'authorization_id' => $auth_id,
                'receiver_id' => $receiver_number_id
            ]);
            
            echo json_encode([
                'success' => true,
                'has_authorization' => true,
                'data' => [
                    'id' => $auth_id,
                    'receiver_number_id' => $receiver_number_id,
                    'receiver_name' => $receiver_name,
                    'receiver_full_name' => $receiver_full_name,
                    'receiver_company' => $receiver_company,
                    'authorization_letter' => $authorization_letter,
                    'id_photo' => $id_photo,
                    'signature' => $signature,
                    'created_at' => $created_at
                ]
            ]);
        } else {
            // No se encontró autorización
            $stmt->close();
            
            writeErrorLog('No authorization found', ['beneficiary_id' => $beneficiary_number_id]);
            
            echo json_encode([
                'success' => true,
                'has_authorization' => false,
                'message' => 'No existe autorización registrada para este beneficiario'
            ]);
        }
        
    } catch (Exception $e) {
        if (isset($stmt)) {
            $stmt->close();
        }
        respondWithError('Error interno del servidor', 'Exception: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine(), ['beneficiary_id' => $beneficiary_number_id]);
    } catch (Error $e) {
        if (isset($stmt)) {
            $stmt->close();
        }
        respondWithError('Error interno del servidor', 'Fatal Error: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine(), ['beneficiary_id' => $beneficiary_number_id]);
    }
} else {
    respondWithError('Método no permitido', 'Invalid request method: ' . $_SERVER['REQUEST_METHOD']);
}
?>