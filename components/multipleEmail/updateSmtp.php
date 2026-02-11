<?php
header('Content-Type: application/json');

// Activar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir conexión a BD
include '../../controller/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$id = trim($_POST['id'] ?? '');
$username = trim($_POST['username'] ?? '');
$host = trim($_POST['host'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$port = trim($_POST['port'] ?? '');
$dependence = trim($_POST['dependence'] ?? '');
$subject = trim($_POST['subject'] ?? '');

if (empty($id)) {
    // Si no hay ID, intentar INSERT directamente
    $stmt = $conn->prepare("INSERT INTO smtpconfig (username, host, email, password, port, dependence, Subject) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssiss", $username, $host, $email, $password, $port, $dependence, $subject);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al insertar: ' . $stmt->error]);
    }
} else {
    // Intentar UPDATE primero
    $stmt = $conn->prepare("UPDATE smtpconfig SET username = ?, host = ?, email = ?, password = ?, port = ?, dependence = ?, Subject = ? WHERE id = ?");
    $stmt->bind_param("ssssissi", $username, $host, $email, $password, $port, $dependence, $subject, $id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        // Si no se actualizó nada, hacer INSERT
        $stmt2 = $conn->prepare("INSERT INTO smtpconfig (username, host, email, password, port, dependence, Subject) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt2->bind_param("ssssiss", $username, $host, $email, $password, $port, $dependence, $subject);
        if ($stmt2->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al insertar: ' . $stmt2->error]);
        }
        $stmt2->close();
    }
}

$stmt->close();
$conn->close();
?>