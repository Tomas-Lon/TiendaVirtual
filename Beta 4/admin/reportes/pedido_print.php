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
    
    // Obtener detalles del pedido (incluye grupo, unidad_medida y unidad_empaque)
    $stmt = $pdo->prepare("
        SELECT dp.*, pr.codigo, pr.descripcion as producto_descripcion, 
               pr.grupo_id, gp.nombre AS grupo_nombre,
               pr.unidad_medida, pr.unidad_empaque
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
        
        .pedido-info {
            margin-bottom: 15px;
            padding: 8px 12px;
            border: 2px solid #000;
            background-color: #f5f5f5;
        }
        
        .pedido-info h4 {
            font-size: 14pt;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .pedido-info p {
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
        
        <div class="pedido-info">
            <div class="row">
                <div class="col-md-6">
                    <h4>PEDIDO <?php echo htmlspecialchars($pedido['numero_documento']); ?></h4>
                    <p><strong>Estado:</strong> <?php echo $estados[$pedido['estado']]; ?></p>
                    <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($pedido['created_at'])); ?></p>
                    <p><strong>Fecha Entrega:</strong> <?php echo $pedido['fecha_entrega_estimada'] ? date('d/m/Y', strtotime($pedido['fecha_entrega_estimada'])) : 'No definida'; ?></p>
                </div>
                <div class="col-md-6 text-end">
                    <p><strong>Empleado:</strong><br><?php echo htmlspecialchars($pedido['empleado_nombre'] ?? 'No asignado'); ?></p>
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
        
        <h5>PRODUCTOS</h5>
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">Código</th>
                    <th style="width: 25%;">Descripción</th>
                    <th style="width: 10%;">Grupo</th>
                    <th class="text-end" style="width: 6%;">Cant.</th>
                    <th class="text-center" style="width: 6%;">U.Med</th>
                    <th class="text-center" style="width: 6%;">U.Emp</th>
                    <th class="text-end" style="width: 11%;">Precio Unit.</th>
                    <th class="text-end" style="width: 7%;">Desc.%</th>
                    <th class="text-end" style="width: 10%;">Desc.$</th>
                    <th class="text-end" style="width: 11%;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($detalles)): ?>
                    <tr>
                        <td colspan="10" class="text-center">No hay productos en este pedido</td>
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
                        <td class="text-center"><?php echo htmlspecialchars($detalle['unidad_medida'] ?? 'und'); ?></td>
                        <td class="text-center"><?php echo htmlspecialchars($detalle['unidad_empaque'] ?? '1'); ?></td>
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
                    <th colspan="9" class="text-end">Subtotal:</th>
                    <th class="text-end">$<?php echo number_format($subtotal, 0, '.', ','); ?></th>
                </tr>
                <?php if ($desc_total > 0 || $desc_pct > 0): ?>
                <tr>
                    <th colspan="9" class="text-end">Descuento (<?php echo number_format($desc_pct, 2); ?>%):</th>
                    <th class="text-end">-$<?php echo number_format($desc_total, 0, '.', ','); ?></th>
                </tr>
                <?php endif; ?>
                <?php if ($impuestos > 0): ?>
                <tr>
                    <th colspan="9" class="text-end">Impuestos:</th>
                    <th class="text-end">$<?php echo number_format($impuestos, 0, '.', ','); ?></th>
                </tr>
                <?php endif; ?>
            </tfoot>
        </table>
        
        <div class="total-final">
            TOTAL: <span>$<?php echo number_format($pedido['total'], 0, '.', ','); ?></span>
        </div>

        <?php if (!empty($descuentos_cliente)): ?>
        <div class="info-box">
            <h6>Descuentos asignados al cliente por grupo</h6>
            <ul>
                <?php foreach ($descuentos_cliente as $dc): ?>
                    <li><?php echo htmlspecialchars($dc['grupo_nombre']); ?>: <?php echo number_format($dc['porcentaje_descuento'], 2); ?>%</li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <?php
        // Obtener información de entrega y comprobante si el pedido está entregado
        if ($pedido['estado'] === 'entregado') {
            $stmt_comprobante = $pdo->prepare("
                SELECT c.*, e.fecha_entrega_real, e.receptor_nombre as receptor_nombre_entrega
                FROM entregas e
                LEFT JOIN comprobantes_entrega c ON c.entrega_id = e.id
                WHERE e.pedido_id = ?
                ORDER BY e.id DESC
                LIMIT 1
            ");
            $stmt_comprobante->execute([$pedido_id]);
            $comprobante_info = $stmt_comprobante->fetch();
            
            if ($comprobante_info):
        ?>
        <div class="info-box" style="background-color: #e8f5e9; border-color: #27ae60;">
            <h6 style="color: #27ae60;"><i class="fas fa-check-circle"></i> Comprobante de Entrega</h6>
            <table class="table table-sm table-borderless" style="margin-bottom: 0;">
                <tr>
                    <td style="width: 30%;"><strong>Fecha de Entrega:</strong></td>
                    <td><?php echo $comprobante_info['fecha_entrega_real'] ? date('d/m/Y H:i', strtotime($comprobante_info['fecha_entrega_real'])) : 'N/A'; ?></td>
                </tr>
                <tr>
                    <td><strong>Recibido por:</strong></td>
                    <td><?php echo htmlspecialchars($comprobante_info['receptor_nombre'] ?? $comprobante_info['receptor_nombre_entrega'] ?? 'N/A'); ?></td>
                </tr>
                <?php if ($comprobante_info['receptor_documento']): ?>
                <tr>
                    <td><strong>Documento:</strong></td>
                    <td><?php echo htmlspecialchars($comprobante_info['receptor_documento']); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($comprobante_info['codigo_qr']): ?>
                <tr>
                    <td><strong>Código QR:</strong></td>
                    <td><code style="background: #fff; padding: 2px 6px; border-radius: 3px;"><?php echo htmlspecialchars($comprobante_info['codigo_qr']); ?></code></td>
                </tr>
                <?php endif; ?>
                <?php if ($comprobante_info['pdf_path']): ?>
                <tr>
                    <td colspan="2">
                        <div class="no-print" style="margin-top: 10px;">
                            <a href="<?php echo htmlspecialchars($comprobante_info['pdf_path']); ?>" target="_blank" class="btn btn-success btn-sm">
                                <i class="fas fa-file-pdf"></i> Ver Comprobante de Entrega
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
        <?php 
            endif;
        }
        ?>
        
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
