<?php
// Activar error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Incluir la conexión a la DB
include '../../controller/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['number_id'])) {
    $number_id = (int)$_POST['number_id'];

    if ($number_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Número de ID inválido.']);
        exit;
    }

    // Consulta preparada para usuario
    $stmt = $conn->prepare("SELECT * FROM gf_users WHERE number_id = ?");
    $stmt->bind_param("i", $number_id);
    $stmt->execute();
    
    // Alternativa compatible sin mysqlnd
    $meta = $stmt->result_metadata();
    $row = [];
    $params = [];
    
    while ($field = $meta->fetch_field()) {
        $params[] = &$row[$field->name];
    }
    
    call_user_func_array([$stmt, 'bind_result'], $params);
    
    $userData = null;
    if ($stmt->fetch()) {
        $userData = [];
        foreach ($row as $key => $val) {
            $userData[$key] = $val;
        }
    }
    $stmt->close();

    if ($userData) {
        // Calcular el tipo de entrega actual basado en gift_category + gift_gender
        $gift_category = $userData['gift_category'] ?? '';
        $gift_gender = $userData['gift_gender'] ?? '';
        $current_delivery_type = trim($gift_category . ' ' . $gift_gender);
        
        // Verificar entregas de este año
        $stmt_deliveries = $conn->prepare("SELECT d.*, u.nombre as delivered_name FROM gf_gift_deliveries d LEFT JOIN users u ON d.delivered_by = u.username WHERE d.user_number_id = ? AND YEAR(d.reception_date) = YEAR(CURDATE()) ORDER BY d.reception_date DESC");
        $stmt_deliveries->bind_param("i", $number_id);
        $stmt_deliveries->execute();
        
        // Obtener metadata para bind_result
        $meta_del = $stmt_deliveries->result_metadata();
        $delivery_row = [];
        $delivery_params = [];
        
        while ($field = $meta_del->fetch_field()) {
            $delivery_params[] = &$delivery_row[$field->name];
        }
        
        call_user_func_array([$stmt_deliveries, 'bind_result'], $delivery_params);
        
        $deliveries = [];
        $delivered_types = [];
        while ($stmt_deliveries->fetch()) {
            $delivery = [];
            foreach ($delivery_row as $key => $val) {
                $delivery[$key] = $val;
            }
            $deliveries[] = $delivery;
            $delivered_types[] = $delivery['tipo_entrega'];
        }
        $stmt_deliveries->close();

        $has_deliveries = count($deliveries) > 0;

        // Verificar si ya tiene entrega del tipo actual (gift_category + gift_gender)
        $can_deliver_current_type = !in_array($current_delivery_type, $delivered_types);
        
        // Validar que el tipo de entrega no esté vacío
        if (empty($current_delivery_type) || $current_delivery_type === ' ') {
            $can_deliver_current_type = false;
        }

        echo json_encode([
            'success' => true,
            'data' => $userData,
            'has_deliveries' => $has_deliveries,
            'deliveries' => $deliveries,
            'delivered_types' => $delivered_types,
            'can_deliver_current_type' => $can_deliver_current_type,
            'current_delivery_type' => $current_delivery_type
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Solicitud inválida.']);
}
?>