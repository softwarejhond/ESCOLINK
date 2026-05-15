<?php
require_once __DIR__ . '/../../controller/conexion.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    exit(json_encode(['success' => false, 'message' => 'No autorizado']));
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Método no permitido']));
}

$grade_level     = trim($_POST['grade_level']  ?? '');
$class_date      = trim($_POST['class_date']   ?? '');
$attendance_json = $_POST['attendance']        ?? '';
$editor_username = $_SESSION['username']       ?? 'system';

if (!$grade_level || !$class_date || !$attendance_json) {
    exit(json_encode(['success' => false, 'message' => 'Datos incompletos']));
}

$attendance = json_decode($attendance_json, true);
if (!is_array($attendance) || empty($attendance)) {
    exit(json_encode(['success' => false, 'message' => 'Datos de asistencia inválidos']));
}

$valid_statuses = ['presente', 'tarde', 'ausente'];

// UUID para agrupar todos los cambios de este guardado
$edit_batch = sprintf(
    '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
    mt_rand(0, 0xffff), mt_rand(0, 0xffff),
    mt_rand(0, 0xffff),
    mt_rand(0, 0x0fff) | 0x4000,
    mt_rand(0, 0x3fff) | 0x8000,
    mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
);

$conn->begin_transaction();

try {
    $stmtGet = $conn->prepare(
        "SELECT ar.id, ar.attendance_status,
                COALESCE(s.name, ar.student_id) AS student_name
         FROM attendance_records ar
         LEFT JOIN el_students s ON CAST(s.document_number AS CHAR) = ar.student_id
         WHERE ar.student_id = ? AND ar.grade_level = ? AND ar.class_date = ?
         LIMIT 1"
    );

    $stmtUpdate = $conn->prepare(
        "UPDATE attendance_records SET attendance_status = ? WHERE id = ?"
    );

    $stmtHistory = $conn->prepare(
        "INSERT INTO attendance_edit_history
            (edit_batch, editor_username, grade_level, class_date, student_id, student_name, old_status, new_status)
         VALUES (?,?,?,?,?,?,?,?)"
    );

    $changedCount = 0;

    foreach ($attendance as $item) {
        $student_id = trim($item['student_id'] ?? '');
        $new_status = trim($item['status']     ?? '');

        if (!$student_id || !in_array($new_status, $valid_statuses, true)) {
            continue;
        }

        $stmtGet->bind_param('sss', $student_id, $grade_level, $class_date);
        $stmtGet->execute();
        $res    = $stmtGet->get_result();
        $record = $res->fetch_assoc();

        if (!$record) {
            continue;
        }

        if ($record['attendance_status'] !== $new_status) {
            // Actualizar registro
            $stmtUpdate->bind_param('si', $new_status, $record['id']);
            $stmtUpdate->execute();

            // Insertar historial
            $old_status   = $record['attendance_status'];
            $student_name = $record['student_name'];
            $stmtHistory->bind_param(
                'ssssssss',
                $edit_batch, $editor_username, $grade_level, $class_date,
                $student_id, $student_name, $old_status, $new_status
            );
            $stmtHistory->execute();
            $changedCount++;
        }
    }

    $stmtGet->close();
    $stmtUpdate->close();
    $stmtHistory->close();

    $conn->commit();

    $msg = $changedCount > 0
        ? "Se actualizaron {$changedCount} registro(s) de asistencia correctamente."
        : "No se detectaron cambios en la asistencia.";

    echo json_encode(['success' => true, 'message' => $msg, 'changes' => $changedCount]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
