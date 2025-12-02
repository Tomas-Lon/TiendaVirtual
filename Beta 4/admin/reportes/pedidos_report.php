<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$pdo = getConnection();

$fecha_desde = $_GET['fecha_desde'] ?? '';
$fecha_hasta = $_GET['fecha_hasta'] ?? '';
$estado = $_GET['estado'] ?? '';
$limit = intval($_GET['limit'] ?? 0);

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

$whereSql = implode(' AND ', $where);

$sql = "SELECT p.numero_documento, p.fecha_pedido, p.estado, p.total, c.nombre as cliente_nombre, c.email as cliente_email
        FROM pedidos p
        LEFT JOIN clientes c ON p.cliente_id = c.id
        WHERE {$whereSql}
        ORDER BY p.fecha_pedido DESC";

if ($limit > 0) {
    $sql .= ' LIMIT ' . $limit;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

function esc($v) { return htmlspecialchars($v); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Pedidos - Reporte</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; color: #222; }
        .header { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f5f5f5; }
        .text-right { text-align: right; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="header">
        <h2>Reporte de Pedidos</h2>
        <div>
            Período: <?php echo $fecha_desde ? esc($fecha_desde) : '—'; ?> — <?php echo $fecha_hasta ? esc($fecha_hasta) : '—'; ?>
        </div>
    </div>

    <div class="no-print" style="margin-bottom:10px;">
        <button onclick="window.print()">Imprimir</button>
    </div>

    <table>
        <thead>
            <tr>
                <th>Número</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Estado</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="5" style="text-align:center;">No hay pedidos para los filtros seleccionados</td></tr>
            <?php else: ?>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?php echo esc($r['numero_documento']); ?></td>
                    <td><?php echo esc(date('d/m/Y H:i', strtotime($r['fecha_pedido']))); ?></td>
                    <td><?php echo esc($r['cliente_nombre']); ?> <br><small><?php echo esc($r['cliente_email']); ?></small></td>
                    <td><?php echo esc(ucfirst(str_replace('_',' ',$r['estado']))); ?></td>
                    <td class="text-right">$<?php echo number_format($r['total'],2); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
        // auto print shortly after render
        setTimeout(function(){ window.print(); }, 500);
    </script>
</body>
</html>
