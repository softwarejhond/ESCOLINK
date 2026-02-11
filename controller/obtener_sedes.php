<?php
session_start();
include 'conexion.php';

// Query con JOIN para obtener el nombre del creador desde la tabla users
$query = "SELECT s.id, s.nombre, s.fecha_creacion, u.nombre AS nombre_creador 
          FROM sedes s 
          JOIN users u ON s.creado_por = u.username 
          ORDER BY s.fecha_creacion DESC";
$result = mysqli_query($conn, $query);

$sedes = [];
while ($row = mysqli_fetch_assoc($result)) {
    $sedes[] = $row;
}

echo json_encode($sedes);
mysqli_close($conn);
?>