<?php
session_start();

header('Content-Type: application/json');

// Verificar sesión de cliente
if (!isset($_SESSION['usuario']) || ($_SESSION['tipo'] ?? '') !== 'cliente') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Validar entrada
$productoId = isset($_POST['producto_id']) ? (int)$_POST['producto_id'] : 0;
$cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;
if ($productoId <= 0 || $cantidad <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

// Validar que el producto exista en BD
require_once '../../config/database.php';
try {
    $pdo = getConnection();
    $stmt = $pdo->prepare('SELECT id FROM productos WHERE id = ? LIMIT 1');
    $stmt->execute([$productoId]);
    $exists = (bool)$stmt->fetchColumn();
    if (!$exists) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
        exit;
    }
} catch (Exception $e) {
    error_log('Error validar producto en agregar_al_carrito: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error del servidor']);
    exit;
}

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Agregar o incrementar cantidad
if (isset($_SESSION['carrito'][$productoId])) {
    $_SESSION['carrito'][$productoId] += $cantidad;
} else {
    $_SESSION['carrito'][$productoId] = $cantidad;
}

// Mantener contador de carrito para badges dinámicos
$_SESSION['cart_count'] = array_sum($_SESSION['carrito']);

echo json_encode([
    'success' => true,
    'message' => 'Producto agregado al carrito',
    'carrito_count' => $_SESSION['cart_count']
]);
?>