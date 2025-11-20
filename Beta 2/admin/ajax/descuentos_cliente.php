<?php
/**
 * API para obtener descuentos de un cliente específico
 * Utilizado para widgets y llamadas AJAX
 */

session_start();

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

require_once '../config/database.php';
require_once '../includes/AdminHelpers.php';

$pdo = getConnection();

// Validar parámetros
$cliente_id = $_GET['cliente_id'] ?? null;

if (!$cliente_id || !is_numeric($cliente_id)) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de cliente inválido']);
    exit;
}

try {
    // Obtener información del cliente
    $cliente_stmt = $pdo->prepare("SELECT nombre, numero_documento FROM clientes WHERE id = ? AND activo = 1");
    $cliente_stmt->execute([intval($cliente_id)]);
    $cliente = $cliente_stmt->fetch();
    
    if (!$cliente) {
        http_response_code(404);
        echo json_encode(['error' => 'Cliente no encontrado']);
        exit;
    }
    
    // Obtener descuentos activos del cliente
    $descuentos_stmt = $pdo->prepare("
        SELECT dc.porcentaje_descuento, gp.nombre as grupo_nombre, gp.id as grupo_id
        FROM descuentos_clientes dc 
        JOIN grupos_productos gp ON dc.grupo_id = gp.id 
        WHERE dc.cliente_id = ? AND dc.activo = 1 AND gp.activo = 1
        ORDER BY dc.porcentaje_descuento DESC
    ");
    $descuentos_stmt->execute([intval($cliente_id)]);
    $descuentos = $descuentos_stmt->fetchAll();
    
    // Calcular estadísticas
    $total_descuentos = count($descuentos);
    $descuento_promedio = $total_descuentos > 0 ? 
        array_sum(array_column($descuentos, 'porcentaje_descuento')) / $total_descuentos : 0;
    $descuento_maximo = $total_descuentos > 0 ? 
        max(array_column($descuentos, 'porcentaje_descuento')) : 0;
    
    $response = [
        'success' => true,
        'cliente' => $cliente,
        'descuentos' => $descuentos,
        'estadisticas' => [
            'total_descuentos' => $total_descuentos,
            'descuento_promedio' => round($descuento_promedio, 2),
            'descuento_maximo' => $descuento_maximo
        ]
    ];
    
    header('Content-Type: application/json');
    echo json_encode($response);
    
} catch (PDOException $e) {
    error_log("Error al obtener descuentos del cliente: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor']);
}
?>
