<?php
session_start();

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../includes/LayoutManager.php';
require_once '../config/database.php';

$pdo = getConnection();
$mensaje = '';
$tipo_mensaje = '';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            try {
                if (empty($_POST['nombre']) || empty($_POST['email'])) {
                    throw new Exception('Nombre y email son obligatorios');
                }
                
                $stmt = $pdo->prepare("INSERT INTO empleados (nombre, email, telefono, cargo, activo) VALUES (?, ?, ?, ?, ?)");
                $success = $stmt->execute([
                    $_POST['nombre'],
                    $_POST['email'],
                    $_POST['telefono'] ?: null,
                    $_POST['cargo'],
                    isset($_POST['activo']) ? 1 : 0
                ]);
                
                if ($success) {
                    $mensaje = 'Empleado creado exitosamente';
                    $tipo_mensaje = 'success';
                } else {
                    throw new Exception('Error al crear el empleado');
                }
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'Duplicate') !== false) {
                    $mensaje = 'Error: Ya existe un empleado con ese email';
                } else {
                    $mensaje = 'Error: ' . $e->getMessage();
                }
                $tipo_mensaje = 'danger';
            } catch (Exception $e) {
                $mensaje = 'Error: ' . $e->getMessage();
                $tipo_mensaje = 'danger';
            }
            break;
            
        case 'update':
            try {
                if (empty($_POST['nombre']) || empty($_POST['email'])) {
                    throw new Exception('Nombre y email son obligatorios');
                }
                
                $stmt = $pdo->prepare("UPDATE empleados SET nombre = ?, email = ?, telefono = ?, cargo = ?, activo = ? WHERE id = ?");
                $success = $stmt->execute([
                    $_POST['nombre'],
                    $_POST['email'],
                    $_POST['telefono'] ?: null,
                    $_POST['cargo'],
                    isset($_POST['activo']) ? 1 : 0,
                    $_POST['id']
                ]);
                
                if ($success) {
                    $mensaje = 'Empleado actualizado exitosamente';
                    $tipo_mensaje = 'success';
                } else {
                    throw new Exception('Error al actualizar el empleado');
                }
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'Duplicate') !== false) {
                    $mensaje = 'Error: Ya existe un empleado con ese email';
                } else {
                    $mensaje = 'Error: ' . $e->getMessage();
                }
                $tipo_mensaje = 'danger';
            } catch (Exception $e) {
                $mensaje = 'Error: ' . $e->getMessage();
                $tipo_mensaje = 'danger';
            }
            break;
            
        case 'delete':
            try {
                // Verificar si es el empleado logueado
                if ($_POST['id'] == $_SESSION['empleado_id']) {
                    $mensaje = 'No puede eliminar su propia cuenta de empleado';
                    $tipo_mensaje = 'warning';
                    break;
                }
                
                // Verificar si el empleado tiene clientes asignados
                $check_stmt = $pdo->prepare("SELECT COUNT(*) as count FROM clientes WHERE empleado_asignado = ?");
                $check_stmt->execute([$_POST['id']]);
                $has_clients = $check_stmt->fetch()['count'] > 0;
                
                if ($has_clients) {
                    $mensaje = 'No se puede eliminar el empleado porque tiene clientes asignados';
                    $tipo_mensaje = 'warning';
                } else {
                    $stmt = $pdo->prepare("DELETE FROM empleados WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    $mensaje = 'Empleado eliminado exitosamente';
                    $tipo_mensaje = 'success';
                }
            } catch (PDOException $e) {
                $mensaje = 'Error al eliminar empleado: ' . $e->getMessage();
                $tipo_mensaje = 'danger';
            }
            break;
    }
}

