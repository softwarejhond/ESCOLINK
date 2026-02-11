<?php
header('Content-Type: application/json');

// Incluir conexión a la base de datos
include '../../controller/conexion.php';

/**
 * Función para escribir logs de error personalizados
 */
function writeErrorLog($message, $context = []) {
    $logFile = __DIR__ . '/search_error.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
    $logEntry = "[{$timestamp}] {$message}{$contextStr}" . PHP_EOL;
    
    // Intentar escribir al archivo de log
    if (!file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX)) {
        // Si no puede escribir al archivo, usar error_log del sistema
        error_log("[SEARCH_USER_ERROR] {$message}{$contextStr}");
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['number_id'])) {
    $number_id = (int)$_POST['number_id'];
    
    if ($number_id <= 0) {
        respondWithError('Número de ID inválido', 'Invalid number_id provided', ['number_id' => $_POST['number_id']]);
    }

    try {
        // Verificar conexión a la base de datos
        if (!isset($conn) || $conn->connect_error) {
            respondWithError('Error de conexión', 'Database connection failed: ' . ($conn->connect_error ?? 'Connection object not found'));
        }

        // Buscar usuario en gf_users - usando bind_result en lugar de get_result
        $stmt = $conn->prepare("SELECT number_id, name, email, company_name, gift_category FROM gf_users WHERE number_id = ?");
        
        if (!$stmt) {
            respondWithError('Error interno del servidor', 'Failed to prepare user query: ' . $conn->error, ['number_id' => $number_id]);
        }

        $stmt->bind_param("i", $number_id);
        
        if (!$stmt->execute()) {
            $stmt->close();
            respondWithError('Error interno del servidor', 'Failed to execute user query: ' . $stmt->error, ['number_id' => $number_id]);
        }

        // Usar bind_result en lugar de get_result
        $stmt->bind_result($user_number_id, $user_name, $user_email, $user_company_name, $user_gift_category);
        
        if (!$stmt->fetch()) {
            $stmt->close();
            writeErrorLog('User not found', ['number_id' => $number_id]);
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
            exit;
        }
        
        // Crear array de usuario con los datos obtenidos
        $user = [
            'number_id' => $user_number_id,
            'name' => $user_name,
            'email' => $user_email,
            'company_name' => $user_company_name,
            'gift_category' => $user_gift_category
        ];
        
        $stmt->close();
        
        // Verificar si ya tiene autorización activa
        $stmt = $conn->prepare("
            SELECT id, receiver_number_id, receiver_name, created_at 
            FROM gf_authorizations 
            WHERE beneficiary_number_id = ? AND status = 'active'
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        
        if (!$stmt) {
            respondWithError('Error interno del servidor', 'Failed to prepare authorization query: ' . $conn->error, ['number_id' => $number_id]);
        }

        $stmt->bind_param("i", $number_id);
        
        if (!$stmt->execute()) {
            $stmt->close();
            respondWithError('Error interno del servidor', 'Failed to execute authorization query: ' . $stmt->error, ['number_id' => $number_id]);
        }

        // Usar bind_result para la consulta de autorización
        $stmt->bind_result($auth_id, $auth_receiver_number_id, $auth_receiver_name, $auth_created_at);
        
        $hasAuthorization = false;
        $authorizationInfo = null;
        
        if ($stmt->fetch()) {
            $hasAuthorization = true;
            $authorizationInfo = [
                'id' => $auth_id,
                'receiver_number_id' => $auth_receiver_number_id,
                'receiver_name' => $auth_receiver_name,
                'created_at' => $auth_created_at
            ];
        }
        $stmt->close();

        // Verificar si esta persona ya está autorizada para reclamar por otra persona
        $stmt = $conn->prepare("
            SELECT id, beneficiary_number_id, beneficiary_name, created_at 
            FROM gf_authorizations 
            WHERE receiver_number_id = ? AND status = 'active'
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        
        if (!$stmt) {
            respondWithError('Error interno del servidor', 'Failed to prepare receiver authorization query: ' . $conn->error, ['number_id' => $number_id]);
        }

        $stmt->bind_param("i", $number_id);
        
        if (!$stmt->execute()) {
            $stmt->close();
            respondWithError('Error interno del servidor', 'Failed to execute receiver authorization query: ' . $stmt->error, ['number_id' => $number_id]);
        }

        // Usar bind_result para la segunda consulta de autorización
        $stmt->bind_result($auth2_id, $auth2_beneficiary_number_id, $auth2_beneficiary_name, $auth2_created_at);
        
        $isAuthorizedForOther = false;
        $authorizedForInfo = null;
        
        if ($stmt->fetch()) {
            $isAuthorizedForOther = true;
            $authorizedForInfo = [
                'id' => $auth2_id,
                'beneficiary_number_id' => $auth2_beneficiary_number_id,
                'beneficiary_name' => $auth2_beneficiary_name,
                'created_at' => $auth2_created_at
            ];
        }
        $stmt->close();
        
        // Log successful operation
        writeErrorLog('Successful user search', ['number_id' => $number_id, 'user_name' => $user['name']]);
        
        echo json_encode([
            'success' => true,
            'user' => $user,
            'hasAuthorization' => $hasAuthorization,
            'authorizationInfo' => $authorizationInfo,
            'isAuthorizedForOther' => $isAuthorizedForOther,
            'authorizedForInfo' => $authorizedForInfo
        ]);
        
    } catch (Exception $e) {
        respondWithError('Error interno del servidor', 'Exception caught: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine(), ['number_id' => $number_id, 'trace' => $e->getTraceAsString()]);
    } catch (Error $e) {
        respondWithError('Error interno del servidor', 'Fatal error caught: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine(), ['number_id' => $number_id, 'trace' => $e->getTraceAsString()]);
    }
} else {
    respondWithError('Solicitud inválida', 'Invalid request method or missing number_id', ['method' => $_SERVER['REQUEST_METHOD'], 'post_data' => $_POST]);
}
?>