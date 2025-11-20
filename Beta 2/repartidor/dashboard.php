<?php
session_start();

// Verificar autenticación y cargo
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'empleado') {
    header('Location: ../index.php?err=not_empleado');
    exit();
}

$_SESSION['cargo'] = strtolower($_SESSION['cargo'] ?? '');
if ($_SESSION['cargo'] === 'admin') {
    header('Location: ../admin/dashboard.php');
    exit();
}
if ($_SESSION['cargo'] !== 'repartidor') {
    header('Location: ../index.php?err=no_repartidor');
    exit();
}

// Asegurar que el Layout use el menú de repartidor
$_SESSION['user_role'] = 'repartidor';

$user_name = $_SESSION['nombre'] ?? $_SESSION['usuario'] ?? 'Repartidor';

// Reloj del servidor
$server_dt = new DateTime('now', new DateTimeZone(date_default_timezone_get()));
$server_ts_ms = $server_dt->getTimestamp() * 1000;

require_once '../includes/LayoutManager.php';
require_once '../config/database.php';

$pdo = getConnection();
$empleado_id = $_SESSION['empleado_id'] ?? 0;

// Obtener estadísticas
function getRepartidorStats(PDO $pdo, int $empleado_id): array {
    $stats = [
        'programadas' => 0,
        'en_transito' => 0,
        'entregadas_hoy' => 0,
        'entregadas_mes' => 0
    ];
    
    // Envíos programados
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM envios WHERE repartidor_id = ? AND estado = 'programado'");
    $stmt->execute([$empleado_id]);
    $stats['programadas'] = $stmt->fetch()['total'] ?? 0;
    
    // Envíos en tránsito
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM envios WHERE repartidor_id = ? AND estado = 'en_transito'");
    $stmt->execute([$empleado_id]);
    $stats['en_transito'] = $stmt->fetch()['total'] ?? 0;
    
    // Entregas completadas hoy
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM envios WHERE repartidor_id = ? AND estado = 'entregado' AND DATE(fecha_entrega_real) = CURDATE()");
    $stmt->execute([$empleado_id]);
    $stats['entregadas_hoy'] = $stmt->fetch()['total'] ?? 0;
    
    // Entregas completadas este mes
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM envios WHERE repartidor_id = ? AND estado = 'entregado' AND MONTH(fecha_entrega_real) = MONTH(CURDATE()) AND YEAR(fecha_entrega_real) = YEAR(CURDATE())");
    $stmt->execute([$empleado_id]);
    $stats['entregadas_mes'] = $stmt->fetch()['total'] ?? 0;
    
    return $stats;
}

