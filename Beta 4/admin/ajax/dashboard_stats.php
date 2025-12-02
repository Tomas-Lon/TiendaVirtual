<?php
session_start();

// Verificar autenticación
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'empleado' || $_SESSION['cargo'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

require_once '../../config/database.php';

try {
    $pdo = getConnection();
    
    // Obtener estadísticas actualizadas usando las tablas correctas
    $stats = [];
    
    // Total productos (sin campo activo en la tabla productos)
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM productos");
    $result = $stmt->fetch();
    $stats['total_productos'] = (int)$result['total'];
    
    // Total clientes activos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM clientes WHERE activo = 1");
    $result = $stmt->fetch();
    $stats['total_clientes'] = (int)$result['total'];
    
    // Total pedidos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pedidos");
    $result = $stmt->fetch();
    $stats['total_pedidos'] = (int)$result['total'];
    
    // Total envíos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM envios");
    $result = $stmt->fetch();
    $stats['total_envios'] = (int)$result['total'];
    
    // Timestamp de actualización
    $stats['ultima_actualizacion'] = date('Y-m-d H:i:s');
    
    echo json_encode([
        'success' => true,
        'data' => $stats,
        'timestamp' => time()
    ]);
    
} catch (PDOException $e) {
    error_log("Error en dashboard_stats.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener estadísticas',
        'error' => $e->getMessage()
    ]);
}
?>
