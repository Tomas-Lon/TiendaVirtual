<?php
session_start();

// Verificar autenticación
if (!isset($_SESSION['usuario']) || ($_SESSION['tipo'] ?? '') !== 'cliente') {
	header('Location: ../index.php');
	exit;
}

if (empty($_SESSION['cliente_id'])) {
	header('Location: ../index.php');
	exit;
}

// Establecer rol para LayoutManager
$_SESSION['user_role'] = 'cliente';

require_once '../includes/LayoutManager.php';
require_once '../config/database.php';

$pdo = getConnection();
$clienteId = (int)$_SESSION['cliente_id'];
$user_name = $_SESSION['nombre'] ?? 'Cliente';

// Generar token CSRF
if (empty($_SESSION['csrf_token'])) {
	$_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}

// Variables iniciales
$cliente = null;
$direcciones = [];

// ===================== PROCESAR POST =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$action = $_POST['action'] ?? '';
	$csrf = $_POST['csrf_token'] ?? '';
	
	// Validar CSRF
	if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
		$_SESSION['flash_message'] = 'Token inválido.';
		$_SESSION['flash_type'] = 'danger';
		header('Location: perfil.php');
		exit;
	}
	
	try {
		if ($action === 'save_profile') {
			$nombre = trim($_POST['nombre'] ?? '');
			$telefono = trim($_POST['telefono'] ?? '');
			$email = trim($_POST['email'] ?? '');
			$ciudad = trim($_POST['ciudad'] ?? '');
			$direccion = trim($_POST['direccion'] ?? '');
			
			$stmt = $pdo->prepare('UPDATE clientes SET nombre=?, telefono=?, email=?, ciudad=?, direccion_principal=? WHERE id=?');
			$stmt->execute([$nombre, $telefono, $email, $ciudad, $direccion, $clienteId]);
			
			$_SESSION['nombre'] = $nombre;
			$_SESSION['flash_message'] = 'Perfil actualizado correctamente.';
			$_SESSION['flash_type'] = 'success';
			header('Location: perfil.php');
			exit;
			
		} elseif ($action === 'add_address') {
			$nombre = trim($_POST['nombre_direccion'] ?? '');
			$direccion = trim($_POST['nueva_direccion'] ?? '');
			$ciudad = trim($_POST['nueva_ciudad'] ?? '');
			$departamento = trim($_POST['nueva_departamento'] ?? '');
			$telefono = trim($_POST['nueva_telefono'] ?? '');
			
			if (empty($nombre) || empty($direccion) || empty($ciudad)) {
				$_SESSION['flash_message'] = 'Nombre, dirección y ciudad son obligatorios.';
				$_SESSION['flash_type'] = 'danger';
				header('Location: perfil.php');
				exit;
			}
			
			try {
				$stmt = $pdo->prepare('INSERT INTO direcciones_clientes (cliente_id, nombre, direccion, ciudad, departamento, telefono, es_envio, activo) VALUES (?,?,?,?,?,?,1,1)');
				$stmt->execute([$clienteId, $nombre, $direccion, $ciudad, $departamento, $telefono]);
				$_SESSION['flash_message'] = 'Dirección agregada correctamente.';
				$_SESSION['flash_type'] = 'success';
			} catch (PDOException $e) {
				error_log('add_address error: ' . $e->getMessage());
				$_SESSION['flash_message'] = 'Error al guardar la dirección.';
				$_SESSION['flash_type'] = 'danger';
			}
			header('Location: perfil.php');
			exit;
			
		} elseif ($action === 'delete_address') {
			$addr_id = (int)($_POST['address_id'] ?? 0);
			if ($addr_id > 0) {
				$stmt = $pdo->prepare('DELETE FROM direcciones_clientes WHERE id=? AND cliente_id=?');
				$stmt->execute([$addr_id, $clienteId]);
				$_SESSION['flash_message'] = 'Dirección eliminada.';
				$_SESSION['flash_type'] = 'success';
			}
			header('Location: perfil.php');
			exit;
			
		} elseif ($action === 'change_password') {
			$current = $_POST['current_password'] ?? '';
			$new = $_POST['new_password'] ?? '';
			$confirm = $_POST['confirm_password'] ?? '';
			
			if ($new !== $confirm || empty($new)) {
				$_SESSION['flash_message'] = 'Las contraseñas no coinciden o están vacías.';
				$_SESSION['flash_type'] = 'danger';
				header('Location: perfil.php');
				exit;
			}
			
			// Obtener contraseña actual de credenciales
			$stmt = $pdo->prepare('SELECT contrasena FROM credenciales WHERE cliente_id=? AND tipo="cliente"');
			$stmt->execute([$clienteId]);
			$row = $stmt->fetch();
			
			if (!$row || !password_verify($current, $row['contrasena'])) {
				$_SESSION['flash_message'] = 'Contraseña actual incorrecta.';
				$_SESSION['flash_type'] = 'danger';
				header('Location: perfil.php');
				exit;
			}
			
			$hashed = password_hash($new, PASSWORD_DEFAULT);
			$stmt = $pdo->prepare('UPDATE credenciales SET contrasena=? WHERE cliente_id=? AND tipo="cliente"');
			$stmt->execute([$hashed, $clienteId]);
			
			$_SESSION['flash_message'] = 'Contraseña actualizada correctamente.';
			$_SESSION['flash_type'] = 'success';
			header('Location: perfil.php');
			exit;
		}
	} catch (Exception $e) {
		error_log('perfil.php POST error: ' . $e->getMessage());
		$_SESSION['flash_message'] = 'Error procesando la solicitud.';
		$_SESSION['flash_type'] = 'danger';
		header('Location: perfil.php');
		exit;
	}
}

