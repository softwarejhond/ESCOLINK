<?php

// --- INICIO DE BLOQUE DE DEBUG Y MANEJO DE ERRORES TEMPRANO ---

// Define una ruta base segura para los logs.
// __DIR__ es más fiable que las rutas relativas.
$logDir = __DIR__;
$logFile = $logDir . '/import.log';

// Función de log de emergencia (disponible desde el inicio)
function emergencyLog($message) {
    global $logFile;
    // Asegurarse de que el mensaje sea una cadena
    if (!is_string($message)) {
        $message = print_r($message, true);
    }
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [EMERGENCY] $message" . PHP_EOL;
    
    // Intentar escribir en el log, suprimiendo errores si falla (ej. permisos)
    @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// Manejador de errores y excepciones global
// Capturará errores fatales que antes no se registraban.
function globalErrorHandler($level, $message, $file, $line) {
    emergencyLog("Error: [$level] $message in $file on line $line");
}
function globalExceptionHandler($exception) {
    emergencyLog("Exception: " . $exception->getMessage());
}
set_error_handler('globalErrorHandler');
set_exception_handler('globalExceptionHandler');
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
        emergencyLog("Fatal Error: [{$error['type']}] {$error['message']} in {$error['file']} on line {$error['line']}");
        
        // Si el buffer está activo, límpialo y envía una respuesta JSON de error
        if (ob_get_level() > 0) {
            ob_clean();
        }
        if (!headers_sent()) {
            header('Content-Type: application/json');
            http_response_code(500); // Asegurar código de estado 500
        }
        echo json_encode([
            'success' => false,
            'message' => 'Error fatal en el servidor. Revise el log de importación para más detalles.',
            'error_details' => "{$error['message']} in {$error['file']}:{$error['line']}"
        ]);
    }
});

// Iniciar buffer de salida DESPUÉS de configurar el manejo de errores
ob_start();

// --- FIN DE BLOQUE DE DEBUG ---


// Configuraciones de memoria y tiempo
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 300);
set_time_limit(300);

// Configuración de errores de PHP (loguear, no mostrar)
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', $logFile); // Forzar que los errores de PHP se escriban en nuestro log

// Usar rutas absolutas para mayor fiabilidad
try {
    // Verificar si los archivos existen antes de incluirlos
    $vendorAutoload = realpath(__DIR__ . '/../../vendor/autoload.php');
    $conexionFile = realpath(__DIR__ . '/../../controller/conexion.php');

    if (!$vendorAutoload) {
        throw new Exception("No se pudo encontrar el archivo 'vendor/autoload.php'.");
    }
    if (!$conexionFile) {
        throw new Exception("No se pudo encontrar el archivo 'controller/conexion.php'.");
    }

    require_once $vendorAutoload;
    include $conexionFile;

} catch (Exception $e) {
    $errorMsg = 'Error crítico en la inicialización: ' . $e->getMessage();
    emergencyLog($errorMsg); // Usar el log de emergencia
    
    ob_clean();
    if (!headers_sent()) {
        header('Content-Type: application/json');
        http_response_code(500);
    }
    echo json_encode(['success' => false, 'message' => $errorMsg]);
    exit;
}


use PhpOffice\PhpSpreadsheet\IOFactory;

// Limpiar cualquier output previo del buffer (si es necesario)
ob_clean();

// Función para escribir errores en el log (ahora usa la de emergencia)
function writeErrorToLog($message) {
    // Reemplazamos la función anterior por nuestro logger robusto
    emergencyLog($message);
}

// Función para normalizar texto
function normalizeText($text) {
    $text = strtoupper(trim($text));
    
    // Reemplazar vocales con tilde por vocales normales
    $replacements = [
        'Á' => 'A', 'á' => 'A',
        'É' => 'E', 'é' => 'E',
        'Í' => 'I', 'í' => 'I',
        'Ó' => 'O', 'ó' => 'O',
        'Ú' => 'U', 'ú' => 'U',
        'ñ' => 'Ñ'
    ];
    
    return strtr($text, $replacements);
}

// Función para normalizar ciudad (mantenemos por compatibilidad)
function normalizeCity($text) {
    return normalizeText($text);
}

