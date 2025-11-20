<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario']) || ($_SESSION['tipo'] ?? '') !== 'cliente') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data || !isset($data['items']) || !is_array($data['items'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Entrada inválida']);
    exit();
}

require_once '../../config/database.php';
$pdo = getConnection();

// Asegurar estructuras en sesión
if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}
$carrito = &$_SESSION['carrito'];

// Validar y agregar productos
$ids = [];
foreach ($data['items'] as $it) {
    $pid = (int)($it['producto_id'] ?? 0);
    $qty = (int)($it['cantidad'] ?? 0);
    if ($pid > 0 && $qty > 0) {
        $ids[$pid] = ($ids[$pid] ?? 0) + $qty;
    }
}
if (empty($ids)) {
    echo json_encode(['success' => false, 'message' => 'Sin productos válidos']);
    exit();
}

// Validar que existan en BD
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT id FROM productos WHERE id IN ($placeholders)");
$stmt->execute(array_keys($ids));
$validIds = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
$validIds = array_map('intval', $validIds);

if (empty($validIds)) {
    echo json_encode(['success' => false, 'message' => 'Productos no encontrados']);
    exit();
}

foreach ($validIds as $pid) {
    if (isset($carrito[$pid])) {
        $carrito[$pid] += $ids[$pid];
    } else {
        $carrito[$pid] = $ids[$pid];
    }
}

$_SESSION['cart_count'] = array_sum($carrito);

echo json_encode([
    'success' => true,
    'message' => 'Carrito virtual actualizado',
    'cart_count' => (int)$_SESSION['cart_count']
]);
