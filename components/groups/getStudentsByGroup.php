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

if ($grade_level === '') {
    exit(json_encode(['students' => []]));
}

$stmt = $conn->prepare(
    "SELECT id, document_type, simat, document_number, student_code, name,
            grade_level, gender, email, cell_phone, cell_phone2,
            address, barrio, comuna, city, sede, status, registration_date
     FROM el_students
     WHERE grade_level = ?
     ORDER BY name ASC"
);
$stmt->bind_param('s', $grade_level);
$stmt->execute();
$result = $stmt->get_result();

$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}
$stmt->close();

echo json_encode(['students' => $students]);
