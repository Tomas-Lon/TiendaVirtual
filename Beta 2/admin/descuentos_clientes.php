<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../includes/LayoutManager.php';
require_once '../includes/ComponentHelper.php';
require_once '../config/database.php';

$pdo = getConnection();
$mensaje = '';
$tipo_mensaje = '';

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING) ?? '';

    try {
        if ($action === 'create') {
            $cliente_id = filter_input(INPUT_POST, 'cliente_id', FILTER_VALIDATE_INT);
            $grupo_id = filter_input(INPUT_POST, 'grupo_id', FILTER_VALIDATE_INT);
            $porcentaje = filter_input(INPUT_POST, 'porcentaje_descuento', FILTER_VALIDATE_FLOAT);
            $activo = isset($_POST['activo']) && $_POST['activo'] ? 1 : 0;

            if (!$cliente_id || !$grupo_id || $porcentaje === false || $porcentaje === null) {
                throw new Exception('Todos los campos son obligatorios y deben ser válidos.');
            }

            if ($porcentaje < 0 || $porcentaje > 100) {
                throw new Exception('El porcentaje debe estar entre 0 y 100.');
            }

            $check = $pdo->prepare("SELECT id FROM descuentos_clientes WHERE cliente_id = :cliente_id AND grupo_id = :grupo_id");
            $check->execute([':cliente_id' => $cliente_id, ':grupo_id' => $grupo_id]);
            if ($check->fetch()) {
                throw new Exception('Ya existe un descuento para este cliente y grupo.');
            }

            $stmt = $pdo->prepare("INSERT INTO descuentos_clientes (cliente_id, grupo_id, porcentaje_descuento, activo, created_at) VALUES (:cliente_id, :grupo_id, :porcentaje, :activo, NOW())");
            $stmt->execute([
                ':cliente_id' => $cliente_id,
                ':grupo_id' => $grupo_id,
                ':porcentaje' => $porcentaje,
                ':activo' => $activo
            ]);

            $mensaje = 'Descuento creado exitosamente';
            $tipo_mensaje = 'success';
        }

        if ($action === 'update') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $grupo_id = filter_input(INPUT_POST, 'grupo_id', FILTER_VALIDATE_INT);
            $porcentaje = filter_input(INPUT_POST, 'porcentaje_descuento', FILTER_VALIDATE_FLOAT);
            $activo = isset($_POST['activo']) && $_POST['activo'] ? 1 : 0;
            $cliente_id = filter_input(INPUT_POST, 'cliente_id', FILTER_VALIDATE_INT); // opcional, por si se envía

            if (!$id || !$grupo_id || $porcentaje === false || $porcentaje === null) {
                throw new Exception('Datos incompletos o inválidos.');
            }

            if ($porcentaje < 0 || $porcentaje > 100) {
                throw new Exception('El porcentaje debe estar entre 0 y 100.');
            }

            // Verificar duplicado excluyendo el registro actual
            $check = $pdo->prepare("SELECT id FROM descuentos_clientes WHERE cliente_id = :cliente_id AND grupo_id = :grupo_id AND id != :id");
            $check->execute([
                ':cliente_id' => $cliente_id,
                ':grupo_id' => $grupo_id,
                ':id' => $id
            ]);
            if ($check->fetch()) {
                throw new Exception('Ya existe otro descuento para este cliente y grupo.');
            }

            $stmt = $pdo->prepare("UPDATE descuentos_clientes SET grupo_id = :grupo_id, porcentaje_descuento = :porcentaje, activo = :activo, updated_at = NOW() WHERE id = :id");
            $stmt->execute([
                ':grupo_id' => $grupo_id,
                ':porcentaje' => $porcentaje,
                ':activo' => $activo,
                ':id' => $id
            ]);

            $mensaje = 'Descuento actualizado exitosamente';
            $tipo_mensaje = 'success';
        }

        if ($action === 'delete') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) {
                throw new Exception('ID de descuento inválido.');
            }

            $stmt = $pdo->prepare("DELETE FROM descuentos_clientes WHERE id = :id");
            $stmt->execute([':id' => $id]);

            $mensaje = 'Descuento eliminado exitosamente';
            $tipo_mensaje = 'success';
        }

        if ($action === 'toggle') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) {
                throw new Exception('ID de descuento inválido.');
            }

            $stmt = $pdo->prepare("UPDATE descuentos_clientes SET activo = NOT activo, updated_at = NOW() WHERE id = :id");
            $stmt->execute([':id' => $id]);

            $mensaje = 'Estado actualizado exitosamente';
            $tipo_mensaje = 'success';
        }
    } catch (Exception $e) {
        $mensaje = 'Error: ' . $e->getMessage();
        $tipo_mensaje = 'danger';
    }
}

