<?php
session_start();

// Verificar que el usuario esté logueado y sea cliente
if (!isset($_SESSION['usuario']) || ($_SESSION['tipo'] ?? '') !== 'cliente') {
    header('Location: ../index.php');
    exit();
}

// Forzar el menú y layout de cliente
$_SESSION['user_role'] = 'cliente';

require_once '../includes/LayoutManager.php';
require_once '../config/database.php';

$pdo = getConnection();

// Filtros y paginación (similar a Admin/Productos, pero solo lectura)
$search = $_GET['search'] ?? '';
$grupos_filter = $_GET['grupos'] ?? [];
$precio_min = $_GET['precio_min'] ?? '';
$precio_max = $_GET['precio_max'] ?? '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// WHERE conditions
$where_conditions = [];
$params = [];

if ($search !== '') {
    $where_conditions[] = '(p.codigo LIKE ? OR p.descripcion LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($grupos_filter) && is_array($grupos_filter)) {
    $placeholders = str_repeat('?,', count($grupos_filter) - 1) . '?';
    $where_conditions[] = "p.grupo_id IN ($placeholders)";
    $params = array_merge($params, $grupos_filter);
}

if ($precio_min !== '' && is_numeric($precio_min)) {
    $where_conditions[] = 'p.precio >= ?';
    $params[] = $precio_min;
}

