<?php
session_start();

// Asegurar que el usuario es cliente
if (!isset($_SESSION['usuario']) || ($_SESSION['tipo'] ?? '') !== 'cliente') {
    header('Location: ../index.php');
    exit();
}

require_once __DIR__ . '/../includes/LayoutManager.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/paths.php';

$pdo = getConnection();
$cliente_id = $_SESSION['cliente_id'];

// Mapeo de colores para estados
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

// Variables para mensajes
$error_message = '';
$pedidos = [];
$total_pedidos = 0;
$total_pages = 1;

try {
    // Parámetros de búsqueda y paginación
    $search = trim($_GET['search'] ?? '');
    $fecha_desde = $_GET['fecha_desde'] ?? '';
    $fecha_hasta = $_GET['fecha_hasta'] ?? '';
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    // Validar fechas
    if ($fecha_desde && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_desde)) {
        throw new Exception('Formato de fecha desde inválido');
    }
    if ($fecha_hasta && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_hasta)) {
        throw new Exception('Formato de fecha hasta inválido');
    }
    if ($fecha_desde && $fecha_hasta && $fecha_desde > $fecha_hasta) {
        throw new Exception('La fecha desde no puede ser mayor que la fecha hasta');
    }

    // Construir WHERE dinámico y parámetros
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

    // Contar total de pedidos del cliente con filtros
    $countSql = "SELECT COUNT(*) FROM pedidos p " . $whereSql;
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $total_pedidos = (int)$countStmt->fetchColumn();
    $total_pages = max(1, (int)ceil(max(1, $total_pedidos) / $limit));

    // Traer pedidos con número de items y comprobante de entrega
    $sql = "SELECT p.id, p.numero_documento, p.fecha_pedido, p.estado, p.total,
                (SELECT COUNT(*) FROM detalle_pedidos dp WHERE dp.pedido_id = p.id) AS total_items,
                comp.pdf_path as comprobante_pdf, comp.codigo_qr as codigo_comprobante
            FROM pedidos p 
            LEFT JOIN entregas ent ON ent.pedido_id = p.id
            LEFT JOIN comprobantes_entrega comp ON comp.entrega_id = ent.id
            " . $whereSql . " ORDER BY p.fecha_pedido DESC LIMIT ? OFFSET ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_merge($params, [$limit, $offset]));
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log('Error en pedidos.php: ' . $e->getMessage());
    $error_message = 'Error al cargar los pedidos. Por favor, intente nuevamente.';
} catch (Exception $e) {
    $error_message = $e->getMessage();
}

// Renderizar vista
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Mis Pedidos</h2>
    <a href="productos.php" class="btn btn-outline-primary"><i class="fas fa-box-open"></i> Ver Catálogo</a>
</div>

<?php if ($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error_message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

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
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <a href="pedidos.php" class="btn btn-outline-secondary" title="Limpiar filtros">
                    <i class="fas fa-times"></i>
                </a>
                <button type="button" class="btn btn-outline-success" id="btnExportCsv" title="Exportar CSV" onclick="exportCSV()">
                    <i class="fas fa-file-csv"></i>
                </button>
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
                                <th style="width:24%">Número</th>
                                <th style="width:18%">Fecha</th>
                                <th style="width:6%">Items</th>
                                <th style="width:14%">Estado</th>
                                <th class="text-end" style="width:12%">Total</th>
                                <th style="width:26%">Acciones</th>
                            </tr>
                </thead>
                <tbody>
                    <?php if (empty($pedidos)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">No hay pedidos disponibles</td></tr>
                    <?php else: ?>
                        <?php foreach ($pedidos as $pedido): ?>
                            <tr>
                                <td class="text-truncate" style="max-width:220px;">
                                    <strong title="#<?php echo htmlspecialchars($pedido['numero_documento']); ?>">#<?php echo htmlspecialchars($pedido['numero_documento']); ?></strong>
                                </td>
                                <td class="text-nowrap"><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></td>
                                <td class="text-center"><?php echo (int)($pedido['total_items'] ?? 0); ?></td>
                                <td class="text-nowrap"><span class="badge bg-<?php echo $estados_color[$pedido['estado']] ?? 'secondary'; ?>"><?php echo ucfirst(str_replace('_',' ',$pedido['estado'])); ?></span></td>
                                <td class="text-end text-nowrap"><strong>$<?php echo number_format($pedido['total'], 0, ',', '.'); ?></strong></td>
                                <td>
                                    <div class="d-flex gap-1 small-actions">
                                        <button class="btn btn-sm btn-outline-primary" onclick="verDetalle(<?php echo (int)$pedido['id']; ?>)" title="Ver detalle">
                                            <i class="fas fa-eye"></i> <span class="d-none d-md-inline">Ver</span>
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary" onclick="window.open('reportes/pedido_print.php?id=<?php echo (int)$pedido['id']; ?>','_blank')" title="Imprimir pedido">
                                            <i class="fas fa-print"></i> <span class="d-none d-md-inline">Imprimir</span>
                                        </button>
                                        <?php if ($pedido['estado'] === 'entregado' && !empty($pedido['comprobante_pdf'])): ?>
                                        <?php $comprobante_url = htmlspecialchars(normalizar_url($pedido['comprobante_pdf'])); ?>
                                        <button class="btn btn-sm btn-outline-success" onclick="window.open('<?php echo $comprobante_url; ?>', '_blank')" title="Ver comprobante de entrega">
                                            <i class="fas fa-file-pdf"></i> <span class="d-none d-md-inline">Comprobante</span>
                                        </button>
                                        <?php endif; ?>
                                    </div>
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

<style>
/* Compact action buttons and truncate long numbers in pedidos table */
.small-actions .btn { padding: 0.25rem 0.5rem; font-size: 0.85rem; }
.table .text-truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
@media (max-width: 767px) {
    .small-actions .d-none.d-md-inline { display: none !important; }
}
</style>

<script>
function exportCSV(){
    const search = document.getElementById('search') ? document.getElementById('search').value.trim() : '';
    const fecha_desde = document.getElementById('fecha_desde') ? document.getElementById('fecha_desde').value : '';
    const fecha_hasta = document.getElementById('fecha_hasta') ? document.getElementById('fecha_hasta').value : '';
    const qs = new URLSearchParams();
    if(search) qs.set('search', search);
    if(fecha_desde) qs.set('fecha_desde', fecha_desde);
    if(fecha_hasta) qs.set('fecha_hasta', fecha_hasta);
    // Navigate to the export endpoint which will return a CSV download
    window.location = 'ajax/export_pedidos.php?' + qs.toString();
}

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
