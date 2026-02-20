<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../controller/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Cache-Control: max-age=0');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}

$gradeLevel = $_POST['grade_level'] ?? '';
$data = json_decode($_POST['data'] ?? '[]', true);
$classes = json_decode($_POST['classes'] ?? '[]', true);

if (empty($gradeLevel) || empty($data)) {
    http_response_code(400);
    exit('Datos insuficientes');
}

try {
    $spreadsheet = new Spreadsheet();

    // ==========================================
    // Funciones auxiliares
    // ==========================================

    /**
     * Obtener observaciones de un estudiante por grade_level y fechas de clase
     */
    function getStudentObservations($conn, $studentId, $gradeLevel, $classDates) {
        $observations = [];

        if (empty($classDates)) {
            return $observations;
        }

        $placeholders = str_repeat('?,', count($classDates) - 1) . '?';
        $sql = "SELECT class_date, observation_type, observation_text 
                FROM class_observations 
                WHERE student_id = ? AND grade_level = ? AND class_date IN ($placeholders)
                ORDER BY class_date";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return $observations;
        }

        $params = array_merge([$studentId, $gradeLevel], $classDates);
        $types = 'ss' . str_repeat('s', count($classDates));

        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $observations[$row['class_date']] = [
                'type' => $row['observation_type'],
                'text' => $row['observation_text']
            ];
        }

        $stmt->close();
        return $observations;
    }

    /**
     * Obtener estado de asistencia de un estudiante en una clase específica
     */
    function getAttendanceStatus($conn, $studentId, $gradeLevel, $classDate) {
        $sql = "SELECT attendance_status 
                FROM attendance_records 
                WHERE student_id = ? AND grade_level = ? AND class_date = ?
                LIMIT 1";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('sss', $studentId, $gradeLevel, $classDate);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row ? $row['attendance_status'] : null;
    }

    /**
     * Obtener estadísticas de asistencia de un estudiante
     */
    function getAttendanceStats($conn, $studentId, $gradeLevel) {
        // Total de clases dictadas hasta hoy
        $sqlTotalClasses = "SELECT COUNT(DISTINCT class_date) AS total_classes
                           FROM attendance_records
                           WHERE grade_level = ? AND class_date <= CURRENT_DATE()";

        $stmtClasses = $conn->prepare($sqlTotalClasses);
        $stmtClasses->bind_param('s', $gradeLevel);
        $stmtClasses->execute();
        $resultClasses = $stmtClasses->get_result();
        $rowClasses = $resultClasses->fetch_assoc();
        $totalClasses = $rowClasses['total_classes'];
        $stmtClasses->close();

        // Asistencias (presente o tarde)
        $sqlAttendance = "SELECT COUNT(*) AS total_attendance
                          FROM attendance_records
                          WHERE student_id = ? 
                          AND grade_level = ?
                          AND class_date <= CURRENT_DATE() 
                          AND (attendance_status = 'presente' OR attendance_status = 'tarde')";

        $stmtAttendance = $conn->prepare($sqlAttendance);
        $stmtAttendance->bind_param('ss', $studentId, $gradeLevel);
        $stmtAttendance->execute();
        $resultAttendance = $stmtAttendance->get_result();
        $rowAttendance = $resultAttendance->fetch_assoc();
        $totalAttendance = $rowAttendance['total_attendance'];
        $stmtAttendance->close();

        // Ausencias
        $sqlAbsences = "SELECT COUNT(*) AS total_absences
                        FROM attendance_records
                        WHERE student_id = ? 
                        AND grade_level = ?
                        AND class_date <= CURRENT_DATE() 
                        AND attendance_status = 'ausente'";

        $stmtAbsences = $conn->prepare($sqlAbsences);
        $stmtAbsences->bind_param('ss', $studentId, $gradeLevel);
        $stmtAbsences->execute();
        $resultAbsences = $stmtAbsences->get_result();
        $rowAbsences = $resultAbsences->fetch_assoc();
        $totalAbsences = $rowAbsences['total_absences'];
        $stmtAbsences->close();

        // Calcular porcentajes
        $totalRecords = $totalAttendance + $totalAbsences;

        if ($totalRecords > 0) {
            $attendancePercentage = round(($totalAttendance / $totalRecords) * 100, 1);
            $absencePercentage = round(($totalAbsences / $totalRecords) * 100, 1);
        } else {
            $attendancePercentage = 0;
            $absencePercentage = 0;
        }

        return [
            'totalClasses' => $totalClasses,
            'totalAttendance' => $totalAttendance,
            'totalAbsences' => $totalAbsences,
            'attendancePercentage' => $attendancePercentage,
            'absencePercentage' => $absencePercentage,
            'absencesDisplay' => $totalAbsences . '/' . $totalClasses
        ];
    }

    /**
     * Obtener información de gestión de asistencia con nombre del responsable
     */
    function getAttendanceManagement($conn, $studentId, $gradeLevel) {
        $sql = "SELECT sam.*, u.nombre as responsible_name 
                FROM student_attendance_management sam
                LEFT JOIN users u ON sam.responsible_username = u.username
                WHERE sam.student_id = ? AND sam.grade_level = ?
                ORDER BY sam.updated_at DESC
                LIMIT 1";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('ss', $studentId, $gradeLevel);
        $stmt->execute();
        $result = $stmt->get_result();
        $management = $result->fetch_assoc();
        $stmt->close();

        return $management;
    }

    /**
     * Obtener el color según estado de asistencia
     */
    function getAttendanceColor($attendanceStatus) {
        switch ($attendanceStatus) {
            case 'presente':
                return '9CCC65'; // Verde
            case 'tarde':
                return 'FFD54F'; // Amarillo
            case 'ausente':
                return 'EF5350'; // Rojo
            default:
                return 'CFD8DC'; // Gris
        }
    }

    /**
     * Obtener texto legible del estado de asistencia
     */
    function getAttendanceStatusText($attendanceStatus) {
        switch ($attendanceStatus) {
            case 'presente':
                return 'Presente';
            case 'tarde':
                return 'Llegada tardía';
            case 'ausente':
                return 'Ausente';
            default:
                return 'Sin registro';
        }
    }

    // ==========================================
    // Construir la hoja de cálculo
    // ==========================================

    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Seguimiento ' . substr($gradeLevel, 0, 25));

    $students = $data;

    if (empty($students)) {
        $sheet->setCellValue('A1', 'No hay estudiantes registrados para el grado ' . $gradeLevel);
        $sheet->getStyle('A1')->getFont()->setBold(true);
    } else {
        // ==========================================
        // Construir encabezados
        // ==========================================
        $headers = [
            'Tipo ID',
            'Documento',
            'Nombre',
            'Correo',
            'Teléfono',
            'Estado'
        ];

        // Agregar columnas para cada clase (Asistencia, Tipo Observación, Observación)
        $classDates = [];
        foreach ($classes as $index => $classInfo) {
            $classNumber = $index + 1;
            $classDate = $classInfo['class_date'] ?? '';
            $classDates[] = $classDate;
            $headers[] = "Clase {$classNumber} - Asistencia ({$classDate})";
            $headers[] = "Clase {$classNumber} - Tipo Obs. ({$classDate})";
            $headers[] = "Clase {$classNumber} - Observación ({$classDate})";
        }

        // Agregar columnas de estadísticas de asistencia
        $headers = array_merge($headers, [
            'Inasistencias/Total',
            '% Asistencia',
            '% Inasistencia'
        ]);

        // Agregar columnas de gestión (seguimiento)
        $headers = array_merge($headers, [
            'Requiere Subsanación',
            'Responsable',
            'Observación Subsanación',
            '¿Resuelta?',
            'Requiere Estrategia Adicional',
            'Observación Estrategia',
            '¿Cumple Estrategia?',
            'Motivo de Retiro',
            'Fecha de Retiro'
        ]);

        // ==========================================
        // Escribir encabezados en fila 1
        // ==========================================
        $col = 1;
        foreach ($headers as $header) {
            $cellRef = Coordinate::stringFromColumnIndex($col) . '1';
            $sheet->setCellValue($cellRef, $header);
            $sheet->getStyle($cellRef)->getFont()->setBold(true);

            // Color base para encabezados
            $headerColor = 'E2E8F0';

            // Colores diferenciados según tipo de columna
            if (strpos($header, 'Clase') !== false) {
                $headerColor = 'DBEAFE'; // Azul claro para clases
            } elseif (strpos($header, '% Asistencia') !== false || strpos($header, 'Inasistencias') !== false || strpos($header, '% Inasistencia') !== false) {
                $headerColor = 'D1FAE5'; // Verde claro para estadísticas
            } elseif (
                strpos($header, 'Requiere') !== false ||
                strpos($header, 'Responsable') !== false ||
                strpos($header, 'Observación Subsanación') !== false ||
                strpos($header, 'Resuelta') !== false ||
                strpos($header, 'Estrategia') !== false ||
                strpos($header, 'Cumple') !== false ||
                strpos($header, 'Retiro') !== false
            ) {
                $headerColor = 'FEF3C7'; // Amarillo claro para gestión
            }

            $sheet->getStyle($cellRef)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB($headerColor);

            $col++;
        }

        // ==========================================
        // Escribir datos de estudiantes
        // ==========================================
        $row = 2;
        foreach ($students as $student) {
            $col = 1;
            $studentId = $student['document_number'] ?? '';
            $studentGrade = $student['grade_level'] ?? $gradeLevel;

            // Datos básicos del estudiante
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $student['document_type'] ?? 'N/A');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $studentId);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $student['name'] ?? 'N/A');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $student['email'] ?? 'N/A');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $student['cell_phone'] ?? 'N/A');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $student['status'] ?? 'N/A');

            // Obtener observaciones del estudiante
            $observations = getStudentObservations($conn, $studentId, $studentGrade, $classDates);

            // Datos por cada clase
            foreach ($classDates as $classDate) {
                // Estado de asistencia
                $attendanceStatus = getAttendanceStatus($conn, $studentId, $studentGrade, $classDate);
                $attendanceText = getAttendanceStatusText($attendanceStatus);
                $cellColor = getAttendanceColor($attendanceStatus);

                // Columna: Asistencia
                $attendanceColIdx = $col++;
                $cellRef = Coordinate::stringFromColumnIndex($attendanceColIdx) . $row;
                $sheet->setCellValue($cellRef, $attendanceText);
                $sheet->getStyle($cellRef)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($cellColor);

                // Columna: Tipo de Observación
                $typeColIdx = $col++;
                $cellRef = Coordinate::stringFromColumnIndex($typeColIdx) . $row;
                $obsType = isset($observations[$classDate]) ? ($observations[$classDate]['type'] ?? '') : '';
                $sheet->setCellValue($cellRef, $obsType);
                $sheet->getStyle($cellRef)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($cellColor);

                // Columna: Texto de Observación
                $textColIdx = $col++;
                $cellRef = Coordinate::stringFromColumnIndex($textColIdx) . $row;
                $obsText = isset($observations[$classDate]) ? ($observations[$classDate]['text'] ?? '') : '';
                $sheet->setCellValue($cellRef, $obsText);
                $sheet->getStyle($cellRef)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($cellColor);
            }

            // Estadísticas de asistencia
            $stats = getAttendanceStats($conn, $studentId, $studentGrade);

            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $stats['absencesDisplay']);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $stats['attendancePercentage'] . '%');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $stats['absencePercentage'] . '%');

            // Información de gestión
            $management = getAttendanceManagement($conn, $studentId, $studentGrade);

            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $management['requires_intervention'] ?? '');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $management['responsible_name'] ?? ($management['responsible_username'] ?? ''));
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $management['intervention_observation'] ?? '');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $management['is_resolved'] ?? '');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $management['requires_additional_strategy'] ?? '');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $management['strategy_observation'] ?? '');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $management['strategy_fulfilled'] ?? '');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $management['withdrawal_reason'] ?? '');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $management['withdrawal_date'] ?? '');

            $row++;
        }

        // ==========================================
        // Estilos generales
        // ==========================================

        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();

        // Ajustar ancho de columnas automáticamente
        $highestColIndex = Coordinate::columnIndexFromString($highestCol);
        for ($i = 1; $i <= $highestColIndex; $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }

        // Aplicar bordes a toda la tabla
        $sheet->getStyle('A1:' . $highestCol . $highestRow)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // Alineación central en encabezados
        $sheet->getStyle('A1:' . $highestCol . '1')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // Wrap text en toda la tabla
        $sheet->getStyle('A1:' . $highestCol . $highestRow)->getAlignment()
            ->setWrapText(true);

        // Alineación vertical centrada para datos
        $sheet->getStyle('A2:' . $highestCol . $highestRow)->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER);

        // Fijar fila de encabezados (congelar)
        $sheet->freezePane('A2');
    }

    // ==========================================
    // Generar y descargar archivo
    // ==========================================
    $filename = 'Seguimiento_' . $gradeLevel . '_' . date('Y-m-d') . '.xlsx';

    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');

} catch (Exception $e) {
    http_response_code(500);
    echo 'Error al generar el archivo: ' . $e->getMessage();
}
?>