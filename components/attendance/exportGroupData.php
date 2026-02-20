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

if (empty($gradeLevel)) {
    http_response_code(400);
    exit('Grado no especificado');
}

try {
    // ==========================================
    // Consultar estudiantes desde la base de datos
    // ==========================================
    $sql = "SELECT 
                document_type,
                document_number,
                name,
                email,
                cell_phone,
                cell_phone2,
                address,
                barrio,
                comuna,
                city,
                status,
                registration_date,
                sede,
                simat,
                student_code
            FROM el_students 
            WHERE grade_level = ? 
            ORDER BY name ASC";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Error al preparar la consulta: ' . $conn->error);
    }
    
    $stmt->bind_param('s', $gradeLevel);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Listado ' . substr($gradeLevel, 0, 25));

    if (empty($students)) {
        $sheet->setCellValue('A1', 'No hay estudiantes registrados para el grado ' . $gradeLevel);
        $sheet->getStyle('A1')->getFont()->setBold(true);
    } else {
        // ==========================================
        // Título y información del grado
        // ==========================================
        $sheet->setCellValue('A1', 'LISTADO DE ESTUDIANTES');
        $sheet->setCellValue('A2', 'Grado: ' . $gradeLevel);
        $sheet->setCellValue('A3', 'Total de estudiantes: ' . count($students));
        $sheet->setCellValue('A4', 'Fecha de generación: ' . date('d/m/Y H:i:s'));

        // Estilos para el título
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A2:A4')->getFont()->setBold(true);

        // ==========================================
        // Encabezados de la tabla
        // ==========================================
        $headers = [
            'N°',
            'Código Estudiante',
            'Tipo ID',
            'Documento',
            'Nombre Completo',
            'Correo Electrónico',
            'Teléfono',
            'Teléfono 2',
            'Dirección',
            'Barrio',
            'Comuna',
            'Ciudad',
            'Estado',
            'Fecha de Registro',
            'Sede',
            'SIMAT'
        ];

        // Escribir encabezados en fila 6
        $startRow = 6;
        $col = 1;
        foreach ($headers as $header) {
            $cellRef = Coordinate::stringFromColumnIndex($col) . $startRow;
            $sheet->setCellValue($cellRef, $header);
            $sheet->getStyle($cellRef)->getFont()->setBold(true);
            
            // Color de fondo para encabezados
            $sheet->getStyle($cellRef)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E2E8F0');
            
            $col++;
        }

        // ==========================================
        // Datos de los estudiantes
        // ==========================================
        $row = $startRow + 1;
        $contador = 1;
        
        foreach ($students as $student) {
            $col = 1;
            
            // Número consecutivo
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $contador);
            
            // Código del estudiante
            $studentCode = trim($student['student_code'] ?? '');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $studentCode);
            
            // Datos del estudiante
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $student['document_type'] ?? 'TI');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $student['document_number'] ?? '');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $student['name'] ?? '');
            
            // Email
            $email = trim($student['email'] ?? '');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $email);
            
            // Teléfonos
            $cellPhone = trim($student['cell_phone'] ?? '');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $cellPhone);
            
            $cellPhone2 = trim($student['cell_phone2'] ?? '');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $cellPhone2);
            
            // Dirección
            $address = trim($student['address'] ?? '');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $address);
            
            // Barrio
            $barrio = trim($student['barrio'] ?? '');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $barrio);
            
            // Comuna
            $comuna = trim($student['comuna'] ?? '');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $comuna);
            
            // Ciudad
            $city = trim($student['city'] ?? '');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $city);
            
            // Estado con color
            $statusCell = Coordinate::stringFromColumnIndex($col++) . $row;
            $status = $student['status'] ?? 'Sin estado';
            $sheet->setCellValue($statusCell, $status);
            
            // Aplicar color según el estado
            switch ($status) {
                case 'Activo':
                    $sheet->getStyle($statusCell)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('D1FAE5'); // Verde claro
                    break;
                case 'Inactivo':
                    $sheet->getStyle($statusCell)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('FEE2E2'); // Rojo claro
                    break;
                case 'Retirado':
                    $sheet->getStyle($statusCell)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('E5E7EB'); // Gris claro
                    break;
            }
            
            // Fecha de registro
            $registrationDate = $student['registration_date'] ?? '';
            if (!empty($registrationDate) && $registrationDate !== '0000-00-00') {
                $dateObj = DateTime::createFromFormat('Y-m-d', $registrationDate);
                $formattedDate = $dateObj ? $dateObj->format('d/m/Y') : '';
            } else {
                $formattedDate = '';
            }
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $formattedDate);
            
            // Sede
            $sede = trim($student['sede'] ?? '');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $sede);
            
            // SIMAT
            $simat = trim($student['simat'] ?? '');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $simat);
            
            $row++;
            $contador++;
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

        // Aplicar bordes a la tabla de datos
        $dataRange = 'A' . $startRow . ':' . $highestCol . $highestRow;
        $sheet->getStyle($dataRange)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // Alineación central en encabezados
        $headerRange = 'A' . $startRow . ':' . $highestCol . $startRow;
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // Alineación para datos específicos
        // Centrar columnas numéricas y códigos
        $centerColumns = ['A', 'B', 'C', 'D', 'G', 'H', 'M', 'N', 'P']; // N°, Código, Tipo ID, Documento, Teléfonos, Estado, Fecha, SIMAT
        foreach ($centerColumns as $colLetter) {
            $colRange = $colLetter . ($startRow + 1) . ':' . $colLetter . $highestRow;
            $sheet->getStyle($colRange)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
        }

        // Alineación izquierda para texto largo
        $leftColumns = ['E', 'F', 'I', 'J', 'K', 'L', 'O']; // Nombre, Email, Dirección, Barrio, Comuna, Ciudad, Sede
        foreach ($leftColumns as $colLetter) {
            $colRange = $colLetter . ($startRow + 1) . ':' . $colLetter . $highestRow;
            $sheet->getStyle($colRange)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                ->setVertical(Alignment::VERTICAL_CENTER);
        }

        // Wrap text para todo el rango de datos
        $sheet->getStyle($dataRange)->getAlignment()->setWrapText(true);

        // Fijar filas de encabezado
        $sheet->freezePane('A' . ($startRow + 1));

        // ==========================================
        // Resumen estadístico al final
        // ==========================================
        $summaryStartRow = $highestRow + 3;
        
        $sheet->setCellValue('A' . $summaryStartRow, 'RESUMEN ESTADÍSTICO');
        $sheet->getStyle('A' . $summaryStartRow)->getFont()->setBold(true)->setSize(14);
        
        // Contar por estado
        $statusCount = [];
        foreach ($students as $student) {
            $status = $student['status'] ?? 'Sin estado';
            if (!isset($statusCount[$status])) {
                $statusCount[$status] = 0;
            }
            $statusCount[$status]++;
        }
        
        $summaryRow = $summaryStartRow + 2;
        foreach ($statusCount as $status => $count) {
            $sheet->setCellValue('A' . $summaryRow, $status . ':');
            $sheet->setCellValue('B' . $summaryRow, $count . ' estudiantes');
            $sheet->getStyle('A' . $summaryRow)->getFont()->setBold(true);
            
            // Aplicar color según el estado
            switch ($status) {
                case 'Activo':
                    $sheet->getStyle('B' . $summaryRow)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('D1FAE5');
                    break;
                case 'Inactivo':
                    $sheet->getStyle('B' . $summaryRow)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('FEE2E2');
                    break;
                case 'Retirado':
                    $sheet->getStyle('B' . $summaryRow)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('E5E7EB');
                    break;
            }
            
            $summaryRow++;
        }
    }

    // ==========================================
    // Generar y descargar archivo
    // ==========================================
    $filename = 'Listado_' . str_replace([' ', '°'], ['_', ''], $gradeLevel) . '_' . date('Y-m-d') . '.xlsx';

    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');

} catch (Exception $e) {
    http_response_code(500);
    echo 'Error al generar el archivo: ' . $e->getMessage();
}
?>