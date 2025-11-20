<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario']) || ($_SESSION['tipo'] ?? '') !== 'cliente') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

$clienteId = $_SESSION['cliente_id'] ?? null;
if (!$clienteId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de cliente no encontrado']);
    exit();
}

if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Archivo no recibido']);
    exit();
}

$file = $_FILES['file'];
$maxSize = 10 * 1024 * 1024; // 10MB
$allowedExt = ['pdf','jpg','jpeg','png','doc','docx'];
$allowedMime = [
    'application/pdf',
    'image/jpeg',
    'image/png',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
];

if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Error en la subida: código ' . $file['error']]);
    exit();
}

if ($file['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'El archivo excede el tamaño máximo (10MB).']);
    exit();
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowedExt)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tipo de archivo no permitido.']);
    exit();
}

// Validación MIME básica (no infalible)
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);
if ($mime && !in_array($mime, $allowedMime)) {
    // Permitimos algunos casos donde el servidor no detecta bien DOCX como zip
    if (!($ext === 'docx' && $mime === 'application/zip')) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El tipo MIME no es válido.']);
        exit();
    }
}

// Construir ruta de destino: uploads/clientes/{clienteId}/nueva_compra/{YmdHis}/
$baseDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'clientes' . DIRECTORY_SEPARATOR . $clienteId . DIRECTORY_SEPARATOR . 'nueva_compra';
if (!is_dir($baseDir)) {
    if (!mkdir($baseDir, 0775, true)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'No se pudo crear el directorio de destino.']);
        exit();
    }
}

// Usar una subcarpeta por día para evitar miles de archivos en un solo lugar
$subDir = $baseDir . DIRECTORY_SEPARATOR . date('Ymd');
if (!is_dir($subDir)) {
    mkdir($subDir, 0775, true);
}

// Normalizar nombre y evitar colisiones
$sanitizedBase = preg_replace('/[^A-Za-z0-9_.-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
$unique = bin2hex(random_bytes(4));
$finalName = $sanitizedBase . '_' . date('His') . '_' . $unique . '.' . $ext;
$destPath = $subDir . DIRECTORY_SEPARATOR . $finalName;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'No se pudo guardar el archivo.']);
    exit();
}

// Construir URL relativa para servir el archivo si el servidor expone /uploads
$relativeUrl = '/uploads/clientes/' . rawurlencode((string)$clienteId) . '/nueva_compra/' . rawurlencode(date('Ymd')) . '/' . rawurlencode($finalName);

// Guardar metadata mínima en sesión para enlazar con la solicitud (si se desea)
$_SESSION['nueva_compra_archivos'] = $_SESSION['nueva_compra_archivos'] ?? [];
$_SESSION['nueva_compra_archivos'][] = [
    'name' => $finalName,
    'original' => $file['name'],
    'size' => (int) $file['size'],
    'path' => $destPath,
    'url' => $relativeUrl,
    'uploaded_at' => date('c')
];

echo json_encode([
    'success' => true,
    'message' => 'Archivo subido correctamente',
    'fileName' => $finalName,
    'originalName' => $file['name'],
    'size' => (int) $file['size'],
    'url' => $relativeUrl
]);
