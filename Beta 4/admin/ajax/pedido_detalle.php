<?php
session_start();

// Simple endpoint to return HTML detail for a pedido (used by admin/pedidos.php -> verDetalle)
// Requires authenticated admin/employee session
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo '<div class="alert alert-danger">Acceso denegado.</div>';
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo '<div class="alert alert-warning">ID de pedido inválido.</div>';
    exit;
}

try {
    $pdo = getConnection();

    // Cabecera del pedido
    $stmt = $pdo->prepare("SELECT p.*, c.nombre AS cliente_nombre, c.email AS cliente_email
        FROM pedidos p
        LEFT JOIN clientes c ON p.cliente_id = c.id
        WHERE p.id = ? LIMIT 1");
    $stmt->execute([$id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        http_response_code(404);
        echo '<div class="alert alert-info">Pedido no encontrado.</div>';
        exit;
    }

    // Detalle de líneas
    $stmt = $pdo->prepare("SELECT dp.*, pr.codigo, pr.descripcion
        FROM detalle_pedidos dp
        LEFT JOIN productos pr ON dp.producto_id = pr.id
        WHERE dp.pedido_id = ? ORDER BY dp.id");
    $stmt->execute([$id]);
    $lineas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Construir HTML
    ob_start();
    ?>
    <div class="mb-3">
        <h5>Pedido: <strong><?php echo htmlspecialchars($pedido['numero_documento']); ?></strong></h5>
        <p class="mb-0"><strong>Cliente:</strong> <?php echo htmlspecialchars($pedido['cliente_nombre'] ?? ''); ?> &nbsp; <small class="text-muted"><?php echo htmlspecialchars($pedido['cliente_email'] ?? ''); ?></small></p>
        <p class="mb-0"><strong>Fecha pedido:</strong> <?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></p>
        <p class="mb-0"><strong>Estado:</strong> <?php echo htmlspecialchars($pedido['estado']); ?></p>
        <?php if (!empty($pedido['observaciones'])): ?>
            <p class="mt-2"><strong>Observaciones:</strong> <?php echo nl2br(htmlspecialchars($pedido['observaciones'])); ?></p>
        <?php endif; ?>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Código</th>
                    <th>Descripción</th>
                    <th class="text-end">Cantidad</th>
                    <th class="text-end">Precio unit.</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total = 0;
                if (!$lineas) {
                    echo '<tr><td colspan="6" class="text-center">No hay líneas para este pedido</td></tr>';
                } else {
                    foreach ($lineas as $i => $l) {
                        $sub = ($l['cantidad'] * $l['precio_unitario']);
                        $total += $sub;
                        ?>
                        <tr>
                            <td><?php echo $i+1; ?></td>
                            <td><?php echo htmlspecialchars($l['codigo']); ?></td>
                            <td><?php echo htmlspecialchars($l['descripcion']); ?></td>
                            <td class="text-end"><?php echo number_format($l['cantidad'],0); ?></td>
                            <td class="text-end">$<?php echo number_format($l['precio_unitario'],2); ?></td>
                            <td class="text-end">$<?php echo number_format($sub,2); ?></td>
                        </tr>
                        <?php
                    }
                }
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="5" class="text-end">Total</th>
                    <th class="text-end">$<?php echo number_format($total,2); ?></th>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php
    $html = ob_get_clean();
    echo $html;
    exit;

} catch (Exception $e) {
    http_response_code(500);
    error_log('Error en pedido_detalle.php: ' . $e->getMessage());
    echo '<div class="alert alert-danger">Error al cargar el detalle del pedido.</div>';
    exit;
}
