<?php
// Activar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/../../controller/conexion.php';

// Verificar que se reciba una solicitud POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    // Verificar que el usuario esté en sesión
    if (!isset($_SESSION['username'])) {
        echo json_encode(['error' => 'Usuario no autorizado']);
        exit;
    }

    // Recoger y validar datos
    $grade_level = $_POST['grade_level'] ?? '';
    $class_date  = $_POST['class_date'] ?? '';

    if (empty($grade_level) || empty($class_date)) {
        echo json_encode(['error' => 'Faltan datos requeridos']);
        exit;
    }

    // Verificar si ya existe registro de asistencia para esta fecha y grado
    $sqlCheck = "SELECT COUNT(*) as count FROM attendance_records WHERE grade_level = ? AND class_date = ?";
    $stmtCheck = mysqli_prepare($conn, $sqlCheck);
    if (!$stmtCheck) {
        echo json_encode(['error' => 'Error al preparar consulta de verificación: ' . mysqli_error($conn)]);
        exit;
    }

    mysqli_stmt_bind_param($stmtCheck, "ss", $grade_level, $class_date);
    if (!mysqli_stmt_execute($stmtCheck)) {
        echo json_encode(['error' => 'Error al ejecutar consulta de verificación: ' . mysqli_stmt_error($stmtCheck)]);
        exit;
    }

    $resultCheck = mysqli_stmt_get_result($stmtCheck);
    $rowCheck = mysqli_fetch_assoc($resultCheck);
    
    if ($rowCheck && $rowCheck['count'] > 0) {
        // Ya existe un registro para este grado y fecha
        echo json_encode([
            'exists' => true, 
            'message' => 'Ya se ha registrado asistencia para este grado en esta fecha. No es posible registrar asistencia dos veces para la misma fecha.'
        ]);
        exit;
    }
    mysqli_stmt_close($stmtCheck);

    // Obtener estudiantes de la tabla el_students según el grade_level
    $sql = "SELECT 
                id,
                document_type,
                document_number,
                name,
                email,
                grade_level,
                sede,
                status
            FROM el_students 
            WHERE grade_level = ? 
            ORDER BY name ASC";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        echo json_encode(['error' => 'Error en la preparación: ' . mysqli_error($conn)]);
        exit;
    }

    mysqli_stmt_bind_param($stmt, "s", $grade_level);

    if (!mysqli_stmt_execute($stmt)) {
        echo json_encode(['error' => 'Error en la ejecución: ' . mysqli_stmt_error($stmt)]);
        exit;
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        echo json_encode(['error' => 'Error al obtener resultados: ' . mysqli_error($conn)]);
        exit;
    }

    // Construir el contenido de la tabla
    $tableContent = '';
    while ($row = mysqli_fetch_assoc($result)) {
        $tableContent .= '<tr>
            <td class="text-center align-middle" style="width: 8%">' . htmlspecialchars($row['document_type']) . '</td>
            <td class="text-center align-middle" style="width: auto">' . htmlspecialchars($row['document_number']) . '</td>
            <td class="align-middle text-truncate" style="width: 30%; max-width: 300px">' . htmlspecialchars($row['name']) . '</td>
            <td class="align-middle">' . htmlspecialchars($row['email'] ?? '') . '</td>
            <td class="text-center align-middle">
                <input type="radio" name="attendance_status_' . htmlspecialchars($row['document_number']) . '" 
                       class="form-check-input estado-asistencia" 
                       data-estado="presente" 
                       data-student-id="' . htmlspecialchars($row['document_number']) . '">
            </td>
            <td class="text-center align-middle">
                <input type="radio" name="attendance_status_' . htmlspecialchars($row['document_number']) . '" 
                       class="form-check-input estado-asistencia" 
                       data-estado="tarde" 
                       data-student-id="' . htmlspecialchars($row['document_number']) . '">
            </td>
            <td class="text-center align-middle">
                <input type="radio" name="attendance_status_' . htmlspecialchars($row['document_number']) . '" 
                       class="form-check-input estado-asistencia" 
                       data-estado="ausente" 
                       data-student-id="' . htmlspecialchars($row['document_number']) . '">
            </td>
        </tr>';
    }

    mysqli_stmt_close($stmt);

    if (empty($tableContent)) {
        $tableContent = '<tr><td colspan="7" class="text-center">No se encontraron estudiantes para este grado</td></tr>';
    }

    // Devolver contenido de tabla simplificado
    echo json_encode([
        'html' => $tableContent,
        'grade_level' => $grade_level
    ]);
    exit;
}
?>