<?php
session_start();

// Asegurar que el usuario es cliente
if (!isset($_SESSION['usuario']) || ($_SESSION['tipo'] ?? '') !== 'cliente') {
    header('Location: ../index.php');
    exit();
}

require_once __DIR__ . '/../includes/LayoutManager.php';
require_once __DIR__ . '/../config/database.php';

$pdo = getConnection();
$cliente_id = $_SESSION['cliente_id'];

// Mapeo de colores para estados (puede compartirse más tarde)
$estados_color = [
    'borrador' => 'secondary',
    'pendiente' => 'warning',
    'confirmado' => 'success',
    'en_preparacion' => 'info',
    'listo_envio' => 'primary',
    'enviado' => 'primary',
    'entregado' => 'success',
    'cancelado' => 'danger'
];

// Parámetros de búsqueda y paginación
$search = trim($_GET['search'] ?? '');
$fecha_desde = $_GET['fecha_desde'] ?? '';
$fecha_hasta = $_GET['fecha_hasta'] ?? '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Construir WHERE dinámico y parámetros
$whereParts = ['p.cliente_id = :cliente_id'];
$params = ['cliente_id' => $cliente_id];

if ($search !== '') {
    $whereParts[] = '(p.numero_documento LIKE :q OR p.observaciones LIKE :q)';
    $params['q'] = '%' . $search . '%';
}
if ($fecha_desde !== '') {
    $whereParts[] = 'DATE(p.fecha_pedido) >= :fecha_desde';
    $params['fecha_desde'] = $fecha_desde;
}
if ($fecha_hasta !== '') {
    $whereParts[] = 'DATE(p.fecha_pedido) <= :fecha_hasta';
    $params['fecha_hasta'] = $fecha_hasta;
}

$whereSql = count($whereParts) ? ('WHERE ' . implode(' AND ', $whereParts)) : '';

// Contar total de pedidos del cliente con filtros
$countSql = "SELECT COUNT(*) FROM pedidos p " . $whereSql;
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$total_pedidos = (int)$countStmt->fetchColumn();
$total_pages = max(1, (int)ceil(max(1, $total_pedidos) / $limit));

// Traer pedidos (con número de items)
$sql = "SELECT p.id, p.numero_documento, p.fecha_pedido, p.estado, p.total,
            (SELECT COUNT(*) FROM detalle_pedidos dp WHERE dp.pedido_id = p.id) AS total_items
        FROM pedidos p " . $whereSql . " ORDER BY p.fecha_pedido DESC LIMIT :offset, :limit";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue(':' . $k, $v);
}
$stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
$stmt->execute();
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Renderizar vista
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Mis Pedidos</h2>
    <a href="productos.php" class="btn btn-outline-primary"><i class="fas fa-box-open"></i> Ver Catálogo</a>
</div>

<div class="card mb-4">
    <div class="card-body p-3">
        <form class="row g-3" id="filterForm" method="get">
            <div class="col-md-4">
                <label class="form-label">Buscar</label>
                <input type="text" id="search" name="search" class="form-control" placeholder="Número de pedido u observaciones" value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Desde</label>
                <input type="date" id="fecha_desde" name="fecha_desde" class="form-control" value="<?php echo htmlspecialchars($fecha_desde); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Hasta</label>
                <input type="date" id="fecha_hasta" name="fecha_hasta" class="form-control" value="<?php echo htmlspecialchars($fecha_hasta); ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Fecha</th>
                        <th>Items</th>
                        <th>Estado</th>
                        <th class="text-end">Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pedidos)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">No hay pedidos disponibles</td></tr>
                    <?php else: ?>
                        <?php foreach ($pedidos as $pedido): ?>
                            <tr>
                                <td><strong>#<?php echo htmlspecialchars($pedido['numero_documento']); ?></strong></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></td>
                                <td><?php echo (int)($pedido['total_items'] ?? 0); ?></td>
                                <td><span class="badge bg-<?php echo $estados_color[$pedido['estado']] ?? 'secondary'; ?>"><?php echo ucfirst(str_replace('_',' ',$pedido['estado'])); ?></span></td>
                                <td class="text-end"><strong>$<?php echo number_format($pedido['total'], 0, ',', '.'); ?></strong></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="verDetalle(<?php echo (int)$pedido['id']; ?>)"><i class="fas fa-eye"></i> Ver</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Paginación" class="mt-3">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php
                        $qs = $_GET;
                        $qs['page'] = $i;
                        $url = '?' . http_build_query($qs);
                    ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>"><a class="page-link" href="<?php echo $url; ?>"><?php echo $i; ?></a></li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Modal detalle -->
<div class="modal fade" id="detalleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Detalle del Pedido</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><div id="detalleContent"><div class="text-center py-4"><div class="spinner-border" role="status"><span class="visually-hidden">Cargando...</span></div></div></div></div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button></div>
        </div>
    </div>
</div>

<script>
function verDetalle(id) {
    document.getElementById("detalleContent").innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>
    `;

    new bootstrap.Modal(document.getElementById("detalleModal")).show();

    // Cargar detalle via AJAX (usa endpoint cliente/ajax/pedido_detalle.php)
        fetch(`ajax/pedido_detalle.php?id=${id}`, { credentials: 'same-origin' })
            .then(response => response.text().then(text => ({ status: response.status, ok: response.ok, text })))
            .then(({ status, ok, text }) => {
                if (ok) {
                    document.getElementById("detalleContent").innerHTML = text;
                } else {
                    console.error('Error cargando detalle, status:', status, 'body:', text);
                    // Mostrar el cuerpo devuelto (puede contener mensaje de error útil)
                    document.getElementById("detalleContent").innerHTML = text || `<div class="alert alert-warning">No se pudo cargar el detalle del pedido. Código: ${status}</div>`;
                }
            })
            .catch(error => {
                console.error('Error cargando detalle:', error);
                document.getElementById("detalleContent").innerHTML = `
                    <div class="alert alert-warning">
                        No se pudo cargar el detalle del pedido. Verifique que el pedido exista o consulte el registro de errores.
                    </div>
                `;
            });
}
</script>

<?php
$content = ob_get_clean();
LayoutManager::renderAdminPage('Mis Pedidos', $content);
?>
