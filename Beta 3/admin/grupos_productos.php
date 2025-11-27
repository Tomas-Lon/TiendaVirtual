<?php
session_start();
require_once '../includes/LayoutManager.php';
require_once '../config/database.php';

$pdo = getConnection();

// Manejo de acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $response = ['success' => false, 'message' => ''];
    
    try {
        if ($_POST['action'] === 'crear') {
            // Verificar que no exista el nombre
            $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM grupos_productos WHERE nombre = ?");
            $check_stmt->execute([$_POST['nombre']]);
            if ($check_stmt->fetchColumn() > 0) {
                throw new Exception('Ya existe un grupo con este nombre');
            }
            
            $stmt = $pdo->prepare("INSERT INTO grupos_productos (nombre, descripcion, activo) VALUES (?, ?, ?)");
            $stmt->execute([
                $_POST['nombre'],
                $_POST['descripcion'],
                1
            ]);
            $response = ['success' => true, 'message' => 'Grupo de productos creado correctamente'];
        }
        
        elseif ($_POST['action'] === 'editar') {
            // Verificar que no exista el nombre en otro registro
            $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM grupos_productos WHERE nombre = ? AND id != ?");
            $check_stmt->execute([$_POST['nombre'], $_POST['id']]);
            if ($check_stmt->fetchColumn() > 0) {
                throw new Exception('Ya existe otro grupo con este nombre');
            }
            
            $stmt = $pdo->prepare("UPDATE grupos_productos SET nombre=?, descripcion=? WHERE id=?");
            $stmt->execute([
                $_POST['nombre'],
                $_POST['descripcion'],
                $_POST['id']
            ]);
            $response = ['success' => true, 'message' => 'Grupo de productos actualizado correctamente'];
        }
        
        elseif ($_POST['action'] === 'eliminar') {
            // Verificar que no tenga productos asociados
            $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE grupo_id = ?");
            $check_stmt->execute([$_POST['id']]);
            if ($check_stmt->fetchColumn() > 0) {
                throw new Exception('No se puede eliminar el grupo porque tiene productos asociados');
            }
            
            $stmt = $pdo->prepare("DELETE FROM grupos_productos WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $response = ['success' => true, 'message' => 'Grupo de productos eliminado correctamente'];
        }
        
        elseif ($_POST['action'] === 'toggle_activo') {
            $stmt = $pdo->prepare("UPDATE grupos_productos SET activo = NOT activo WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $response = ['success' => true, 'message' => 'Estado actualizado correctamente'];
        }
        
    } catch (Exception $e) {
        $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Obtener filtros
$filtro_estado = $_GET['estado'] ?? '';
$search = $_GET['search'] ?? '';

// Construir consulta con filtros
$where_conditions = [];
$params = [];

if (!empty($filtro_estado)) {
    if ($filtro_estado === 'activo') {
        $where_conditions[] = "gp.activo = 1";
    } elseif ($filtro_estado === 'inactivo') {
        $where_conditions[] = "gp.activo = 0";
    }
}

if (!empty($search)) {
    $where_conditions[] = "(gp.nombre LIKE ? OR gp.descripcion LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Obtener grupos de productos con conteo de productos
$sql = "SELECT gp.*, 
               COUNT(p.id) as total_productos
        FROM grupos_productos gp
        LEFT JOIN productos p ON gp.id = p.grupo_id
        {$where_clause}
        GROUP BY gp.id, gp.nombre, gp.descripcion, gp.activo
        ORDER BY gp.nombre ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$grupos = $stmt->fetchAll();

// Preparar contenido de la página
ob_start();
?>

<!-- Header con botón de nuevo grupo -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Grupos de Productos</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalGrupo">
        <i class="fas fa-plus"></i> Nuevo Grupo
    </button>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="estado" class="form-label">Estado</label>
                <select class="form-select" id="estado" name="estado">
                    <option value="">Todos los estados</option>
                    <option value="activo" <?php echo $filtro_estado === 'activo' ? 'selected' : ''; ?>>Activo</option>
                    <option value="inactivo" <?php echo $filtro_estado === 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="search" class="form-label">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Nombre o descripción...">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary me-2">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <a href="grupos_productos.php" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de grupos -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Productos</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($grupos)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No se encontraron grupos de productos
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($grupos as $grupo): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($grupo['nombre']); ?></strong>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($grupo['descripcion']); ?>
                            </td>
                            <td>
                                <span class="badge bg-info"><?php echo $grupo['total_productos']; ?> productos</span>
                            </td>
                            <td>
                                <?php if ($grupo['activo']): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="editarGrupo(<?php echo htmlspecialchars(json_encode($grupo)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-<?php echo $grupo['activo'] ? 'warning' : 'success'; ?>" 
                                            onclick="toggleActivo(<?php echo $grupo['id']; ?>)">
                                        <i class="fas fa-<?php echo $grupo['activo'] ? 'pause' : 'play'; ?>"></i>
                                    </button>
                                    <a href="productos.php?grupo=<?php echo $grupo['id']; ?>" class="btn btn-outline-info">
                                        <i class="fas fa-box"></i>
                                    </a>
                                    <?php if ($grupo['total_productos'] == 0): ?>
                                        <button class="btn btn-outline-danger" onclick="eliminarGrupo(<?php echo $grupo['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-outline-danger" disabled title="No se puede eliminar, tiene productos asociados">
                                            <i class="fas fa-trash"></i>
                                        </button>
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

<!-- Modal para crear/editar grupo -->
<div class="modal fade" id="modalGrupo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formGrupo">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalGrupoTitle">Nuevo Grupo de Productos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="grupo_id" name="id">
                    <input type="hidden" id="action" name="action" value="crear">
                    
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="nombre" class="form-label">Nombre del Grupo *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required 
                                   maxlength="255" placeholder="Ej: Accesorios SCH 80, Tuberías CPVC">
                            <div class="form-text">Nombre único del grupo de productos</div>
                        </div>
                        <div class="col-12">
                            <label for="descripcion" class="form-label">Descripción *</label>
                            <input type="text" class="form-control" id="descripcion" name="descripcion" required 
                                   maxlength="100" placeholder="Nombre descriptivo del grupo">
                        </div>
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

// JavaScript adicional
$additionalJS = '
<script>
// Manejar formulario de grupo
document.getElementById("formGrupo").addEventListener("submit", function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch("grupos_productos.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById("modalGrupo")).hide();
            location.reload();
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("Error al procesar la solicitud");
    });
});

function editarGrupo(grupo) {
    document.getElementById("modalGrupoTitle").textContent = "Editar Grupo de Productos";
    document.getElementById("action").value = "editar";
    document.getElementById("grupo_id").value = grupo.id;
    document.getElementById("nombre").value = grupo.nombre;
    document.getElementById("descripcion").value = grupo.descripcion;
    
    new bootstrap.Modal(document.getElementById("modalGrupo")).show();
}

function toggleActivo(id) {
    if (confirm("¿Está seguro de que desea cambiar el estado de este grupo?")) {
        const formData = new FormData();
        formData.append("action", "toggle_activo");
        formData.append("id", id);
        
        fetch("grupos_productos.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("Error al procesar la solicitud");
        });
    }
}

function eliminarGrupo(id) {
    if (confirm("¿Está seguro de que desea eliminar este grupo de productos?\\n\\nEsta acción no se puede deshacer.")) {
        const formData = new FormData();
        formData.append("action", "eliminar");
        formData.append("id", id);
        
        fetch("grupos_productos.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("Error al procesar la solicitud");
        });
    }
}

// Limpiar modal al cerrarse
document.getElementById("modalGrupo").addEventListener("hidden.bs.modal", function() {
    document.getElementById("formGrupo").reset();
    document.getElementById("modalGrupoTitle").textContent = "Nuevo Grupo de Productos";
    document.getElementById("action").value = "crear";
    document.getElementById("grupo_id").value = "";
});
</script>';

// Renderizar la página
LayoutManager::renderAdminPage('Grupos de Productos', $content, '', $additionalJS);
?>
