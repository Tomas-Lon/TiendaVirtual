<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario']) || ($_SESSION['tipo'] ?? '') !== 'cliente') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

require_once '../../config/database.php';
$pdo = getConnection();

$carrito = $_SESSION['carrito'] ?? [];
if (!is_array($carrito) || empty($carrito)) {
    echo json_encode(['success' => true, 'items' => []]);
    exit();
}

$ids = array_map('intval', array_keys($carrito));
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT id, codigo, descripcion, precio FROM productos WHERE id IN ($placeholders)");
$stmt->execute($ids);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

$items = [];
foreach ($rows as $r) {
    $pid = (int)$r['id'];
    $items[] = [
        'id' => $pid,
        'codigo' => $r['codigo'],
        'descripcion' => $r['descripcion'],
        'precio' => (float)$r['precio'],
        'cantidad' => (int)($carrito[$pid] ?? 0)
    ];
}

echo json_encode(['success' => true, 'items' => $items]);
