<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['empleado_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

$codigo = trim($_POST['codigo'] ?? '');
if ($codigo === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Código requerido']);
    exit();
}

require_once '../../config/database.php';
$pdo = getConnection();

$stmt = $pdo->prepare('SELECT id, codigo, descripcion, precio FROM productos WHERE codigo = ? LIMIT 1');
$stmt->execute([$codigo]);
$prod = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$prod) {
    echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
    exit();
}

echo json_encode([
    'success' => true,
    'producto' => [
        'id' => (int)$prod['id'],
        'codigo' => $prod['codigo'],
        'descripcion' => $prod['descripcion'],
        'precio' => (float)$prod['precio']
    ]
]);
?>