// ===================== CARGAR DATOS =====================
try {
	// Cargar cliente
	$stmt = $pdo->prepare('SELECT id, nombre, telefono, email, ciudad, direccion_principal, activo, created_at FROM clientes WHERE id=?');
	$stmt->execute([$clienteId]);
	$cliente = $stmt->fetch(PDO::FETCH_ASSOC);
	
	if ($cliente) {
		$_SESSION['nombre'] = $cliente['nombre'];
		$user_name = $cliente['nombre'];
	}
	
	// Cargar direcciones (usar la tabla existente con columna 'nombre')
	$stmt = $pdo->prepare('SELECT id, nombre, direccion, ciudad, departamento, telefono, created_at FROM direcciones_clientes WHERE cliente_id=? AND activo=1 ORDER BY created_at DESC');
	$stmt->execute([$clienteId]);
	$direcciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
	if (!is_array($direcciones)) {
		$direcciones = [];
	}
} catch (Exception $e) {
	error_log('perfil.php load error: ' . $e->getMessage());
}

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
	<div>
		<h2 class="mb-0">Mi Perfil</h2>
		<small class="text-muted">Hola, <?= htmlspecialchars($user_name) ?></small>
	</div>
</div>

<!-- Flash Message -->
<?php if (!empty($_SESSION['flash_message'])): ?>
	<?php $msg = $_SESSION['flash_message']; $type = $_SESSION['flash_type'] ?? 'info'; unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
	<div class="alert alert-<?= htmlspecialchars($type) ?> alert-dismissible fade show" role="alert">
		<?= htmlspecialchars($msg) ?>
		<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
	</div>
<?php endif; ?>

<!-- Tabs Navigation -->
<ul class="nav nav-tabs mb-3" id="perfilTabs" role="tablist">
	<li class="nav-item">
		<button class="nav-link active" id="tab-perfil" data-bs-toggle="tab" data-bs-target="#content-perfil" type="button">
			<i class="fas fa-user-circle me-2"></i>Perfil
		</button>
	</li>
	<li class="nav-item">
		<button class="nav-link" id="tab-direcciones" data-bs-toggle="tab" data-bs-target="#content-direcciones" type="button">
			<i class="fas fa-map-marker-alt me-2"></i>Direcciones
		</button>
	</li>
