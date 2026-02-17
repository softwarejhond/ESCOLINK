<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../controller/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Cache-Control: max-age=0');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}

$studentId = $_POST['student_id'] ?? '';
$studentName = $_POST['student_name'] ?? '';
$gradeLevel = $_POST['grade_level'] ?? '';
$historyData = json_decode($_POST['history_data'] ?? '[]', true);

if (empty($studentId)) {
    http_response_code(400);
    exit('Datos insuficientes');
}

try {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Historial de Gestiones');
    
    // Encabezado
    $sheet->setCellValue('A1', 'HISTORIAL DE GESTIONES');
    $sheet->setCellValue('A2', 'Estudiante: ' . $studentName);
    $sheet->setCellValue('A3', 'Documento: ' . $studentId);
    $sheet->setCellValue('A4', 'Grado: ' . $gradeLevel);
    $sheet->setCellValue('A5', 'Fecha de exportación: ' . date('d/m/Y H:i'));
    
    $sheet->getStyle('A1:A5')->getFont()->setBold(true);
    $sheet->getStyle('A1')->getFont()->setSize(16);
    $sheet->mergeCells('A1:J1');
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    // Encabezados de tabla en la fila 7
    $headers = [
        'Fecha', 'Responsable', 'Requiere Subsanación', 'Observación Subsanación', 
        'Resuelta', 'Requiere Estrategia', 'Observación Estrategia', 
        'Estrategia Cumplida', 'Motivo de Retiro', 'Fecha de Retiro'
    ];
    
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . '7', $header);
        $sheet->getStyle($col . '7')->getFont()->setBold(true);
        $sheet->getStyle($col . '7')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('4BACC6');
        $sheet->getStyle($col . '7')->getFont()->getColor()->setRGB('FFFFFF');
        $col++;
    }
    
    // Llenar datos
    $row = 8;
    foreach ($historyData as $item) {
        $col = 'A';
        $sheet->setCellValue($col++ . $row, $item['formatted_date'] ?? $item['created_at'] ?? '');
        $sheet->setCellValue($col++ . $row, $item['responsible_name'] ?? $item['responsible_username'] ?? 'N/A');
        $sheet->setCellValue($col++ . $row, $item['requires_intervention'] ?? 'N/A');
        $sheet->setCellValue($col++ . $row, $item['intervention_observation'] ?? 'N/A');
        $sheet->setCellValue($col++ . $row, $item['is_resolved'] ?? 'N/A');
        $sheet->setCellValue($col++ . $row, $item['requires_additional_strategy'] ?? 'N/A');
        $sheet->setCellValue($col++ . $row, $item['strategy_observation'] ?? 'N/A');
        $sheet->setCellValue($col++ . $row, $item['strategy_fulfilled'] ?? 'N/A');
        $sheet->setCellValue($col++ . $row, $item['withdrawal_reason'] ?? 'N/A');
        
        $withdrawalDate = !empty($item['withdrawal_date']) ? 
            date('d/m/Y', strtotime($item['withdrawal_date'])) : 'N/A';
        $sheet->setCellValue($col++ . $row, $withdrawalDate);
        
        // Colores condicionales
        if (($item['requires_intervention'] ?? '') == 'Si') {
            $sheet->getStyle('C' . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('FFEB9C');
        }
        if (($item['is_resolved'] ?? '') == 'Si') {
            $sheet->getStyle('E' . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('C6EFCE');
        }
        if (($item['requires_additional_strategy'] ?? '') == 'Si') {
            $sheet->getStyle('F' . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('FFEB9C');
        }
        if (!empty($item['withdrawal_reason']) && $item['withdrawal_reason'] != 'N/A') {
            $sheet->getStyle('I' . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('FFC7CE');
        }
        
        $row++;
    }
    
    if (empty($historyData)) {
        $sheet->setCellValue('A8', 'No hay registros de gestión para este estudiante');
        $sheet->mergeCells('A8:J8');
        $sheet->getStyle('A8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
    
    foreach (range('A', 'J') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    $lastRow = $row - 1;
    if ($lastRow < 8) $lastRow = 8;
    
    $sheet->getStyle('A7:J' . $lastRow)->getBorders()->getAllBorders()
        ->setBorderStyle(Border::BORDER_THIN);
    
    $sheet->getStyle('D7:D' . $lastRow)->getAlignment()->setWrapText(true);
    $sheet->getStyle('G7:G' . $lastRow)->getAlignment()->setWrapText(true);
    
    $sheet->getStyle('A7:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('C7:C' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('E7:E' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('F7:F' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('H7:H' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('J7:J' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    $filename = 'Historial_Gestiones_' . $studentId . '_' . date('Y-m-d') . '.xlsx';
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    
} catch (Exception $e) {
    http_response_code(500);
    echo 'Error al generar el archivo: ' . $e->getMessage();
}
?>