<?php
require_once '../../vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

function generateAuthorizationLetter($beneficiaryName, $beneficiaryId, $receiverName, $receiverId, $giftCategory, $signatureBase64) {
    // Configurar zona horaria de Bogotá
    date_default_timezone_set('America/Bogota');
    
    // Configurar opciones de Dompdf
    $options = new Options();
    $options->set('defaultFont', 'Arial');
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', true);
    $options->set('isRemoteEnabled', true); // Permitir imágenes locales
    
    $dompdf = new Dompdf($options);
    
    // Preparar la fecha actual
    $fechaActual = date('d/m/Y');
    
    // Ruta del logo (convertir a base64 para evitar problemas de ruta)
    $logoPath = '../../img/logo-metrofem.png';
    $logoData = '';
    if (file_exists($logoPath)) {
        $logoData = base64_encode(file_get_contents($logoPath));
        $logoSrc = 'data:image/png;base64,' . $logoData;
    }
    
    // Crear el contenido HTML de la carta
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Carta de Autorización</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 12px;
                line-height: 1.6;
                margin: 20px;
                color: #333;
            }
            .header {
                display: table;
                width: 100%;
                margin-bottom: 40px;
                padding-bottom: 20px;
                border-bottom: 2px solid #006d68;
            }
            .logo-section {
                display: table-cell;
                vertical-align: middle;
                width: 120px;
                text-align: left;
            }
            .logo-img {
                max-width: 100px;
                max-height: 80px;
                width: auto;
                height: auto;
            }
            .title-section {
                display: table-cell;
                vertical-align: middle;
                text-align: center;
                padding-left: 20px;
            }
            .title-section h1 {
                color: #006d68;
                font-size: 18px;
                margin: 0;
                font-weight: bold;
            }
            .content {
                text-align: justify;
                margin-bottom: 40px;
            }
            .content p {
                margin-bottom: 20px;
                font-size: 14px;
            }
            .highlight {
                font-weight: bold;
                color: #006d68;
            }
            .signature-section {
                margin-top: 60px;
                text-align: center;
            }
            .signature-box {
                border: 1px solid #ccc;
                width: 300px;
                height: 120px;
                margin: 20px auto;
                display: flex;
                align-items: center;
                justify-content: center;
                background-color: #f9f9f9;
            }
            .signature-img {
                max-width: 280px;
                max-height: 100px;
            }
            .signature-line {
                margin-top: 20px;
                text-align: center;
            }
            .date {
                text-align: right;
                margin-bottom: 30px;
                font-size: 12px;
            }
            .footer {
                margin-top: 50px;
                text-align: center;
                font-size: 10px;
                color: #666;
                border-top: 1px solid #ccc;
                padding-top: 20px;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <div class="logo-section">';
            
    if (!empty($logoData)) {
        $html .= '<img src="' . $logoSrc . '" class="logo-img" alt="Logo">';
    }
    
    $html .= '
            </div>
            <div class="title-section">
                <h1>CARTA DE AUTORIZACIÓN<br>PARA ENTREGA DE REGALO</h1>
            </div>
        </div>
        
        <div class="date">
            Fecha: ' . $fechaActual . '
        </div>
        
        <div class="content">
            <p>
                Yo <span class="highlight">' . strtoupper($beneficiaryName) . '</span>, 
                identificado(a) con la cédula # <span class="highlight">' . $beneficiaryId . '</span>, 
                por medio de la presente autorizo a <span class="highlight">' . strtoupper($receiverName) . '</span> 
                identificado(a) con cédula # <span class="highlight">' . $receiverId . '</span> 
                para que reclame mi <span class="highlight">' . strtolower($giftCategory) . '</span>.
            </p>
            
            <p>
                Esta autorización es válida únicamente para la persona mencionada anteriormente 
                y para el regalo específico indicado.
            </p>
        </div>
        
        <div class="signature-section">
            <p><strong>Firma de autorización:</strong></p>
            <div class="signature-box">';
            
    if (!empty($signatureBase64)) {
        $html .= '<img src="' . $signatureBase64 . '" class="signature-img" alt="Firma">';
    }
    
    $html .= '
            </div>
            <div class="signature-line">
                ________________________________<br>
                <strong>' . strtoupper($beneficiaryName) . '</strong><br>
                CC: ' . $beneficiaryId . '
            </div>
        </div>
        
        <div class="footer">
            <p>Fecha de generación: ' . date('d/m/Y H:i:s') . ' </p>
        </div>
    </body>
    </html>';
    
    // Cargar HTML en Dompdf
    $dompdf->loadHtml($html);
    
    // Configurar el tamaño de papel
    $dompdf->setPaper('A4', 'portrait');
    
    // Renderizar el PDF
    $dompdf->render();
    
    // Generar nombre único para el archivo
    $fileName = 'carta_autorizacion_' . $beneficiaryId . '_' . time() . '.pdf';
    
    // Directorio donde se guardará
    $uploadDir = '../../uploads/authorizations/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Guardar el archivo PDF
    $pdfContent = $dompdf->output();
    $filePath = $uploadDir . $fileName;
    file_put_contents($filePath, $pdfContent);
    
    return $fileName;
}
?>