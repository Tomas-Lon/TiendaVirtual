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

$items = $data['items']; // [{producto_id, cantidad}]
if (count($items) === 0) {
    echo json_encode(['success' => false, 'message' => 'No hay productos para guardar']);
    exit();
}

require_once '../../config/database.php';
$pdo = getConnection();

// Validar productos y construir mapa
$ids = [];
foreach ($items as $it) {
    $pid = (int)($it['producto_id'] ?? 0);
    $qty = (int)($it['cantidad'] ?? 0);
    if ($pid <= 0 || $qty <= 0) continue;
    $ids[$pid] = ($ids[$pid] ?? 0) + $qty; // sumar cantidades repetidas
}
if (empty($ids)) {
    echo json_encode(['success' => false, 'message' => 'Productos/cantidades inválidas']);
    exit();
}

// Traer productos existentes
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT id FROM productos WHERE id IN ($placeholders)");
$stmt->execute(array_keys($ids));
$validIds = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
$validIds = array_map('intval', $validIds);

// Construir carrito de sesión
$_SESSION['carrito'] = $_SESSION['carrito'] ?? [];
$carrito = &$_SESSION['carrito'];

// Reset y set según importación (decisión: reemplazar para reflejar CSV + adiciones del panel)
$carrito = [];
foreach ($validIds as $pid) {
    $carrito[$pid] = $ids[$pid];
}

// Actualizar contador
$_SESSION['cart_count'] = array_sum($carrito);

echo json_encode([
    'success' => true,
    'message' => 'Carrito actualizado',
    'cart_count' => (int)$_SESSION['cart_count']
]);
?>
