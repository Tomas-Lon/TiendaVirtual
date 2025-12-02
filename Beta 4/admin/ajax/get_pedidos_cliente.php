<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['cliente_id']) || !is_numeric($_GET['cliente_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de cliente inválido']);
    exit;
}

try {
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("SELECT id, numero_documento, fecha_pedido, total, estado 
                          FROM pedidos 
                          WHERE cliente_id = ? 
                          ORDER BY fecha_pedido DESC 
                          LIMIT 10");
    
    $stmt->execute([$_GET['cliente_id']]);
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($pedidos);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener los pedidos']);
}
