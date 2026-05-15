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

$id            = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$delete_reason = trim($_POST['delete_reason'] ?? '');
$deleted_by    = $_SESSION['username'] ?? 'system';

if (!$id) {
    exit(json_encode(['success' => false, 'message' => 'ID inválido']));
}

// Iniciar transacción
$conn->begin_transaction();

try {
    // 1. Copiar el registro a la tabla de archivo
    $stmtInsert = $conn->prepare(
        "INSERT INTO el_students_archived
            (original_id, student_code, document_type, document_number, name,
             grade_level, gender, email, cell_phone, cell_phone2, address,
             barrio, comuna, city, status, registration_date, sede, simat,
             updated_by, created_at, updated_at, deleted_by, deleted_at, delete_reason)
         SELECT id, student_code, document_type, document_number, name,
                grade_level, gender, email, cell_phone, cell_phone2, address,
                barrio, comuna, city, status, registration_date, sede, simat,
                updated_by, created_at, updated_at, ?, NOW(), ?
         FROM el_students WHERE id = ?"
    );
    $stmtInsert->bind_param('ssi', $deleted_by, $delete_reason, $id);
    $stmtInsert->execute();

    if ($stmtInsert->affected_rows === 0) {
        throw new Exception('No se encontró el estudiante con el ID indicado.');
    }
    $stmtInsert->close();

    // 2. Eliminar de la tabla original
    $stmtDelete = $conn->prepare("DELETE FROM el_students WHERE id = ?");
    $stmtDelete->bind_param('i', $id);
    $stmtDelete->execute();
    $stmtDelete->close();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Estudiante archivado y eliminado correctamente.']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
