<?php
session_start();
header('Content-Type: application/json');

// Verificar autenticación de empleado (admin)
if (!isset($_SESSION['empleado_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

// Solo CSV
if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Archivo CSV no recibido']);
    exit();
}

$file = $_FILES['file'];
$maxSize = 2 * 1024 * 1024; // 2MB
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Error de subida: ' . $file['error']]);
    exit();
}
if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'message' => 'El CSV excede 2MB']);
    exit();
}
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($ext !== 'csv') {
    echo json_encode(['success' => false, 'message' => 'Solo se permiten archivos .csv']);
    exit();
}

require_once '../../config/database.php';
$pdo = getConnection();

$fh = fopen($file['tmp_name'], 'r');
if (!$fh) {
    echo json_encode(['success' => false, 'message' => 'No se pudo leer el CSV']);
    exit();
}

$items = [];
$unknown = [];
$warnings = [];
$lineNum = 0;

// Permitimos separadores ; o , y trim con posibles comillas
while (($row = fgetcsv($fh, 0, ",")) !== false) {
    $lineNum++;
    if ($row === [null] || count($row) === 0) continue;
    // Si solo vino una columna, tal vez separador es ;
    if (count($row) === 1 && strpos($row[0], ';') !== false) {
        $row = str_getcsv($row[0], ';');
    }
    // Esperamos: codigo, cantidad
    if (count($row) < 2) {
        $warnings[] = "Línea $lineNum: columnas insuficientes";
        continue;
    }
    $codigo = trim($row[0]);
    $cantidadStr = trim($row[1]);
    if ($codigo === '') { $warnings[] = "Línea $lineNum: código vacío"; continue; }
    $cantidad = (int)preg_replace('/[^0-9]/', '', $cantidadStr);
    if ($cantidad <= 0) { $warnings[] = "Línea $lineNum: cantidad inválida"; continue; }

    // Buscar producto por código exacto
    $stmt = $pdo->prepare('SELECT id, codigo, descripcion, precio FROM productos WHERE codigo = ? LIMIT 1');
    $stmt->execute([$codigo]);
    $prod = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$prod) {
        $unknown[] = $codigo;
        continue;
    }
    $items[] = [
        'id' => (int)$prod['id'],
        'codigo' => $prod['codigo'],
        'descripcion' => $prod['descripcion'],
        'precio' => (float)$prod['precio'],
        'cantidad' => $cantidad
    ];
}
fclose($fh);

echo json_encode([
    'success' => true,
    'items' => $items,
    'unknown' => $unknown,
    'warnings' => $warnings
]);
?>
