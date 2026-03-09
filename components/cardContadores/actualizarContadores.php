<?php
header('Content-Type: application/json');

include '../../controller/conexion.php';

function writeErrorLog($message, $context = []) {
    $logFile = __DIR__ . '/contadores_error.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
    $logEntry = "[{$timestamp}] {$message}{$contextStr}" . PHP_EOL;
    
    if (!file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX)) {
        error_log("[CONTADORES_ERROR] {$message}{$contextStr}");
    }
}

function respondWithError($userMessage, $technicalError = null, $context = []) {
    if ($technicalError) {
        writeErrorLog($technicalError, $context);
    }
    echo json_encode([
        'success' => false, 
        'message' => $userMessage,
        'totalEstudiantes' => 0,
        'estudiantesActivos' => 0,
        'registrosMes' => 0,
        'totalSedes' => 0,
        'labelsSedes' => [],
        'valuesSedes' => [],
        'labelsGrados' => [],
        'valuesGrados' => [],
        'labelsGenero' => [],
        'valuesGenero' => [],
        'labelsMeses' => [],
        'valoresMeses' => [],
        'labelsComunas' => [],
        'valuesComunas' => []
    ]);
    exit;
}

try {
    if (!$conn) {
        respondWithError('Error de conexión', 'Database connection not available');
    }

    $totalEstudiantes = 0;
    $estudiantesActivos = 0;
    $registrosMes = 0;
    $totalSedes = 0;

    // Contador 1: Total de Estudiantes
    $stmt = $conn->prepare("SELECT COUNT(*) FROM el_students");
    if ($stmt && $stmt->execute()) {
        $stmt->bind_result($totalEstudiantes);
        $stmt->fetch();
        $stmt->close();
    }

    // Contador 2: Estudiantes Activos
    $stmt = $conn->prepare("SELECT COUNT(*) FROM el_students WHERE status = 'ACTIVO'");
    if ($stmt && $stmt->execute()) {
        $stmt->bind_result($estudiantesActivos);
        $stmt->fetch();
        $stmt->close();
    }

    // Contador 3: Registros Este Mes
    $stmt = $conn->prepare("SELECT COUNT(*) FROM el_students WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
    if ($stmt && $stmt->execute()) {
        $stmt->bind_result($registrosMes);
        $stmt->fetch();
        $stmt->close();
    }

    // Contador 4: Total de Sedes
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT sede) FROM el_students WHERE sede IS NOT NULL AND sede != ''");
    if ($stmt && $stmt->execute()) {
        $stmt->bind_result($totalSedes);
        $stmt->fetch();
        $stmt->close();
    }

    // Gráfico 1: Estudiantes por Sede (Barras)
    $labelsSedes = [];
    $valuesSedes = [];
    $stmt = $conn->prepare("SELECT IFNULL(sede, 'Sin sede') as sede_nombre, COUNT(*) as total FROM el_students GROUP BY sede ORDER BY total DESC LIMIT 10");
    if ($stmt && $stmt->execute()) {
        $stmt->bind_result($sede, $total);
        while ($stmt->fetch()) {
            $labelsSedes[] = $sede;
            $valuesSedes[] = (int)$total;
        }
        $stmt->close();
    }

    // Gráfico 2: Estudiantes por Grado (Barras horizontales)
    $labelsGrados = [];
    $valuesGrados = [];
    $stmt = $conn->prepare("SELECT grade_level, COUNT(*) as total FROM el_students GROUP BY grade_level ORDER BY grade_level ASC");
    if ($stmt && $stmt->execute()) {
        $stmt->bind_result($grado, $total);
        while ($stmt->fetch()) {
            $labelsGrados[] = $grado;
            $valuesGrados[] = (int)$total;
        }
        $stmt->close();
    }

    // Gráfico 3: Distribución por Género (Dona)
    $labelsGenero = [];
    $valuesGenero = [];
    $stmt = $conn->prepare("SELECT 
        CASE gender 
            WHEN 'M' THEN 'Masculino' 
            WHEN 'F' THEN 'Femenino' 
            WHEN 'OTRO' THEN 'Otro' 
        END as genero, 
        COUNT(*) as total 
        FROM el_students GROUP BY gender ORDER BY total DESC");
    if ($stmt && $stmt->execute()) {
        $stmt->bind_result($genero, $total);
        while ($stmt->fetch()) {
            $labelsGenero[] = $genero;
            $valuesGenero[] = (int)$total;
        }
        $stmt->close();
    }

    // Gráfico 4: Registros por Mes (Línea - últimos 12 meses)
    $labelsMeses = [];
    $valoresMeses = [];
    $stmt = $conn->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m') as mes, COUNT(*) as total 
                            FROM el_students 
                            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                            GROUP BY mes
                            ORDER BY mes ASC");
    if ($stmt && $stmt->execute()) {
        $stmt->bind_result($mes, $total);
        while ($stmt->fetch()) {
            $labelsMeses[] = $mes;
            $valoresMeses[] = (int)$total;
        }
        $stmt->close();
    }

    // Gráfico 5: Top 10 Comunas (Barras)
    $labelsComunas = [];
    $valuesComunas = [];
    $stmt = $conn->prepare("SELECT IFNULL(comuna, 'Sin comuna') as comuna_nombre, COUNT(*) as total 
                            FROM el_students 
                            GROUP BY comuna 
                            ORDER BY total DESC 
                            LIMIT 10");
    if ($stmt && $stmt->execute()) {
        $stmt->bind_result($comuna, $total);
        while ($stmt->fetch()) {
            $labelsComunas[] = $comuna;
            $valuesComunas[] = (int)$total;
        }
        $stmt->close();
    }

    // Gráfico 6: Estudiantes por Estado (Dona)
    $labelsEstatus = [];
    $valuesEstatus = [];
    $stmt = $conn->prepare("SELECT IFNULL(status, 'Sin estado') as estado, COUNT(*) as total FROM el_students GROUP BY status ORDER BY total DESC");
    if ($stmt && $stmt->execute()) {
        $stmt->bind_result($estado, $total);
        while ($stmt->fetch()) {
            $labelsEstatus[] = $estado;
            $valuesEstatus[] = (int)$total;
        }
        $stmt->close();
    }

    $totalEstudiantes = (int)($totalEstudiantes ?? 0);
    $estudiantesActivos = (int)($estudiantesActivos ?? 0);
    $registrosMes = (int)($registrosMes ?? 0);
    $totalSedes = (int)($totalSedes ?? 0);

    echo json_encode([
        'success' => true,
        'totalEstudiantes' => $totalEstudiantes,
        'estudiantesActivos' => $estudiantesActivos,
        'registrosMes' => $registrosMes,
        'totalSedes' => $totalSedes,
        'labelsSedes' => $labelsSedes,
        'valuesSedes' => $valuesSedes,
        'labelsGrados' => $labelsGrados,
        'valuesGrados' => $valuesGrados,
        'labelsGenero' => $labelsGenero,
        'valuesGenero' => $valuesGenero,
        'labelsMeses' => $labelsMeses,
        'valoresMeses' => $valoresMeses,
        'labelsComunas' => $labelsComunas,
        'valuesComunas' => $valuesComunas,
        'labelsEstatus' => $labelsEstatus,
        'valuesEstatus' => $valuesEstatus
    ]);

} catch (Exception $e) {
    respondWithError('Error interno del servidor', 'Exception: ' . $e->getMessage());
} catch (Error $e) {
    respondWithError('Error interno del servidor', 'Fatal Error: ' . $e->getMessage());
}

if ($conn) {
    $conn->close();
}
?>