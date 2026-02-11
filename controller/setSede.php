<?php
session_start();
$success = true;
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['sede'])) {
        $_SESSION['sede'] = trim($_POST['sede']);
    }
    if (isset($_POST['tipo_entrega'])) {
        $tipo_base = trim($_POST['tipo_entrega']);
        $genero_regalo = isset($_POST['genero_regalo']) ? trim($_POST['genero_regalo']) : '';
        
        // Concatenar tipo de entrega con género si se proporciona
        if (!empty($genero_regalo)) {
            $_SESSION['tipo_entrega'] = $tipo_base . ' - ' . $genero_regalo;
            $_SESSION['tipo_entrega_base'] = $tipo_base; // Guardar tipo base por separado
            $_SESSION['genero_regalo'] = $genero_regalo; // Guardar género por separado
        } else {
            $_SESSION['tipo_entrega'] = $tipo_base;
            $_SESSION['tipo_entrega_base'] = $tipo_base;
            $_SESSION['genero_regalo'] = '';
        }
    }
    if (!isset($_POST['sede']) && !isset($_POST['tipo_entrega'])) {
        $success = false;
        $message = 'Error al guardar la sede o tipo de entrega';
    }
} else {
    $success = false;
    $message = 'Método no permitido';
}
echo json_encode(['success' => $success, 'message' => $message]);
?>