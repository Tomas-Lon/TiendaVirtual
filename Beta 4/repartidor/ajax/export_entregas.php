<?php
session_start();

// Verificar autenticación y rol de repartidor
if (!isset($_SESSION['usuario']) || ($_SESSION['cargo'] ?? '') !== 'repartidor') {
    http_response_code(401);
    echo 'No autorizado';
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../admin/classes/CSVExporter.php';

$pdo = getConnection();
$repartidor_id = $_SESSION['empleado_id'] ?? 0;

$fecha_desde = $_GET['fecha_desde'] ?? '';
$fecha_hasta = $_GET['fecha_hasta'] ?? '';
$estado = $_GET['estado'] ?? '';

$where = ['e.repartidor_id = ?'];
$params = [$repartidor_id];

if ($fecha_desde !== '') {
    $where[] = 'DATE(e.fecha_programada) >= ?';
    $params[] = $fecha_desde;
}
if ($fecha_hasta !== '') {
    $where[] = 'DATE(e.fecha_programada) <= ?';
    $params[] = $fecha_hasta;
}
if ($estado !== '' && $estado !== 'todas') {
    $where[] = 'e.estado = ?';
    $params[] = $estado;
}

$whereSql = implode(' AND ', $where);

$sql = "SELECT e.id as entrega_id, e.estado, e.fecha_programada, p.numero_documento, c.nombre as cliente_nombre, p.total as pedido_total, dir.direccion as direccion_entrega
        FROM envios e
        INNER JOIN pedidos p ON e.pedido_id = p.id
        INNER JOIN clientes c ON p.cliente_id = c.id
        LEFT JOIN direcciones_clientes dir ON e.direccion_entrega_id = dir.id
        WHERE {$whereSql}
        ORDER BY e.fecha_programada ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$headers = ['EntregaID', 'Pedido', 'Fecha Entrega', 'Cliente', 'Estado', 'Dirección', 'Total'];
$outRows = [];

foreach ($rows as $r) {
    $fecha = $r['fecha_programada'] ? date('Y-m-d H:i:s', strtotime($r['fecha_programada'])) : '';
    $estado_text = ucfirst(str_replace('_', ' ', $r['estado'] ?? ''));
    $total = is_numeric($r['pedido_total']) ? (float)$r['pedido_total'] : null;

    $outRows[] = [
        $r['entrega_id'],
        $r['numero_documento'] ?? '',
        $fecha,
        $r['cliente_nombre'] ?? '',
        $estado_text,
        $r['direccion_entrega'] ?? '',
        $total
    ];
}

CSVExporter::rawExport($headers, $outRows, 'entregas_' . date('Y-m-d') . '.csv');

?>
