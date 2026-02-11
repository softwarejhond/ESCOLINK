<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../../controller/conexion.php'; // Ruta corregida: subir dos niveles desde addUsers/ a la raíz del proyecto, luego a controller/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = intval($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash seguro
    $nombre = $_POST['nombre'];
    $rol = intval($_POST['rol']);
    $rol_informativo = 0; // Valor vacío
    $extra_rol = 0; // Valor vacío
    $foto = '';
    $orden = 1; // Valor por defecto
    $fechaCreacionUser = date('dmYHis'); // Formato ajustado a varchar(15): ddmmyyyyhhmmss
    $email = $_POST['email'];
    $genero = $_POST['genero'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];
    $edad = intval($_POST['edad']);

    // Verificar si el username ya existe
    $checkStmt = $conn->prepare("SELECT username FROM users WHERE username = ?");
    $checkStmt->bind_param("i", $username);
    $checkStmt->execute();
    $checkStmt->store_result();
    if ($checkStmt->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'El usuario con esa cédula ya existe.']);
        $checkStmt->close();
        $conn->close();
        exit;
    }
    $checkStmt->close();

    // Manejar subida de foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $allowed = ['png', 'jpg', 'jpeg'];
        $filename = $_FILES['foto']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $newName = $username . '_' . date('dmY') . '.' . $ext;
            $target = __DIR__ . '/../../img/fotoUsuarios/' . $newName;
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $target)) {
                $foto = $newName;
            }
        }
    }

    // Insertar en users
    $stmt = $conn->prepare("INSERT INTO users (username, password, nombre, rol, rol_informativo, extra_rol, foto, orden, fechaCreacionUser, email, genero, telefono, direccion, edad) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issiiisissssss", $username, $password, $nombre, $rol, $rol_informativo, $extra_rol, $foto, $orden, $fechaCreacionUser, $email, $genero, $telefono, $direccion, $edad); // Corregido: 14 tipos para 14 placeholders
    
    if ($stmt->execute()) {
        if ($rol == 3) { // Asesor
            $sede1 = $_POST['sede1'];
            $sede2 = $_POST['sede2'];
            $creador_username = $_SESSION['username']; // Asumir username en sesión
            $stmt2 = $conn->prepare("INSERT INTO asesores_sedes (username, sede1, sede2, creador_username) VALUES (?, ?, ?, ?)");
            $stmt2->bind_param("issi", $username, $sede1, $sede2, $creador_username);
            $stmt2->execute();
            $stmt2->close();
        }
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al insertar usuario.']);
    }
    $stmt->close();
    $conn->close();
}
?>