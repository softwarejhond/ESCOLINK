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
$nombre = trim($_POST['nombre'] ?? '');
$nit = trim($_POST['nit'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$email = trim($_POST['email'] ?? '');
$ciudad = trim($_POST['ciudad'] ?? '');
$web = trim($_POST['web'] ?? '');

// Manejar logo
$logoPath = '';
if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../../img/logos/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $logoFileName = 'logo_' . time() . '.' . pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
    if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $logoFileName)) {
        $logoPath = $logoFileName;
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al subir el logo']);
        exit;
    }
}

if (empty($id)) {
    // Si no hay ID, intentar INSERT directamente
    $stmt = $conn->prepare("INSERT INTO company (nombre, nit, direccion, telefono, logo, email, ciudad, web) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $nombre, $nit, $direccion, $telefono, $logoPath, $email, $ciudad, $web);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al insertar: ' . $stmt->error]);
    }
} else {
    // Intentar UPDATE primero
    $updateFields = "nombre = ?, nit = ?, direccion = ?, telefono = ?, email = ?, ciudad = ?, web = ?";
    $params = [$nombre, $nit, $direccion, $telefono, $email, $ciudad, $web];
    $types = "sssssss";

    if (!empty($logoPath)) {
        $updateFields .= ", logo = ?";
        $params[] = $logoPath;
        $types .= "s";
    }

    $params[] = $id;
    $types .= "i";

    $stmt = $conn->prepare("UPDATE company SET $updateFields WHERE id = ?");
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0 || $stmt->errno == 0) { // errno 0 significa éxito incluso si no cambió nada
        echo json_encode(['success' => true]);
    } else {
        // Si no se actualizó nada, hacer INSERT
        $stmt2 = $conn->prepare("INSERT INTO company (nombre, nit, direccion, telefono, logo, email, ciudad, web) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt2->bind_param("ssssssss", $nombre, $nit, $direccion, $telefono, $logoPath, $email, $ciudad, $web);
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