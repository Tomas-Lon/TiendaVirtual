<?php
session_start();

// Verificar que el usuario esté autenticado como cliente
if (!isset($_SESSION['usuario']) || ($_SESSION['tipo'] ?? '') !== 'cliente') {
	header('Location: ../index.php');
	exit;
}

// Asegurar que el Layout use el menú de cliente
$_SESSION['user_role'] = 'cliente';

// Datos de usuario y hora del servidor
$user_name = $_SESSION['nombre'] ?? $_SESSION['usuario'] ?? 'Cliente';
$server_dt = new DateTime('now', new DateTimeZone(date_default_timezone_get()));
$server_ts_ms = $server_dt->getTimestamp() * 1000;

require_once '../includes/LayoutManager.php';
require_once '../config/database.php';

$pdo = getConnection();
$clienteId = (int)($_SESSION['cliente_id'] ?? 0);

// Inicializar
$stats = [
	'mis_pedidos' => 0,
	'en_proceso' => 0,
	'total_gastado' => 0.0,
	'carrito' => (int)array_sum($_SESSION['carrito'] ?? [])
];
$pedidos_recientes = [];
$productos_sugeridos = [];

function getClienteStats(PDO $pdo, int $clienteId): array {
	$data = [
		'mis_pedidos' => 0,
		'en_proceso' => 0,
		'total_gastado' => 0.0
	];
	try {
		// Total de pedidos del cliente
		$stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM pedidos WHERE cliente_id = ?");
		$stmt->execute([$clienteId]);
		$data['mis_pedidos'] = (int)($stmt->fetch()['total'] ?? 0);

		// Pedidos en proceso (no entregado ni cancelado)
		$stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM pedidos WHERE cliente_id = ? AND estado NOT IN ('entregado','cancelado')");
		$stmt->execute([$clienteId]);
		$data['en_proceso'] = (int)($stmt->fetch()['total'] ?? 0);

		// Total gastado (excluye cancelados)
		$stmt = $pdo->prepare("SELECT COALESCE(SUM(total),0) AS gastado FROM pedidos WHERE cliente_id = ? AND estado <> 'cancelado'");
		$stmt->execute([$clienteId]);
		$data['total_gastado'] = (float)($stmt->fetch()['gastado'] ?? 0);
	} catch (PDOException $e) {
		error_log('Error stats cliente: ' . $e->getMessage());
	}
	return $data;
}

function getPedidosRecientesCliente(PDO $pdo, int $clienteId): array {
	try {
		$stmt = $pdo->prepare("SELECT numero_documento, fecha_pedido, estado, total FROM pedidos WHERE cliente_id = ? ORDER BY created_at DESC LIMIT 5");
		$stmt->execute([$clienteId]);
		return $stmt->fetchAll();
	} catch (PDOException $e) {
		error_log('Error pedidos recientes cliente: ' . $e->getMessage());
		return [];
	}
}

function getProductosSugeridos(PDO $pdo, int $clienteId): array {
	try {
		// Basado en compras previas del cliente; si no tiene, mostrar top generales
		$sqlCliente = "
			SELECT pr.codigo, pr.descripcion, pr.precio, COALESCE(SUM(dp.cantidad),0) AS cantidad
			FROM detalle_pedidos dp
			INNER JOIN pedidos p ON p.id = dp.pedido_id AND p.cliente_id = :cliente
			INNER JOIN productos pr ON pr.id = dp.producto_id
			GROUP BY pr.id
			ORDER BY cantidad DESC, pr.precio DESC
			LIMIT 5
		";
		$stmt = $pdo->prepare($sqlCliente);
		$stmt->execute([':cliente' => $clienteId]);
		$rows = $stmt->fetchAll();
		if ($rows && count($rows) > 0) return $rows;

		// Fallback: populares generales (excluye borrador/cancelado)
		$sqlAll = "
			SELECT pr.codigo, pr.descripcion, pr.precio, COALESCE(SUM(dp.cantidad),0) AS cantidad
			FROM productos pr
			LEFT JOIN detalle_pedidos dp ON dp.producto_id = pr.id
			LEFT JOIN pedidos p ON p.id = dp.pedido_id AND p.estado NOT IN ('borrador','cancelado')
			GROUP BY pr.id
			ORDER BY cantidad DESC, pr.precio DESC
			LIMIT 5
		";
		$stmt = $pdo->query($sqlAll);
		return $stmt->fetchAll();
	} catch (PDOException $e) {
		error_log('Error productos sugeridos: ' . $e->getMessage());
		return [];
	}
}

// Cargar datos iniciales
$stat_core = getClienteStats($pdo, $clienteId);
$stats = array_merge($stats, $stat_core);
$pedidos_recientes = getPedidosRecientesCliente($pdo, $clienteId);
$productos_sugeridos = getProductosSugeridos($pdo, $clienteId);

// Contenido
ob_start();
?>

