<?php
session_start();
require_once '../../config/database.php';

// Verificar autenticación
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'empleado' || $_SESSION['cargo'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

$pedido_id = $_GET['id'] ?? 0;

if (!$pedido_id) {
    die('ID de pedido no válido');
}

try {
    $pdo = getConnection();
    
    // Obtener información del pedido
    $stmt = $pdo->prepare("
        SELECT p.*, c.nombre as cliente_nombre, c.email as cliente_email, 
               c.telefono as cliente_telefono, c.numero_documento as cliente_documento,
               c.tipo_documento as cliente_tipo_documento,
               e.nombre as empleado_nombre,
               dc.direccion, dc.ciudad, dc.departamento, dc.codigo_postal
        FROM pedidos p 
        JOIN clientes c ON p.cliente_id = c.id 
        LEFT JOIN empleados e ON p.empleado_id = e.id
        LEFT JOIN direcciones_clientes dc ON p.direccion_entrega_id = dc.id
        WHERE p.id = ?
    ");
    $stmt->execute([$pedido_id]);
    $pedido = $stmt->fetch();
    
    if (!$pedido) {
        die('Pedido no encontrado');
    }
    
    // Obtener detalles del pedido
    $stmt = $pdo->prepare("
        SELECT dp.*, pr.codigo, pr.descripcion as producto_descripcion
        FROM detalle_pedidos dp
        JOIN productos pr ON dp.producto_id = pr.id
        WHERE dp.pedido_id = ?
        ORDER BY dp.id
    ");
        // Obtener detalles del pedido (incluye grupo)
        $stmt = $pdo->prepare("
            SELECT dp.*, pr.codigo, pr.descripcion as producto_descripcion, pr.grupo_id, gp.nombre AS grupo_nombre
            FROM detalle_pedidos dp
            JOIN productos pr ON dp.producto_id = pr.id
            LEFT JOIN grupos_productos gp ON pr.grupo_id = gp.id
            WHERE dp.pedido_id = ?
            ORDER BY dp.id
        ");
        $stmt->execute([$pedido_id]);
        $detalles = $stmt->fetchAll();

        // Obtener descuentos por grupo asignados al cliente
        $stmt = $pdo->prepare("SELECT dc.grupo_id, gp.nombre AS grupo_nombre, dc.porcentaje_descuento
                                FROM descuentos_clientes dc
                                JOIN grupos_productos gp ON gp.id = dc.grupo_id
                                WHERE dc.cliente_id = ? AND dc.activo = 1 AND gp.activo = 1
                                ORDER BY gp.nombre");
        $stmt->execute([$pedido['cliente_id']]);
        $descuentos_cliente = $stmt->fetchAll();
    
    $estados = [
        'borrador' => 'Borrador',
        'confirmado' => 'Confirmado',
        'en_preparacion' => 'En Preparación',
        'listo_envio' => 'Listo para Envío',
        'enviado' => 'Enviado',
        'entregado' => 'Entregado',
        'cancelado' => 'Cancelado'
    ];
    
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido <?php echo htmlspecialchars($pedido['numero_documento']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; }
            .container { max-width: 100% !important; }
        }
        .company-header {
            border-bottom: 2px solid #007bff;
            margin-bottom: 30px;
            padding-bottom: 20px;
        }
        .table td, .table th {
            padding: 8px;
            font-size: 14px;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin-bottom: 20px;
        }
        @media print {
            .info-box {
                background-color: transparent !important;
                border: 1px solid #dee2e6 !important;
            }
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="company-header text-center">
            <h2>SOLTECIND</h2>
            <p class="mb-0">Soluciones Técnicas Industriales</p>
            <small class="text-muted">Suministros Industriales y Técnicos</small>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <h4>PEDIDO <?php echo htmlspecialchars($pedido['numero_documento']); ?></h4>
                <p><strong>Estado:</strong> <?php echo $estados[$pedido['estado']]; ?></p>
                <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($pedido['created_at'])); ?></p>
                <p><strong>Fecha Entrega:</strong> <?php echo $pedido['fecha_entrega_estimada'] ? date('d/m/Y', strtotime($pedido['fecha_entrega_estimada'])) : 'No definida'; ?></p>
            </div>
            <div class="col-md-6 text-end">
                <p><strong>Empleado:</strong> <?php echo htmlspecialchars($pedido['empleado_nombre'] ?? 'No asignado'); ?></p>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <h5>DATOS DEL CLIENTE</h5>
                <p><strong><?php echo htmlspecialchars($pedido['cliente_nombre']); ?></strong></p>
                <p><?php echo htmlspecialchars($pedido['cliente_tipo_documento']); ?>: <?php echo htmlspecialchars($pedido['cliente_documento']); ?></p>
                <?php if ($pedido['cliente_email']): ?>
                <p>Email: <?php echo htmlspecialchars($pedido['cliente_email']); ?></p>
                <?php endif; ?>
                <?php if ($pedido['cliente_telefono']): ?>
                <p>Teléfono: <?php echo htmlspecialchars($pedido['cliente_telefono']); ?></p>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <?php if ($pedido['direccion']): ?>
                <h5>DIRECCIÓN DE ENTREGA</h5>
                <p>
                    <?php echo htmlspecialchars($pedido['direccion']); ?><br>
                    <?php echo htmlspecialchars($pedido['ciudad'] . ', ' . $pedido['departamento']); ?><br>
                    CP: <?php echo htmlspecialchars($pedido['codigo_postal']); ?>
                </p>
                <?php endif; ?>
            </div>
        </div>
        
        <h5>PRODUCTOS</h5>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>Grupo</th>
                        <th class="text-end">Cant.</th>
                        <th class="text-end">Precio Unit.</th>
                        <th class="text-end">Desc. %</th>
                        <th class="text-end">Desc. $</th>
                        <th class="text-end">Subtotal bruto</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($detalles)): ?>
                    <tr>
                            <td colspan="8" class="text-center">No hay productos en este pedido</td>
                    </tr>
                <?php else: ?>
                        <?php foreach ($detalles as $detalle): 
                            $bruto = (float)($detalle['subtotal'] ?? 0);
                            if ($bruto <= 0) { $bruto = $detalle['cantidad'] * $detalle['precio_unitario']; }
                            $desc_pct = (float)($detalle['descuento_porcentaje'] ?? 0);
                            $desc_monto = (float)($detalle['descuento_monto'] ?? 0);
                        ?>
                    <tr>
                            <td><?php echo htmlspecialchars($detalle['codigo']); ?></td>
                            <td><?php echo htmlspecialchars($detalle['producto_descripcion']); ?></td>
                            <td><?php echo htmlspecialchars($detalle['grupo_nombre'] ?? ''); ?></td>
                            <td class="text-end"><?php echo number_format($detalle['cantidad'], 0, '.', ','); ?></td>
                            <td class="text-end">$<?php echo number_format($detalle['precio_unitario'], 0, '.', ','); ?></td>
                            <td class="text-end"><?php echo number_format($desc_pct, 2); ?>%</td>
                            <td class="text-end">$<?php echo number_format($desc_monto, 0, '.', ','); ?></td>
                            <td class="text-end">$<?php echo number_format($bruto, 0, '.', ','); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <?php
                $subtotal = (float)($pedido['subtotal'] ?? 0);
                $desc_pct = (float)($pedido['descuento_porcentaje'] ?? 0);
                $desc_total = (float)($pedido['descuento_total'] ?? 0);
                $impuestos = (float)($pedido['impuestos_total'] ?? 0);
                ?>
                <tr>
                    <th colspan="4" class="text-end">Subtotal</th>
                    <th>$<?php echo number_format($subtotal, 0, '.', ','); ?></th>
                </tr>
                <?php if ($desc_total > 0 || $desc_pct > 0): ?>
                <tr>
                    <th colspan="4" class="text-end">Descuento (<?php echo number_format($desc_pct, 2); ?>%)</th>
                    <th>-$<?php echo number_format($desc_total, 0, '.', ','); ?></th>
                </tr>
                <?php endif; ?>
                <?php if ($impuestos > 0): ?>
                <tr>
                    <th colspan="4" class="text-end">Impuestos</th>
                    <th>$<?php echo number_format($impuestos, 0, '.', ','); ?></th>
                </tr>
                <?php endif; ?>
                <tr class="table-dark">
                    <th colspan="4" class="text-end">TOTAL</th>
                    <th>$<?php echo number_format($pedido['total'], 0, '.', ','); ?></th>
                </tr>
            </tfoot>
        </table>

            <?php if (!empty($descuentos_cliente)): ?>
            <div class="info-box">
                <h6 class="mb-2">Descuentos asignados al cliente por grupo</h6>
                <ul class="mb-0">
                    <?php foreach ($descuentos_cliente as $dc): ?>
                        <li><?php echo htmlspecialchars($dc['grupo_nombre']); ?>: <?php echo number_format($dc['porcentaje_descuento'], 2); ?>%</li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        
        <?php if ($pedido['observaciones']): ?>
        <div class="mt-4">
            <h5>OBSERVACIONES</h5>
            <p><?php echo nl2br(htmlspecialchars($pedido['observaciones'])); ?></p>
        </div>
        <?php endif; ?>
        
        <div class="text-center mt-5 no-print">
            <button class="btn btn-primary" onclick="window.print()">Imprimir</button>
            <button class="btn btn-secondary" onclick="window.close()">Cerrar</button>
        </div>
    </div>
    
    <script>
        // Auto-imprimir cuando se carga la página
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
