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

try {
    // ==========================================
    // Consultar todos los estudiantes
    // ==========================================
    $sql = "SELECT 
                student_code,
                document_type,
                document_number,
                name,
                grade_level,
                gender,
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
                updated_at,
                created_at,
                updated_by
            FROM el_students 
            ORDER BY grade_level ASC, name ASC";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception('Error al consultar estudiantes: ' . $conn->error);
    }
    
    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }

    $spreadsheet = new Spreadsheet();
    
    // ==========================================
    // HOJA 1: INFORME COMPLETO
    // ==========================================
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Informe Completo');

    if (empty($students)) {
        $sheet->setCellValue('A1', 'No hay estudiantes registrados en el sistema');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    } else {
        // Título principal
        $sheet->setCellValue('A1', 'INFORME GENERAL DE ESTUDIANTES');
        $sheet->setCellValue('A2', 'SISTEMA ESCOLINK');
        $sheet->setCellValue('A3', 'Total de estudiantes: ' . count($students));
        $sheet->setCellValue('A4', 'Fecha de generación: ' . date('d/m/Y H:i:s'));

        // Estilos para el título
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A3:A4')->getFont()->setBold(true)->setSize(12);

        // ==========================================
        // Encabezados de la tabla principal
        // ==========================================
        $headers = [
            'N°',
            'Código Estudiante', 
            'Tipo ID',
            'Documento',
            'Nombre Completo',
            'Grado/Nivel',
            'Género',
            'Correo Electrónico',
            'Teléfono',
            'Teléfono 2',
            'Dirección',
            'Barrio',
            'Comuna', 
            'Ciudad',
            'Estado',
            'Fecha Registro',
            'Sede',
            'SIMAT',
            'Última Actualización',
            'Actualizado Por'
        ];

        $startRow = 6;
        $col = 1;
        foreach ($headers as $header) {
            $cellRef = Coordinate::stringFromColumnIndex($col) . $startRow;
            $sheet->setCellValue($cellRef, $header);
            $sheet->getStyle($cellRef)->getFont()->setBold(true)->setSize(11);
            
            // Color de fondo para encabezados
            $sheet->getStyle($cellRef)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('2F5496');
            $sheet->getStyle($cellRef)->getFont()->getColor()->setRGB('FFFFFF');
            
            $col++;
        }

        // ==========================================
        // Datos de todos los estudiantes
        // ==========================================
        $row = $startRow + 1;
        $contador = 1;
        
        foreach ($students as $student) {
            $col = 1;
            
            // Número consecutivo
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $contador);
            
            // Código del estudiante
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $student['student_code'] ?? '');
            
            // Tipo de documento
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $student['document_type'] ?? 'TI');
            
            // Número de documento
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $student['document_number'] ?? '');
            
            // Nombre completo
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $student['name'] ?? '');
            
            // Grado/Nivel
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $student['grade_level'] ?? '');
            
            // Género
            $genderText = '';
            switch($student['gender']) {
                case 'M': $genderText = 'Masculino'; break;
                case 'F': $genderText = 'Femenino'; break; 
                case 'OTRO': $genderText = 'Otro'; break;
                default: $genderText = $student['gender'] ?? '';
            }
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $genderText);
            
            // Email
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $student['email'] ?? '');
            
            // Teléfonos
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $student['cell_phone'] ?? '');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $student['cell_phone2'] ?? '');
            
            // Dirección
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $student['address'] ?? '');
            
            // Barrio
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $student['barrio'] ?? '');
            
            // Comuna
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $student['comuna'] ?? '');
            
            // Ciudad
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $student['city'] ?? '');
            
            // Estado con color
            $statusCell = Coordinate::stringFromColumnIndex($col++) . $row;
            $status = $student['status'] ?? 'Sin estado';
            $sheet->setCellValue($statusCell, $status);
            
            // Aplicar color según el estado
            switch ($status) {
                case 'Activo':
                    $sheet->getStyle($statusCell)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('D1FAE5');
                    break;
                case 'Inactivo':
                    $sheet->getStyle($statusCell)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('FEE2E2');
                    break;
                case 'Retirado':
                    $sheet->getStyle($statusCell)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('E5E7EB');
                    break;
            }
            
            // Fecha de registro
            $registrationDate = $student['created_at'] ?? '';
            if (!empty($registrationDate) && $registrationDate !== '0000-00-00') {
                $dateObj = DateTime::createFromFormat('Y-m-d', $registrationDate);
                $formattedDate = $dateObj ? $dateObj->format('d/m/Y') : '';
            } else {
                $formattedDate = '';
            }
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $formattedDate);
            
            // Sede
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $student['sede'] ?? '');
            
            // SIMAT
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $student['simat'] ?? '');
            
            // Última actualización
            $updatedAt = $student['updated_at'] ?? '';
            if (!empty($updatedAt)) {
                $dateObj = DateTime::createFromFormat('Y-m-d H:i:s', $updatedAt);
                $formattedDateTime = $dateObj ? $dateObj->format('d/m/Y H:i') : '';
            } else {
                $formattedDateTime = '';
            }
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $formattedDateTime);
            
            // Actualizado por
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $student['updated_by'] ?? '');
            
            $row++;
            $contador++;
        }

        // ==========================================
        // Estilos generales para la tabla principal
        // ==========================================
        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();

        // Aplicar bordes a toda la tabla
        $dataRange = 'A' . $startRow . ':' . $highestCol . $highestRow;
        $sheet->getStyle($dataRange)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // Alineación de encabezados
        $headerRange = 'A' . $startRow . ':' . $highestCol . $startRow;
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // Ajustar ancho de columnas
        $highestColIndex = Coordinate::columnIndexFromString($highestCol);
        for ($i = 1; $i <= $highestColIndex; $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }

        // Fijar encabezados
        $sheet->freezePane('A' . ($startRow + 1));

        // ==========================================
        // HOJA 2: RESUMEN GENERAL
        // ==========================================
        $summarySheet = $spreadsheet->createSheet();
        $summarySheet->setTitle('Resumen General');
        
        $summarySheet->setCellValue('A1', 'RESUMEN GENERAL DEL SISTEMA');
        $summarySheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        
        // Estadísticas generales
        $totalStudents = count($students);
        $statusCount = [];
        $genderCount = [];
        $sedeCount = [];
        
        foreach ($students as $student) {
            // Por estado
            $status = $student['status'] ?? 'Sin estado';
            $statusCount[$status] = ($statusCount[$status] ?? 0) + 1;
            
            // Por género
            $gender = $student['gender'] ?? 'No especificado';
            $genderCount[$gender] = ($genderCount[$gender] ?? 0) + 1;
            
            // Por sede
            $sede = $student['sede'] ?? 'Sin sede';
            $sedeCount[$sede] = ($sedeCount[$sede] ?? 0) + 1;
        }
        
        $summaryRow = 3;
        $summarySheet->setCellValue('A' . $summaryRow, 'Total de estudiantes:');
        $summarySheet->setCellValue('B' . $summaryRow, $totalStudents);
        $summarySheet->getStyle('A' . $summaryRow)->getFont()->setBold(true);
        
        $summaryRow += 2;
        $summarySheet->setCellValue('A' . $summaryRow, 'POR ESTADO:');
        $summarySheet->getStyle('A' . $summaryRow)->getFont()->setBold(true);
        $summaryRow++;
        
        foreach ($statusCount as $status => $count) {
            $summarySheet->setCellValue('A' . $summaryRow, $status . ':');
            $summarySheet->setCellValue('B' . $summaryRow, $count);
            $summarySheet->setCellValue('C' . $summaryRow, '(' . round(($count / $totalStudents) * 100, 2) . '%)');
            $summaryRow++;
        }
        
        $summaryRow += 1;
        $summarySheet->setCellValue('A' . $summaryRow, 'POR GÉNERO:');
        $summarySheet->getStyle('A' . $summaryRow)->getFont()->setBold(true);
        $summaryRow++;
        
        foreach ($genderCount as $gender => $count) {
            $genderText = '';
            switch($gender) {
                case 'M': $genderText = 'Masculino'; break;
                case 'F': $genderText = 'Femenino'; break;
                case 'OTRO': $genderText = 'Otro'; break;
                default: $genderText = $gender;
            }
            $summarySheet->setCellValue('A' . $summaryRow, $genderText . ':');
            $summarySheet->setCellValue('B' . $summaryRow, $count);
            $summarySheet->setCellValue('C' . $summaryRow, '(' . round(($count / $totalStudents) * 100, 2) . '%)');
            $summaryRow++;
        }

        // Ajustar ancho de columnas en resumen
        foreach (range('A', 'C') as $col) {
            $summarySheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    // ==========================================
    // Generar y descargar archivo
    // ==========================================
    $filename = 'Informe_General_Estudiantes_' . date('Y-m-d_H-i-s') . '.xlsx';
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');

} catch (Exception $e) {
    http_response_code(500);
    echo 'Error al generar el informe: ' . $e->getMessage();
}
?>