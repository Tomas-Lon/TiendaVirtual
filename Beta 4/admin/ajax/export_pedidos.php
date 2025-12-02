<?php
session_start();

// Verificar autenticación de admin/empleado
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo 'No autorizado';
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../classes/CSVExporter.php';

$pdo = getConnection();

$fecha_desde = $_GET['fecha_desde'] ?? '';
$fecha_hasta = $_GET['fecha_hasta'] ?? '';
$estado = $_GET['estado'] ?? '';
$cliente = $_GET['cliente_id'] ?? '';

$where = ['1=1'];
$params = [];

if ($fecha_desde !== '') {
    $where[] = 'DATE(p.fecha_pedido) >= ?';
    $params[] = $fecha_desde;
}
if ($fecha_hasta !== '') {
    $where[] = 'DATE(p.fecha_pedido) <= ?';
    $params[] = $fecha_hasta;
}
if ($estado !== '') {
    $where[] = 'p.estado = ?';
    $params[] = $estado;
}
if ($cliente !== '') {
    $where[] = 'p.cliente_id = ?';
    $params[] = $cliente;
}

$whereSql = implode(' AND ', $where);

$sql = "SELECT p.id, p.numero_documento, p.fecha_pedido, p.estado, p.total, c.nombre as cliente_nombre, e.repartidor_id
        FROM pedidos p
        LEFT JOIN clientes c ON p.cliente_id = c.id
        LEFT JOIN envios e ON e.pedido_id = p.id
        WHERE {$whereSql}
        ORDER BY p.fecha_pedido DESC";
$limit = intval($_GET['limit'] ?? 0);
if ($limit > 0) {
    $sql .= ' LIMIT ' . $limit;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$headers = ['Número', 'Fecha', 'Cliente', 'Estado', 'Total'];
$outRows = [];

foreach ($rows as $r) {
    $fecha = $r['fecha_pedido'] ? date('Y-m-d H:i:s', strtotime($r['fecha_pedido'])) : '';
    $cliente_nombre = $r['cliente_nombre'] ?? '';
    $estado_text = ucfirst(str_replace('_', ' ', $r['estado'] ?? ''));
    $total = is_numeric($r['total']) ? (float)$r['total'] : null;

    $outRows[] = [
        $r['numero_documento'] ?? '',
        $fecha,
        $cliente_nombre,
        $estado_text,
        $total
    ];
}

CSVExporter::rawExport($headers, $outRows, 'pedidos_' . date('Y-m-d') . '.csv');

?>
