<?php
session_start();

// Verificar sesión de cliente
if (!isset($_SESSION['usuario']) || ($_SESSION['tipo'] ?? '') !== 'cliente') {
    header('Location: ../index.php');
    exit();
}

$_SESSION['user_role'] = 'cliente';

require_once '../includes/LayoutManager.php';
require_once '../config/database.php';

$pdo = getConnection();

// Inicializar carrito
if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Acciones del carrito (POST)
$action = $_POST['action'] ?? '';
if ($action) {
    $productoId = isset($_POST['producto_id']) ? (int)$_POST['producto_id'] : 0;
    $cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 0;

    switch ($action) {
        case 'update':
            if ($productoId > 0) {
                if ($cantidad <= 0) {
                    unset($_SESSION['carrito'][$productoId]);
                } else {
                    $_SESSION['carrito'][$productoId] = $cantidad;
                }
            }
            break;
        case 'remove':
            if ($productoId > 0) {
                unset($_SESSION['carrito'][$productoId]);
            }
            break;
        case 'clear':
            $_SESSION['carrito'] = [];
            break;
    }
    // Actualizar contador global
    $_SESSION['cart_count'] = array_sum($_SESSION['carrito']);
    // Redirigir para evitar repost
    header('Location: carrito.php');
    exit();
}

// Paginación de items del carrito
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Asegurar consistencia: obtener todos los productos del carrito, limpiar faltantes y calcular total general
$productIds = array_keys($_SESSION['carrito']);
$items = [];
$grand_total = 0.0;

