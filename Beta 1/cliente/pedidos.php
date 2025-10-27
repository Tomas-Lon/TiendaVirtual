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
$cliente_id = $_SESSION['cliente_id'];

// Paginación
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 9; // 3 columnas x 3 filas
$offset = ($page - 1) * $limit;

// Contar total de pedidos
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE cliente_id = ?");
$countStmt->execute([$cliente_id]);
$total_pedidos = (int)$countStmt->fetchColumn();
$total_pages = max(1, (int)ceil($total_pedidos / $limit));

// Obtener pedidos del cliente para la página
$stmt = $pdo->prepare("
    SELECT p.*, 
           COUNT(dp.id) as items_count,
           SUM(dp.cantidad) as total_items
    FROM pedidos p 
    LEFT JOIN detalle_pedidos dp ON p.id = dp.pedido_id
    WHERE p.cliente_id = ? 
    GROUP BY p.id
    ORDER BY p.created_at DESC
    LIMIT $limit OFFSET $offset
");
$stmt->execute([$cliente_id]);
$pedidos = $stmt->fetchAll() ?: [];

$estados_color = [
    'borrador' => 'secondary',
    'confirmado' => 'info',
    'en_preparacion' => 'warning',
    'listo_envio' => 'primary',
    'enviado' => 'success',
    'entregado' => 'success',
    'cancelado' => 'danger'
];

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Mis Pedidos</h2>
    <a href="productos.php" class="btn btn-outline-primary"><i class="fas fa-box-open"></i> Ver Catálogo</a>
    </div>

<?php if (empty($pedidos)): ?>
    <div class="text-center py-5">
        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
        <h4>No tienes pedidos aún</h4>
        <p class="text-muted">Explora nuestro catálogo y realiza tu primer pedido</p>
        <a href="productos.php" class="btn btn-primary">Ver Catálogo</a>
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($pedidos as $pedido): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h6 class="card-title">#<?= htmlspecialchars($pedido['numero_documento']) ?></h6>
                            <span class="badge bg-<?= $estados_color[$pedido['estado']] ?? 'secondary' ?>">
                                <?= ucfirst(str_replace('_', ' ', $pedido['estado'])) ?>
                            </span>
                        </div>
                        <p class="text-muted small">
                            <i class="fas fa-calendar"></i>
                            <?= date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])) ?>
                        </p>
                        <p class="text-muted small">
                            <i class="fas fa-boxes"></i>
                            <?= $pedido['total_items'] ?? 0 ?> productos
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h6">$<?= number_format($pedido['total'], 0, ',', '.') ?></span>
                            <a class="btn btn-outline-primary btn-sm" href="pedidos.php?id=<?= (int)$pedido['id'] ?>">Ver Detalle</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($total_pages > 1): ?>
    <nav aria-label="Paginación">
        <ul class="pagination justify-content-center">
            <?php
                function pedidos_page_url($pageNum) {
                    $params = $_GET; $params['page'] = $pageNum; return '?' . http_build_query($params);
                }
                $firstDisabled = $page <= 1;
                echo '<li class="page-item ' . ($firstDisabled ? 'disabled' : '') . '"><a class="page-link" href="' . ($firstDisabled ? '#' : pedidos_page_url(1)) . '"><i class="fas fa-angle-double-left"></i></a></li>';
                echo '<li class="page-item ' . ($firstDisabled ? 'disabled' : '') . '"><a class="page-link" href="' . ($firstDisabled ? '#' : pedidos_page_url($page - 1)) . '"><i class="fas fa-angle-left"></i></a></li>';
                $range = 2; $initial = max(1, $page - $range); $limit_pg = min($total_pages, $page + $range);
                if ($initial > 1) {
                    echo '<li class="page-item"><a class="page-link" href="' . pedidos_page_url(1) . '">1</a></li>';
                    if ($initial > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
                for ($i = $initial; $i <= $limit_pg; $i++) {
                    echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '"><a class="page-link" href="' . pedidos_page_url($i) . '">' . $i . '</a></li>';
                }
                if ($limit_pg < $total_pages) {
                    if ($limit_pg < $total_pages - 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    echo '<li class="page-item"><a class="page-link" href="' . pedidos_page_url($total_pages) . '">' . $total_pages . '</a></li>';
                }
                $lastDisabled = $page >= $total_pages;
                echo '<li class="page-item ' . ($lastDisabled ? 'disabled' : '') . '"><a class="page-link" href="' . ($lastDisabled ? '#' : pedidos_page_url($page + 1)) . '"><i class="fas fa-angle-right"></i></a></li>';
                echo '<li class="page-item ' . ($lastDisabled ? 'disabled' : '') . '"><a class="page-link" href="' . ($lastDisabled ? '#' : pedidos_page_url($total_pages)) . '"><i class="fas fa-angle-double-right"></i></a></li>';
            ?>
        </ul>
    </nav>
    <?php endif; ?>
<?php endif; ?>

<?php
$content = ob_get_clean();
LayoutManager::renderAdminPage('Mis Pedidos', $content);
?>
