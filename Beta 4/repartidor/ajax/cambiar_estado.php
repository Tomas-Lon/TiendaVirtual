<?php
session_start();

// Verificar autenticación
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'empleado' || strtolower($_SESSION['cargo']) !== 'repartidor') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

require_once '../../config/database.php';

header('Content-Type: application/json');

$pdo = getConnection();
$empleado_id = $_SESSION['empleado_id'] ?? 0;

// Leer datos JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit();
}

$entrega_id = intval($data['entrega_id'] ?? 0);
$nuevo_estado = $data['estado'] ?? '';
$motivo = $data['motivo'] ?? '';
$observaciones = $data['observaciones'] ?? '';

// Validaciones
if (!$entrega_id) {
    echo json_encode(['success' => false, 'message' => 'ID de entrega requerido']);
    exit();
}

// Permitir también reintentar (programado) desde estado 'fallido'
if (!in_array($nuevo_estado, ['en_transito', 'fallido', 'devuelto', 'programado'])) {
    echo json_encode(['success' => false, 'message' => 'Estado no válido']);
    exit();
}

// Verificar que el envío pertenezca al repartidor
$stmt = $pdo->prepare("SELECT id, estado FROM envios WHERE id = ? AND repartidor_id = ?");
$stmt->execute([$entrega_id, $empleado_id]);
$entrega = $stmt->fetch();

if (!$entrega) {
    echo json_encode(['success' => false, 'message' => 'Envío no encontrado o no asignado a ti']);
    exit();
}

// Validar transición de estado
$estado_actual = $entrega['estado'];

if ($nuevo_estado === 'en_transito') {
    if (!in_array($estado_actual, ['programado', 'en_preparacion'])) {
        echo json_encode(['success' => false, 'message' => 'Solo puedes iniciar envíos programados o en preparación']);
        exit();
    }
}

if ($nuevo_estado === 'programado') {
    // Reintentar solo permitido desde 'fallido'
    if (!in_array($estado_actual, ['fallido'])) {
        echo json_encode(['success' => false, 'message' => 'Solo puedes reprogramar envíos que estén en estado fallido']);
        exit();
    }
}

if ($nuevo_estado === 'fallido') {
    if (!in_array($estado_actual, ['programado', 'en_preparacion', 'en_transito'])) {
        echo json_encode(['success' => false, 'message' => 'No puedes marcar como fallido un envío ' . $estado_actual]);
        exit();
    }
    if (!$motivo) {
        echo json_encode(['success' => false, 'message' => 'Motivo es requerido para envíos fallidos']);
        exit();
    }
}

if ($nuevo_estado === 'devuelto') {
    if (!in_array($estado_actual, ['fallido', 'en_transito'])) {
        echo json_encode(['success' => false, 'message' => 'Solo puedes devolver envíos fallidos o en tránsito']);
        exit();
    }
}

try {
    $pdo->beginTransaction();
    
    // Verificar si existe columna updated_at
    $checkColumn = $pdo->query("SHOW COLUMNS FROM envios LIKE 'updated_at'")->fetch();
    
    // Actualizar estado
    if ($checkColumn) {
        $stmt = $pdo->prepare("UPDATE envios SET estado = ?, updated_at = NOW() WHERE id = ?");
    } else {
        $stmt = $pdo->prepare("UPDATE envios SET estado = ? WHERE id = ?");
    }
    $stmt->execute([$nuevo_estado, $entrega_id]);
    
    // Preparar notas para historial
    $notas = '';
    if ($nuevo_estado === 'fallido') {
        $notas = "Motivo: {$motivo}";
        if ($observaciones) {
            $notas .= " | Observaciones: {$observaciones}";
        }
    } elseif ($nuevo_estado === 'devuelto') {
        $notas = "Envío devuelto";
        if ($motivo) {
            $notas .= " | Motivo: {$motivo}";
        }
        if ($observaciones) {
            $notas .= " | Observaciones: {$observaciones}";
        }
    } elseif ($nuevo_estado === 'en_transito') {
        $notas = "Envío iniciado";
    }
    
    // Registrar en historial (solo si la tabla existe y tiene las columnas correctas)
    try {
        $stmt = $pdo->prepare("
            INSERT INTO historial_entregas (entrega_id, estado_anterior, estado_nuevo, notas)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $entrega_id,
            $estado_actual,
            $nuevo_estado,
            $notas
        ]);
    } catch (PDOException $e) {
        // Si la tabla no existe o falta alguna columna, solo registrar el error pero no fallar
        error_log("Advertencia: No se pudo registrar en historial_entregas: " . $e->getMessage());
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Estado actualizado correctamente',
        'nuevo_estado' => $nuevo_estado
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Error cambiar_estado.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al actualizar estado: ' . $e->getMessage()
    ]);
}
?>