if (!empty($productIds)) {
    $ph_all = implode(',', array_fill(0, count($productIds), '?'));
    $stmt = $pdo->prepare("SELECT p.id, p.codigo, p.descripcion, p.precio, g.nombre AS grupo_nombre
                            FROM productos p
                            LEFT JOIN grupos_productos g ON p.grupo_id = g.id
                            WHERE p.id IN ($ph_all)");
    $stmt->execute($productIds);
    $rows_all = $stmt->fetchAll();
    $mapAll = [];
    foreach ($rows_all as $r) { $mapAll[$r['id']] = $r; }

    // Limpiar productos inexistentes y calcular total general
    foreach ($productIds as $pid) {
        if (!isset($mapAll[$pid])) {
            unset($_SESSION['carrito'][$pid]);
            continue;
        }
        $qty = (int)($_SESSION['carrito'][$pid] ?? 0);
        if ($qty <= 0) continue;
        $grand_total += $qty * (float)$mapAll[$pid]['precio'];
    }

    // Recalcular IDs y paginación tras limpieza si cambió el carrito
    $productIds = array_keys($_SESSION['carrito']);
}

$_SESSION['cart_count'] = array_sum($_SESSION['carrito']);
$cartCount = (int)$_SESSION['cart_count'];

$total_items = count($productIds);
$total_pages = max(1, (int)ceil($total_items / $limit));
$ids_pagina = array_slice($productIds, $offset, $limit);

if (!empty($ids_pagina)) {
    // Usar el mapa completo para construir los ítems de la página actual
    foreach ($ids_pagina as $pid) {
        if (!isset($mapAll[$pid])) continue;
        $qty = (int)($_SESSION['carrito'][$pid] ?? 0);
        if ($qty <= 0) continue;
        $price = (float)$mapAll[$pid]['precio'];
        $items[] = [
            'id' => $pid,
            'codigo' => $mapAll[$pid]['codigo'],
            'descripcion' => $mapAll[$pid]['descripcion'],
            'grupo_nombre' => $mapAll[$pid]['grupo_nombre'] ?? 'Sin grupo',
            'precio' => $price,
            'cantidad' => $qty,
            'total' => $qty * $price
        ];
    }
}

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Mi Carrito</h2>
    <div>
        <a href="productos.php" class="btn btn-outline-secondary me-2"><i class="fas fa-arrow-left"></i> Seguir comprando</a>
        <form method="POST" class="d-inline" onsubmit="return confirm('¿Vaciar todo el carrito?');">
            <input type="hidden" name="action" value="clear">
            <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash"></i> Vaciar</button>
        </form>
        <span class="badge bg-success ms-3" id="cartCountHead">Items: <?= $cartCount ?></span>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($items)): ?>
            <p class="text-center text-muted py-4 mb-0">Tu carrito está vacío</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Descripción</th>
                            <th>Grupo</th>
                            <th class="text-end">Precio</th>
                            <th class="text-center" style="width:140px;">Cantidad</th>
                            <th class="text-end">Subtotal</th>
                            <th style="width:120px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $it): ?>
                        <tr>
                            <td><?= htmlspecialchars($it['codigo']) ?></td>
                            <td><?= htmlspecialchars($it['descripcion']) ?></td>
                            <td><?= htmlspecialchars($it['grupo_nombre']) ?></td>
                            <td class="text-end">$<?= number_format($it['precio'], 2) ?></td>
                            <td class="text-center">
                                <form method="POST" class="d-inline-flex align-items-center">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="producto_id" value="<?= (int)$it['id'] ?>">
                                    <div class="input-group input-group-sm" style="width: 120px;">
                                        <button class="btn btn-outline-secondary" type="button" onclick="stepQty(this, -1)">-</button>
                                        <input type="number" class="form-control text-center" name="cantidad" value="<?= (int)$it['cantidad'] ?>" min="0">
                                        <button class="btn btn-outline-secondary" type="button" onclick="stepQty(this, 1)">+</button>
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-primary ms-2">Actualizar</button>
                                </form>
                            </td>
                            <td class="text-end">$<?= number_format($it['total'], 2) ?></td>
                            <td>
                                <form method="POST" onsubmit="return confirm('¿Quitar este producto?');">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="producto_id" value="<?= (int)$it['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100"><i class="fas fa-times"></i> Quitar</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Paginación">
                <ul class="pagination justify-content-center">
                    <?php
                        function car_generatePageUrl($pageNum) {
                            $params = $_GET;
                            $params['page'] = $pageNum;
                            return '?' . http_build_query($params);
                        }

                        $firstDisabled = $page <= 1;
                        echo '<li class="page-item ' . ($firstDisabled ? 'disabled' : '') . '"><a class="page-link" href="' . ($firstDisabled ? '#' : car_generatePageUrl(1)) . '"><i class="fas fa-angle-double-left"></i></a></li>';
                        echo '<li class="page-item ' . ($firstDisabled ? 'disabled' : '') . '"><a class="page-link" href="' . ($firstDisabled ? '#' : car_generatePageUrl($page - 1)) . '"><i class="fas fa-angle-left"></i></a></li>';

                        $range = 2;
                        $initial = max(1, $page - $range);
                        $limit_pg = min($total_pages, $page + $range);

                        if ($initial > 1) {
                            echo '<li class="page-item"><a class="page-link" href="' . car_generatePageUrl(1) . '">1</a></li>';
                            if ($initial > 2) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                        }

                        for ($i = $initial; $i <= $limit_pg; $i++) {
                            echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '"><a class="page-link" href="' . car_generatePageUrl($i) . '">' . $i . '</a></li>';
                        }

                        if ($limit_pg < $total_pages) {
                            if ($limit_pg < $total_pages - 1) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                            echo '<li class="page-item"><a class="page-link" href="' . car_generatePageUrl($total_pages) . '">' . $total_pages . '</a></li>';
                        }

                        $lastDisabled = $page >= $total_pages;
                        echo '<li class="page-item ' . ($lastDisabled ? 'disabled' : '') . '"><a class="page-link" href="' . ($lastDisabled ? '#' : car_generatePageUrl($page + 1)) . '"><i class="fas fa-angle-right"></i></a></li>';
                        echo '<li class="page-item ' . ($lastDisabled ? 'disabled' : '') . '"><a class="page-link" href="' . ($lastDisabled ? '#' : car_generatePageUrl($total_pages)) . '"><i class="fas fa-angle-double-right"></i></a></li>';
                    ?>
                </ul>
            </nav>
            <?php endif; ?>

            <!-- Totales y acciones -->
            <div class="d-flex justify-content-end align-items-center mt-3">
                <div class="me-4">
                    <div class="h5 mb-0">Subtotal: $<?= number_format($grand_total, 2) ?></div>
                    <small class="text-muted">Impuestos y envío calculados al finalizar compra</small>
                </div>
                <a href="#" class="btn btn-success btn-lg disabled" aria-disabled="true">Finalizar Compra (demo)</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();

$additionalJS = <<<JS
<script>
function stepQty(btn, delta) {
    const input = btn.closest('.input-group').querySelector('input[name="cantidad"]');
    let val = parseInt(input.value || '0', 10);
    val = isNaN(val) ? 0 : val + delta;
    if (val < 0) val = 0;
    input.value = val;
}
</script>
JS;

LayoutManager::renderAdminPage('Mi Carrito', $content, '', $additionalJS);
?>
