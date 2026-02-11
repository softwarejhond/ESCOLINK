<?php
// Activar error reporting para depuración
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión para acceder al username
session_start();

require_once '../../vendor/autoload.php'; // Incluir PHPSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

// Incluir la conexión a la DB desde conexion.php
include '../../controller/conexion.php'; // Ajusta la ruta si es necesario

// Procesar el archivo si se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file']['tmp_name'];
    if (!empty($file)) {
        try {
            $spreadsheet = IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Saltar la primera fila si es encabezado
            array_shift($rows);

            $successCount = 0;
            $errors = [];
            $inserts = 0;
            $updates = 0;

            foreach ($rows as $row) {
                // Asegurar que el array tenga al menos 3 elementos (índices 0-2)
                $row = array_pad($row, 3, '');
                
                // Procesar Columna A: CELULAR → phone_number
                $phone_raw = trim($row[0]);
                $phone_number = preg_replace('/\D/', '', $phone_raw); // Quitar todo excepto dígitos
                if (strlen($phone_number) > 10) {
                    $phone_number = substr($phone_number, -10); // Tomar los últimos 10 dígitos si es más largo
                }
                // Mantener como string para BIGINT
                
                // Procesar Columna B: Nombres → name
                $name_raw = trim($row[1]);
                $name = strtoupper($name_raw);
                // Quitar tildes
                $name = iconv('UTF-8', 'ASCII//TRANSLIT', $name);
                $name = preg_replace('/[^A-Z\s]/', '', $name); // Quitar caracteres no alfabéticos ni espacios
                
                // Procesar Columna C: AHORRO → saving
                $saving = trim($row[2]); // Solo trim, mantener el $ si está
                
                // Validar campos obligatorios
                if (empty($phone_number) || empty($name) || empty($saving)) {
                    $errors[] = "Fila inválida: phone_number, name o saving faltante o inválido.";
                    continue;
                }

                // Obtener created_by de la sesión
                if (!isset($_SESSION['username'])) {
                    $errors[] = "Sesión no válida: no se encontró username.";
                    continue;
                }
                $created_by = $_SESSION['username'];

                // Verificar si existe
                $checkStmt = $conn->prepare("SELECT COUNT(*) FROM saving_users WHERE phone_number = ?");
                $checkStmt->bind_param("s", $phone_number);
                $checkStmt->execute();
                $checkStmt->bind_result($count);
                $checkStmt->fetch();
                $checkStmt->close();

                if ($count > 0) {
                    // Actualizar
                    $stmt = $conn->prepare("UPDATE saving_users SET name=?, saving=? WHERE phone_number=?");
                    $stmt->bind_param("sss", $name, $saving, $phone_number);
                    if ($stmt->execute()) {
                        $updates++;
                    } else {
                        $errors[] = "Error al actualizar: " . $stmt->error;
                    }
                } else {
                    // Insertar
                    $stmt = $conn->prepare("INSERT INTO saving_users (phone_number, name, saving, created_by) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssss", $phone_number, $name, $saving, $created_by);
                    if ($stmt->execute()) {
                        $inserts++;
                    } else {
                        $errors[] = "Error al insertar: " . $stmt->error;
                    }
                }
                $stmt->close();
            }

            // Respuesta JSON
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => "Importación completada. Nuevos registros: $inserts. Registros actualizados: $updates. Errores: " . count($errors),
                'inserts' => $inserts,
                'updates' => $updates,
                'errors' => $errors
            ]);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error al procesar el archivo: ' . $e->getMessage()]);
            exit;
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No se seleccionó un archivo.']);
        exit;
    }
}
// No cerrar $conn aquí, ya que es manejado en conexion.php si es necesario
?>