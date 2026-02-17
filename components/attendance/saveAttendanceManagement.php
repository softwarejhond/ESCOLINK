<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');
ob_start();

require_once __DIR__ . '/../../controller/conexion.php';

if (!isset($_POST['student_id']) || !isset($_POST['grade_level'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan parámetros necesarios']);
    exit;
}

$studentId = trim($_POST['student_id']);
$gradeLevel = trim($_POST['grade_level']);
$requiresIntervention = isset($_POST['requires_intervention']) ? trim($_POST['requires_intervention']) : null;
$responsibleUsername = isset($_POST['responsible_username']) ? trim($_POST['responsible_username']) : null;
$interventionObservation = isset($_POST['intervention_observation']) ? trim($_POST['intervention_observation']) : null;
$isResolved = isset($_POST['is_resolved']) ? trim($_POST['is_resolved']) : null;
$requiresAdditionalStrategy = isset($_POST['requires_additional_strategy']) ? trim($_POST['requires_additional_strategy']) : null;
$strategyObservation = isset($_POST['strategy_observation']) ? trim($_POST['strategy_observation']) : null;
$strategyFulfilled = isset($_POST['strategy_fulfilled']) ? trim($_POST['strategy_fulfilled']) : null;
$withdrawalReason = isset($_POST['withdrawal_reason']) ? trim($_POST['withdrawal_reason']) : null;
$withdrawalDate = !empty($_POST['withdrawal_date']) ? trim($_POST['withdrawal_date']) : null;

try {
    if (!$conn) {
        throw new Exception("Error de conexión a la base de datos");
    }
    
    $sql = "INSERT INTO student_attendance_management (
                student_id, grade_level, requires_intervention, 
                responsible_username, intervention_observation, is_resolved,
                requires_additional_strategy, strategy_observation, strategy_fulfilled,
                withdrawal_reason, withdrawal_date
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Error en la preparación de la consulta: " . $conn->error);
    }
    
    $stmt->bind_param('sssssssssss',
        $studentId,
        $gradeLevel,
        $requiresIntervention,
        $responsibleUsername,
        $interventionObservation,
        $isResolved,
        $requiresAdditionalStrategy,
        $strategyObservation,
        $strategyFulfilled,
        $withdrawalReason,
        $withdrawalDate
    );
    
    if ($stmt->execute()) {
        ob_clean();
        echo json_encode(['success' => true]);
    } else {
        throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
    }
    
} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Error al guardar datos de gestión: ' . $e->getMessage()
    ]);
}

ob_end_flush();
?>