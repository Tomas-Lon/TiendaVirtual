<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../includes/LayoutManager.php';
require_once '../config/database.php';

$pdo = getConnection();
$mensaje = '';
$tipo_mensaje = '';

function sanitizeClienteData(array $data): array {
    return [
        'nombre' => trim($data['nombre'] ?? ''),
        'numero_documento' => isset($data['numero_documento']) && $data['numero_documento'] !== '' ? trim($data['numero_documento']) : null,
        'tipo_documento' => isset($data['tipo_documento']) && $data['tipo_documento'] !== '' ? trim($data['tipo_documento']) : null,
        'email' => isset($data['email']) && $data['email'] !== '' ? filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL) : null,
        'telefono' => isset($data['telefono']) && $data['telefono'] !== '' ? preg_replace('/[^0-9+]/', '', $data['telefono']) : null,
        'direccion_principal' => isset($data['direccion']) && $data['direccion'] !== '' ? trim($data['direccion']) : null,
        'ciudad' => isset($data['ciudad']) && $data['ciudad'] !== '' ? trim($data['ciudad']) : null,
        'contacto_principal' => isset($data['contacto_principal']) && $data['contacto_principal'] !== '' ? trim($data['contacto_principal']) : null,
        'empleado_asignado' => isset($data['empleado_asignado']) && is_numeric($data['empleado_asignado']) ? intval($data['empleado_asignado']) : null,
        'activo' => isset($data['activo']) ? 1 : 0
    ];
}

function validateClienteData(array $data): array {
    $errors = [];
    if ($data['nombre'] === '' || $data['nombre'] === null) {
        $errors[] = 'El nombre es obligatorio';
    } elseif (mb_strlen($data['nombre']) < 2) {
        $errors[] = 'El nombre debe tener al menos 2 caracteres';
    }
    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'El email no es válido';
    }
    if (!empty($data['telefono']) && strlen($data['telefono']) < 8) {
        $errors[] = 'El teléfono debe tener al menos 8 dígitos';
    }
    return $errors;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING) ?? '';

    try {
        if ($action === 'create') {
            $clienteData = sanitizeClienteData($_POST);
            $validation = validateClienteData($clienteData);
            if (!empty($validation)) {
                throw new Exception(implode('. ', $validation));
            }

            $stmt = $pdo->prepare(
                "INSERT INTO clientes (
                    nombre,
                    numero_documento,
                    tipo_documento,
                    email,
                    telefono,
                    direccion_principal,
                    ciudad,
                    contacto_principal,
                    empleado_asignado,
                    activo,
                    created_at
                ) VALUES (
                    :nombre, :numero_documento, :tipo_documento, :email, :telefono,
                    :direccion_principal, :ciudad, :contacto_principal, :empleado_asignado, :activo, NOW()
                )"
            );

            $stmt->execute([
                ':nombre' => $clienteData['nombre'],
                ':numero_documento' => $clienteData['numero_documento'],
                ':tipo_documento' => $clienteData['tipo_documento'],
                ':email' => $clienteData['email'],
                ':telefono' => $clienteData['telefono'],
                ':direccion_principal' => $clienteData['direccion_principal'],
                ':ciudad' => $clienteData['ciudad'],
                ':contacto_principal' => $clienteData['contacto_principal'],
                ':empleado_asignado' => $clienteData['empleado_asignado'],
                ':activo' => $clienteData['activo']
            ]);

            $mensaje = 'Cliente creado exitosamente';
            $tipo_mensaje = 'success';
        }

        if ($action === 'update') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) throw new Exception('ID de cliente inválido');

            $clienteData = sanitizeClienteData($_POST);
            $validation = validateClienteData($clienteData);
            if (!empty($validation)) throw new Exception(implode('. ', $validation));

            $checkStmt = $pdo->prepare("SELECT id FROM clientes WHERE id = :id");
            $checkStmt->execute([':id' => $id]);
            if (!$checkStmt->fetch()) throw new Exception('Cliente no encontrado');

            $camposPermitidos = [
                'nombre',
                'numero_documento',
                'tipo_documento',
                'email',
                'telefono',
                'direccion_principal',
                'ciudad',
                'contacto_principal',
                'empleado_asignado',
                'activo'
            ];

            $updatePairs = [];
            $updateValues = [];

            foreach ($camposPermitidos as $campo) {
                if (array_key_exists($campo, $clienteData)) {
                    $valor = $clienteData[$campo];
                    if ($campo === 'activo') {
                        $updatePairs[] = "activo = :activo";
                        $updateValues[':activo'] = $clienteData['activo'];
                        continue;
                    }
                    if ($valor !== null && $valor !== '') {
                        $updatePairs[] = "{$campo} = :{$campo}";
                        $updateValues[":{$campo}"] = $valor;
                    }
                }
            }

            if (empty($updatePairs)) {
                throw new Exception('No hay campos para actualizar');
            }

            $updateValues[':id'] = $id;
            $sql = "UPDATE clientes SET " . implode(', ', $updatePairs) . ", updated_at = NOW() WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($updateValues);

            $mensaje = 'Cliente actualizado exitosamente';
            $tipo_mensaje = 'success';
        }

        if ($action === 'toggle') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) {
                throw new Exception('ID de cliente inválido.');
            }

            $stmt = $pdo->prepare("UPDATE clientes SET activo = NOT activo, updated_at = NOW() WHERE id = :id");
            $stmt->execute([':id' => $id]);

            $mensaje = 'Estado actualizado exitosamente';
            $tipo_mensaje = 'success';
        }

        if ($action === 'delete') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) throw new Exception('ID de cliente inválido');

            $check_stmt = $pdo->prepare("SELECT COUNT(*) as count FROM pedidos WHERE cliente_id = :id");
            $check_stmt->execute([':id' => $id]);
            $has_orders = (int)$check_stmt->fetchColumn() > 0;
            if ($has_orders) {
                $mensaje = 'No se puede eliminar el cliente porque tiene pedidos asociados';
                $tipo_mensaje = 'warning';
            } else {
                $stmt = $pdo->prepare("DELETE FROM clientes WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $mensaje = 'Cliente eliminado exitosamente';
                $tipo_mensaje = 'success';
            }
        }
    } catch (Exception $e) {
        error_log("Clientes error: " . $e->getMessage());
        $mensaje = 'Error: ' . $e->getMessage();
        $tipo_mensaje = 'danger';
    }
}

