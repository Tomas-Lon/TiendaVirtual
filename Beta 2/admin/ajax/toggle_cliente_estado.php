<?php
session_start();
require_once '../../config/database.php';

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Método no permitido']));
}

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode(['error' => 'No autorizado']));
}

// Validar parámetros
if (!isset($_POST['id']) || !isset($_POST['estado'])) {
    http_response_code(400);
    die(json_encode(['error' => 'Parámetros incompletos']));
}

try {
    $pdo = getConnection();
    
    // Preparar la consulta
    $stmt = $pdo->prepare("UPDATE clientes SET activo = ?, updated_at = NOW() WHERE id = ?");
    
    // Ejecutar la actualización
    $resultado = $stmt->execute([
        $_POST['estado'],
        $_POST['id']
    ]);

    if ($resultado) {
        echo json_encode([
            'success' => true,
            'message' => 'Estado actualizado correctamente',
            'nuevo_estado' => $_POST['estado']
        ]);
    } else {
        throw new Exception('Error al actualizar el estado');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error al actualizar el estado',
        'message' => $e->getMessage()
    ]);
}
?>
