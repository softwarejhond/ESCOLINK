<?php
require_once __DIR__ . '/../../controller/conexion.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    exit(json_encode(['error' => 'No autorizado']));
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['error' => 'Método no permitido']));
}

$grade_level = trim($_POST['grade_level'] ?? '');
$class_date  = trim($_POST['class_date']  ?? '');

if (!$grade_level || !$class_date) {
    exit(json_encode(['records' => [], 'message' => 'Datos incompletos']));
}

$stmt = $conn->prepare(
    "SELECT editor_username, student_id, student_name,
            old_status, new_status, edited_at, edit_batch
     FROM attendance_edit_history
     WHERE grade_level = ? AND class_date = ?
     ORDER BY edited_at DESC
     LIMIT 200"
);
$stmt->bind_param('ss', $grade_level, $class_date);
$stmt->execute();
$result = $stmt->get_result();

$records = [];
while ($row = $result->fetch_assoc()) {
    $records[] = $row;
}
$stmt->close();

echo json_encode(['records' => $records]);