</ul>

<div class="tab-content">
	<!-- TAB: PERFIL -->
	<div class="tab-pane fade show active" id="content-perfil">
		<?php if (!$cliente): ?>
			<div class="alert alert-danger">No se pudo cargar tu información.</div>
		<?php else: ?>
			<div class="row">
				<div class="col-lg-8">
					<div class="card">
						<div class="card-header bg-light">
							<h5 class="mb-0">Información Personal</h5>
						</div>
						<div class="card-body">
							<form method="POST">
								<input type="hidden" name="action" value="save_profile">
								<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
								
								<div class="row g-3">
									<div class="col-md-6">
										<label class="form-label">Nombre completo</label>
										<input type="text" class="form-control" name="nombre" value="<?= htmlspecialchars($cliente['nombre'] ?? '') ?>" required>
									</div>
									<div class="col-md-6">
										<label class="form-label">Teléfono</label>
										<input type="text" class="form-control" name="telefono" value="<?= htmlspecialchars($cliente['telefono'] ?? '') ?>">
									</div>
									<div class="col-md-6">
										<label class="form-label">Email</label>
										<input type="email" class="form-control" name="email" value="<?= htmlspecialchars($cliente['email'] ?? '') ?>">
									</div>
									<div class="col-md-6">
										<label class="form-label">Ciudad</label>
										<input type="text" class="form-control" name="ciudad" value="<?= htmlspecialchars($cliente['ciudad'] ?? '') ?>">
									</div>
									<div class="col-12">
										<label class="form-label">Dirección principal</label>
										<input type="text" class="form-control" name="direccion" value="<?= htmlspecialchars($cliente['direccion_principal'] ?? '') ?>">
									</div>
								</div>
								<div class="mt-4">
									<button type="submit" class="btn btn-primary">
										<i class="fas fa-save me-2"></i>Guardar cambios
									</button>
								</div>
							</form>
						</div>
					</div>
				</div>
				
				<div class="col-lg-4">
					<div class="card">
						<div class="card-header bg-light">
							<h5 class="mb-0">Información de Cuenta</h5>
						</div>
						<div class="card-body">
							<div class="mb-3">
								<small class="text-muted">ID Cliente</small>
								<p class="fw-bold"><?= $clienteId ?></p>
							</div>
							<div class="mb-3">
								<small class="text-muted">Estado</small>
								<p class="fw-bold">
									<?php if ($cliente['activo']): ?>
										<span class="badge bg-success">Activo</span>
									<?php else: ?>
										<span class="badge bg-warning">Inactivo</span>
									<?php endif; ?>
								</p>
							</div>
							<div class="mb-3">
								<small class="text-muted">Miembro desde</small>
								<p class="fw-bold"><?= date('d/m/Y', strtotime($cliente['created_at'])) ?></p>
							</div>
							<hr>
							<button type="button" class="btn btn-outline-danger btn-sm w-100" data-bs-toggle="modal" data-bs-target="#cambiarContrasenaModal">
								<i class="fas fa-lock me-1"></i>Cambiar contraseña
							</button>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
	
	<!-- TAB: DIRECCIONES -->
	<div class="tab-pane fade" id="content-direcciones">
		<div class="row mb-3">
			<div class="col-12 d-flex justify-content-between align-items-center">
				<h5 class="mb-0">Mis direcciones guardadas</h5>
				<button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#agregarDireccionModal">
					<i class="fas fa-plus me-1"></i>Agregar dirección
				</button>
			</div>
		</div>
		
		<?php if (empty($direcciones)): ?>
			<div class="card">
				<div class="card-body text-center py-5">
					<i class="fas fa-map-marked fa-3x text-muted mb-3 d-block"></i>
					<p class="text-muted">No tienes direcciones guardadas. ¡Agrega una para comenzar!</p>
				</div>
			</div>
		<?php else: ?>
			<div class="row row-cols-1 row-cols-md-2 g-3">
				<?php foreach ($direcciones as $d): ?>
					<div class="col">
						<div class="card h-100">
							<div class="card-body">
								<div class="d-flex justify-content-between align-items-start mb-3">
									<div>
										<h6 class="card-title mb-1"><?= htmlspecialchars($d['nombre']) ?></h6>
										<small class="text-muted"><?= date('d/m/Y', strtotime($d['created_at'])) ?></small>
									</div>
									<form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar esta dirección?')">
										<input type="hidden" name="action" value="delete_address">
										<input type="hidden" name="address_id" value="<?= (int)$d['id'] ?>">
										<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
										<button type="submit" class="btn btn-sm btn-outline-danger">
											<i class="fas fa-trash-alt"></i>
										</button>
									</form>
								</div>
								<p class="text-muted small mb-2 pb-2 border-bottom">
									<?= nl2br(htmlspecialchars($d['direccion'])) ?>
								</p>
								<div class="small">
									<div class="mb-1"><strong>Ciudad:</strong> <?= htmlspecialchars($d['ciudad'] ?? 'N/A') ?></div>
									<?php if (!empty($d['departamento'])): ?>
										<div class="mb-1"><strong>Departamento:</strong> <?= htmlspecialchars($d['departamento']) ?></div>
									<?php endif; ?>
									<div><strong>Teléfono:</strong> <?= htmlspecialchars($d['telefono'] ?? 'N/A') ?></div>
								</div>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</div>

