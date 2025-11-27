<?php
session_start();

header('Content-Type: application/json');

// Autorizar solo a clientes
if (!isset($_SESSION['usuario']) || ($_SESSION['tipo'] ?? '') !== 'cliente') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once '../../config/database.php';

try {
    $pdo = getConnection();
    $clienteId = (int)($_SESSION['cliente_id'] ?? 0);

    // Estadísticas del cliente
    $stats = [
        'mis_pedidos' => 0,
        'en_proceso' => 0,
        'total_gastado' => 0.0,
        'carrito' => (int)array_sum($_SESSION['carrito'] ?? [])
    ];

    // Total de pedidos
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM pedidos WHERE cliente_id = ?");
    $stmt->execute([$clienteId]);
    $stats['mis_pedidos'] = (int)($stmt->fetch()['total'] ?? 0);

    // Pedidos en proceso
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM pedidos WHERE cliente_id = ? AND estado NOT IN ('entregado','cancelado')");
    $stmt->execute([$clienteId]);
    $stats['en_proceso'] = (int)($stmt->fetch()['total'] ?? 0);

    // Total gastado (excluye cancelados)
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total),0) AS gastado FROM pedidos WHERE cliente_id = ? AND estado <> 'cancelado'");
    $stmt->execute([$clienteId]);
    $stats['total_gastado'] = (float)($stmt->fetch()['gastado'] ?? 0);

    echo json_encode(['success' => true, 'data' => $stats, 'timestamp' => time()]);
} catch (PDOException $e) {
    error_log('Error dashboard_stats cliente: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al obtener estadísticas']);
}
