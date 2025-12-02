<?php
session_start();
header('Content-Type: application/json');

// Verificar autenticación
if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once '../../config/database.php';

$cliente_id = isset($_GET['cliente_id']) ? (int)$_GET['cliente_id'] : 0;

if ($cliente_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de cliente inválido']);
    exit;
}

try {
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("
        SELECT id, nombre, direccion, ciudad, departamento, telefono, es_principal,
               contacto_receptor, documento_receptor
        FROM direcciones_clientes
        WHERE cliente_id = ? AND activo = 1 AND es_envio = 1
        ORDER BY es_principal DESC, nombre ASC
    ");
    $stmt->execute([$cliente_id]);
    $direcciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'direcciones' => $direcciones
    ]);
    
} catch (Exception $e) {
    error_log("Error en get_direcciones_cliente.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener direcciones: ' . $e->getMessage()
    ]);
}
?>
