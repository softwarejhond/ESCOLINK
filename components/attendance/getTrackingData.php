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

    // Definir las materias
    $courseTypes = ['matematicas', 'espanol', 'ingles', 'ciencias', 'tecnologia'];
    
    $data = [];
    $classes = [];

    foreach ($courseTypes as $courseType) {
        // Todos los estudiantes van en todas las materias
        $data[$courseType] = $students;

        // Obtener fechas de clases registradas para este grado y materia
        $sqlClasses = "SELECT DISTINCT class_date 
                       FROM attendance_records 
                       WHERE grade_level = ? AND course_type = ?
                       ORDER BY class_date ASC";
        
        $stmtClasses = $conn->prepare($sqlClasses);
        if (!$stmtClasses) {
            $classes[$courseType] = [];
            continue;
        }
        
        $stmtClasses->bind_param('ss', $gradeLevel, $courseType);
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
            
            foreach ($students as $student) {
                $sqlAttendance = "SELECT attendance_status 
                                  FROM attendance_records 
                                  WHERE student_id = ? AND grade_level = ? AND course_type = ? AND class_date = ?
                                  LIMIT 1";
                
                $stmtAttendance = $conn->prepare($sqlAttendance);
                if ($stmtAttendance) {
                    $docNumber = (string)$student['document_number'];
                    $stmtAttendance->bind_param('ssss', $docNumber, $gradeLevel, $courseType, $classDate);
                    $stmtAttendance->execute();
                    $resultAttendance = $stmtAttendance->get_result();
                    $rowAttendance = $resultAttendance->fetch_assoc();
                    
                    if ($rowAttendance) {
                        $classDates[$index]['attendance_by_student'][$student['document_number']] = $rowAttendance['attendance_status'];
                    }
                    $stmtAttendance->close();
                }
            }
        }

        $classes[$courseType] = $classDates;
    }

    echo json_encode([
        'success' => true,
        'data' => $data,
        'classes' => $classes,
        'total_students' => count($students)
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>