<?php
session_start();

if (!isset($_SESSION['usuario']) || ($_SESSION['tipo'] ?? '') !== 'cliente') {
    header('HTTP/1.1 403 Forbidden');
    echo 'Acceso no autorizado';
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/paths.php';

$pdo = getConnection();
$cliente_id = $_SESSION['cliente_id'];

$search = trim($_GET['search'] ?? '');
$fecha_desde = $_GET['fecha_desde'] ?? '';
$fecha_hasta = $_GET['fecha_hasta'] ?? '';

$whereParts = ['p.cliente_id = ?'];
$params = [$cliente_id];

if ($search !== '') {
    $whereParts[] = '(p.numero_documento LIKE ? OR p.observaciones LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}
if ($fecha_desde !== '') {
    $whereParts[] = 'DATE(p.fecha_pedido) >= ?';
    $params[] = $fecha_desde;
}
if ($fecha_hasta !== '') {
    $whereParts[] = 'DATE(p.fecha_pedido) <= ?';
    $params[] = $fecha_hasta;
}

$whereSql = 'WHERE ' . implode(' AND ', $whereParts);

$sql = "SELECT p.id, p.numero_documento, p.fecha_pedido, p.estado, p.total,
            (SELECT COUNT(*) FROM detalle_pedidos dp WHERE dp.pedido_id = p.id) AS total_items,
            comp.pdf_path as comprobante_pdf
        FROM pedidos p
        LEFT JOIN entregas ent ON ent.pedido_id = p.id
        LEFT JOIN comprobantes_entrega comp ON comp.entrega_id = ent.id
        " . $whereSql . " ORDER BY p.fecha_pedido DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Output CSV
$filename = 'mis_pedidos_' . date('Ymd') . '.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// BOM for Excel
echo "\xEF\xBB\xBF";
$out = fopen('php://output', 'w');

// Header row (tabla simple: Número;Fecha;Items;Estado;Total)
fputcsv($out, ['Número', 'Fecha', 'Items', 'Estado', 'Total'], ';');

foreach ($rows as $r) {
    // Fecha en formato d/m/Y H:i (como en la vista)
    $fecha = $r['fecha_pedido'] ? date('d/m/Y H:i', strtotime($r['fecha_pedido'])) : '';
    $items = (int)($r['total_items'] ?? 0);
    $estado = ucfirst(str_replace('_', ' ', $r['estado']));

    // Formatear Total para que se parezca a la tabla: sin decimales y miles con punto, prefijo $
    if (is_numeric($r['total'])) {
        $total_formatted = '$' . number_format((float)$r['total'], 0, ',', '.');
    } else {
        $total_formatted = $r['total'];
    }

    // Escribir fila usando ; como separador (más compatible con Excel local)
    fputcsv($out, [$r['numero_documento'], $fecha, $items, $estado, $total_formatted], ';');
}

fclose($out);
exit();
