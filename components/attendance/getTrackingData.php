<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once __DIR__ . '/../../controller/conexion.php';

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$gradeLevel = $_POST['grade_level'] ?? '';

if (empty($gradeLevel)) {
    echo json_encode(['success' => false, 'message' => 'Grado faltante']);
    exit;
}

try {
    // Obtener estudiantes del grado
    $sqlStudents = "SELECT id, document_type, document_number, name, email, cell_phone, grade_level, status
                    FROM el_students 
                    WHERE grade_level = ? 
                    ORDER BY name ASC";
    
    $stmtStudents = $conn->prepare($sqlStudents);
    if (!$stmtStudents) {
        throw new Exception('Error al preparar consulta de estudiantes: ' . $conn->error);
    }
    
    $stmtStudents->bind_param('s', $gradeLevel);
    $stmtStudents->execute();
    $resultStudents = $stmtStudents->get_result();
    
    $students = [];
    while ($row = $resultStudents->fetch_assoc()) {
        $students[] = $row;
    }
    $stmtStudents->close();

    // Obtener fechas de clases registradas para este grado
    $sqlClasses = "SELECT DISTINCT class_date 
                   FROM attendance_records 
                   WHERE grade_level = ?
                   ORDER BY class_date ASC";
    
    $stmtClasses = $conn->prepare($sqlClasses);
    if (!$stmtClasses) {
        throw new Exception('Error al preparar consulta de clases: ' . $conn->error);
    }
    
    $stmtClasses->bind_param('s', $gradeLevel);
    $stmtClasses->execute();
    $resultClasses = $stmtClasses->get_result();
    
    $classDates = [];
    while ($row = $resultClasses->fetch_assoc()) {
        $classDates[] = ['class_date' => $row['class_date']];
    }
    $stmtClasses->close();

    // Para cada fecha de clase, obtener la asistencia de cada estudiante
    foreach ($classDates as $index => $classInfo) {
        $classDate = $classInfo['class_date'];
        $classDates[$index]['attendance_by_student'] = [];
        
        // Obtener asistencia de todos los estudiantes para esta fecha
        $sqlAttendance = "SELECT student_id, attendance_status 
                          FROM attendance_records 
                          WHERE grade_level = ? AND class_date = ?";
        
        $stmtAttendance = $conn->prepare($sqlAttendance);
        if ($stmtAttendance) {
            $stmtAttendance->bind_param('ss', $gradeLevel, $classDate);
            $stmtAttendance->execute();
            $resultAttendance = $stmtAttendance->get_result();
            
            while ($rowAttendance = $resultAttendance->fetch_assoc()) {
                $classDates[$index]['attendance_by_student'][$rowAttendance['student_id']] = $rowAttendance['attendance_status'];
            }
            $stmtAttendance->close();
        }
    }

    echo json_encode([
        'success' => true,
        'data' => $students,
        'classes' => $classDates,
        'total_students' => count($students)
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>