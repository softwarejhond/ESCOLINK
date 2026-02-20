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
     * Obtener el estado de asistencia codificado
     */
    function getAttendanceCode($attendanceStatus) {
        switch ($attendanceStatus) {
            case 'presente':
                return 1;
            case 'ausente':
                return 0;
            case 'tarde':
                return 2;
            default:
                return '-'; // Sin registro
        }
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

    // ==========================================
    // Construir la hoja de cálculo
    // ==========================================

    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Asistencia ' . substr($gradeLevel, 0, 25));

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

        // Agregar una columna por cada fecha de clase
        $classDates = [];
        foreach ($classes as $index => $classInfo) {
            $classDate = $classInfo['class_date'] ?? '';
            $classDates[] = $classDate;

            // Convertir fecha a formato DD/MM/YYYY
            $dateObj = DateTime::createFromFormat('Y-m-d', $classDate);
            $formattedDate = $dateObj ? $dateObj->format('d/m/Y') : $classDate;
            $headers[] = $formattedDate;
        }

        // ==========================================
        // Escribir encabezados en fila 1
        // ==========================================
        $col = 1;
        foreach ($headers as $header) {
            $cellRef = Coordinate::stringFromColumnIndex($col) . '1';
            $sheet->setCellValue($cellRef, $header);
            $sheet->getStyle($cellRef)->getFont()->setBold(true);

            // Color de fondo para encabezados
            $headerColor = 'E2E8F0';
            if ($col > 6) {
                $headerColor = 'DBEAFE'; // Azul claro para columnas de fechas
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

            // Datos de asistencia por fecha
            foreach ($classDates as $classDate) {
                $attendanceStatus = getAttendanceStatus($conn, $studentId, $studentGrade, $classDate);
                $attendanceCode = getAttendanceCode($attendanceStatus);

                $cellRef = Coordinate::stringFromColumnIndex($col) . $row;
                $sheet->setCellValue($cellRef, $attendanceCode);

                // Centrar el contenido
                $sheet->getStyle($cellRef)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // Aplicar color de fondo según estado
                if ($attendanceStatus !== null) {
                    $bgColor = '';
                    switch ($attendanceStatus) {
                        case 'presente':
                            $bgColor = 'D1FAE5'; // Verde claro
                            break;
                        case 'ausente':
                            $bgColor = 'FEE2E2'; // Rojo claro
                            break;
                        case 'tarde':
                            $bgColor = 'FEF3C7'; // Amarillo claro
                            break;
                    }
                    if ($bgColor) {
                        $sheet->getStyle($cellRef)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB($bgColor);
                    }
                }

                $col++;
            }

            $row++;
        }

        // ==========================================
        // Estilos generales
        // ==========================================
        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();

        // Ajustar ancho de columnas automáticamente para las primeras 6
        for ($i = 1; $i <= 6; $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }

        // Ancho fijo para columnas de fechas
        $highestColIndex = Coordinate::columnIndexFromString($highestCol);
        for ($i = 7; $i <= $highestColIndex; $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setWidth(12);
        }

        // Aplicar bordes a toda la tabla
        $sheet->getStyle('A1:' . $highestCol . $highestRow)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // Alineación central en encabezados
        $sheet->getStyle('A1:' . $highestCol . '1')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // Wrap text en encabezados
        $sheet->getStyle('A1:' . $highestCol . '1')->getAlignment()
            ->setWrapText(true);

        // Alineación vertical centrada para datos
        $sheet->getStyle('A2:' . $highestCol . $highestRow)->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER);

        // Fijar fila de encabezados
        $sheet->freezePane('A2');
    }

    // ==========================================
    // Hoja de leyenda
    // ==========================================
    $legendSheet = $spreadsheet->createSheet();
    $legendSheet->setTitle('Leyenda');

    // Título
    $legendSheet->setCellValue('A1', 'LEYENDA DE CÓDIGOS DE ASISTENCIA');
    $legendSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

    // Encabezados de leyenda
    $legendSheet->setCellValue('A3', 'Código');
    $legendSheet->setCellValue('B3', 'Significado');
    $legendSheet->setCellValue('C3', 'Color');
    $legendSheet->getStyle('A3:C3')->getFont()->setBold(true);
    $legendSheet->getStyle('A3:C3')->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setRGB('E2E8F0');

    // Presente
    $legendSheet->setCellValue('A4', '1');
    $legendSheet->setCellValue('B4', 'Presente');
    $legendSheet->setCellValue('C4', '');
    $legendSheet->getStyle('C4')->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setRGB('D1FAE5');

    // Ausente
    $legendSheet->setCellValue('A5', '0');
    $legendSheet->setCellValue('B5', 'Ausente');
    $legendSheet->setCellValue('C5', '');
    $legendSheet->getStyle('C5')->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setRGB('FEE2E2');

    // Tarde
    $legendSheet->setCellValue('A6', '2');
    $legendSheet->setCellValue('B6', 'Llegada tardía');
    $legendSheet->setCellValue('C6', '');
    $legendSheet->getStyle('C6')->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setRGB('FEF3C7');

    // Sin registro
    $legendSheet->setCellValue('A7', '-');
    $legendSheet->setCellValue('B7', 'Sin registro');
    $legendSheet->setCellValue('C7', '');

    // Bordes a la leyenda
    $legendSheet->getStyle('A3:C7')->getBorders()->getAllBorders()
        ->setBorderStyle(Border::BORDER_THIN);

    // Ajustar ancho de columnas de la leyenda
    $legendSheet->getColumnDimension('A')->setWidth(10);
    $legendSheet->getColumnDimension('B')->setWidth(20);
    $legendSheet->getColumnDimension('C')->setWidth(10);

    // Centrar contenido de la leyenda
    $legendSheet->getStyle('A3:C7')->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
        ->setVertical(Alignment::VERTICAL_CENTER);

    // ==========================================
    // Generar y descargar archivo
    // ==========================================
    $filename = 'Asistencia_' . $gradeLevel . '_' . date('Y-m-d') . '.xlsx';

    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');

} catch (Exception $e) {
    http_response_code(500);
    echo 'Error al generar el archivo: ' . $e->getMessage();
}
?>