// Filtros y paginación (GET)
$search = trim(filter_input(INPUT_GET, 'search', FILTER_UNSAFE_RAW) ?? '');
$cliente_filter = filter_input(INPUT_GET, 'cliente', FILTER_VALIDATE_INT);
$grupo_filter = filter_input(INPUT_GET, 'grupo', FILTER_VALIDATE_INT);
$activo_filter = isset($_GET['activo']) ? $_GET['activo'] : '';
$page = max(1, intval(filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

// Construir WHERE clause con parámetros nombrados
$where_conditions = [];
$params = [];

if ($search !== '') {
    $where_conditions[] = "(c.nombre LIKE :search OR g.nombre LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($cliente_filter) {
    $where_conditions[] = "d.cliente_id = :cliente_id";
    $params[':cliente_id'] = $cliente_filter;
}

if ($grupo_filter) {
    $where_conditions[] = "d.grupo_id = :grupo_id";
    $params[':grupo_id'] = $grupo_filter;
}

if ($activo_filter !== '') {
    // Aceptar '1' o '0'
    if ($activo_filter === '1' || $activo_filter === '0') {
        $where_conditions[] = "d.activo = :activo";
        $params[':activo'] = (int)$activo_filter;
    }
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Obtener total
$count_sql = "SELECT COUNT(*) FROM descuentos_clientes d
              JOIN clientes c ON d.cliente_id = c.id
              JOIN grupos_productos g ON d.grupo_id = g.id
              $where_clause";

$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_descuentos = (int)$stmt->fetchColumn();
$total_pages = $total_descuentos > 0 ? (int)ceil($total_descuentos / $limit) : 1;

// Obtener descuentos paginados
$sql = "SELECT
            d.id,
            d.cliente_id,
            d.grupo_id,
            d.porcentaje_descuento,
            d.activo,
            d.created_at,
            c.nombre as cliente_nombre,
            g.nombre as grupo_nombre
        FROM descuentos_clientes d
        JOIN clientes c ON d.cliente_id = c.id
        JOIN grupos_productos g ON d.grupo_id = g.id
        $where_clause
        ORDER BY c.nombre, g.nombre
        LIMIT :limit OFFSET :offset";

$params_with_limit = $params;
$params_with_limit[':limit'] = $limit;
$params_with_limit[':offset'] = $offset;

$stmt = $pdo->prepare($sql);
foreach ($params_with_limit as $key => $val) {
    if ($key === ':limit' || $key === ':offset') {
        $stmt->bindValue($key, (int)$val, PDO::PARAM_INT);
    } else {
        $stmt->bindValue($key, $val);
    }
}
$stmt->execute();
$descuentos = $stmt->fetchAll();

// Obtener listas para selects
$clientes = $pdo->query("SELECT id, nombre FROM clientes WHERE activo = 1 ORDER BY nombre")->fetchAll();
$grupos = $pdo->query("SELECT id, nombre FROM grupos_productos WHERE activo = 1 ORDER BY nombre")->fetchAll();

// Estadísticas
try {
    $stats_sql = "SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activos,
                    SUM(CASE WHEN activo = 0 THEN 1 ELSE 0 END) as inactivos
                  FROM descuentos_clientes";
    $stats_stmt = $pdo->query($stats_sql);
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
    if (!$stats) {
        $stats = ['total' => 0, 'activos' => 0, 'inactivos' => 0];
    }
} catch (PDOException $e) {
    error_log("Error obteniendo estadísticas: " . $e->getMessage());
    $stats = ['total' => 0, 'activos' => 0, 'inactivos' => 0];
}

// Preparar contenido (mantener vista actual)
ob_start();
?>

<?php if ($mensaje): ?>
    <div class="alert alert-<?php echo htmlspecialchars($tipo_mensaje); ?> alert-dismissible fade show">
        <?php echo htmlspecialchars($mensaje); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestión de Descuentos por Cliente</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#descuentoModal">
        <i class="fas fa-plus"></i> Nuevo Descuento
    </button>
</div>

<!-- Contadores -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Total Descuentos</h6>
                        <h2 class="mt-2 mb-0"><?php echo (int)$stats['total']; ?></h2>
                    </div>
                    <i class="fas fa-percentage fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Descuentos Activos</h6>
                        <h2 class="mt-2 mb-0"><?php echo (int)$stats['activos']; ?></h2>
                    </div>
                    <i class="fas fa-check-circle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-secondary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Descuentos Inactivos</h6>
                        <h2 class="mt-2 mb-0"><?php echo (int)$stats['inactivos']; ?></h2>
                    </div>
                    <i class="fas fa-ban fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Buscar</label>
                <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Cliente</label>
                <select class="form-select" name="cliente">
                    <option value="">Todos los clientes</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?php echo (int)$cliente['id']; ?>" <?php echo ($cliente_filter == $cliente['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cliente['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Grupo</label>
                <select class="form-select" name="grupo">
                    <option value="">Todos los grupos</option>
                    <?php foreach ($grupos as $grupo): ?>
                        <option value="<?php echo (int)$grupo['id']; ?>" <?php echo ($grupo_filter == $grupo['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($grupo['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Estado</label>
                <select class="form-select" name="activo">
                    <option value="">Todos</option>
                    <option value="1" <?php echo $activo_filter === '1' ? 'selected' : ''; ?>>Activos</option>
                    <option value="0" <?php echo $activo_filter === '0' ? 'selected' : ''; ?>>Inactivos</option>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <a href="?" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Grupo</th>
                        <th>Descuento</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($descuentos)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-3">No se encontraron descuentos</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($descuentos as $descuento): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($descuento['cliente_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($descuento['grupo_nombre']); ?></td>
                                <td><?php echo number_format((float)$descuento['porcentaje_descuento'], 2); ?>%</td>
                                <td>
                                    <span class="badge bg-<?php echo $descuento['activo'] ? 'success' : 'secondary'; ?>">
                                        <?php echo $descuento['activo'] ? 'Activo' : 'Inactivo'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                onclick='editarDescuento(<?php echo htmlspecialchars(json_encode($descuento), ENT_QUOTES, 'UTF-8'); ?>)'>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="toggle">
                                            <input type="hidden" name="id" value="<?php echo (int)$descuento['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-<?php echo $descuento['activo'] ? 'warning' : 'success'; ?>">
                                                <i class="fas fa-<?php echo $descuento['activo'] ? 'ban' : 'check'; ?>"></i>
                                            </button>
                                        </form>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('¿Está seguro de eliminar este descuento?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo (int)$descuento['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php
        // Paginación usando LayoutManager helper
        echo LayoutManager::renderPagination($page, $total_pages, basename($_SERVER['PHP_SELF']), [
            'search' => $search,
            'cliente' => $cliente_filter,
            'grupo' => $grupo_filter,
            'activo' => $activo_filter
        ]);
        ?>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="descuentoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="descuentoForm" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Descuento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" id="descuentoAction" value="create">
                    <input type="hidden" name="id" id="descuentoId" value="">

                    <div class="mb-3">
                        <label class="form-label">Cliente</label>
                        <select class="form-select" name="cliente_id" id="clienteId" required>
                            <option value="">Seleccione un cliente</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?php echo (int)$cliente['id']; ?>">
                                    <?php echo htmlspecialchars($cliente['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Grupo de Productos</label>
                        <select class="form-select" name="grupo_id" id="grupoId" required>
                            <option value="">Seleccione un grupo</option>
                            <?php foreach ($grupos as $grupo): ?>
                                <option value="<?php echo (int)$grupo['id']; ?>">
                                    <?php echo htmlspecialchars($grupo['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Porcentaje de Descuento</label>
                        <input type="number" class="form-control" name="porcentaje_descuento" id="porcentaje"
                               required min="0" max="100" step="0.01">
                        <div class="form-text">Valor entre 0 y 100</div>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="activo" id="activo" checked>
                        <label class="form-check-label">Activo</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

$additionalJS = "
<script>
function editarDescuento(descuento) {
    if (!descuento || !descuento.id) {
        alert('Error: Datos del descuento no válidos');
        return;
    }

    document.getElementById('descuentoAction').value = 'update';
    document.getElementById('descuentoId').value = descuento.id;
    document.getElementById('clienteId').value = descuento.cliente_id;
    document.getElementById('grupoId').value = descuento.grupo_id;
    document.getElementById('porcentaje').value = descuento.porcentaje_descuento;
    document.getElementById('activo').checked = Boolean(Number(descuento.activo));

    document.getElementById('clienteId').disabled = true;
    document.querySelector('#descuentoModal .modal-title').textContent = 'Editar Descuento';
    new bootstrap.Modal(document.getElementById('descuentoModal')).show();
}

document.getElementById('descuentoModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('descuentoForm').reset();
    document.getElementById('descuentoAction').value = 'create';
    document.getElementById('descuentoId').value = '';
    document.getElementById('clienteId').disabled = false;
    document.querySelector('#descuentoModal .modal-title').textContent = 'Nuevo Descuento';
});

document.getElementById('descuentoModal').addEventListener('show.bs.modal', function() {
    if (document.getElementById('descuentoAction').value !== 'update') {
        document.querySelector('#descuentoModal .modal-title').textContent = 'Nuevo Descuento';
    }
});
</script>
";

LayoutManager::renderAdminPage('Gestión de Descuentos', $content, '', $additionalJS);
