<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario']) || ($_SESSION['tipo'] ?? '') !== 'cliente') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit();
}

require_once __DIR__ . '/../../config/database.php';
$pdo = getConnection();

$cliente_id = (int)($_SESSION['cliente_id'] ?? 0);
if ($cliente_id <= 0) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Cliente no identificado']);
    exit();
}

try {
    // Cabecera
    $stmt = $pdo->prepare('SELECT id, numero_documento, fecha_pedido, fecha_entrega_estimada, direccion_entrega_id, subtotal, descuento_porcentaje, descuento_total, impuestos_total, total, observaciones, estado FROM pedidos WHERE id = ? AND cliente_id = ? LIMIT 1');
    $stmt->execute([$id, $cliente_id]);
    $cab = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$cab) {
        // Log for debugging: requested id and current cliente_id
        error_log("get_pedido.php: pedido_id={$id} no encontrado para cliente_id={$cliente_id}");
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Cotización no encontrada']);
        exit();
    }

    // Detalle
    $stmt = $pdo->prepare('SELECT dp.producto_id, dp.cantidad, dp.precio_unitario as precio, p.codigo, p.descripcion FROM detalle_pedidos dp LEFT JOIN productos p ON p.id = dp.producto_id WHERE dp.pedido_id = ?');
    $stmt->execute([$id]);
    $detalle = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'pedido' => $cab, 'detalle' => $detalle]);
    exit();
} catch (Exception $e) {
    // Log exception details for server-side debugging
    error_log('get_pedido.php exception: ' . $e->getMessage() . ' -- pedido_id=' . $id . ' cliente_id=' . $cliente_id);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno al cargar la cotización']);
    exit();
}