// Función para verificar si una fila está vacía
function isEmptyRow($row) {
    // Verificar si todos los elementos están vacíos o son null
    foreach ($row as $cell) {
        if (!empty(trim($cell))) {
            return false;
        }
    }
    return true;
}

// Función para normalizar valores booleanos (SI/NO)
function normalizeBoolean($value) {
    $value = strtoupper(trim($value));
    $siVariants = ['SI', 'SÍ', 'SI', 'SÍ', 'YES', 'S', 'TRUE', '1'];
    $noVariants = ['NO', 'NO', 'N', 'FALSE', '0'];
    
    if (in_array($value, $siVariants)) {
        return 'SI';
    } elseif (in_array($value, $noVariants)) {
        return 'NO';
    } else {
        return ''; // Dejar vacío si no coincide con ninguna variante
    }
}

// Nuevas funciones de normalización para la tabla el_students
function normalizeGender($value) {
    $value = strtoupper(trim($value));
    if ($value === 'M') return 'M';
    if ($value === 'F') return 'F';
    if ($value === 'O') return 'OTRO';
    return 'OTRO'; // default
}

function normalizeStatus($value) {
    $value = strtoupper(trim($value));
    $valid = ['NUEVO', 'RENOVACION', 'REPITENTE', 'CANCELADO', 'PENDIENTE RENOVACIÓN', 'ASUMIDO'];
    if (in_array($value, $valid)) return $value;
    return 'NUEVO'; // default
}

function normalizeDocumentType($value) {
    $value = strtoupper(trim($value));
    $valid = ['TI', 'CC', 'CE', 'RC', 'PAS'];
    if (in_array($value, $valid)) return $value;
    return 'TI'; // default
}

