<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once __DIR__ . '/../../controller/conexion.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$grade_level = trim($_POST['grade_level'] ?? '');
if (empty($grade_level)) {
    echo json_encode(['success' => false, 'message' => 'Grado requerido']);
    exit;
}

try {
    // 1. Total estudiantes únicos con asistencia registrada
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT student_id) AS total FROM attendance_records WHERE grade_level = ?");
    $stmt->bind_param('s', $grade_level);
    $stmt->execute();
    $totalStudents = (int)$stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    // 2. Distribución global de asistencia (presente / tarde / ausente)
    $stmt = $conn->prepare("SELECT attendance_status, COUNT(*) AS total FROM attendance_records WHERE grade_level = ? GROUP BY attendance_status");
    $stmt->bind_param('s', $grade_level);
    $stmt->execute();
    $result = $stmt->get_result();
    $distribution = ['presente' => 0, 'tarde' => 0, 'ausente' => 0];
    while ($row = $result->fetch_assoc()) {
        if (array_key_exists($row['attendance_status'], $distribution)) {
            $distribution[$row['attendance_status']] = (int)$row['total'];
        }
    }
    $stmt->close();

    $totalRecords  = array_sum($distribution);
    $avgAttendance = $totalRecords > 0
        ? round(($distribution['presente'] + $distribution['tarde']) / $totalRecords * 100, 1)
        : 0;

    // 3. Total de clases únicas registradas para el grado
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT class_date) AS total FROM attendance_records WHERE grade_level = ?");
    $stmt->bind_param('s', $grade_level);
    $stmt->execute();
    $totalClasses = (int)$stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    // 4. Tendencia de asistencia clase por clase (% presente+tarde)
    $stmt = $conn->prepare("
        SELECT
            class_date,
            SUM(attendance_status = 'presente') AS presentes,
            SUM(attendance_status = 'tarde')    AS tardes,
            SUM(attendance_status = 'ausente')  AS ausentes,
            COUNT(*)                            AS total
        FROM attendance_records
        WHERE grade_level = ?
        GROUP BY class_date
        ORDER BY class_date ASC
    ");
    $stmt->bind_param('s', $grade_level);
    $stmt->execute();
    $result = $stmt->get_result();
    $trend = [];
    while ($row = $result->fetch_assoc()) {
        $t   = (int)$row['total'];
        $pct = $t > 0 ? round(((int)$row['presentes'] + (int)$row['tardes']) / $t * 100, 1) : 0;
        $trend[] = [
            'date'           => $row['class_date'],
            'pct_asistencia' => $pct,
            'presentes'      => (int)$row['presentes'],
            'tardes'         => (int)$row['tardes'],
            'ausentes'       => (int)$row['ausentes'],
            'total'          => $t,
        ];
    }
    $stmt->close();

    // 5. Top 10 estudiantes con más ausencias
    $stmt = $conn->prepare("
        SELECT
            ar.student_id,
            COALESCE(s.name, ar.student_id)    AS nombre,
            SUM(ar.attendance_status = 'ausente') AS ausencias
        FROM attendance_records ar
        LEFT JOIN el_students s ON ar.student_id = s.document_number
        WHERE ar.grade_level = ?
        GROUP BY ar.student_id, nombre
        ORDER BY ausencias DESC
        LIMIT 10
    ");
    $stmt->bind_param('s', $grade_level);
    $stmt->execute();
    $result = $stmt->get_result();
    $topAbsences = [];
    while ($row = $result->fetch_assoc()) {
        $topAbsences[] = [
            'student_id' => $row['student_id'],
            'nombre'     => trim($row['nombre']),
            'ausencias'  => (int)$row['ausencias'],
        ];
    }
    $stmt->close();

    // 6. Estado de intervenciones del grado
    $stmt = $conn->prepare("
        SELECT
            SUM(requires_intervention      = 'Si') AS con_intervencion,
            SUM(is_resolved                = 'Si') AS resueltas,
            SUM(requires_additional_strategy = 'Si') AS con_estrategia,
            SUM(strategy_fulfilled         = 'Si') AS estrategia_cumplida
        FROM student_attendance_management
        WHERE grade_level = ?
    ");
    $stmt->bind_param('s', $grade_level);
    $stmt->execute();
    $invRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $interventions = [
        'con_intervencion'    => (int)($invRow['con_intervencion']    ?? 0),
        'resueltas'           => (int)($invRow['resueltas']           ?? 0),
        'con_estrategia'      => (int)($invRow['con_estrategia']      ?? 0),
        'estrategia_cumplida' => (int)($invRow['estrategia_cumplida'] ?? 0),
    ];

    // Intervenciones activas = con intervención que aún no fueron resueltas
    $activeInterventions = max(0, $interventions['con_intervencion'] - $interventions['resueltas']);

    echo json_encode([
        'success' => true,
        'data'    => [
            'total_students'       => $totalStudents,
            'avg_attendance'       => $avgAttendance,
            'total_classes'        => $totalClasses,
            'active_interventions' => $activeInterventions,
            'distribution'         => $distribution,
            'trend'                => $trend,
            'top_absences'         => $topAbsences,
            'interventions'        => $interventions,
        ],
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}