if ($precio_max !== '' && is_numeric($precio_max)) {
    $where_conditions[] = 'p.precio <= ?';
    $params[] = $precio_max;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Contar total
$count_sql = "SELECT COUNT(*) as total FROM productos p $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_productos = (int)($count_stmt->fetch()['total'] ?? 0);
$total_pages = max(1, (int)ceil($total_productos / $limit));

// Obtener página
$sql = "SELECT p.*, g.nombre as grupo_nombre 
        FROM productos p 
        LEFT JOIN grupos_productos g ON p.grupo_id = g.id 
        $where_clause 
        ORDER BY p.created_at DESC 
        LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll() ?: [];

// Obtener grupos activos
$grupos_stmt = $pdo->query('SELECT id, nombre FROM grupos_productos WHERE activo = 1 ORDER BY nombre');
$grupos = $grupos_stmt->fetchAll() ?: [];

// Contenido de la página
$cartCount = (int)array_sum($_SESSION['carrito'] ?? []);
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Catálogo de Productos</h2>
    <div class="btn-group">
        <button type="button" class="btn btn-info dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-download"></i> Exportar Catálogo
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#" onclick="exportarCatalogo('pdf'); return false;">
                <i class="fas fa-file-pdf text-danger"></i> Exportar PDF
            </a></li>
            <li><a class="dropdown-item" href="#" onclick="exportarCatalogo('xlsx'); return false;">
                <i class="fas fa-file-excel text-success"></i> Exportar Excel
            </a></li>
        </ul>
        <a href="nueva_compra.php" class="btn btn-outline-success ms-2">
            <i class="fas fa-cart-plus me-1"></i>
            Nueva Compra <span class="badge bg-success ms-2" id="cartCount"><?= $cartCount ?></span>
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?= htmlspecialchars($search) ?>" 
                       placeholder="Código o descripción">
            </div>
            <div class="col-md-2">
                <label for="precio_min" class="form-label">Precio Min</label>
                <input type="number" class="form-control" id="precio_min" name="precio_min" 
                       value="<?= htmlspecialchars($precio_min) ?>" step="0.01">
            </div>
            <div class="col-md-2">
                <label for="precio_max" class="form-label">Precio Max</label>
                <input type="number" class="form-control" id="precio_max" name="precio_max" 
                       value="<?= htmlspecialchars($precio_max) ?>" step="0.01">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Filtrar
                </button>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <a href="?" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-eraser"></i> Limpiar
                </a>
            </div>

            <!-- Filtro de grupos -->
            <div class="col-12 mt-4">
                <label class="form-label fw-bold">Filtrar por grupos (selección múltiple):</label>
                <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                    <div class="row g-2">
                        <?php foreach ($grupos as $grupo): ?>
                            <div class="col-md-4 col-lg-3">
                                <div class="form-check">
                                    <input class="form-check-input grupo-checkbox" type="checkbox" 
                                           name="grupos[]" value="<?= $grupo['id'] ?>" 
                                           id="grupo_<?= $grupo['id'] ?>"
                                           <?= (is_array($grupos_filter) && in_array($grupo['id'], $grupos_filter)) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="grupo_<?= $grupo['id'] ?>">
                                        <?= htmlspecialchars($grupo['nombre']) ?>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="seleccionarTodosGrupos()">
                            <i class="fas fa-check-double"></i> Seleccionar todos
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="limpiarGrupos()">
                            <i class="fas fa-times"></i> Deseleccionar todos
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de productos (solo lectura con acción Agregar) -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>Grupo</th>
                        <th>Precio</th>
                        <th>Fecha Creación</th>
                        <th style="width:140px;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($productos)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <p class="text-muted">No hay productos disponibles</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($productos as $producto): ?>
                        <tr>
                            <td><?= htmlspecialchars($producto['codigo']) ?></td>
                            <td><?= htmlspecialchars($producto['descripcion']) ?></td>
                            <td><?= htmlspecialchars($producto['grupo_nombre'] ?? 'Sin grupo') ?></td>
                            <td>$<?= number_format((float)$producto['precio'], 2) ?></td>
                            <td><?= isset($producto['created_at']) ? date('d/m/Y', strtotime($producto['created_at'])) : '' ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary w-100" onclick="addToCart(<?= (int)$producto['id'] ?>)">
                                    <i class="fas fa-cart-plus"></i> Agregar
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Paginación">
            <ul class="pagination justify-content-center">
                <?php
                    // Construir array de parámetros base para la paginación
                    $params_pag = [
                        'search' => $search,
                        'precio_min' => $precio_min,
                        'precio_max' => $precio_max
                    ];
                    if (!empty($grupos_filter)) {
                        $params_pag['grupos'] = $grupos_filter;
                    }

                    // Helper URL para cliente
                    function cli_generatePageUrl($pageNum, $params_pag) {
                        $params_pag['page'] = $pageNum;
                        $url_params = [];
                        foreach ($params_pag as $key => $value) {
                            if (is_array($value)) {
                                foreach ($value as $val) {
                                    $url_params[] = urlencode($key) . '[]=' . urlencode($val);
                                }
                            } else if ($value !== '') {
                                $url_params[] = urlencode($key) . '=' . urlencode($value);
                            }
                        }
                        return '?' . implode('&', $url_params);
                    }

                    $firstDisabled = $page <= 1;
                    echo '<li class="page-item ' . ($firstDisabled ? 'disabled' : '') . '"><a class="page-link" href="' . ($firstDisabled ? '#' : cli_generatePageUrl(1, $params_pag)) . '"><i class="fas fa-angle-double-left"></i></a></li>';

                    echo '<li class="page-item ' . ($firstDisabled ? 'disabled' : '') . '"><a class="page-link" href="' . ($firstDisabled ? '#' : cli_generatePageUrl($page - 1, $params_pag)) . '"><i class="fas fa-angle-left"></i></a></li>';

                    $range = 2;
                    $initial = max(1, $page - $range);
                    $limit_pg = min($total_pages, $page + $range);

                    if ($initial > 1) {
                        echo '<li class="page-item"><a class="page-link" href="' . cli_generatePageUrl(1, $params_pag) . '">1</a></li>';
                        if ($initial > 2) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                    }

                    for ($i = $initial; $i <= $limit_pg; $i++) {
                        echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '"><a class="page-link" href="' . cli_generatePageUrl($i, $params_pag) . '">' . $i . '</a></li>';
                    }

                    if ($limit_pg < $total_pages) {
                        if ($limit_pg < $total_pages - 1) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                        echo '<li class="page-item"><a class="page-link" href="' . cli_generatePageUrl($total_pages, $params_pag) . '">' . $total_pages . '</a></li>';
                    }

                    $lastDisabled = $page >= $total_pages;
                    echo '<li class="page-item ' . ($lastDisabled ? 'disabled' : '') . '"><a class="page-link" href="' . ($lastDisabled ? '#' : cli_generatePageUrl($page + 1, $params_pag)) . '"><i class="fas fa-angle-right"></i></a></li>';

                    echo '<li class="page-item ' . ($lastDisabled ? 'disabled' : '') . '"><a class="page-link" href="' . ($lastDisabled ? '#' : cli_generatePageUrl($total_pages, $params_pag)) . '"><i class="fas fa-angle-double-right"></i></a></li>';
                ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();

// JS adicional (helpers de grupos + agregar al carrito)
$additionalJS = <<<'JS'
<script>
// Función para exportar catálogo completo de productos
function exportarCatalogo(formato) {
    if (!formato || !['pdf', 'xlsx'].includes(formato)) {
        alert('Formato no válido');
        return;
    }
    
    // Crear un enlace temporal para descargar
    const url = 'ajax/exportar_productos_lista.php?formato=' + formato;
    const link = document.createElement('a');
    link.href = url;
    link.download = 'catalogo_productos_' + new Date().toISOString().split('T')[0] + '.' + (formato === 'xlsx' ? 'csv' : formato);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Mostrar mensaje de éxito
    showFlash('Exportando catálogo de productos...', 'success');
}

function seleccionarTodosGrupos() {
    document.querySelectorAll('.grupo-checkbox').forEach(cb => cb.checked = true);
}
function limpiarGrupos() {
    document.querySelectorAll('.grupo-checkbox').forEach(cb => cb.checked = false);
}

function addToCart(productId, cantidad = 1) {
    // Agregar a carrito virtual de sesión y sugerir ir a Nueva Compra
    const payload = { items: [{ producto_id: Number(productId), cantidad: Number(cantidad) }] };
    fetch('ajax/virtual_cart_append.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (data && data.success) {
            const countEl = document.getElementById('cartCount');
            if (countEl) countEl.textContent = data.cart_count ?? 0;
            showFlash('Agregado. Abre "Nueva Compra" para revisar y enviar.', 'success');
        } else {
            showFlash(data.message || 'No se pudo agregar', 'danger');
        }
    })
    .catch(err => {
        console.error(err);
        showFlash('Error al agregar', 'danger');
    });
}

function showFlash(message, type = 'info') {
    const container = document.createElement('div');
    container.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    container.style.zIndex = 1080;
    container.style.top = '1rem';
    container.style.right = '1rem';
    container.innerHTML = `
        <div>${message}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(container);
    setTimeout(() => {
        try { new bootstrap.Alert(container).close(); } catch (_) { container.remove(); }
    }, 2500);
}
</script>
JS;

LayoutManager::renderAdminPage('Catálogo de Productos', $content, '', $additionalJS);
?>
