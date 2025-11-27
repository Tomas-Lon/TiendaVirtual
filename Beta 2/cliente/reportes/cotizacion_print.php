<?php
session_start();

// Verificar que el usuario sea cliente
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'cliente') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../config/database.php';

$pedido_id = $_GET['id'] ?? 0;

if (!$pedido_id) {
    die('ID de cotización no válido');
}

try {
    $pdo = getConnection();
    $cliente_id = $_SESSION['cliente_id'];
    
    // Obtener información del pedido/cotización
    $stmt = $pdo->prepare("
        SELECT p.*, c.nombre as cliente_nombre, c.email as cliente_email, 
               c.telefono as cliente_telefono, c.numero_documento as cliente_documento,
               c.tipo_documento as cliente_tipo_documento,
               dc.direccion, dc.ciudad, dc.departamento, dc.codigo_postal
        FROM pedidos p 
        JOIN clientes c ON p.cliente_id = c.id 
        LEFT JOIN direcciones_clientes dc ON p.direccion_entrega_id = dc.id
        WHERE p.id = ? AND p.cliente_id = ? AND p.estado = 'borrador'
    ");
    $stmt->execute([$pedido_id, $cliente_id]);
    $pedido = $stmt->fetch();
    
    if (!$pedido) {
        die('Cotización no encontrada o no tiene acceso');
    }
    
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

    // Obtener solo los descuentos por grupo que se usaron en esta cotización
    $stmt = $pdo->prepare("SELECT DISTINCT gp.nombre AS grupo_nombre, dc.porcentaje_descuento
                            FROM detalle_pedidos dp
                            JOIN productos pr ON dp.producto_id = pr.id
                            JOIN grupos_productos gp ON pr.grupo_id = gp.id
                            JOIN descuentos_clientes dc ON dc.grupo_id = gp.id AND dc.cliente_id = ?
                            WHERE dp.pedido_id = ? 
                            AND dc.activo = 1 
                            AND gp.activo = 1
                            AND (dp.descuento_porcentaje > 0 OR dp.descuento_monto > 0)
                            ORDER BY gp.nombre");
    $stmt->execute([$pedido['cliente_id'], $pedido_id]);
    $descuentos_cliente = $stmt->fetchAll();
    
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotización <?php echo htmlspecialchars($pedido['numero_documento']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12pt;
            line-height: 1.4;
            color: #000;
        }
        
        .container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 10mm;
        }
        
        .company-header {
            text-align: center;
            border-bottom: 3px solid #000;
            margin-bottom: 20px;
            padding-bottom: 15px;
        }
        
        .company-header h2 {
            font-size: 24pt;
            font-weight: bold;
            margin-bottom: 5px;
            color: #000;
        }
        
        .company-header p {
            margin: 3px 0;
            font-size: 11pt;
        }
        
        .company-header small {
            font-size: 10pt;
            color: #555;
        }
        
        .cotizacion-info {
            margin-bottom: 15px;
            padding: 8px 12px;
            border: 2px solid #000;
            background-color: #f5f5f5;
        }
        
        .cotizacion-info h4 {
            font-size: 14pt;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .cotizacion-info p {
            margin: 2px 0;
            font-size: 10pt;
        }
        
        .row {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        
        .col-md-6 {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 0 10px;
        }
        
        .col-md-6:first-child {
            padding-left: 0;
        }
        
        .col-md-6:last-child {
            padding-right: 0;
        }
        
        .text-end {
            text-align: right;
        }
        
        h5 {
            font-size: 14pt;
            font-weight: bold;
            margin: 15px 0 10px 0;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
        }
        
        .info-section {
            margin-bottom: 15px;
        }
        
        .info-section p {
            margin: 4px 0;
            font-size: 11pt;
        }
        
        .info-section strong {
            font-weight: bold;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 10pt;
        }
        
        table thead {
            background-color: #000;
            color: #fff;
        }
        
        table th,
        table td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: left;
        }
        
        table th {
            font-weight: bold;
            font-size: 10pt;
        }
        
        table td {
            font-size: 10pt;
        }
        
        table .text-end {
            text-align: right;
        }
        
        table .text-center {
            text-align: center;
        }
        
        table tfoot tr {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        table tfoot tr:last-child {
            background-color: #000;
            color: #fff;
        }
        
        table tfoot th {
            border: 1px solid #000;
            padding: 8px;
            font-size: 11pt;
        }
        
        .total-final {
            margin-top: 10px;
            padding: 10px;
            background-color: #000;
            color: #fff;
            text-align: right;
            font-weight: bold;
            font-size: 12pt;
            border: 2px solid #000;
        }
        
        .total-final span {
            display: inline-block;
            margin-left: 20px;
        }
        
        .info-box {
            border: 2px solid #000;
            padding: 12px;
            margin: 15px 0;
            background-color: #f9f9f9;
        }
        
        .info-box h6 {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .info-box ul {
            margin: 5px 0 0 20px;
            padding: 0;
        }
        
        .info-box li {
            margin: 3px 0;
            font-size: 10pt;
        }
        
        .observaciones {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #000;
        }
        
        .observaciones h5 {
            margin-top: 0;
        }
        
        .validity-note {
            margin-top: 20px;
            padding: 10px;
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
        }
        
        .validity-note h6 {
            font-size: 11pt;
            font-weight: bold;
            color: #856404;
            margin-bottom: 5px;
        }
        
        .validity-note p {
            font-size: 10pt;
            color: #856404;
            margin: 3px 0;
        }
        
        @media screen {
            .no-print {
                display: block !important;
                text-align: center;
                margin: 30px 0;
            }
            
            .no-print button {
                margin: 0 10px;
                padding: 10px 30px;
                font-size: 14pt;
                cursor: pointer;
            }
            
            .btn-primary {
                background-color: #007bff;
                color: white;
                border: none;
                border-radius: 5px;
            }
            
            .btn-primary:hover {
                background-color: #0056b3;
            }
            
            .btn-secondary {
                background-color: #6c757d;
                color: white;
                border: none;
                border-radius: 5px;
            }
            
            .btn-secondary:hover {
                background-color: #545b62;
            }
        }
        
        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .no-print {
                display: none !important;
            }
            
            body {
                margin: 0;
                padding: 0;
            }
            
            .container {
                max-width: 100%;
                padding: 5mm;
            }
            
            .page-break {
                page-break-after: always;
            }
            
            table {
                page-break-inside: auto;
            }
            
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            
            thead {
                display: table-header-group;
            }
            
            tfoot {
                display: table-footer-group;
            }
            
            .company-header {
                border-bottom: 3px solid #000;
            }
            
            table thead {
                background-color: #000 !important;
                color: #fff !important;
            }
            
            table tfoot tr:last-child {
                background-color: #000 !important;
                color: #fff !important;
            }
        }
        
        @page {
            size: letter;
            margin: 15mm;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="company-header">
            <h2>SOLTECIND</h2>
            <p>Soluciones Técnicas Industriales</p>
            <small>Suministros Industriales y Técnicos</small>
        </div>
        
        <div class="cotizacion-info">
            <div class="row">
                <div class="col-md-6">
                    <h4>COTIZACIÓN <?php echo htmlspecialchars($pedido['numero_documento']); ?></h4>
                    <p><strong>Estado:</strong> <span style="background: #6c757d; color: white; padding: 2px 8px; border-radius: 3px;">BORRADOR</span></p>
                    <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($pedido['created_at'])); ?></p>
                </div>
                <div class="col-md-6 text-end">
                    <p><strong>Válida hasta:</strong><br><?php echo date('d/m/Y', strtotime('+15 days', strtotime($pedido['created_at']))); ?></p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="info-section">
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
            </div>
            <div class="col-md-6">
                <?php if ($pedido['direccion']): ?>
                <div class="info-section">
                    <h5>DIRECCIÓN DE ENTREGA</h5>
                    <p>
                        <?php echo htmlspecialchars($pedido['direccion']); ?><br>
                        <?php echo htmlspecialchars($pedido['ciudad'] . ', ' . $pedido['departamento']); ?><br>
                        CP: <?php echo htmlspecialchars($pedido['codigo_postal']); ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <h5>PRODUCTOS COTIZADOS</h5>
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">Código</th>
                    <th style="width: 30%;">Descripción</th>
                    <th style="width: 12%;">Grupo</th>
                    <th class="text-end" style="width: 8%;">Cant.</th>
                    <th class="text-end" style="width: 12%;">Precio Unit.</th>
                    <th class="text-end" style="width: 8%;">Desc.%</th>
                    <th class="text-end" style="width: 10%;">Desc.$</th>
                    <th class="text-end" style="width: 10%;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($detalles)): ?>
                    <tr>
                        <td colspan="8" class="text-center">No hay productos en esta cotización</td>
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
                        <td><?php echo htmlspecialchars($detalle['grupo_nombre'] ?? '-'); ?></td>
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
                    <th colspan="7" class="text-end">Subtotal:</th>
                    <th class="text-end">$<?php echo number_format($subtotal, 0, '.', ','); ?></th>
                </tr>
                <?php if ($desc_total > 0 || $desc_pct > 0): ?>
                <tr>
                    <th colspan="7" class="text-end">Descuento (<?php echo number_format($desc_pct, 2); ?>%):</th>
                    <th class="text-end">-$<?php echo number_format($desc_total, 0, '.', ','); ?></th>
                </tr>
                <?php endif; ?>
                <?php if ($impuestos > 0): ?>
                <tr>
                    <th colspan="7" class="text-end">Impuestos:</th>
                    <th class="text-end">$<?php echo number_format($impuestos, 0, '.', ','); ?></th>
                </tr>
                <?php endif; ?>
            </tfoot>
        </table>
        
        <div class="total-final">
            TOTAL COTIZADO: <span>$<?php echo number_format($pedido['total'], 0, '.', ','); ?></span>
        </div>

        <?php if (!empty($descuentos_cliente)): ?>
        <div class="info-box">
            <h6>Descuentos asignados por grupo</h6>
            <ul>
                <?php foreach ($descuentos_cliente as $dc): ?>
                    <li><?php echo htmlspecialchars($dc['grupo_nombre']); ?>: <?php echo number_format($dc['porcentaje_descuento'], 2); ?>%</li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <div class="validity-note">
            <h6>⚠ NOTA IMPORTANTE</h6>
            <p><strong>• Esta es una cotización preliminar.</strong> Los precios pueden estar sujetos a cambios.</p>
            <p><strong>• Validez:</strong> 15 días calendario desde la fecha de emisión.</p>
            <p><strong>• Para confirmar:</strong> Contacte con nuestro equipo de ventas para convertir esta cotización en pedido formal.</p>
        </div>
        
        <?php if ($pedido['observaciones']): ?>
        <div class="observaciones">
            <h5>OBSERVACIONES</h5>
            <p><?php echo nl2br(htmlspecialchars($pedido['observaciones'])); ?></p>
        </div>
        <?php endif; ?>
        
        <div class="text-center mt-5 no-print">
            <button class="btn btn-primary" onclick="window.print()">Imprimir</button>
            <button class="btn btn-secondary" onclick="window.close()">Cerrar</button>
        </div>
        
        <!-- Pie de página -->
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #999; font-size: 12px;">
            <p>Esta cotización es un documento preliminar y no representa un compromiso de compra.</p>
            <p>Para cualquier consulta, contacta con nuestro equipo de ventas.</p>
            <p>&copy; <?php echo date('Y'); ?> SolTecnInd. Todos los derechos reservados.</p>
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
