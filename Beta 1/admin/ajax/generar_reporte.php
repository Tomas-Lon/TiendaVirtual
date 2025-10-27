<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    exit('No autorizado');
}

$pdo = getConnection();

$tipo = $_POST['tipo'] ?? '';
$formato = $_POST['formato'] ?? 'pdf';
$fecha_inicio = $_POST['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_POST['fecha_fin'] ?? date('Y-m-d');

if ($formato === 'excel') {
    // Exportar a Excel (CSV)
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="reporte_ventas_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Encabezados BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    if ($tipo === 'ventas') {
        // Reporte de ventas
        fputcsv($output, ['Fecha', 'Total Pedidos', 'Total Ventas', 'Promedio Venta'], ';');
        
        $sql = "SELECT 
                    DATE(p.fecha_pedido) as fecha,
                    COUNT(p.id) as total_pedidos,
                    SUM(p.total) as total_ventas,
                    AVG(p.total) as promedio_venta
                FROM pedidos p 
                WHERE DATE(p.fecha_pedido) BETWEEN ? AND ? 
                AND p.estado NOT IN ('cancelado', 'borrador')
                GROUP BY DATE(p.fecha_pedido)
                ORDER BY fecha DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$fecha_inicio, $fecha_fin]);
        
        while ($row = $stmt->fetch()) {
            fputcsv($output, [
                $row['fecha'],
                $row['total_pedidos'],
                number_format($row['total_ventas'], 2),
                number_format($row['promedio_venta'], 2)
            ], ';');
        }
        
    } elseif ($tipo === 'productos') {
        // Reporte de productos
        fputcsv($output, ['Código', 'Descripción', 'Cantidad Vendida', 'Total Ingresos', 'Pedidos'], ';');
        
        $sql = "SELECT 
                    p.codigo,
                    p.descripcion,
                    SUM(dp.cantidad) as total_vendido,
                    SUM(dp.subtotal) as total_ingresos,
                    COUNT(DISTINCT dp.pedido_id) as pedidos_count
                FROM detalle_pedidos dp
                INNER JOIN productos p ON dp.producto_id = p.id
                INNER JOIN pedidos ped ON dp.pedido_id = ped.id
                WHERE DATE(ped.fecha_pedido) BETWEEN ? AND ?
                AND ped.estado NOT IN ('cancelado', 'borrador')
                GROUP BY p.id, p.codigo, p.descripcion
                ORDER BY total_vendido DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$fecha_inicio, $fecha_fin]);
        
        while ($row = $stmt->fetch()) {
            fputcsv($output, [
                $row['codigo'],
                $row['descripcion'],
                $row['total_vendido'],
                number_format($row['total_ingresos'], 2),
                $row['pedidos_count']
            ], ';');
        }
        
    } elseif ($tipo === 'clientes') {
        // Reporte de clientes
        fputcsv($output, ['Cliente', 'Email', 'Total Pedidos', 'Total Comprado'], ';');
        
        $sql = "SELECT 
                    c.nombre,
                    c.email,
                    COUNT(p.id) as total_pedidos,
                    SUM(p.total) as total_comprado
                FROM clientes c
                INNER JOIN pedidos p ON c.id = p.cliente_id
                WHERE DATE(p.fecha_pedido) BETWEEN ? AND ?
                AND p.estado NOT IN ('cancelado', 'borrador')
                GROUP BY c.id, c.nombre, c.email
                ORDER BY total_comprado DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$fecha_inicio, $fecha_fin]);
        
        while ($row = $stmt->fetch()) {
            fputcsv($output, [
                $row['nombre'],
                $row['email'],
                $row['total_pedidos'],
                number_format($row['total_comprado'], 2)
            ], ';');
        }
    }
    
    fclose($output);
    exit;
}

