<?php
// Activar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/../../controller/conexion.php';

header('Content-Type: application/json');

// Verificar sesión
if (!isset($_SESSION['username'])) {
    echo json_encode(['error' => 'Usuario no autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['error' => 'Datos no válidos']);
    exit;
}

$grade_level = $data['grade_level'] ?? null;
$courseType = $data['courseType'] ?? null;
$class_date = $data['class_date'] ?? null;
$attendance = $data['attendance'] ?? [];

if (empty($grade_level) || empty($courseType) || empty($class_date)) {
    echo json_encode(['error' => 'Faltan datos requeridos']);
    exit;
}

// Verificar que no existan registros para esta fecha, grado y materia
$sqlCheck = "SELECT COUNT(*) as count FROM attendance_records WHERE grade_level = ? AND course_type = ? AND class_date = ?";
$stmtCheck = mysqli_prepare($conn, $sqlCheck);
if (!$stmtCheck) {
    echo json_encode(['error' => 'Error al preparar consulta de verificación: ' . mysqli_error($conn)]);
    exit;
}

mysqli_stmt_bind_param($stmtCheck, "sss", $grade_level, $courseType, $class_date);
if (!mysqli_stmt_execute($stmtCheck)) {
    echo json_encode(['error' => 'Error al ejecutar consulta de verificación: ' . mysqli_stmt_error($stmtCheck)]);
    exit;
}

$resultCheck = mysqli_stmt_get_result($stmtCheck);
$rowCheck = mysqli_fetch_assoc($resultCheck);

if ($rowCheck && $rowCheck['count'] > 0) {
    echo json_encode([
        'error' => 'Ya se ha registrado asistencia para este grado y materia en esta fecha. No es posible registrar asistencia dos veces para la misma fecha.'
    ]);
    exit;
}
mysqli_stmt_close($stmtCheck);

// Obtener todos los estudiantes del grado y marcar como ausentes los que no fueron seleccionados
$sqlStudents = "SELECT document_number FROM el_students WHERE grade_level = ? ORDER BY name ASC";
$stmtStudents = mysqli_prepare($conn, $sqlStudents);
if (!$stmtStudents) {
    echo json_encode(['error' => 'Error al preparar consulta de estudiantes: ' . mysqli_error($conn)]);
    exit;
}

mysqli_stmt_bind_param($stmtStudents, "s", $grade_level);
if (!mysqli_stmt_execute($stmtStudents)) {
    echo json_encode(['error' => 'Error al ejecutar consulta de estudiantes: ' . mysqli_stmt_error($stmtStudents)]);
    exit;
}

$resultStudents = mysqli_stmt_get_result($stmtStudents);
$allStudents = [];
while ($row = mysqli_fetch_assoc($resultStudents)) {
    $studentId = $row['document_number'];
    // Si el estudiante no está en el array de attendance, marcarlo como ausente
    if (!isset($attendance[$studentId])) {
        $attendance[$studentId] = 'ausente';
    }
    $allStudents[] = $studentId;
}
mysqli_stmt_close($stmtStudents);

// Verificar que hay estudiantes en el grado
if (empty($allStudents)) {
    echo json_encode(['error' => 'No se encontraron estudiantes activos en este grado']);
    exit;
}

// Comenzar transacción para asegurar la integridad de datos
mysqli_begin_transaction($conn);

try {
    // Preparar la consulta de inserción (sin columnas sede y recorded_hours)
    $sql = "INSERT INTO attendance_records 
            (teacher_id, student_id, grade_level, course_type, modality, class_date, attendance_status)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        throw new Exception('Error al preparar la consulta: ' . mysqli_error($conn));
    }

    $errors = [];
    $teacher_id = $_SESSION['username']; // Usar el username de la sesión como teacher_id
    
    foreach ($attendance as $student_id => $status) {
        $modality = 'Presencial'; // Valor por defecto, puedes ajustarlo según necesites

        // Insertar registro de asistencia (sin sede y recorded_hours)
        mysqli_stmt_bind_param($stmt, "sssssss", 
            $teacher_id,      // teacher_id
            $student_id,      // student_id 
            $grade_level,     // grade_level
            $courseType,      // course_type
            $modality,        // modality
            $class_date,      // class_date
            $status          // attendance_status
        );
        
        if (!mysqli_stmt_execute($stmt)) {
            $errors[] = "Error al guardar asistencia para estudiante $student_id: " . mysqli_stmt_error($stmt);
        }
    }

    mysqli_stmt_close($stmt);

    // Verificar si hubo errores
    if (!empty($errors)) {
        throw new Exception("Errores durante el guardado: " . implode(", ", $errors));
    }
    
    // Si todo salió bien, confirmar la transacción
    mysqli_commit($conn);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Asistencias guardadas correctamente',
        'records_saved' => count($attendance)
    ]);

} catch (Exception $e) {
    // Si ocurrió algún error, revertir la transacción
    mysqli_rollback($conn);
    echo json_encode(['error' => $e->getMessage()]);
}

mysqli_close($conn);
?>