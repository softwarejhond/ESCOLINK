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
$class_date  = trim($_POST['class_date'] ?? '');

if (!$grade_level || !$class_date) {
    exit(json_encode(['exists' => false, 'message' => 'Seleccione un grupo y una fecha.']));
}

$stmt = $conn->prepare(
    "SELECT ar.id, ar.student_id, ar.attendance_status,
            COALESCE(s.document_type, '') AS document_type,
            COALESCE(s.name, ar.student_id) AS student_name,
            COALESCE(s.email, '') AS email
     FROM attendance_records ar
     LEFT JOIN el_students s ON CAST(s.document_number AS CHAR) = ar.student_id
     WHERE ar.grade_level = ? AND ar.class_date = ?
     ORDER BY s.name ASC, ar.student_id ASC"
);
$stmt->bind_param('ss', $grade_level, $class_date);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
$stmt->close();

if (empty($rows)) {
    exit(json_encode([
        'exists'  => false,
        'message' => 'No hay asistencia registrada para este grupo en la fecha seleccionada.'
    ]));
}

$html = '';
foreach ($rows as $row) {
    $sid      = htmlspecialchars($row['student_id'], ENT_QUOTES);
    $presente = $row['attendance_status'] === 'presente' ? 'checked' : '';
    $tarde    = $row['attendance_status'] === 'tarde'    ? 'checked' : '';
    $ausente  = $row['attendance_status'] === 'ausente'  ? 'checked' : '';

    $html .= '<tr>
        <td class="text-center align-middle">' . htmlspecialchars($row['document_type'], ENT_QUOTES) . '</td>
        <td class="text-center align-middle">' . $sid . '</td>
        <td class="align-middle">' . htmlspecialchars($row['student_name'], ENT_QUOTES) . '</td>
        <td class="align-middle">' . htmlspecialchars($row['email'], ENT_QUOTES) . '</td>
        <td class="text-center align-middle">
            <input type="radio" name="att_' . $sid . '" class="form-check-input att-radio"
                   data-student-id="' . $sid . '" value="presente" ' . $presente . '>
        </td>
        <td class="text-center align-middle">
            <input type="radio" name="att_' . $sid . '" class="form-check-input att-radio"
                   data-student-id="' . $sid . '" value="tarde" ' . $tarde . '>
        </td>
        <td class="text-center align-middle">
            <input type="radio" name="att_' . $sid . '" class="form-check-input att-radio"
                   data-student-id="' . $sid . '" value="ausente" ' . $ausente . '>
        </td>
    </tr>';
}

echo json_encode([
    'exists'  => true,
    'html'    => $html,
    'count'   => count($rows)
]);