// Para PDF - generar HTML mejorado
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ventas</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            background: white;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
        }
        
        .company-logo {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        
        .report-title {
            font-size: 20px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .report-period {
            color: #666;
            font-size: 14px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .stat-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .section {
            margin: 40px 0;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        
        th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .text-right {
            text-align: right;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
            
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-logo">🏪 Sistema de Tienda Virtual</div>
        <div class="report-title">Reporte de Ventas y Estadísticas</div>
        <div class="report-period">
            Período: <?php echo date('d/m/Y', strtotime($fecha_inicio)); ?> - <?php echo date('d/m/Y', strtotime($fecha_fin)); ?>
        </div>
        <div class="report-period">
            Generado el: <?php echo date('d/m/Y H:i:s'); ?>
        </div>
    </div>

    <?php
    // Estadísticas generales
    $stats_sql = "SELECT 
                    COUNT(*) as total_pedidos,
                    SUM(total) as total_ventas,
                    AVG(total) as ticket_promedio
                  FROM pedidos 
                  WHERE DATE(fecha_pedido) BETWEEN ? AND ? 
                  AND estado NOT IN ('cancelado', 'borrador')";
    $stats_stmt = $pdo->prepare($stats_sql);
    $stats_stmt->execute([$fecha_inicio, $fecha_fin]);
    $stats = $stats_stmt->fetch();
    ?>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?php echo number_format($stats['total_pedidos']); ?></div>
            <div class="stat-label">Total Pedidos</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">$<?php echo number_format($stats['total_ventas'], 2); ?></div>
            <div class="stat-label">Total Ventas</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">$<?php echo number_format($stats['ticket_promedio'], 2); ?></div>
            <div class="stat-label">Ticket Promedio</div>
        </div>
    </div>

    <!-- Ventas por Día -->
    <div class="section">
        <div class="section-title">📊 Ventas por Día</div>
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th class="text-right">Pedidos</th>
                    <th class="text-right">Total Ventas</th>
                    <th class="text-right">Promedio</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $ventas_sql = "SELECT 
                                DATE(p.fecha_pedido) as fecha,
                                COUNT(p.id) as total_pedidos,
                                SUM(p.total) as total_ventas,
                                AVG(p.total) as promedio_venta
                               FROM pedidos p 
                               WHERE DATE(p.fecha_pedido) BETWEEN ? AND ? 
                               AND p.estado NOT IN ('cancelado', 'borrador')
                               GROUP BY DATE(p.fecha_pedido)
                               ORDER BY fecha DESC
                               LIMIT 15";
                $ventas_stmt = $pdo->prepare($ventas_sql);
                $ventas_stmt->execute([$fecha_inicio, $fecha_fin]);
                
                while ($venta = $ventas_stmt->fetch()):
                ?>
                <tr>
                    <td><?php echo date('d/m/Y', strtotime($venta['fecha'])); ?></td>
                    <td class="text-right"><?php echo number_format($venta['total_pedidos']); ?></td>
                    <td class="text-right">$<?php echo number_format($venta['total_ventas'], 2); ?></td>
                    <td class="text-right">$<?php echo number_format($venta['promedio_venta'], 2); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Productos Más Vendidos -->
    <div class="section">
        <div class="section-title">🏆 Top 10 Productos Más Vendidos</div>
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Descripción</th>
                    <th class="text-right">Cantidad</th>
                    <th class="text-right">Ingresos</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $productos_sql = "SELECT 
                                    p.codigo,
                                    p.descripcion,
                                    SUM(dp.cantidad) as total_vendido,
                                    SUM(dp.subtotal) as total_ingresos
                                  FROM detalle_pedidos dp
                                  INNER JOIN productos p ON dp.producto_id = p.id
                                  INNER JOIN pedidos ped ON dp.pedido_id = ped.id
                                  WHERE DATE(ped.fecha_pedido) BETWEEN ? AND ?
                                  AND ped.estado NOT IN ('cancelado', 'borrador')
                                  GROUP BY p.id, p.codigo, p.descripcion
                                  ORDER BY total_vendido DESC
                                  LIMIT 10";
                $productos_stmt = $pdo->prepare($productos_sql);
                $productos_stmt->execute([$fecha_inicio, $fecha_fin]);
                
                while ($producto = $productos_stmt->fetch()):
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($producto['codigo']); ?></td>
                    <td><?php echo htmlspecialchars($producto['descripcion']); ?></td>
                    <td class="text-right"><?php echo number_format($producto['total_vendido']); ?></td>
                    <td class="text-right">$<?php echo number_format($producto['total_ingresos'], 2); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Clientes Más Activos -->
    <div class="section">
        <div class="section-title">👥 Top 10 Clientes Más Activos</div>
        <table>
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Email</th>
                    <th class="text-right">Pedidos</th>
                    <th class="text-right">Total Comprado</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $clientes_sql = "SELECT 
                                   c.nombre,
                                   c.email,
                                   COUNT(p.id) as total_pedidos,
                                   SUM(p.total) as total_comprado
                                 FROM clientes c
                                 INNER JOIN pedidos p ON c.id = p.cliente_id
                                 WHERE DATE(p.fecha_pedido) BETWEEN ? AND ?
                                 AND p.estado NOT IN ('cancelado', 'borrador')
                                 GROUP BY c.id, c.nombre, c.email
                                 ORDER BY total_comprado DESC
                                 LIMIT 10";
                $clientes_stmt = $pdo->prepare($clientes_sql);
                $clientes_stmt->execute([$fecha_inicio, $fecha_fin]);
                
                while ($cliente = $clientes_stmt->fetch()):
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($cliente['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                    <td class="text-right"><?php echo number_format($cliente['total_pedidos']); ?></td>
                    <td class="text-right">$<?php echo number_format($cliente['total_comprado'], 2); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Reporte generado automáticamente por el Sistema de Tienda Virtual</p>
        <p>Este documento contiene información confidencial de la empresa</p>
    </div>

    <script>
        // Auto-imprimir cuando se abre
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>