// Obtener entregas recientes
function getEntregasRecientes(PDO $pdo, int $empleado_id, int $limit = 10): array {
    $stmt = $pdo->prepare("
        SELECT e.id, e.estado, e.fecha_programada as fecha_entrega, e.fecha_entrega_real,
               dir.direccion as direccion_entrega, dir.ciudad,
               p.numero_documento, c.nombre AS cliente_nombre, c.telefono
        FROM envios e
        INNER JOIN pedidos p ON e.pedido_id = p.id
        INNER JOIN clientes c ON p.cliente_id = c.id
        LEFT JOIN direcciones_clientes dir ON e.direccion_entrega_id = dir.id
        WHERE e.repartidor_id = ?
        ORDER BY 
            CASE e.estado 
                WHEN 'en_transito' THEN 1
                WHEN 'programado' THEN 2
                WHEN 'en_preparacion' THEN 3
                ELSE 4
            END,
            e.fecha_programada ASC
        LIMIT ?
    ");
    $stmt->execute([$empleado_id, $limit]);
    return $stmt->fetchAll();
}

function getEstadoBadge(string $estado): string {
    $badges = [
        'programado' => 'bg-secondary',
        'en_preparacion' => 'bg-warning',
        'en_transito' => 'bg-primary',
        'entregado' => 'bg-success',
        'fallido' => 'bg-danger',
        'devuelto' => 'bg-dark'
    ];
    return $badges[$estado] ?? 'bg-secondary';
}

function getEstadoTexto(string $estado): string {
    $textos = [
        'programado' => 'Programado',
        'en_preparacion' => 'En Preparación',
        'en_transito' => 'En Tránsito',
        'entregado' => 'Entregado',
        'fallido' => 'Fallido',
        'devuelto' => 'Devuelto'
    ];
    return $textos[$estado] ?? ucfirst(str_replace('_', ' ', $estado));
}

$stats = getRepartidorStats($pdo, $empleado_id);
$entregas_recientes = getEntregasRecientes($pdo, $empleado_id, 10);

// Construir contenido
ob_start();
?>

<!-- Header: título + usuario + reloj -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0">Panel de Control - Repartidor</h2>
        <small class="text-muted">Bienvenido, <?= htmlspecialchars($user_name) ?></small>
    </div>

    <div class="d-flex align-items-center">
        <div class="me-3 text-end d-none d-md-block">
            <div class="small text-muted">Repartidor</div>
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
    <?php 
    $stats_config = [
        ['label' => 'Programadas', 'value' => $stats['programadas'], 'color' => 'warning', 'icon' => 'clock'],
        ['label' => 'En Tránsito', 'value' => $stats['en_transito'], 'color' => 'primary', 'icon' => 'shipping-fast'],
        ['label' => 'Hoy', 'value' => $stats['entregadas_hoy'], 'color' => 'success', 'icon' => 'check-circle'],
        ['label' => 'Este Mes', 'value' => $stats['entregadas_mes'], 'color' => 'info', 'icon' => 'calendar-check']
    ];
    foreach ($stats_config as $stat): ?>
        <div class="col-md-3 mb-3">
            <div class="card bg-white shadow-sm">
                <div class="card-body text-center">
                    <h3 class="fw-bold mb-2"><?= number_format($stat['value']) ?></h3>
                    <p class="mb-0 text-muted"><?= htmlspecialchars($stat['label']) ?></p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Actividad reciente -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Entregas Recientes</h5>
                    <a href="entregas.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-truck me-1"></i> Ver Todas
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($entregas_recientes)): ?>
                    <p class="text-center text-muted py-4 mb-0">No tienes entregas asignadas</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Pedido</th>
                                    <th>Cliente</th>
                                    <th>Dirección</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($entregas_recientes as $entrega): ?>
                                <tr>
                                    <td><strong>#<?= $entrega['id'] ?></strong></td>
                                    <td><?= htmlspecialchars($entrega['numero_documento'] ?: 'Sin pedido') ?></td>
                                    <td>
                                        <?= htmlspecialchars($entrega['cliente_nombre'] ?: 'No especificado') ?>
                                        <?php if ($entrega['telefono']): ?>
                                            <br><small class="text-muted">
                                                <i class="fas fa-phone"></i> 
                                                <a href="tel:<?= htmlspecialchars($entrega['telefono']) ?>"><?= htmlspecialchars($entrega['telefono']) ?></a>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small><?= htmlspecialchars($entrega['direccion_entrega'] ?: 'No especificada') ?></small>
                                        <?php if ($entrega['ciudad']): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($entrega['ciudad']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($entrega['fecha_entrega'])) ?>
                                        <?php if ($entrega['fecha_entrega_real']): ?>
                                            <br><small class="text-success">✓ <?= date('d/m H:i', strtotime($entrega['fecha_entrega_real'])) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= getEstadoBadge($entrega['estado']) ?>">
                                            <?= getEstadoTexto($entrega['estado']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <?php if (in_array($entrega['estado'], ['programado', 'en_preparacion', 'en_transito'])): ?>
                                                <a href="entrega.php?id=<?= $entrega['id'] ?>" class="btn btn-success" title="Completar entrega">
                                                    <i class="fas fa-check-circle"></i>
                                                </a>
                                            <?php elseif ($entrega['estado'] === 'entregado'): ?>
                                                <button class="btn btn-info" onclick="verComprobante(<?= $entrega['id'] ?>)" title="Ver comprobante">
                                                    <i class="fas fa-file-pdf"></i>
                                                </button>
                                            <?php endif; ?>
                                            <a href="entregas.php" class="btn btn-outline-secondary" title="Ver todos">
                                                <i class="fas fa-list"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Acciones rápidas -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Acciones Rápidas</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <a href="entregas.php?estado=programado" class="btn btn-outline-warning w-100 py-3 text-decoration-none">
                            <i class="fas fa-clock fa-lg mb-2 d-block"></i>
                            <span>Ver Programados</span>
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="entregas.php?estado=en_transito" class="btn btn-outline-primary w-100 py-3 text-decoration-none">
                            <i class="fas fa-shipping-fast fa-lg mb-2 d-block"></i>
                            <span>En Tránsito</span>
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="entregas.php?estado=entregado" class="btn btn-outline-success w-100 py-3 text-decoration-none">
                            <i class="fas fa-check-circle fa-lg mb-2 d-block"></i>
                            <span>Entregados</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

$additionalJS = <<<JS
<script>
function verComprobante(entregaId) {
    fetch('ajax/obtener_comprobante.php?entrega_id=' + entregaId)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.comprobante_url) {
                window.open(data.comprobante_url, '_blank');
            } else {
                alert('No se encontró comprobante para esta entrega');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al obtener el comprobante');
        });
}

// Reloj del panel: sincronizado con servidor
(function() {
    const serverTsMs = <?= (int)$server_ts_ms ?>;
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

    updateClock();
    setInterval(updateClock, 1000);
})();
</script>
JS;

LayoutManager::renderAdminPage('Panel de Control', $content, '', $additionalJS);
?>
