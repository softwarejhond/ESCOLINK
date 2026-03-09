<?php
require_once 'conexion.php'; // Ruta corregida: subir dos niveles desde addUsers/ a la raíz del proyecto, luego a controller/

$usuarios = [
    ['1128452538', 'Lina Marcela Marín Sarria', 'Administrador', 'F', 'gerencia@poliandino.edu.co', '3107372281'],
    ['32503836', 'Ana Lucia de Chiquinquira Giraldo Gallego', 'Administrador', 'F', 'coordinacioncencal@poliandino.edu.co', '3128871092'],
    ['42784607', 'Ruby Elizabeth Londoño Florez', 'Administrador', 'F', 'colegiocencal@poliandino.edu.co', '3122796849'],
    ['1035875996', 'Jessica Lorena Muñoz Zapata', 'Administrador', 'F', 'asesoriaeducativa2@poliandino.edu.co', '3044653201'],
    ['1214727469', 'Yesenia Valencia Parra', 'Administrador', 'F', 'asesoriaeducativa6@poliandino.edu.co', '3003538717'],
    ['1128277711', 'Marcela Moreno Cadavid', 'Administrador', 'F', 'auxiliarproyectos@poliandino.edu.co', '3193634777'],
    ['1035850248', 'Edwin Alberto Echeverri Cordoba', 'Administrador', 'M', 'liderproyectost1@poliandino.edu.co', '3245494657'],
];

foreach ($usuarios as $u) {
    $username = intval($u[0]);
    $password = password_hash((string)$username, PASSWORD_DEFAULT);
    $nombre = $u[1];
    $rol = 1;
    $rol_informativo = 0;
    $extra_rol = 0;
    $foto = '';
    $orden = 1;
    $fechaCreacionUser = date('dmYHis');
    $email = $u[4];
    $genero = $u[3];
    $telefono = $u[5];
    $direccion = '';
    $edad = 0;

    // Verifica si ya existe
    $checkStmt = $conn->prepare("SELECT username FROM users WHERE username = ?");
    $checkStmt->bind_param("i", $username);
    $checkStmt->execute();
    $checkStmt->store_result();
    if ($checkStmt->num_rows > 0) {
        echo "El usuario $username ya existe.<br>";
        $checkStmt->close();
        continue;
    }
    $checkStmt->close();

    $stmt = $conn->prepare("INSERT INTO users (username, password, nombre, rol, rol_informativo, extra_rol, foto, orden, fechaCreacionUser, email, genero, telefono, direccion, edad) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issiiisissssss", $username, $password, $nombre, $rol, $rol_informativo, $extra_rol, $foto, $orden, $fechaCreacionUser, $email, $genero, $telefono, $direccion, $edad);

    if ($stmt->execute()) {
        echo "Usuario $username insertado correctamente.<br>";
    } else {
        echo "Error al insertar $username: " . $stmt->error . "<br>";
    }
    $stmt->close();
}

$conn->close();
?>