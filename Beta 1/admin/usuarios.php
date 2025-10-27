<?php
session_start();
require_once '../includes/LayoutManager.php';
require_once '../config/database.php';

$pdo = getConnection();
$message = '';
$message_type = '';

// Operaciones CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                try {
                    // Verificar que el usuario no exista
                    $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM credenciales WHERE usuario = ?");
                    $check_stmt->execute([$_POST['usuario']]);
                    if ($check_stmt->fetchColumn() > 0) {
                        $message = 'El nombre de usuario ya existe';
                        $message_type = 'danger';
                        break;
                    }

                    // Encriptar contraseña
                    $hashed_password = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
                    
                    $stmt = $pdo->prepare("INSERT INTO credenciales (usuario, contrasena, tipo, empleado_id, cliente_id, activo) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $_POST['usuario'],
                        $hashed_password,
                        $_POST['tipo'],
                        $_POST['tipo'] === 'empleado' ? $_POST['empleado_id'] : null,
                        $_POST['tipo'] === 'cliente' ? $_POST['cliente_id'] : null,
                        isset($_POST['activo']) ? 1 : 0
                    ]);
                    $message = 'Usuario creado exitosamente';
                    $message_type = 'success';
                } catch (PDOException $e) {
                    $message = 'Error al crear usuario: ' . $e->getMessage();
                    $message_type = 'danger';
                }
                break;

            case 'edit':
                try {
                    // Verificar que el usuario no exista (excepto el actual)
                    $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM credenciales WHERE usuario = ? AND id != ?");
                    $check_stmt->execute([$_POST['usuario'], $_POST['id']]);
                    if ($check_stmt->fetchColumn() > 0) {
                        $message = 'El nombre de usuario ya existe';
                        $message_type = 'danger';
                        break;
                    }

                    if (!empty($_POST['contrasena'])) {
                        // Actualizar con nueva contraseña
                        $hashed_password = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE credenciales SET usuario = ?, contrasena = ?, tipo = ?, empleado_id = ?, cliente_id = ?, activo = ? WHERE id = ?");
                        $stmt->execute([
                            $_POST['usuario'],
                            $hashed_password,
                            $_POST['tipo'],
                            $_POST['tipo'] === 'empleado' ? $_POST['empleado_id'] : null,
                            $_POST['tipo'] === 'cliente' ? $_POST['cliente_id'] : null,
                            isset($_POST['activo']) ? 1 : 0,
                            $_POST['id']
                        ]);
                    } else {
                        // Actualizar sin cambiar contraseña
                        $stmt = $pdo->prepare("UPDATE credenciales SET usuario = ?, tipo = ?, empleado_id = ?, cliente_id = ?, activo = ? WHERE id = ?");
                        $stmt->execute([
                            $_POST['usuario'],
                            $_POST['tipo'],
                            $_POST['tipo'] === 'empleado' ? $_POST['empleado_id'] : null,
                            $_POST['tipo'] === 'cliente' ? $_POST['cliente_id'] : null,
                            isset($_POST['activo']) ? 1 : 0,
                            $_POST['id']
                        ]);
                    }
                    $message = 'Usuario actualizado exitosamente';
                    $message_type = 'success';
                } catch (PDOException $e) {
                    $message = 'Error al actualizar usuario: ' . $e->getMessage();
                    $message_type = 'danger';
                }
                break;

            case 'delete':
                try {
                    $stmt = $pdo->prepare("DELETE FROM credenciales WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    $message = 'Usuario eliminado exitosamente';
                    $message_type = 'success';
                } catch (PDOException $e) {
                    $message = 'Error al eliminar usuario: ' . $e->getMessage();
                    $message_type = 'danger';
                }
                break;
        }
    }
}

