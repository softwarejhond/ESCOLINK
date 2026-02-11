<?php
// Control de errores para prevenir salida inesperada
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Iniciar sesión
session_start();

// Corregir ruta del autoload
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../controller/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

// Configurar zona horaria Bogotá
date_default_timezone_set('America/Bogota');

// Verificar permisos
$rol = $_SESSION['rol'] ?? '';
if (!in_array($rol, [1, 12])) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No tienes permisos para exportar.']);
    exit;
}

try {
    // Crear nueva instancia de Spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Matriz Completa Regalos');

    // Títulos de las columnas
    $titulos = [
        'Número ID', 'Nombre', 'Empresa', 'Celular', 'Email', 'Dirección', 
        'Ciudad', 'Fecha Registro', 'Género', 'Datos Actualizados', 'Actualizado Por',
        'Sede Usuario', 'Tiene Entrega', 'Fecha Entrega', 'Receptor ID', 'Receptor Nombre',
        'Sede Entrega', 'Tipo Entrega', 'Entregado Por', 'Nombre Entregado Por',
        'URL Firma', 'URL Foto ID', 'URL Carta Autorización', 'Receptor es Usuario',
        'Empresa Receptor', 'Ciudad Receptor'
    ];

    // Agregar títulos
    foreach ($titulos as $col => $titulo) {
        $sheet->setCellValue(chr(65 + $col) . '1', $titulo);
    }

    // Estilo para títulos
    $lastColumn = count($titulos);
    $headerRange = 'A1:' . chr(64 + $lastColumn) . '1';
    
    $sheet->getStyle($headerRange)->applyFromArray([
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '4A90E2'],
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
            ],
        ],
    ]);

    // Consulta principal para obtener todos los usuarios
    $sql = "SELECT * FROM gf_users ORDER BY number_id ASC";
    $resultado = mysqli_query($conn, $sql);

    if (!$resultado) {
        throw new Exception('Error en la consulta: ' . mysqli_error($conn));
    }

    $row = 2; // Empezar desde la fila 2 (después de los títulos)
    $dominio = "https://beneficiosmetrofem.agenciaeaglesoftware.com/";
    $userCount = 0;

    while ($user = mysqli_fetch_assoc($resultado)) {
        $userCount++;
        // Datos básicos del usuario
        $sheet->setCellValue('A' . $row, $user['number_id']);
        $sheet->setCellValue('B' . $row, $user['name'] ?? '');
        $sheet->setCellValue('C' . $row, $user['company_name'] ?? '');
        $sheet->setCellValue('D' . $row, $user['cell_phone'] ?? '');
        $sheet->setCellValue('E' . $row, $user['email'] ?? '');
        $sheet->setCellValue('F' . $row, $user['address'] ?? '');
        $sheet->setCellValue('G' . $row, $user['city'] ?? '');
        $sheet->setCellValue('H' . $row, $user['registration_date'] ?? '');
        $sheet->setCellValue('I' . $row, $user['gender'] ?? '');
        $sheet->setCellValue('J' . $row, $user['data_update'] ?: 'NO');
        $sheet->setCellValue('K' . $row, $user['updated_by'] ?? '');
        $sheet->setCellValue('L' . $row, $user['sede'] ?? '');

        // Verificar si tiene entrega este año
        $stmt_delivery = $conn->prepare("SELECT * FROM gf_gift_deliveries WHERE user_number_id = ? AND YEAR(reception_date) = YEAR(CURDATE()) LIMIT 1");
        if (!$stmt_delivery) {
            throw new Exception('Error preparando consulta de entrega: ' . $conn->error);
        }
        
        $stmt_delivery->bind_param("i", $user['number_id']);
        $stmt_delivery->execute();
        $result_delivery = $stmt_delivery->get_result();

        if ($result_delivery->num_rows > 0) {
            $delivery = $result_delivery->fetch_assoc();
            $sheet->setCellValue('M' . $row, 'SI');
            $sheet->setCellValue('N' . $row, $delivery['reception_date'] ?? '');
            $sheet->setCellValue('O' . $row, $delivery['recipient_number_id'] ?? '');
            $sheet->setCellValue('P' . $row, $delivery['recipient_name'] ?? '');
            $sheet->setCellValue('Q' . $row, $delivery['sede'] ?? '');
            $sheet->setCellValue('R' . $row, $delivery['tipo_entrega'] ?? '');
            $sheet->setCellValue('S' . $row, $delivery['delivered_by'] ?? '');

            // Obtener nombre del entregador
            $delivered_name = '';
            $stmt_delivered = $conn->prepare("SELECT nombre FROM users WHERE username = ?");
            if ($stmt_delivered && $delivery['delivered_by']) {
                $stmt_delivered->bind_param("s", $delivery['delivered_by']);
                $stmt_delivered->execute();
                $stmt_delivered->bind_result($delivered_name);
                $stmt_delivered->fetch();
                $stmt_delivered->close();
            }
            $sheet->setCellValue('T' . $row, $delivered_name ?? '');

            // URLs de archivos
            $sheet->setCellValue('U' . $row, $delivery['signature'] ? $dominio . 'img/firmasRegalos/' . $delivery['signature'] : '');
            $sheet->setCellValue('V' . $row, $delivery['id_photo'] ? $dominio . 'uploads/idPhotos/' . $delivery['id_photo'] : '');
            $sheet->setCellValue('W' . $row, ($delivery['authorization_letter'] && $delivery['authorization_letter'] != 'N/A') ? $dominio . 'uploads/cartasAutorizacion/' . $delivery['authorization_letter'] : 'N/A');

            // Verificar si el receptor es un usuario registrado (solo si es diferente)
            if ($delivery['recipient_number_id'] != $user['number_id']) {
                $stmt_recipient = $conn->prepare("SELECT * FROM gf_users WHERE number_id = ?");
                if ($stmt_recipient) {
                    $stmt_recipient->bind_param("i", $delivery['recipient_number_id']);
                    $stmt_recipient->execute();
                    $result_recipient = $stmt_recipient->get_result();
                    
                    if ($result_recipient->num_rows > 0) {
                        $recipient = $result_recipient->fetch_assoc();
                        $sheet->setCellValue('X' . $row, 'SI');
                        $sheet->setCellValue('Y' . $row, $recipient['company_name'] ?? '');
                        $sheet->setCellValue('Z' . $row, $recipient['city'] ?? '');
                    } else {
                        $sheet->setCellValue('X' . $row, 'NO');
                        $sheet->setCellValue('Y' . $row, '');
                        $sheet->setCellValue('Z' . $row, '');
                    }
                    $stmt_recipient->close();
                }
            } else {
                // Misma persona
                $sheet->setCellValue('X' . $row, 'MISMA PERSONA');
                $sheet->setCellValue('Y' . $row, $user['company_name'] ?? '');
                $sheet->setCellValue('Z' . $row, $user['city'] ?? '');
            }
        } else {
            // Sin entrega - llenar con valores vacíos
            $sheet->setCellValue('M' . $row, 'NO');
            for ($col = 13; $col < $lastColumn; $col++) { // N hasta Z
                $sheet->setCellValue(chr(65 + $col) . $row, '');
            }
        }
        $stmt_delivery->close();
        $row++;
    }

    // Ajustar ancho de columnas automáticamente
    $lastColumnLetter = chr(65 + $lastColumn - 1);
    foreach (range('A', $lastColumnLetter) as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Aplicar bordes a los títulos siempre (incluso sin datos)
    $headerRange = 'A1:' . $lastColumnLetter . '1';
    $borderStyle = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000']
            ]
        ]
    ];
    $sheet->getStyle($headerRange)->applyFromArray($borderStyle);

    // Si hay datos, aplicar formato adicional
    if ($userCount > 0) {
        // Formato para fechas
        $sheet->getStyle('H2:H' . ($row-1))->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
        $sheet->getStyle('N2:N' . ($row-1))->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DATETIME);
        
        // Aplicar bordes a toda la tabla con datos
        $tableRange = 'A1:' . $lastColumnLetter . ($row - 1);
        $sheet->getStyle($tableRange)->applyFromArray($borderStyle);
    } else {
        // Si no hay datos, agregar una fila con mensaje informativo (opcional)
        $sheet->setCellValue('A2', 'No hay datos disponibles');
        $sheet->mergeCells('A2:' . $lastColumnLetter . '2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2')->getFont()->setItalic(true);
    }

    // Crear el writer
    $writer = new Xlsx($spreadsheet);

    // Nombre del archivo
    $filename = 'Matriz_Regalos_' . date('Y-m-d_H-i-s') . '.xlsx';

    // Configurar headers para descarga
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Pragma: public');
    header('Expires: 0');

    // Limpiar cualquier salida previa
    ob_end_clean();

    // Guardar el archivo al output
    $writer->save('php://output');
    
} catch (Exception $e) {
    // Limpiar output buffer en caso de error
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al generar el archivo: ' . $e->getMessage()]);
}

// Cerrar conexión
if (isset($conn)) {
    mysqli_close($conn);
}
exit;
?>