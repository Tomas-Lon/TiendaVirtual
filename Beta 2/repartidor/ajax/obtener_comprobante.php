<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

require_once '../../config/database.php';

header('Content-Type: application/json');

try {
    $pdo = getConnection();
    $entrega_id = $_GET['entrega_id'] ?? null;
    
    if (!$entrega_id) {
        throw new Exception('ID de entrega requerido');
    }
    
    // Obtener comprobante
    $stmt = $pdo->prepare("
        SELECT pdf_path FROM comprobantes_entrega
        WHERE entrega_id = ?
        LIMIT 1
    ");
    $stmt->execute([$entrega_id]);
    $comprobante = $stmt->fetch();
    
    if ($comprobante && $comprobante['pdf_path']) {
        echo json_encode([
            'success' => true,
            'comprobante_url' => $comprobante['pdf_path']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No se encontró comprobante'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
