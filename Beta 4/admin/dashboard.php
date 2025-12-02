<?php
session_start();

// Verificar autenticación
if (empty($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Nuevo: nombre de usuario y tiempo del servidor
$user_name = $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'Usuario';
$server_dt = new DateTime('now', new DateTimeZone(date_default_timezone_get()));
$server_ts_ms = $server_dt->getTimestamp() * 1000;

require_once '../includes/ComponentHelper.php';
require_once '../includes/LayoutManager.php';
require_once '../config/database.php';

$pdo = getConnection();

// Inicializar datos
$stats_data = [
    'total_productos' => 0,
    'total_clientes' => 0,
    'total_pedidos' => 0,
    'total_envios' => 0
];
$pedidos_recientes = [];
$productos_populares = [];

/**
 * Obtiene las estadísticas principales
 */
function getStatsData(PDO $pdo): array {
    $queries = [
        'total_productos' => "SELECT COUNT(*) AS total FROM productos",
        'total_clientes' => "SELECT COUNT(*) AS total FROM clientes WHERE activo = 1",
        'total_pedidos' => "SELECT COUNT(*) AS total FROM pedidos",
        'total_envios' => "SELECT COUNT(*) AS total FROM envios"
    ];

    $stats = [];
    foreach ($queries as $key => $sql) {
        try {
            $stmt = $pdo->query($sql);
            $result = $stmt->fetch();
            $stats[$key] = (int)($result['total'] ?? 0);
        } catch (PDOException $e) {
            error_log("Error en $key: " . $e->getMessage());
            $stats[$key] = 0;
        }
    }

    return $stats;
}

/**
 * Obtiene los pedidos más recientes
 */
function getPedidosRecientes(PDO $pdo): array {
    try {
        $stmt = $pdo->prepare("
            SELECT p.numero_documento, p.fecha_pedido, p.estado, p.total, c.nombre AS cliente
            FROM pedidos p
            INNER JOIN clientes c ON p.cliente_id = c.id
            ORDER BY p.created_at DESC
            LIMIT 5
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error al obtener pedidos recientes: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtiene los productos más vendidos
 */
function getProductosPopulares(PDO $pdo): array {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                pr.codigo, 
                pr.descripcion, 
                pr.precio, 
                COALESCE(SUM(dp.cantidad), 0) AS total_vendido
            FROM productos pr
            LEFT JOIN detalle_pedidos dp ON dp.producto_id = pr.id
            LEFT JOIN pedidos p ON dp.pedido_id = p.id AND p.estado NOT IN ('borrador', 'cancelado')
            GROUP BY pr.id
            ORDER BY total_vendido DESC, pr.precio DESC
            LIMIT 5
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error al obtener productos populares: " . $e->getMessage());
        return [];
    }
}

// Cargar datos
$stats_data = getStatsData($pdo);
$pedidos_recientes = getPedidosRecientes($pdo);
$productos_populares = getProductosPopulares($pdo);

// Contenido de la página
ob_start();
?>

<!-- Header: título + usuario + reloj -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0">Panel de Control</h2>
        <small class="text-muted">Bienvenido, <?= htmlspecialchars($user_name) ?></small>
    </div>

    <div class="d-flex align-items-center">
        <div class="me-3 text-end d-none d-md-block">
            <div class="small text-muted">Usuario</div>
            <div class="fw-bold"><?= htmlspecialchars($user_name) ?></div>
        </div>

        <div class="card bg-white shadow-sm p-2 text-center" id="userClock" style="min-width:170px;">
            <div class="small text-muted">Hora del Panel</div>
            <div class="fw-bold" id="clockDisplay"><?= $server_dt->format('H:i:s') ?></div>
            <div class="small text-muted" id="clockDate"><?= $server_dt->format('d/m/Y') ?></div>
        </div>
    </div>
</div>

<!-- Tarjetas de estadísticas -->
<div class="row mb-4 stats-card">
    <?php foreach ([
        'Productos' => $stats_data['total_productos'],
        'Clientes' => $stats_data['total_clientes'],
        'Pedidos' => $stats_data['total_pedidos'],
        'Envíos' => $stats_data['total_envios']
    ] as $label => $value): ?>
        <div class="col-md-3 mb-3">
            <div class="card bg-white shadow-sm">
                <div class="card-body text-center">
                    <h3 class="fw-bold mb-2"><?= number_format($value) ?></h3>
                    <p class="mb-0 text-muted"><?= htmlspecialchars($label) ?></p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Actividad reciente -->
<div class="row">
    <!-- Pedidos recientes -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Últimos Pedidos</h5>
            </div>
            <div class="card-body">
                <?php if (!$pedidos_recientes): ?>
                    <p class="text-center text-muted py-4 mb-0">No hay pedidos recientes</p>
                <?php else: ?>
                    <?php foreach ($pedidos_recientes as $p): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <div>
                                <h6 class="mb-1">#<?= htmlspecialchars($p['numero_documento']) ?></h6>
                                <small class="text-muted"><?= htmlspecialchars($p['cliente']) ?></small>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold">$<?= number_format($p['total'], 0, ',', '.') ?></div>
                                <small class="text-muted"><?= date('d/m/Y', strtotime($p['fecha_pedido'])) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Productos destacados -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Productos Destacados</h5>
            </div>
            <div class="card-body">
                <?php if (!$productos_populares): ?>
                    <p class="text-center text-muted py-4 mb-0">No hay datos disponibles</p>
                <?php else: ?>
                    <?php foreach ($productos_populares as $prod): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <div>
                                <h6 class="mb-1"><?= htmlspecialchars(mb_strimwidth($prod['descripcion'], 0, 30, '...')) ?></h6>
                                <small class="text-muted">Código: <?= htmlspecialchars($prod['codigo']) ?></small>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold">$<?= number_format($prod['precio'], 0, ',', '.') ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Acciones rápidas -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Acciones Rápidas</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php
                    $acciones = [
                        ['productos.php?action=add', 'primary', 'plus-circle', 'Nuevo Producto'],
                        ['clientes.php?action=add', 'success', 'user-plus', 'Nuevo Cliente'],
                        ['pedidos.php', 'warning', 'shopping-cart', 'Ver Pedidos'],
                        ['envios.php', 'info', 'truck', 'Gestionar Envíos'],
                        ['empleados.php', 'secondary', 'user-tie', 'Empleados'],
                        ['reportes.php', 'dark', 'chart-line', 'Reportes']
                    ];
                    foreach ($acciones as [$link, $color, $icon, $text]): ?>
                        <div class="col-md-4 mb-3">
                            <a href="<?= $link ?>" class="btn btn-outline-<?= $color ?> w-100 py-3 text-decoration-none">
                                <i class="fas fa-<?= $icon ?> fa-lg mb-2 d-block"></i>
                                <span><?= $text ?></span>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Reemplazar/Extender $additionalJS para incluir actualización del reloj
$additionalJS = <<<JS
<script>
function actualizarEstadisticas() {
    fetch("ajax/dashboard_stats.php")
        .then(r => r.json())
        .then(result => {
            if (result.success) {
                const data = result.data;
                const stats = [
                    data.total_productos,
                    data.total_clientes,
                    data.total_pedidos,
                    data.total_envios
                ];
                document.querySelectorAll(".stats-card .fw-bold").forEach((el, i) => {
                    el.textContent = stats[i]?.toLocaleString() ?? '0';
                });
            }
        })
        .catch(console.error);
}
setInterval(actualizarEstadisticas, 30000);

// Reloj del panel: inicializar usando offset entre servidor y cliente
(function() {
    // Timestamp inicial en ms inyectado desde el servidor (valor PHP)
    const serverTsMs = <?= (int)$server_ts_ms ?>;
    // Calculamos offset = server - local
    let offset = serverTsMs - Date.now();

    const clockDisplay = document.getElementById('clockDisplay');
    const clockDate = document.getElementById('clockDate');

    function pad(n) { return n < 10 ? '0' + n : n; }

    function getServerNow() {
        return new Date(Date.now() + offset);
    }

    function updateClock() {
        const now = getServerNow();
        const h = pad(now.getHours());
        const m = pad(now.getMinutes());
        const s = pad(now.getSeconds());
        clockDisplay && (clockDisplay.textContent = h + ':' + m + ':' + s);

        const d = pad(now.getDate());
        const mo = pad(now.getMonth() + 1);
        const y = now.getFullYear();
        clockDate && (clockDate.textContent = d + '/' + mo + '/' + y);
    }

    // Actualizar cada segundo
    updateClock();
    setInterval(updateClock, 1000);

    // Re-sincronizar suavemente si detectamos un desfase grande (>2s)
    setInterval(function() {
        // Intentamos usar cabecera Date de una petición HEAD a un endpoint ligero
        fetch('ajax/dashboard_stats.php', { method: 'HEAD' })
            .then(response => {
                const dateHeader = response.headers.get('Date');
                if (dateHeader) {
                    const serverDate = new Date(dateHeader).getTime();
                    const localEstimate = Date.now() + offset;
                    const diff = serverDate - localEstimate;
                    if (Math.abs(diff) > 2000) {
                        // Ajuste gradual del offset para evitar saltos visibles
                        offset += diff * 0.2; // ajustar el 20% del desfase cada minuto
                    }
                }
            })
            .catch(() => { /* ignorar fallos de sincronización */ });
    }, 60000);
})();
</script>
JS;

// Render the page using LayoutManager
LayoutManager::renderAdminPage(
    'Panel de Control',  // título
    $content,           // contenido
    '',                 // CSS adicional
    $additionalJS       // JavaScript adicional
);