<!-- MODAL: Agregar Dirección -->
<div class="modal fade" id="agregarDireccionModal" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content">
			<form method="POST">
				<input type="hidden" name="action" value="add_address">
				<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
				
				<div class="modal-header">
					<h5 class="modal-title">Agregar dirección</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				
				<div class="modal-body">
					<div class="mb-3">
						<label class="form-label">Nombre/Identificador <span class="text-danger">*</span></label>
						<input type="text" name="nombre_direccion" class="form-control" placeholder="Ej: Casa, Oficina, Almacén" required>
					</div>
					<div class="mb-3">
						<label class="form-label">Dirección <span class="text-danger">*</span></label>
						<textarea name="nueva_direccion" class="form-control" rows="3" placeholder="Calle, número, apto, etc." required></textarea>
					</div>
					<div class="row g-2">
						<div class="col-md-6">
							<label class="form-label">Ciudad <span class="text-danger">*</span></label>
							<input type="text" name="nueva_ciudad" class="form-control" required>
						</div>
						<div class="col-md-6">
							<label class="form-label">Departamento</label>
							<input type="text" name="nueva_departamento" class="form-control">
						</div>
					</div>
					<div class="mb-3">
						<label class="form-label">Teléfono</label>
						<input type="text" name="nueva_telefono" class="form-control">
					</div>
				</div>
				
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
					<button type="submit" class="btn btn-primary">Guardar dirección</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- MODAL: Cambiar Contraseña -->
<div class="modal fade" id="cambiarContrasenaModal" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content">
			<form method="POST">
				<input type="hidden" name="action" value="change_password">
				<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
				
				<div class="modal-header">
					<h5 class="modal-title">Cambiar contraseña</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				
				<div class="modal-body">
					<div class="mb-3">
						<label class="form-label">Contraseña actual <span class="text-danger">*</span></label>
						<input type="password" name="current_password" class="form-control" required>
					</div>
					<div class="mb-3">
						<label class="form-label">Nueva contraseña <span class="text-danger">*</span></label>
						<input type="password" name="new_password" class="form-control" required>
					</div>
					<div class="mb-3">
						<label class="form-label">Confirmar contraseña <span class="text-danger">*</span></label>
						<input type="password" name="confirm_password" class="form-control" required>
					</div>
				</div>
				
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
					<button type="submit" class="btn btn-primary">Actualizar contraseña</button>
				</div>
			</form>
		</div>
	</div>
</div>

<?php
$content = ob_get_clean();
LayoutManager::renderAdminPage('Mi Perfil', $content);
?>
