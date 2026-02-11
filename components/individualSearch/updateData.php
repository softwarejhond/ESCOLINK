<?php
// Activar error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión
session_start();

// Incluir la conexión a la DB
include '../../controller/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $number_id = (int)$_POST['number_id'];
    $original_number_id = (int)$_POST['original_number_id'];
    $birthdate = !empty(trim($_POST['birthdate'])) ? $_POST['birthdate'] : null;
    $name = strtoupper(trim($_POST['name']));
    $company_name = strtoupper(trim($_POST['company_name']));
    $cell_phone = preg_replace('/\D/', '', $_POST['cell_phone']); // Solo números
    $email = trim($_POST['email']); // No convertir a mayúsculas para emails
    $address = strtoupper(trim($_POST['address']));
    $city = strtoupper(trim($_POST['city']));
    $registration_date = $_POST['registration_date'];
    $gender = strtoupper(trim($_POST['gender']));
    $sede = strtoupper(trim($_POST['sede']));
    $gift_category = strtoupper(trim($_POST['gift_category']));
    $gift_gender = strtoupper(trim($_POST['gift_gender']));
    
    // Verificar si es edición limitada
    $limited_edit = isset($_POST['limited_edit']) && $_POST['limited_edit'] === 'true';

    // Validar campos obligatorios básicos
    if (empty($original_number_id) || empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Campos obligatorios faltantes (ID y nombre son requeridos).']);
        exit;
    }

    // Validar email si se proporciona
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'El formato del email no es válido.']);
        exit;
    }

    // Validar celular si se proporciona (debe tener 10 dígitos)
    if (!empty($cell_phone) && (strlen($cell_phone) < 10 || strlen($cell_phone) > 10)) {
        echo json_encode(['success' => false, 'message' => 'El celular debe tener exactamente 10 dígitos.']);
        exit;
    }

    // Validar fecha de nacimiento si se proporciona
    if (!empty($birthdate)) {
        $birthdate_obj = DateTime::createFromFormat('Y-m-d', $birthdate);
        if (!$birthdate_obj || $birthdate_obj->format('Y-m-d') !== $birthdate) {
            echo json_encode(['success' => false, 'message' => 'Formato de fecha de nacimiento inválido.']);
            exit;
        }
        
        // Verificar que no sea una fecha futura
        if ($birthdate_obj > new DateTime()) {
            echo json_encode(['success' => false, 'message' => 'La fecha de nacimiento no puede ser una fecha futura.']);
            exit;
        }
    }

    // Validar género de regalo si se proporciona
    if (!empty($gift_gender) && !in_array($gift_gender, ['F', 'M', 'N', 'NA'])) {
        echo json_encode(['success' => false, 'message' => 'Género de regalo inválido. Debe ser F, M, N o NA.']);
        exit;
    }

    try {
        if ($limited_edit) {
            // EDICIÓN LIMITADA - Solo actualizar celular y email para usuarios con entregas
            $stmt = $conn->prepare("UPDATE gf_users SET cell_phone=?, email=?, update_date=CURRENT_TIMESTAMP WHERE number_id=?");
            $stmt->bind_param("ssi", $cell_phone, $email, $original_number_id);
        } else {
            // EDICIÓN COMPLETA - Actualizar todos los campos para usuarios sin entregas
            $stmt = $conn->prepare("UPDATE gf_users SET number_id=?, birthdate=?, name=?, company_name=?, cell_phone=?, email=?, address=?, city=?, registration_date=?, gender=?, sede=?, gift_category=?, gift_gender=?, update_date=CURRENT_TIMESTAMP WHERE number_id=?");
            $stmt->bind_param("issssssssssssi", 
                $number_id, 
                $birthdate, 
                $name, 
                $company_name, 
                $cell_phone, 
                $email, 
                $address, 
                $city, 
                $registration_date, 
                $gender, 
                $sede, 
                $gift_category, 
                $gift_gender, 
                $original_number_id
            );
        }
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode([
                    'success' => true, 
                    'message' => $limited_edit ? 'Datos de contacto actualizados correctamente.' : 'Datos actualizados correctamente.',
                    'updated_fields' => $limited_edit ? ['cell_phone', 'email'] : 'all'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se encontró el registro para actualizar o no hubo cambios.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al ejecutar la actualización: ' . $stmt->error]);
        }
        $stmt->close();

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Método de solicitud inválido.']);
}
?>