// Filtros y paginación
$search = $_GET['search'] ?? '';
$tipo_filter = $_GET['tipo'] ?? '';
$activo_filter = $_GET['activo'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Construir WHERE clause
$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(c.usuario LIKE ? OR e.nombre LIKE ? OR cl.nombre LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($tipo_filter) {
    $where_conditions[] = "c.tipo = ?";
    $params[] = $tipo_filter;
}

if ($activo_filter !== '') {
    $where_conditions[] = "c.activo = ?";
    $params[] = $activo_filter;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Contar total de usuarios
$count_sql = "SELECT COUNT(*) as total FROM credenciales c 
              LEFT JOIN empleados e ON c.empleado_id = e.id 
              LEFT JOIN clientes cl ON c.cliente_id = cl.id 
              $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_usuarios = $count_stmt->fetch()['total'];
$total_pages = ceil($total_usuarios / $limit);

// Obtener usuarios paginados
$sql = "SELECT c.*, e.nombre as empleado_nombre, cl.nombre as cliente_nombre 
        FROM credenciales c 
        LEFT JOIN empleados e ON c.empleado_id = e.id 
        LEFT JOIN clientes cl ON c.cliente_id = cl.id 
        $where_clause 
        ORDER BY c.created_at DESC 
        LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$usuarios = $stmt->fetchAll();

// Obtener empleados y clientes para los selects
$empleados_stmt = $pdo->query("SELECT id, nombre FROM empleados WHERE activo = 1 ORDER BY nombre");
$empleados = $empleados_stmt->fetchAll();

$clientes_stmt = $pdo->query("SELECT id, nombre FROM clientes WHERE activo = 1 ORDER BY nombre");
$clientes = $clientes_stmt->fetchAll();

// Preparar contenido de la página
ob_start();
?>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Header con botón agregar -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestión de Usuarios</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#usuarioModal">
        <i class="fas fa-plus"></i> Nuevo Usuario
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
                       placeholder="Usuario o nombre">
            </div>
            <div class="col-md-3">
                <label for="tipo" class="form-label">Tipo</label>
                <select class="form-select" id="tipo" name="tipo">
                    <option value="">Todos los tipos</option>
                    <option value="empleado" <?php echo $tipo_filter === 'empleado' ? 'selected' : ''; ?>>Empleado</option>
                    <option value="cliente" <?php echo $tipo_filter === 'cliente' ? 'selected' : ''; ?>>Cliente</option>
                    <option value="repartidor" <?php echo $tipo_filter === 'repartidor' ? 'selected' : ''; ?>>Repartidor</option>
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
                <a href="usuarios.php" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de usuarios -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Tipo</th>
                        <th>Asociado a</th>
                        <th>Estado</th>
                        <th>Fecha Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($usuarios)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <p class="text-muted">No hay usuarios disponibles</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($usuario['usuario']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $usuario['tipo'] === 'empleado' ? 'primary' : ($usuario['tipo'] === 'cliente' ? 'success' : 'info'); ?>">
                                    <?php echo ucfirst($usuario['tipo']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($usuario['tipo'] === 'empleado'): ?>
                                    <?php echo htmlspecialchars($usuario['empleado_nombre'] ?? 'Sin asociar'); ?>
                                <?php elseif ($usuario['tipo'] === 'cliente'): ?>
                                    <?php echo htmlspecialchars($usuario['cliente_nombre'] ?? 'Sin asociar'); ?>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $usuario['activo'] ? 'success' : 'secondary'; ?>">
                                    <?php echo $usuario['activo'] ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($usuario['created_at'])); ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                        onclick="editUsuario(<?php echo htmlspecialchars(json_encode($usuario)); ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                        onclick="deleteUsuario(<?php echo $usuario['id']; ?>)">
                                    <i class="fas fa-trash"></i>
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
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&tipo=<?php echo urlencode($tipo_filter); ?>&activo=<?php echo urlencode($activo_filter); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para crear/editar usuario -->
<div class="modal fade" id="usuarioModal" tabindex="-1" aria-labelledby="usuarioModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="usuarioModalLabel">Nuevo Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="usuarioForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" id="usuarioAction" value="add">
                    <input type="hidden" name="id" id="usuarioId">
                    
                    <div class="mb-3">
                        <label for="usuario" class="form-label">Usuario *</label>
                        <input type="text" class="form-control" id="usuario" name="usuario" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="contrasena" class="form-label">Contraseña *</label>
                        <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                        <div class="form-text" id="contrasenaHelp">Deja en blanco para mantener la contraseña actual al editar.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="tipo" class="form-label">Tipo *</label>
                        <select class="form-select" id="tipo" name="tipo" required onchange="toggleAssociation()">
                            <option value="">Seleccionar tipo</option>
                            <option value="empleado">Empleado</option>
                            <option value="cliente">Cliente</option>
                            <option value="repartidor">Repartidor</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="empleado_select" style="display: none;">
                        <label for="empleado_id" class="form-label">Empleado</label>
                        <select class="form-select" id="empleado_id" name="empleado_id">
                            <option value="">Seleccionar empleado</option>
                            <?php foreach ($empleados as $empleado): ?>
                                <option value="<?php echo $empleado['id']; ?>"><?php echo htmlspecialchars($empleado['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="cliente_select" style="display: none;">
                        <label for="cliente_id" class="form-label">Cliente</label>
                        <select class="form-select" id="cliente_id" name="cliente_id">
                            <option value="">Seleccionar cliente</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?php echo $cliente['id']; ?>"><?php echo htmlspecialchars($cliente['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="activo" name="activo" checked>
                        <label class="form-check-label" for="activo">Usuario activo</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Usuario</button>
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
function toggleAssociation() {
    const tipo = document.getElementById("tipo").value;
    const empleadoSelect = document.getElementById("empleado_select");
    const clienteSelect = document.getElementById("cliente_select");
    
    empleadoSelect.style.display = tipo === "empleado" ? "block" : "none";
    clienteSelect.style.display = tipo === "cliente" ? "block" : "none";
    
    if (tipo !== "empleado") {
        document.getElementById("empleado_id").value = "";
    }
    if (tipo !== "cliente") {
        document.getElementById("cliente_id").value = "";
    }
}

function editUsuario(usuario) {
    document.getElementById("usuarioAction").value = "edit";
    document.getElementById("usuarioId").value = usuario.id;
    document.getElementById("usuario").value = usuario.usuario;
    document.getElementById("contrasena").value = "";
    document.getElementById("contrasena").required = false;
    document.getElementById("contrasenaHelp").style.display = "block";
    document.getElementById("tipo").value = usuario.tipo;
    document.getElementById("empleado_id").value = usuario.empleado_id || "";
    document.getElementById("cliente_id").value = usuario.cliente_id || "";
    document.getElementById("activo").checked = usuario.activo == 1;
    
    toggleAssociation();
    
    document.getElementById("usuarioModalLabel").textContent = "Editar Usuario";
    new bootstrap.Modal(document.getElementById("usuarioModal")).show();
}

function deleteUsuario(id) {
    if (confirm("¿Estás seguro de que quieres eliminar este usuario?")) {
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
document.getElementById("usuarioModal").addEventListener("hidden.bs.modal", function() {
    document.getElementById("usuarioForm").reset();
    document.getElementById("usuarioAction").value = "add";
    document.getElementById("usuarioId").value = "";
    document.getElementById("usuarioModalLabel").textContent = "Nuevo Usuario";
    document.getElementById("contrasena").required = true;
    document.getElementById("contrasenaHelp").style.display = "none";
    document.getElementById("activo").checked = true;
    toggleAssociation();
});
</script>';

// Renderizar la página
LayoutManager::renderAdminPage('Gestión de Usuarios', $content, '', $additionalJS);
?>
