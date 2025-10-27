<?php
session_start();

// Verificar que el usuario esté logueado y sea cliente
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'cliente') {
    header('Location: ../index.php');
    exit();
}

$_SESSION['user_role'] = 'cliente';

require_once '../includes/LayoutManager.php';
require_once '../config/database.php';
$pdo = getConnection();

$cliente_id = (int)($_SESSION['cliente_id'] ?? 0);
$cotizaciones = [];
try {
    $sql = "SELECT p.id, p.numero_documento, p.fecha_pedido, p.total, p.estado,
                   COALESCE(SUM(dp.cantidad),0) AS total_items
            FROM pedidos p
            LEFT JOIN detalle_pedidos dp ON dp.pedido_id = p.id
            WHERE p.cliente_id = ? AND p.estado = 'borrador'
            GROUP BY p.id
            ORDER BY p.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cliente_id]);
    $cotizaciones = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Exception $e) {
    $cotizaciones = [];
}

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Mis Cotizaciones</h2>
    <a href="nueva_compra.php" class="btn btn-outline-primary"><i class="fas fa-plus-circle"></i> Nueva solicitud</a>
</div>

<?php if (!$cotizaciones): ?>
    <div class="text-center py-5">
        <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
        <h4>No tienes cotizaciones</h4>
        <p class="text-muted">Crea una nueva desde la sección Nueva Compra</p>
        <a href="nueva_compra.php" class="btn btn-primary">Nueva Cotización</a>
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($cotizaciones as $c): ?>
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="card-title mb-1">#<?= htmlspecialchars($c['numero_documento']) ?></h5>
                                <span class="badge bg-secondary">Borrador</span>
                            </div>
                            <div class="text-end">
                                <div class="small text-muted">Fecha</div>
                                <div><?= htmlspecialchars(date('d/m/Y', strtotime($c['fecha_pedido']))) ?></div>
                            </div>
                        </div>
                        <div class="mt-3 d-flex justify-content-between">
                            <div class="small text-muted">Items</div>
                            <div><?= (int)($c['total_items'] ?? 0) ?></div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <div class="small text-muted">Total</div>
                            <div class="text-success fw-bold">$<?= number_format((float)$c['total'], 2, ',', '.') ?></div>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <a href="pedidos.php?id=<?= (int)$c['id'] ?>" class="btn btn-sm btn-outline-primary">Ver detalle</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
LayoutManager::renderAdminPage('Cotizaciones', $content);
?>
