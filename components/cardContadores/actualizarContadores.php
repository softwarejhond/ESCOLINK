<?php
header('Content-Type: application/json');

// Incluir conexión a BD
include '../../controller/conexion.php';

/**
 * Función para escribir logs de error personalizados
 */
function writeErrorLog($message, $context = []) {
    $logFile = __DIR__ . '/contadores_error.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
    $logEntry = "[{$timestamp}] {$message}{$contextStr}" . PHP_EOL;
    
    if (!file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX)) {
        error_log("[CONTADORES_ERROR] {$message}{$contextStr}");
    }
}

/**
 * Función para responder con error sin exponer detalles
 */
function respondWithError($userMessage, $technicalError = null, $context = []) {
    if ($technicalError) {
        writeErrorLog($technicalError, $context);
    }
    echo json_encode([
        'success' => false, 
        'message' => $userMessage,
        'totalEntregas' => 0,
        'totalUsuarios' => 0,
        'entregasMes' => 0,
        'labels' => [],
        'values' => [],
        'labelsMeses' => [],
        'valoresMeses' => []
    ]);
    exit;
}

try {
    // Verificar conexión
    if (!$conn) {
        respondWithError('Error de conexión', 'Database connection not available');
    }

    // INICIALIZAR VARIABLES CON VALORES POR DEFECTO
    $totalEntregas = 0;
    $totalUsuarios = 0;
    $entregasMes = 0;

    // Contador 1: Total de Regalos Entregados
    $stmt1 = $conn->prepare("SELECT COUNT(*) as total FROM gf_gift_deliveries");
    if (!$stmt1) {
        respondWithError('Error interno del servidor', 'Failed to prepare query 1: ' . $conn->error);
    }
    
    if ($stmt1->execute()) {
        $stmt1->bind_result($totalEntregas);
        if (!$stmt1->fetch()) {
            $totalEntregas = 0;
        }
    } else {
        writeErrorLog('Failed to execute query 1: ' . $stmt1->error);
    }
    $stmt1->close();

    // Contador 2: Total de Usuarios Registrados
    $stmt2 = $conn->prepare("SELECT COUNT(*) as total FROM gf_users");
    if (!$stmt2) {
        respondWithError('Error interno del servidor', 'Failed to prepare query 2: ' . $conn->error);
    }
    
    if ($stmt2->execute()) {
        $stmt2->bind_result($totalUsuarios);
        if (!$stmt2->fetch()) {
            $totalUsuarios = 0;
        }
    } else {
        writeErrorLog('Failed to execute query 2: ' . $stmt2->error);
    }
    $stmt2->close();

    // Contador 3: Entregas Este Mes
    $stmt3 = $conn->prepare("SELECT COUNT(*) as total FROM gf_gift_deliveries WHERE MONTH(reception_date) = MONTH(CURDATE()) AND YEAR(reception_date) = YEAR(CURDATE())");
    if (!$stmt3) {
        respondWithError('Error interno del servidor', 'Failed to prepare query 3: ' . $conn->error);
    }
    
    if ($stmt3->execute()) {
        $stmt3->bind_result($entregasMes);
        if (!$stmt3->fetch()) {
            $entregasMes = 0;
        }
    } else {
        writeErrorLog('Failed to execute query 3: ' . $stmt3->error);
    }
    $stmt3->close();

    // Consulta 4: Entregas por sede para el gráfico
    $labels = [];
    $values = [];
    
    $stmt4 = $conn->prepare("SELECT sede, COUNT(*) as total FROM gf_gift_deliveries GROUP BY sede ORDER BY total DESC");
    if ($stmt4 && $stmt4->execute()) {
        $stmt4->bind_result($sede, $total_sede);
        while ($stmt4->fetch()) {
            $labels[] = $sede ?? 'Sin sede';
            $values[] = (int)($total_sede ?? 0);
        }
        $stmt4->close();
    } else {
        writeErrorLog('Failed with query 4: ' . ($stmt4 ? $stmt4->error : $conn->error));
        if ($stmt4) $stmt4->close();
    }

    // Consulta 5: Entregas por mes (últimos 12 meses)
    $labelsMeses = [];
    $valoresMeses = [];
    
    $stmt5 = $conn->prepare("SELECT DATE_FORMAT(reception_date, '%Y-%m') as mes, COUNT(*) as total 
                            FROM gf_gift_deliveries 
                            WHERE reception_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                            GROUP BY mes
                            ORDER BY mes");
    if ($stmt5 && $stmt5->execute()) {
        $stmt5->bind_result($mes, $total_mes);
        while ($stmt5->fetch()) {
            $labelsMeses[] = $mes ?? '';
            $valoresMeses[] = (int)($total_mes ?? 0);
        }
        $stmt5->close();
    } else {
        writeErrorLog('Failed with query 5: ' . ($stmt5 ? $stmt5->error : $conn->error));
        if ($stmt5) $stmt5->close();
    }

    // ASEGURAR QUE TODOS LOS VALORES SEAN NÚMEROS ENTEROS
    $totalEntregas = (int)($totalEntregas ?? 0);
    $totalUsuarios = (int)($totalUsuarios ?? 0);
    $entregasMes = (int)($entregasMes ?? 0);

    // Log successful operation
    writeErrorLog('Counters updated successfully', [
        'totalEntregas' => $totalEntregas,
        'totalUsuarios' => $totalUsuarios,
        'entregasMes' => $entregasMes
    ]);

    echo json_encode([
        'success' => true,
        'totalEntregas' => $totalEntregas,
        'totalUsuarios' => $totalUsuarios,
        'entregasMes' => $entregasMes,
        'labels' => $labels,
        'values' => $values,
        'labelsMeses' => $labelsMeses,
        'valoresMeses' => $valoresMeses
    ]);

} catch (Exception $e) {
    respondWithError('Error interno del servidor', 'Exception: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine());
} catch (Error $e) {
    respondWithError('Error interno del servidor', 'Fatal Error: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine());
}

if ($conn) {
    $conn->close();
}
?>