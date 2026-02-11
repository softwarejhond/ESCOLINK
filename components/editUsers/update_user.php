<?php
include '../../controller/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    $id = $_POST['id'];
    $nombre = trim($_POST['nombre']);
    $rol = $_POST['rol'];
    $orden = $_POST['orden']; // Nuevo campo
    $email = trim($_POST['email']);
    $genero = $_POST['genero'];
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);
    $edad = $_POST['edad'];

    // Obtener username del usuario
    $sqlUser = "SELECT username FROM users WHERE id = ?";
    $stmtUser = $conn->prepare($sqlUser);
    $stmtUser->bind_param("i", $id);
    $stmtUser->execute();
    $resultUser = $stmtUser->get_result();
    if ($resultUser->num_rows == 0) {
        throw new Exception("Usuario no encontrado");
    }
    $userRow = $resultUser->fetch_assoc();
    $username = $userRow['username'];
    $stmtUser->close();

    // Iniciar la construcción de la consulta SQL para users
    $sql = "UPDATE users SET nombre = ?, rol = ?, orden = ?, email = ?, genero = ?, telefono = ?, direccion = ?, edad = ?";
    $params = [$nombre, $rol, $orden, $email, $genero, $telefono, $direccion, $edad];
    $types = "siissssi"; // Corregido: 8 tipos para 8 campos iniciales

    // Si se está actualizando la contraseña
    if (isset($_POST['password']) && !empty($_POST['password'])) {
        $password = $_POST['password'];
        $passwordHashed = password_hash($password, PASSWORD_DEFAULT);
        $sql .= ", password = ?";
        $params[] = $passwordHashed;
        $types .= "s";
    }

    // Completar la consulta para users
    $sql .= " WHERE id = ?";
    $params[] = $id;
    $types .= "i";

    // Preparar y ejecutar la consulta para users
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        // Si el rol es Asesor (3), manejar asesores_sedes
        if ($rol == 3) {
            $sede1 = $_POST['sede1'] ?? '';
            $sede2 = $_POST['sede2'] ?? '';
            // Verificar si ya existe un registro
            $sqlCheck = "SELECT id FROM asesores_sedes WHERE username = ?";
            $stmtCheck = $conn->prepare($sqlCheck);
            $stmtCheck->bind_param("i", $username);
            $stmtCheck->execute();
            $resultCheck = $stmtCheck->get_result();
            if ($resultCheck->num_rows > 0) {
                // Actualizar
                $sqlSedes = "UPDATE asesores_sedes SET sede1 = ?, sede2 = ? WHERE username = ?";
                $stmtSedes = $conn->prepare($sqlSedes);
                $stmtSedes->bind_param("ssi", $sede1, $sede2, $username);
            } else {
                // Insertar (asumo creador_username es el username del usuario actual, ajusta si es necesario)
                $creador = $username; // O usa $infoUsuario['username'] si está disponible
                $sqlSedes = "INSERT INTO asesores_sedes (username, sede1, sede2, creador_username) VALUES (?, ?, ?, ?)";
                $stmtSedes = $conn->prepare($sqlSedes);
                $stmtSedes->bind_param("issi", $username, $sede1, $sede2, $creador);
            }
            $stmtSedes->execute();
            $stmtSedes->close();
            $stmtCheck->close();
        }
        echo json_encode(['success' => true, 'message' => 'Usuario actualizado correctamente']);
    } else {
        throw new Exception("Error al actualizar el usuario: " . $stmt->error);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>