// Filtros y paginación
$search = $_GET['search'] ?? '';
$cargo_filter = $_GET['cargo'] ?? '';
$activo_filter = $_GET['activo'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Construir WHERE clause
$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(nombre LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($cargo_filter) {
    $where_conditions[] = "cargo = ?";
    $params[] = $cargo_filter;
}

if ($activo_filter !== '') {
    $where_conditions[] = "activo = ?";
    $params[] = $activo_filter;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Contar total de empleados
$count_sql = "SELECT COUNT(*) as total FROM empleados $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_empleados = $count_stmt->fetch()['total'];
$total_pages = ceil($total_empleados / $limit);

// Obtener empleados paginados
$sql = "SELECT * FROM empleados $where_clause ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$empleados = $stmt->fetchAll();

// Cargos disponibles
$cargos = ['admin', 'vendedor', 'repartidor', 'supervisor'];

// Preparar contenido de la página
ob_start();
?>

<?php if ($mensaje): ?>
    <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($mensaje); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Header con botón agregar -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestión de Empleados</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#empleadoModal">
        <i class="fas fa-plus"></i> Nuevo Empleado
    </button>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Nombre o email">
            </div>
            <div class="col-md-3">
                <label for="cargo" class="form-label">Cargo</label>
                <select class="form-select" id="cargo" name="cargo">
                    <option value="">Todos los cargos</option>
                    <?php foreach ($cargos as $cargo): ?>
                        <option value="<?php echo $cargo; ?>" 
                                <?php echo $cargo_filter === $cargo ? 'selected' : ''; ?>>
                            <?php echo ucfirst($cargo); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="activo" class="form-label">Estado</label>
                <select class="form-select" id="activo" name="activo">
                    <option value="">Todos</option>
                    <option value="1" <?php echo $activo_filter === '1' ? 'selected' : ''; ?>>Activos</option>
                    <option value="0" <?php echo $activo_filter === '0' ? 'selected' : ''; ?>>Inactivos</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary me-2">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <a href="empleados.php" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de empleados -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Cargo</th>
                        <th>Estado</th>
                        <th>Fecha Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($empleados)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <p class="text-muted">No hay empleados disponibles</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($empleados as $empleado): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($empleado['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($empleado['email']); ?></td>
                            <td><?php echo htmlspecialchars($empleado['telefono'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="badge bg-primary"><?php echo ucfirst($empleado['cargo']); ?></span>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $empleado['activo'] ? 'success' : 'secondary'; ?>">
                                    <?php echo $empleado['activo'] ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($empleado['created_at'])); ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                        onclick="editEmpleado(<?php echo htmlspecialchars(json_encode($empleado)); ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if ($empleado['id'] != $_SESSION['empleado_id']): ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="deleteEmpleado(<?php echo $empleado['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php else: ?>
                                    <span class="badge bg-info d-inline-flex align-items-center" 
                                          title="Usuario actualmente conectado">
                                        <i class="fas fa-user-circle me-1"></i>
                                    </span>
                                <?php endif; ?>
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
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&cargo=<?php echo urlencode($cargo_filter); ?>&activo=<?php echo urlencode($activo_filter); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para crear/editar empleado -->
<div class="modal fade" id="empleadoModal" tabindex="-1" aria-labelledby="empleadoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="empleadoModalLabel">Nuevo Empleado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="empleadoForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" id="empleadoAction" value="create">
                    <input type="hidden" name="id" id="empleadoId">
                    
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre *</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="text" class="form-control" id="telefono" name="telefono">
                    </div>
                    
                    <div class="mb-3">
                        <label for="cargo" class="form-label">Cargo *</label>
                        <select class="form-select" id="cargo" name="cargo" required>
                            <option value="">Seleccionar cargo</option>
                            <?php foreach ($cargos as $cargo): ?>
                                <option value="<?php echo $cargo; ?>"><?php echo ucfirst($cargo); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="activo" name="activo" checked>
                        <label class="form-check-label" for="activo">Empleado activo</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Empleado</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// JavaScript adicional
$additionalJS = '
<script>
function editEmpleado(empleado) {
    document.getElementById("empleadoAction").value = "update";
    document.getElementById("empleadoId").value = empleado.id;
    document.getElementById("nombre").value = empleado.nombre;
    document.getElementById("email").value = empleado.email;
    document.getElementById("telefono").value = empleado.telefono || "";
    document.getElementById("cargo").value = empleado.cargo;
    document.getElementById("activo").checked = empleado.activo == 1;
    
    document.getElementById("empleadoModalLabel").textContent = "Editar Empleado";
    new bootstrap.Modal(document.getElementById("empleadoModal")).show();
}

function deleteEmpleado(id) {
    if (confirm("¿Estás seguro de que quieres eliminar este empleado?")) {
        const form = document.createElement("form");
        form.method = "POST";
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Resetear modal al cerrarse
document.getElementById("empleadoModal").addEventListener("hidden.bs.modal", function() {
    document.getElementById("empleadoForm").reset();
    document.getElementById("empleadoAction").value = "create";
    document.getElementById("empleadoId").value = "";
    document.getElementById("empleadoModalLabel").textContent = "Nuevo Empleado";
    document.getElementById("activo").checked = true;
});
</script>';

// Renderizar la página
LayoutManager::renderAdminPage('Gestión de Empleados', $content, '', $additionalJS);
?>
