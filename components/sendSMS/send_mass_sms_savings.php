<?php
session_start(); // Iniciar sesión para acceder al username
header('Content-Type: application/json');

// Incluir conexión a la BD
include_once '../../controller/conexion.php'; // Ajusta la ruta si es necesario

// Verificar si se recibió POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener acción
$action = isset($_POST['action']) ? trim($_POST['action']) : '';

if ($action === 'count') {
    // Contar posibles envíos
    $currentYear = date('Y');
    $query = "SELECT su.phone_number, su.name, su.saving FROM saving_users su WHERE NOT EXISTS (
        SELECT 1 FROM sms_logs sl WHERE sl.phone = su.phone_number AND YEAR(sl.sent_at) = ?
    )";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $currentYear);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->num_rows;
    $stmt->close();
    echo json_encode(['success' => true, 'count' => $count]);
    exit;
} elseif ($action === 'send') {
    // Enviar SMS masivos
    $currentYear = date('Y');
    $query = "SELECT su.phone_number, su.name, su.saving FROM saving_users su WHERE NOT EXISTS (
        SELECT 1 FROM sms_logs sl WHERE sl.phone = su.phone_number AND YEAR(sl.sent_at) = ?
    )";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $currentYear);
    $stmt->execute();
    $result = $stmt->get_result();

    $errors = [];
    $sentCount = 0;

    // Obtener credenciales
    $credQuery = "SELECT apiKey, apiSecret FROM sms_credentials LIMIT 1";
    $credResult = $conn->query($credQuery);
    if (!$credResult || $credResult->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'Credenciales no encontradas']);
        exit;
    }
    $credRow = $credResult->fetch_assoc();
    $credentials = [
        'apiKey' => $credRow['apiKey'],
        'apiSecret' => $credRow['apiSecret']
    ];

    while ($row = $result->fetch_assoc()) {
        $phone = $row['phone_number'];
        $name = $row['name'];
        $saving = $row['saving'];

        // Procesar primera palabra del name
        $nameParts = explode(' ', trim($name));
        $firstWord = isset($nameParts[0]) ? $nameParts[0] : '';
        $firstWord = strtoupper($firstWord);
        $firstWord = iconv('UTF-8', 'ASCII//TRANSLIT', $firstWord);
        $firstWord = preg_replace('/[^A-Z]/', '', $firstWord);

        // Construir mensaje
        $message = "Metrofem: $firstWord, tu ahorro navideño fue de $saving. Ingresa al link para indicarnos que quieres hacer con tu dinero https://acortar.link/9E4Sy5";

        // Agregar '57' al teléfono
        
        $sDestination = '57' . $phone;

        try {
            // Enviar SMS usando la función adaptada
            $response = sendAltiriaSMS($sDestination, $message, '', $credentials);

            $json_parsed = json_decode($response);
            if ($json_parsed && isset($json_parsed->status) && $json_parsed->status == '000') {
                // Registrar en logs
                $sender = isset($_SESSION['username']) ? $_SESSION['username'] : 'Desconocido';
                $logStmt = $conn->prepare("INSERT INTO sms_logs (phone, message, sender) VALUES (?, ?, ?)");
                $logStmt->bind_param("sss", $phone, $message, $sender);
                $logStmt->execute();
                $logStmt->close();
                $sentCount++;
            } else {
                $errors[] = "Error en $phone: " . ($json_parsed->status ?? 'Respuesta inválida');
            }
        } catch (Exception $e) {
            $errors[] = "Error en $phone: " . $e->getMessage();
        }
    }

    $stmt->close();
    echo json_encode([
        'success' => true,
        'sent' => $sentCount,
        'errors' => $errors
    ]);
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    exit;
}

// Función AltiriaSMS adaptada
function sendAltiriaSMS($sDestination, $sMessage, $sSenderId, $credentials) {
    $baseUrl = 'https://www.altiria.net:8443/apirest/ws';
    $ch = curl_init($baseUrl.'/sendSms');

    $destinations = array($sDestination);

    $jsonMessage = array(
        'msg' => substr($sMessage, 0, 160),
        'senderId' => $sSenderId 
    );

    $jsonData = array(
        'credentials' => $credentials, 
        'destination' => $destinations,
        'message' => $jsonMessage
    );
     
    $jsonDataEncoded = json_encode($jsonData);
     
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=UTF-8'));

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        throw new Exception(curl_error($ch));
    }

    curl_close($ch);
    return $response;
}
?>