// Filtros y paginación
$search = trim(filter_input(INPUT_GET, 'search', FILTER_UNSAFE_RAW) ?? '');
$ciudad_filter = trim(filter_input(INPUT_GET, 'ciudad', FILTER_UNSAFE_RAW) ?? '');
$activo_filter = isset($_GET['activo']) ? $_GET['activo'] : '';
$page = max(1, (int)filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
$limit = 10;
$offset = ($page - 1) * $limit;

$where_conditions = [];
$params = [];

if ($search !== '') {
    $where_conditions[] = "(c.nombre LIKE :search OR c.email LIKE :search OR c.numero_documento LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($ciudad_filter !== '') {
    $where_conditions[] = "c.ciudad = :ciudad";
    $params[':ciudad'] = $ciudad_filter;
}

if ($activo_filter !== '') {
    if ($activo_filter === '1' || $activo_filter === '0') {
        $where_conditions[] = "c.activo = :activo";
        $params[':activo'] = (int)$activo_filter;
    }
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Total clientes
$count_sql = "SELECT COUNT(*) FROM clientes c $where_clause";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_clientes = (int)$stmt->fetchColumn();
$total_pages = $total_clientes > 0 ? (int)ceil($total_clientes / $limit) : 1;

// Consulta principal con GROUP BY (segura)
$sql = "SELECT c.*,
               COUNT(DISTINCT p.id) AS pedidos_count,
               COUNT(DISTINCT dc.id) AS descuentos_count,
               e.nombre AS empleado_asignado_nombre
        FROM clientes c
        LEFT JOIN empleados e ON c.empleado_asignado = e.id
        LEFT JOIN pedidos p ON c.id = p.cliente_id
        LEFT JOIN descuentos_clientes dc ON c.id = dc.cliente_id
        $where_clause
        GROUP BY c.id
        ORDER BY c.created_at DESC
        LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lista de ciudades
$ciudades_stmt = $pdo->query("SELECT DISTINCT ciudad FROM clientes WHERE ciudad IS NOT NULL AND ciudad <> '' ORDER BY ciudad");
$ciudades = $ciudades_stmt->fetchAll(PDO::FETCH_COLUMN);

// Render content (vista mantenida)
ob_start();
?>

<?php if ($mensaje): ?>
    <div class="alert alert-<?php echo htmlspecialchars($tipo_mensaje); ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($mensaje); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestión de Clientes</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#clienteModal">
        <i class="fas fa-plus"></i> Nuevo Cliente
    </button>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-6">
                <label for="search" class="form-label">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Nombre, email o documento">
            </div>
            <div class="col-md-3">
                <label for="ciudad" class="form-label">Ciudad</label>
                <select class="form-select" id="ciudad" name="ciudad">
                    <option value="">Todas</option>
                    <?php foreach ($ciudades as $ciudad): ?>
                        <option value="<?php echo htmlspecialchars($ciudad); ?>" <?php echo $ciudad_filter === $ciudad ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ciudad); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="activo" class="form-label">Estado</label>
                <select class="form-select" id="activo" name="activo">
                    <option value="">Todos</option>
                    <option value="1" <?php echo $activo_filter === '1' ? 'selected' : ''; ?>>Activos</option>
                    <option value="0" <?php echo $activo_filter === '0' ? 'selected' : ''; ?>>Inactivos</option>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <a href="clientes.php" class="btn btn-secondary">
                    <i class="fas fa-eraser"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body px-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th class="px-3" style="width:25%">Nombre/Empresa</th>
                        <th style="width:15%">Documento</th>
                        <th style="width:20%">Contacto</th>
                        <th style="width:15%">Teléfono</th>
                        <th style="width:10%">Estado</th>
                        <th style="width:15%">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($clientes)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4"><p class="text-muted mb-0">No hay clientes disponibles</p></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($clientes as $cliente): ?>
                            <tr>
                                <td class="px-3">
                                    <div class="fw-bold"><?php echo htmlspecialchars($cliente['nombre']); ?></div>
                                    <small class="text-muted">
                                        <?php if (!empty($cliente['email'])): ?>
                                            <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($cliente['email']); ?>
                                        <?php endif; ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark"><?php echo htmlspecialchars($cliente['tipo_documento']); ?></span>
                                    <small class="d-block"><?php echo htmlspecialchars($cliente['numero_documento']); ?></small>
                                </td>
                                <td><?php echo !empty($cliente['contacto_principal']) ? htmlspecialchars($cliente['contacto_principal']) : '<span class="text-muted">--</span>'; ?></td>
                                <td><?php echo !empty($cliente['telefono']) ? '<i class="fas fa-phone me-1"></i'.htmlspecialchars($cliente['telefono']) : '<span class="text-muted">--</span>'; ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-<?php echo $cliente['activo'] ? 'success' : 'secondary'; ?>"><?php echo $cliente['activo'] ? 'Activo' : 'Inactivo'; ?></span>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="toggle">
                                            <input type="hidden" name="id" value="<?php echo (int)$cliente['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-<?php echo $cliente['activo'] ? 'warning' : 'success'; ?>" title="<?php echo $cliente['activo'] ? 'Desactivar' : 'Activar'; ?> cliente">
                                                <i class="fas fa-<?php echo $cliente['activo'] ? 'ban' : 'check'; ?>"></i>
                                                <span class="d-none d-md-inline ms-1"><?php echo $cliente['activo'] ? 'Desactivar' : 'Activar'; ?></span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick='verDetallesCliente(<?php echo json_encode($cliente, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'><i class="fas fa-eye"></i></button>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick='editCliente(<?php echo json_encode($cliente, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'><i class="fas fa-edit"></i></button>
                                        <?php if (!empty($cliente['pedidos_count']) && $cliente['pedidos_count'] > 0): ?>
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="verPedidos(<?php echo (int)$cliente['id']; ?>)"><i class="fas fa-shopping-cart"></i></button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($total_pages > 1): ?>
<nav aria-label="Paginación" class="mt-4">
    <ul class="pagination justify-content-center">
        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=1&search=<?php echo urlencode($search); ?>&ciudad=<?php echo urlencode($ciudad_filter); ?>&activo=<?php echo urlencode($activo_filter); ?>"><i class="fas fa-angle-double-left"></i></a>
        </li>
        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>"><a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&ciudad=<?php echo urlencode($ciudad_filter); ?>&activo=<?php echo urlencode($activo_filter); ?>"><?php echo $i; ?></a></li>
        <?php endfor; ?>
        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $total_pages; ?>&search=<?php echo urlencode($search); ?>&ciudad=<?php echo urlencode($ciudad_filter); ?>&activo=<?php echo urlencode($activo_filter); ?>"><i class="fas fa-angle-double-right"></i></a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<!-- Modal crear/editar -->
<div class="modal fade" id="clienteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="clienteForm" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="clienteModalLabel">Nuevo Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" id="clienteAction" value="create">
                    <input type="hidden" name="id" id="clienteId">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre *</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                    <div class="mb-3">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="text" class="form-control" id="telefono" name="telefono">
                    </div>
                    <div class="mb-3">
                        <label for="direccion" class="form-label">Dirección</label>
                        <input type="text" class="form-control" id="direccion" name="direccion">
                    </div>
                    <div class="mb-3">
                        <label for="tipo_documento" class="form-label">Tipo Documento</label>
                        <select class="form-select" id="tipo_documento" name="tipo_documento">
                            <option value="">--</option>
                            <option value="NIT">NIT</option>
                            <option value="CC">Cédula de Ciudadanía</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="numero_documento" class="form-label">Número Documento</label>
                        <input type="text" class="form-control" id="numero_documento" name="numero_documento">
                    </div>
                    <div class="mb-3">
                        <label for="ciudad" class="form-label">Ciudad</label>
                        <input type="text" class="form-control" id="ciudad" name="ciudad">
                    </div>
                    <div class="mb-3">
                        <label for="contacto_principal" class="form-label">Contacto Principal</label>
                        <input type="text" class="form-control" id="contacto_principal" name="contacto_principal">
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="activo" name="activo" checked>
                        <label class="form-check-label" for="activo">Activo</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cliente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal detalles -->
<div class="modal fade" id="detallesClienteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Detalles del Cliente</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header"><h6 class="mb-0">Información Personal</h6></div>
                            <div class="card-body">
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">Nombre Completo</dt><dd class="col-sm-8" id="detalle-nombre"></dd>
                                    <dt class="col-sm-4">Email</dt><dd class="col-sm-8" id="detalle-email"></dd>
                                    <dt class="col-sm-4">Teléfono</dt><dd class="col-sm-8" id="detalle-telefono"></dd>
                                    <dt class="col-sm-4">Dirección</dt><dd class="col-sm-8" id="detalle-direccion"></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header"><h6 class="mb-0">Información Adicional</h6></div>
                            <div class="card-body">
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">Estado</dt><dd class="col-sm-8" id="detalle-estado"></dd>
                                    <dt class="col-sm-4">Fecha Registro</dt><dd class="col-sm-8" id="detalle-fecha"></dd>
                                    <dt class="col-sm-4">Total Pedidos</dt><dd class="col-sm-8" id="detalle-pedidos"></dd>
                                    <dt class="col-sm-4">Descuentos</dt><dd class="col-sm-8" id="detalle-descuentos"></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button></div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

$additionalJS = <<<'JS'
<script>
function editCliente(cliente) {
    if (!cliente || !cliente.id) return;
    document.getElementById('clienteAction').value = 'update';
    document.getElementById('clienteId').value = cliente.id;
    const fields = ['nombre','email','telefono','direccion','tipo_documento','numero_documento','ciudad','contacto_principal'];
    fields.forEach(f => {
        const el = document.getElementById(f);
        if (!el) return;
        el.value = cliente[f] ?? (cliente[f+'_principal'] ?? '');
    });
    document.getElementById('activo').checked = cliente.activo ? true : false;
    document.getElementById('clienteModalLabel').textContent = 'Editar Cliente';
    new bootstrap.Modal(document.getElementById('clienteModal')).show();
}

function verDetallesCliente(cliente) {
    if (!cliente) return;
    document.getElementById('detalle-nombre').textContent = cliente.nombre || '-';
    document.getElementById('detalle-email').textContent = cliente.email || '-';
    document.getElementById('detalle-telefono').textContent = cliente.telefono || '-';
    document.getElementById('detalle-direccion').textContent = cliente.direccion_principal || '-';
    document.getElementById('detalle-estado').innerHTML = `<span class="badge bg-${cliente.activo ? 'success' : 'secondary'}">${cliente.activo ? 'Activo' : 'Inactivo'}</span>`;
    document.getElementById('detalle-fecha').textContent = cliente.created_at ? new Date(cliente.created_at).toLocaleString('es-ES') : '-';
    document.getElementById('detalle-pedidos').innerHTML = `<span class="badge bg-info">${cliente.pedidos_count ?? 0}</span>`;
    document.getElementById('detalle-descuentos').innerHTML = `<span class="badge bg-success">${cliente.descuentos_count ?? 0}</span>`;
    new bootstrap.Modal(document.getElementById('detallesClienteModal')).show();
}

function verPedidos(clienteId) {
    if (!clienteId) return;
    const modal = new bootstrap.Modal(document.getElementById('pedidosModal'));
    const container = document.getElementById('pedidos-container');
    container.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div></div>`;
    fetch(`get_pedidos_cliente.php?id=${encodeURIComponent(clienteId)}`)
    .then(r => r.json())
    .then(pedidos => {
        if (!Array.isArray(pedidos) || pedidos.length === 0) {
            container.innerHTML = '<p class="text-center text-muted py-4">No hay pedidos registrados</p>';
            modal.show();
            return;
        }
        const estadoClases = {'borrador':'secondary','pendiente':'warning','confirmado':'primary','en_preparacion':'info','listo_envio':'info','enviado':'success','entregado':'success','cancelado':'danger'};
        const rows = pedidos.map(p => `<tr><td>${p.numero_documento}</td><td>${new Date(p.fecha_pedido).toLocaleDateString('es-ES')}</td><td>${new Intl.NumberFormat('es-CO',{style:'currency',currency:'COP'}).format(p.total)}</td><td><span class="badge bg-${estadoClases[p.estado]||'secondary'}">${(p.estado||'').replace(/_/g,' ')}</span></td></tr>`).join('');
        container.innerHTML = `<div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Número</th><th>Fecha</th><th>Total</th><th>Estado</th></tr></thead><tbody>${rows}</tbody></table></div>`;
        modal.show();
    })
    .catch(() => {
        container.innerHTML = '<div class="alert alert-danger">Error al cargar los pedidos</div>';
        modal.show();
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const clienteModal = document.getElementById('clienteModal');
    if (clienteModal) {
        clienteModal.addEventListener('hidden.bs.modal', function() {
            const form = document.getElementById('clienteForm');
            if (form) {
                form.reset();
                document.getElementById('clienteAction').value = 'create';
                document.getElementById('clienteId').value = '';
                document.getElementById('clienteModalLabel').textContent = 'Nuevo Cliente';
                document.getElementById('activo').checked = true;
            }
        });
    }

    const clienteForm = document.getElementById('clienteForm');
    if (clienteForm) {
        clienteForm.addEventListener('submit', function(e) {
            const nombre = document.getElementById('nombre').value.trim();
            const email = document.getElementById('email').value.trim();
            const telefono = document.getElementById('telefono').value.trim();
            const direccion = document.getElementById('direccion').value.trim();
            const errors = [];
            if (nombre.length < 2) errors.push("El nombre debe tener al menos 2 caracteres");
            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) errors.push("El email no es válido");
            if (telefono && telefono.replace(/\D/g,'').length < 8) errors.push("El teléfono debe tener al menos 8 dígitos");
            if (direccion && direccion.length < 5) errors.push("La dirección debe tener al menos 5 caracteres");
            if (errors.length > 0) {
                e.preventDefault();
                alert("Por favor corrija los siguientes errores:\n\n" + errors.join("\n"));
            }
        });
    }
});
</script>
JS;

LayoutManager::renderAdminPage('Gestión de Clientes', $content, '', $additionalJS);