<!-- Header: título + usuario + reloj -->
<div class="d-flex justify-content-between align-items-center mb-4">
	<div>
		<h2 class="mb-0">Inicio</h2>
		<small class="text-muted">Hola, <?= htmlspecialchars($user_name) ?></small>
	</div>

	<div class="d-flex align-items-center">
		<div class="me-3 text-end d-none d-md-block">
			<div class="small text-muted">Cliente</div>
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
		'Mis Pedidos' => number_format($stats['mis_pedidos']),
		'En Proceso' => number_format($stats['en_proceso']),
		'Total Gastado' => '$' . number_format($stats['total_gastado'], 0, ',', '.'),
		'Carrito' => number_format($stats['carrito'])
	] as $label => $value): ?>
		<div class="col-6 col-md-3 mb-3">
			<div class="card bg-white shadow-sm">
				<div class="card-body text-center">
					<h3 class="fw-bold mb-2"><?= $value ?></h3>
					<p class="mb-0 text-muted"><?= htmlspecialchars($label) ?></p>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
	</div>

<div class="row">
	<!-- Pedidos recientes -->
	<div class="col-md-6 mb-4">
		<div class="card">
			<div class="card-header bg-light">
				<h5 class="card-title mb-0">Mis últimos pedidos</h5>
			</div>
			<div class="card-body">
				<?php if (!$pedidos_recientes): ?>
					<p class="text-center text-muted py-4 mb-0">Aún no tienes pedidos</p>
				<?php else: ?>
					<?php foreach ($pedidos_recientes as $p): ?>
						<div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
							<div>
								<h6 class="mb-1">#<?= htmlspecialchars($p['numero_documento']) ?></h6>
								<small class="text-muted">Estado: <?= htmlspecialchars($p['estado']) ?></small>
							</div>
							<div class="text-end">
								<div class="fw-bold">$<?= number_format((float)$p['total'], 0, ',', '.') ?></div>
								<small class="text-muted"><?= isset($p['fecha_pedido']) ? date('d/m/Y', strtotime($p['fecha_pedido'])) : '' ?></small>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<!-- Productos sugeridos -->
	<div class="col-md-6 mb-4">
		<div class="card">
			<div class="card-header bg-light">
				<h5 class="card-title mb-0">Recomendados para ti</h5>
			</div>
			<div class="card-body">
				<?php if (!$productos_sugeridos): ?>
					<p class="text-center text-muted py-4 mb-0">No hay sugerencias por ahora</p>
				<?php else: ?>
					<?php foreach ($productos_sugeridos as $prod): ?>
						<div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
							<div>
								<h6 class="mb-1"><?= htmlspecialchars(mb_strimwidth($prod['descripcion'], 0, 32, '...')) ?></h6>
								<small class="text-muted">Código: <?= htmlspecialchars($prod['codigo']) ?></small>
							</div>
							<div class="text-end">
								<div class="fw-bold">$<?= number_format((float)$prod['precio'], 0, ',', '.') ?></div>
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
				<h5 class="card-title mb-0">Accesos rápidos</h5>
			</div>
			<div class="card-body">
				<div class="row">
					<?php
					$acciones = [
						['productos.php', 'primary', 'box-open', 'Ver Catálogo'],
						['nueva_compra.php', 'success', 'cart-plus', 'Nueva Compra'],
						['pedidos.php', 'warning', 'clipboard-list', 'Mis Pedidos'],
						['cotizaciones.php', 'info', 'file-invoice-dollar', 'Cotizaciones'],
						['perfil.php', 'secondary', 'user-circle', 'Mi Perfil']
					];
					foreach ($acciones as [$link, $color, $icon, $text]): ?>
						<div class="col-md-4 col-lg-3 mb-3">
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

$additionalJS = <<<JS
<script>
function actualizarEstadisticasCliente() {
	fetch('ajax/dashboard_stats.php', { credentials: 'same-origin' })
		.then(r => r.json())
		.then(result => {
			if (result.success) {
				const data = result.data;
				const stats = [
					(data.mis_pedidos || 0),
					(data.en_proceso || 0),
					((data.total_gastado || 0).toLocaleString('es-CO', {style:'currency', currency:'COP'})),
					(data.carrito || 0)
				];
				// Seleccionar los H3 de las tarjetas y actualizar
				document.querySelectorAll('.stats-card .fw-bold').forEach((el, i) => {
					el.textContent = stats[i] !== undefined ? (i === 2 && typeof stats[i] === 'string' ? stats[i] : Number(stats[i]).toLocaleString()) : '0';
				});
			}
		})
		.catch(console.error);
}
setInterval(actualizarEstadisticasCliente, 30000);

// Reloj con hora del servidor
(function() {
	let serverTime = new Date(<?= (int)$server_ts_ms ?>);
	const clockDisplay = document.getElementById('clockDisplay');
	const clockDate = document.getElementById('clockDate');

	function pad(n) { return n < 10 ? '0' + n : n; }

	function updateClock() {
		const h = pad(serverTime.getHours());
		const m = pad(serverTime.getMinutes());
		const s = pad(serverTime.getSeconds());
		if (clockDisplay) clockDisplay.textContent = h + ':' + m + ':' + s;

		const d = pad(serverTime.getDate());
		const mo = pad(serverTime.getMonth() + 1);
		const y = serverTime.getFullYear();
		if (clockDate) clockDate.textContent = d + '/' + mo + '/' + y;

		serverTime = new Date(serverTime.getTime() + 1000);
	}

	updateClock();
	setInterval(updateClock, 1000);
})();
</script>
JS;

// Renderizar página usando LayoutManager (aprovecha mismo layout elegante)
LayoutManager::renderAdminPage(
	'Inicio',
	$content,
	'',
	$additionalJS
);
?>

