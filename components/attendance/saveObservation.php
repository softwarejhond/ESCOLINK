<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');
ob_start();

require_once __DIR__ . '/../../controller/conexion.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$studentId = $_POST['student_id'] ?? '';
$gradeLevel = $_POST['grade_level'] ?? '';
$courseType = $_POST['course_type'] ?? '';
$classDate = $_POST['class_date'] ?? '';
$observationType = $_POST['observation_type'] ?? '';
$observationText = $_POST['observation_text'] ?? '';
$createdBy = $_SESSION['username'] ?? 'usuario_desconocido';

if (empty($studentId) || empty($gradeLevel) || empty($courseType) || empty($classDate) || empty($observationType)) {
    echo json_encode(['success' => false, 'message' => 'Parámetros obligatorios faltantes']);
    exit;
}

try {
    if (!$conn) {
        throw new Exception("Error de conexión a la base de datos");
    }
    
    $sql = "INSERT INTO class_observations (student_id, grade_level, course_type, class_date, observation_type, observation_text, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE 
                observation_type = VALUES(observation_type), 
                observation_text = VALUES(observation_text),
                created_by = VALUES(created_by),
                updated_at = CURRENT_TIMESTAMP";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Error en la preparación de consulta: ' . $conn->error);
    }
    
    $stmt->bind_param("sssssss", $studentId, $gradeLevel, $courseType, $classDate, $observationType, $observationText, $createdBy);
    
    if (!$stmt->execute()) {
        throw new Exception('Error al ejecutar consulta: ' . $stmt->error);
    }
    
    $stmt->close();
    
    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Observación guardada correctamente'
    ]);
    
} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

ob_end_flush();
?>