// Procesar el archivo si se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file']['tmp_name'];
    if (!empty($file)) {
        try {
            // Escribir inicio de importación en log
            writeErrorToLog("=== INICIO DE IMPORTACIÓN ===");
            writeErrorToLog("Archivo procesado: " . $_FILES['excel_file']['name']);
            
            $spreadsheet = IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            array_shift($rows);

            $successCount = 0;
            $errors = [];
            $inserts = 0;
            $updates = 0;
            $skippedRows = 0;
            $rowNumber = 1; // Para contar la fila real en el Excel

            foreach ($rows as $row) {
                $rowNumber++; // Incrementar contador de fila (empezando desde 2 porque quitamos header)

                // Verificar si la fila está completamente vacía
                if (isEmptyRow($row)) {
                    $skippedRows++;
                    continue; // Saltar filas vacías sin contarlas como error
                }

                $row = array_pad($row, 17, ''); // Ahora son 17 columnas

                $document_type = normalizeDocumentType($row[0]);
                $simat = trim($row[1]);
                $document_number = (int)preg_replace('/\D/', '', $row[2]);
                $student_code = trim($row[3]);
                $name = normalizeText($row[4]);
                $registration_date = !empty(trim($row[5])) ? date('Y-m-d', strtotime(str_replace('/', '-', $row[5]))) : date('Y-m-d');
                $gender = normalizeGender($row[6]);
                $grade_level = trim($row[7]);
                $group_section = trim($row[8]);
                $email = trim($row[9]);
                $cell_phone = preg_replace('/\D/', '', $row[10]);
                $cell_phone2 = preg_replace('/\D/', '', $row[11]);
                $address = trim($row[12]);
                $barrio = trim($row[13]);
                $comuna = trim($row[14]);
                $city = normalizeText($row[15]);
                $sede = trim($row[16]);
                $status = normalizeStatus($row[17]);
                writeErrorToLog("DEBUG - Fila $rowNumber: row[17]='" . $row[17] . "', status='$status'");
                $updated_by = null; // Definir variable para updated_by

                // Validar campos obligatorios
                if (empty($document_number) || empty($name) || empty($registration_date) || empty($grade_level)) {
                    $errorMsg = "Fila $rowNumber inválida: document_number=$document_number, name='$name', registration_date='$registration_date', grade_level='$grade_level'";
                    $errors[] = $errorMsg;
                    writeErrorToLog("ERROR VALIDACIÓN - $errorMsg");
                    continue;
                }

                // Verificar si ya tiene entrega este año
                // (Eliminado para la nueva tabla el_students)

                // Verificar si existe
                $checkStmt = $conn->prepare("SELECT COUNT(*) FROM el_students WHERE document_number = ?");
                $checkStmt->bind_param("i", $document_number);
                $checkStmt->execute();
                $checkStmt->bind_result($count);
                $checkStmt->fetch();
                $checkStmt->close();

                if ($count > 0) {
                    // Actualizar
                    $stmt = $conn->prepare("UPDATE el_students SET student_code=?, document_type=?, name=?, grade_level=?, gender=?, email=?, cell_phone=?, address=?, city=?, status=?, registration_date=?, sede=?, simat=?, cell_phone2=?, barrio=?, comuna=?, updated_by=? WHERE document_number=?");
                    $stmt->bind_param("sssssssssssssssssi", $student_code, $document_type, $name, $grade_level, $gender, $email, $cell_phone, $address, $city, $status, $registration_date, $sede, $simat, $cell_phone2, $barrio, $comuna, $updated_by, $document_number);
                    if ($stmt->execute()) {
                        $updates++;
                        writeErrorToLog("SUCCESS - Fila $rowNumber: Estudiante $document_number actualizado correctamente");
                    } else {
                        $errorMsg = "Error al actualizar fila $rowNumber: " . $stmt->error;
                        $errors[] = $errorMsg;
                        writeErrorToLog("ERROR UPDATE - $errorMsg");
                    }
                } else {
                    // Insertar
                    $stmt = $conn->prepare("INSERT INTO el_students (student_code, document_type, document_number, name, grade_level, gender, email, cell_phone, address, city, status, registration_date, sede, simat, cell_phone2, barrio, comuna, updated_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssisssssssssssssss", $student_code, $document_type, $document_number, $name, $grade_level, $gender, $email, $cell_phone, $address, $city, $status, $registration_date, $sede, $simat, $cell_phone2, $barrio, $comuna, $updated_by);
                    if ($stmt->execute()) {
                        $inserts++;
                        writeErrorToLog("SUCCESS - Fila $rowNumber: Estudiante $document_number insertado correctamente");
                    } else {
                        $errorMsg = "Error al insertar fila $rowNumber: " . $stmt->error;
                        $errors[] = $errorMsg;
                        writeErrorToLog("ERROR INSERT - $errorMsg");
                    }
                }
                $stmt->close();
            }

            // Escribir resumen en log
            writeErrorToLog("=== RESUMEN DE IMPORTACIÓN ===");
            writeErrorToLog("Nuevos registros: $inserts");
            writeErrorToLog("Registros actualizados: $updates");
            writeErrorToLog("Filas vacías omitidas: $skippedRows");
            writeErrorToLog("Total de errores: " . count($errors));
            writeErrorToLog("=== FIN DE IMPORTACIÓN ===");

            // Cambiar la lógica del resultado final
            ob_clean();
            header('Content-Type: application/json');

            // Preparar mensaje con información completa
            $totalProcessed = $inserts + $updates;
            $message = "Importación completada. Nuevos registros: $inserts. Registros actualizados: $updates.";

            // if ($skippedRows > 0) {
            //     $message .= " Filas vacías omitidas: $skippedRows.";
            // }

            if (count($errors) > 0) {
                $message .= " Errores: " . count($errors);
            }

            echo json_encode([
                'success' => true,
                'message' => $message,
                'inserts' => $inserts,
                'updates' => $updates,
                'skipped_rows' => $skippedRows,
                'errors' => count($errors) > 0 ? array_slice($errors, 0, 5) : [],
                'total_errors' => count($errors)
            ]);
            exit;

        } catch (Exception $e) {
            $errorMsg = 'Error al procesar el archivo: ' . $e->getMessage();
            writeErrorToLog("EXCEPCIÓN - $errorMsg");
            
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $errorMsg]);
            exit;
        }
    } else {
        writeErrorToLog("ERROR - No se seleccionó un archivo");
        
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No se seleccionó un archivo.']);
        exit;
    }
}

// Si no es una petición válida
writeErrorToLog("ERROR - Petición no válida");

ob_clean();
header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'Petición no válida.']);
exit;
?>