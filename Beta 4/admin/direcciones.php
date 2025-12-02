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
            // Si es dirección principal, desactivar otras principales del mismo cliente
            if (isset($_POST['es_principal']) && $_POST['es_principal']) {
                $stmt = $pdo->prepare("UPDATE direcciones_clientes SET es_principal = 0 WHERE cliente_id = ?");
                $stmt->execute([$_POST['cliente_id']]);
            }
            
            $stmt = $pdo->prepare("INSERT INTO direcciones_clientes (cliente_id, nombre, direccion, ciudad, departamento, codigo_postal, telefono, es_principal) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['cliente_id'],
                $_POST['nombre'],
                $_POST['direccion'],
                $_POST['ciudad'],
                $_POST['departamento'],
                $_POST['codigo_postal'],
                $_POST['telefono'],
                isset($_POST['es_principal']) ? 1 : 0
            ]);
            $response = ['success' => true, 'message' => 'Dirección creada correctamente'];
        }
        
        elseif ($_POST['action'] === 'editar') {
            // Si es dirección principal, desactivar otras principales del mismo cliente
            if (isset($_POST['es_principal']) && $_POST['es_principal']) {
                $stmt = $pdo->prepare("UPDATE direcciones_clientes SET es_principal = 0 WHERE cliente_id = (SELECT cliente_id FROM direcciones_clientes WHERE id = ?) AND id != ?");
                $stmt->execute([$_POST['id'], $_POST['id']]);
            }
            
            $stmt = $pdo->prepare("UPDATE direcciones_clientes SET nombre=?, direccion=?, ciudad=?, departamento=?, codigo_postal=?, telefono=?, es_principal=? WHERE id=?");
            $stmt->execute([
                $_POST['nombre'],
                $_POST['direccion'],
                $_POST['ciudad'],
                $_POST['departamento'],
                $_POST['codigo_postal'],
                $_POST['telefono'],
                isset($_POST['es_principal']) ? 1 : 0,
                $_POST['id']
            ]);
            $response = ['success' => true, 'message' => 'Dirección actualizada correctamente'];
        }
        
        elseif ($_POST['action'] === 'eliminar') {
            $stmt = $pdo->prepare("DELETE FROM direcciones_clientes WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $response = ['success' => true, 'message' => 'Dirección eliminada correctamente'];
        }
        
        elseif ($_POST['action'] === 'set_principal') {
            // Desactivar todas las direcciones principales del cliente
            $stmt = $pdo->prepare("UPDATE direcciones_clientes SET es_principal = 0 WHERE cliente_id = (SELECT cliente_id FROM direcciones_clientes WHERE id = ?)");
            $stmt->execute([$_POST['id']]);
            
            // Activar la dirección seleccionada como principal
            $stmt = $pdo->prepare("UPDATE direcciones_clientes SET es_principal = 1 WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $response = ['success' => true, 'message' => 'Dirección establecida como principal'];
        }
        
        elseif ($_POST['action'] === 'toggle_status') {
            $stmt = $pdo->prepare("UPDATE direcciones_clientes SET activo = NOT activo WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $response = ['success' => true, 'message' => 'Estado de dirección actualizado correctamente'];
        }
        
    } catch (Exception $e) {
        $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Obtener filtros
$search = $_GET['search'] ?? '';
$cliente_filter = $_GET['cliente'] ?? '';
$page = max(1, $_GET['page'] ?? 1);
$limit = 15;
$offset = ($page - 1) * $limit;

// Construir consulta con filtros
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(d.direccion LIKE ? OR d.ciudad LIKE ? OR d.departamento LIKE ? OR d.nombre LIKE ? OR c.nombre LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if (!empty($cliente_filter)) {
    $where_conditions[] = "d.cliente_id = ?";
    $params[] = $cliente_filter;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Contar total para paginación
$count_sql = "SELECT COUNT(*) as total FROM direcciones_clientes d INNER JOIN clientes c ON d.cliente_id = c.id {$where_clause}";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_direcciones = $count_stmt->fetch()['total'];
$total_pages = ceil($total_direcciones / $limit);

// Obtener direcciones
$sql = "SELECT d.*, 
               c.nombre as cliente_nombre,
               c.email as cliente_email,
               c.numero_documento
        FROM direcciones_clientes d 
        INNER JOIN clientes c ON d.cliente_id = c.id 
        {$where_clause} 
        ORDER BY c.nombre ASC, d.es_principal DESC, d.created_at DESC 
        LIMIT {$limit} OFFSET {$offset}";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$direcciones = $stmt->fetchAll();

// Obtener clientes para el modal
$clientes_sql = "SELECT id, nombre, numero_documento, email FROM clientes WHERE activo = 1 ORDER BY nombre";
$clientes_stmt = $pdo->prepare($clientes_sql);
$clientes_stmt->execute();
$clientes = $clientes_stmt->fetchAll();

// Preparar contenido de la página
ob_start();
?>

<!-- Header con botón de nueva dirección -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestión de Direcciones</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalDireccion">
        <i class="fas fa-plus"></i> Nueva Dirección
    </button>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="cliente" class="form-label">Cliente</label>
                <select class="form-select" id="cliente" name="cliente">
                    <option value="">Todos los clientes</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?php echo $cliente['id']; ?>" <?php echo $cliente_filter == $cliente['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cliente['nombre']) . ' - ' . htmlspecialchars($cliente['numero_documento']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-5">
                <label for="search" class="form-label">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Dirección, ciudad, departamento...">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary me-2">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <a href="direcciones.php" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de direcciones -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Nombre/Dirección</th>
                        <th>Ciudad/Departamento</th>
                        <th>Teléfono</th>
                        <th>Principal</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($direcciones)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No se encontraron direcciones
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($direcciones as $direccion): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($direccion['cliente_nombre']); ?></strong><br>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($direccion['numero_documento']); ?><br>
                                    <?php echo htmlspecialchars($direccion['cliente_email']); ?>
                                </small>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($direccion['nombre']); ?></strong><br>
                                <?php echo htmlspecialchars($direccion['direccion']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($direccion['ciudad']); ?><br>
                                <small class="text-muted"><?php echo htmlspecialchars($direccion['departamento']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($direccion['telefono'] ?: '-'); ?></td>
                            <td>
                                <?php if ($direccion['es_principal']): ?>
                                    <span class="badge bg-success">Principal</span>
                                <?php else: ?>
                                    <button class="btn btn-outline-success btn-sm" onclick="setPrincipal(<?php echo $direccion['id']; ?>)">
                                        <i class="fas fa-star"></i> Hacer Principal
                                    </button>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $direccion['activo'] ? 'success' : 'secondary'; ?>">
                                    <?php echo $direccion['activo'] ? 'Activa' : 'Inactiva'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="editarDireccion(<?php echo htmlspecialchars(json_encode($direccion)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-<?php echo $direccion['activo'] ? 'warning' : 'success'; ?>" 
                                            onclick="toggleStatus(<?php echo $direccion['id']; ?>)"
                                            title="<?php echo $direccion['activo'] ? 'Desactivar' : 'Activar'; ?>">
                                        <i class="fas fa-<?php echo $direccion['activo'] ? 'eye-slash' : 'eye'; ?>"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="eliminarDireccion(<?php echo $direccion['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Paginación de direcciones" class="mt-3">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&cliente=<?php echo urlencode($cliente_filter); ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&cliente=<?php echo urlencode($cliente_filter); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&cliente=<?php echo urlencode($cliente_filter); ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para crear/editar dirección -->
<div class="modal fade" id="modalDireccion" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formDireccion">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDireccionTitle">Nueva Dirección</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="direccion_id" name="id">
                    <input type="hidden" id="action" name="action" value="crear">
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="cliente_id" class="form-label">Cliente *</label>
                            <select class="form-select" id="cliente_id" name="cliente_id" required>
                                <option value="">Seleccionar cliente</option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?php echo $cliente['id']; ?>">
                                        <?php echo htmlspecialchars($cliente['nombre']) . ' - ' . htmlspecialchars($cliente['numero_documento']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="nombre" class="form-label">Nombre de la Dirección *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required 
                                   placeholder="Ej: Oficina Principal, Bodega Sur, Casa">
                        </div>
                        <div class="col-md-6">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono" 
                                   placeholder="Teléfono de contacto">
                        </div>
                        <div class="col-12">
                            <label for="direccion" class="form-label">Dirección Completa *</label>
                            <textarea class="form-control" id="direccion" name="direccion" rows="3" required 
                                    placeholder="Calle, número, barrio, referencias..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="ciudad" class="form-label">Ciudad *</label>
                            <input type="text" class="form-control" id="ciudad" name="ciudad" required>
                        </div>
                        <div class="col-md-6">
                            <label for="departamento" class="form-label">Departamento *</label>
                            <input type="text" class="form-control" id="departamento" name="departamento" required>
                        </div>
                        <div class="col-md-6">
                            <label for="codigo_postal" class="form-label">Código Postal</label>
                            <input type="text" class="form-control" id="codigo_postal" name="codigo_postal">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="es_principal" name="es_principal">
                                <label class="form-check-label" for="es_principal">
                                    Dirección principal
                                </label>
                            </div>
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
// Manejar formulario de dirección
document.getElementById("formDireccion").addEventListener("submit", function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch("direcciones.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById("modalDireccion")).hide();
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

function editarDireccion(direccion) {
    document.getElementById("modalDireccionTitle").textContent = "Editar Dirección";
    document.getElementById("action").value = "editar";
    document.getElementById("direccion_id").value = direccion.id;
    document.getElementById("cliente_id").value = direccion.cliente_id;
    document.getElementById("cliente_id").disabled = true; // No permitir cambiar cliente
    document.getElementById("nombre").value = direccion.nombre;
    document.getElementById("telefono").value = direccion.telefono || "";
    document.getElementById("direccion").value = direccion.direccion;
    document.getElementById("ciudad").value = direccion.ciudad;
    document.getElementById("departamento").value = direccion.departamento;
    document.getElementById("codigo_postal").value = direccion.codigo_postal || "";
    document.getElementById("es_principal").checked = direccion.es_principal == 1;
    
    new bootstrap.Modal(document.getElementById("modalDireccion")).show();
}

function setPrincipal(id) {
    if (confirm("¿Está seguro de que desea establecer esta dirección como principal?")) {
        const formData = new FormData();
        formData.append("action", "set_principal");
        formData.append("id", id);
        
        fetch("direcciones.php", {
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

function eliminarDireccion(id) {
    if (confirm("¿Está seguro de que desea eliminar esta dirección?\\n\\nEsta acción no se puede deshacer.")) {
        const formData = new FormData();
        formData.append("action", "eliminar");
        formData.append("id", id);
        
        fetch("direcciones.php", {
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

function toggleStatus(id) {
    if (confirm("¿Está seguro de que desea cambiar el estado de esta dirección?")) {
        const formData = new FormData();
        formData.append("action", "toggle_status");
        formData.append("id", id);
        
        fetch("direcciones.php", {
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
document.getElementById("modalDireccion").addEventListener("hidden.bs.modal", function() {
    document.getElementById("formDireccion").reset();
    document.getElementById("modalDireccionTitle").textContent = "Nueva Dirección";
    document.getElementById("action").value = "crear";
    document.getElementById("direccion_id").value = "";
    document.getElementById("cliente_id").disabled = false; // Habilitar selección de cliente para nueva dirección
});
</script>';

// Renderizar la página
LayoutManager::renderAdminPage('Gestión de Direcciones', $content, '', $additionalJS);
?>
