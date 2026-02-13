<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../controller/conexion.php';

if (!isset($_POST['student_id']) || !isset($_POST['grade_level']) || !isset($_POST['course_type'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan parámetros necesarios']);
    exit;
}

$studentId = $_POST['student_id'];
$gradeLevel = $_POST['grade_level'];
$courseType = $_POST['course_type'];

try {
    $sql = "SELECT sam.*, u.nombre as responsible_name 
            FROM student_attendance_management sam
            LEFT JOIN users u ON sam.responsible_username = u.username
            WHERE sam.student_id = ? AND sam.grade_level = ? AND sam.course_type = ?
            ORDER BY sam.created_at DESC
            LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $studentId, $gradeLevel, $courseType);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        echo json_encode(['success' => true, 'data' => null]);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener información de gestión: ' . $e->getMessage()
    ]);
}
?>