<?php
session_start();

// Verificar autenticación
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

/**
 * Sanitiza y valida los datos del producto
 */
function sanitizeProductoData($data) {
    return [
        'codigo' => trim($data['codigo'] ?? ''),
        'descripcion' => trim($data['descripcion'] ?? ''),
        'precio' => floatval($data['precio'] ?? 0),
        'grupo_id' => !empty($data['grupo_id']) ? intval($data['grupo_id']) : null
    ];
}

/**
 * Valida que los campos obligatorios estén presentes
 */
function validateProductoData($data) {
    $errors = [];
    
    if (empty($data['codigo'])) {
        $errors[] = 'El código es obligatorio';
    } elseif (strlen($data['codigo']) < 2) {
        $errors[] = 'El código debe tener al menos 2 caracteres';
    }
    
    if (empty($data['descripcion'])) {
        $errors[] = 'La descripción es obligatoria';
    } elseif (strlen($data['descripcion']) < 3) {
        $errors[] = 'La descripción debe tener al menos 3 caracteres';
    }
    
    if ($data['precio'] <= 0) {
        $errors[] = 'El precio debe ser mayor a 0';
    }
    
    return $errors;
}

// Manejar acciones CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            try {
                $productoData = sanitizeProductoData($_POST);
                $validationErrors = validateProductoData($productoData);
                
                if (!empty($validationErrors)) {
                    $mensaje = 'Errores de validación: ' . implode(', ', $validationErrors);
                    $tipo_mensaje = 'danger';
                    break;
                }
                
                // Verificar si ya existe un producto con el mismo código
                $check_stmt = $pdo->prepare("SELECT COUNT(*) as count FROM productos WHERE codigo = ?");
                $check_stmt->execute([$productoData['codigo']]);
                
                if ($check_stmt->fetch()['count'] > 0) {
                    $mensaje = 'Ya existe un producto con este código';
                    $tipo_mensaje = 'warning';
                    break;
                }
                
                $stmt = $pdo->prepare("INSERT INTO productos (codigo, descripcion, precio, grupo_id) VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $productoData['codigo'],
                    $productoData['descripcion'],
                    $productoData['precio'],
                    $productoData['grupo_id']
                ]);
                $mensaje = "Producto creado exitosamente";
                $tipo_mensaje = "success";
            } catch (PDOException $e) {
                error_log("Error al crear producto: " . $e->getMessage());
                $mensaje = "Error al crear producto. Por favor, inténtelo de nuevo.";
                $tipo_mensaje = "danger";
            }
            break;
            
        case 'update':
            try {
                $productoData = sanitizeProductoData($_POST);
                $validationErrors = validateProductoData($productoData);
                
                if (!empty($validationErrors)) {
                    $mensaje = 'Errores de validación: ' . implode(', ', $validationErrors);
                    $tipo_mensaje = 'danger';
                    break;
                }
                
                if (empty($_POST['id']) || !is_numeric($_POST['id'])) {
                    $mensaje = 'ID de producto inválido';
                    $tipo_mensaje = 'danger';
                    break;
                }
                
                // Verificar si ya existe otro producto con el mismo código
                $check_stmt = $pdo->prepare("SELECT COUNT(*) as count FROM productos WHERE codigo = ? AND id != ?");
                $check_stmt->execute([$productoData['codigo'], $_POST['id']]);
                
                if ($check_stmt->fetch()['count'] > 0) {
                    $mensaje = 'Ya existe otro producto con este código';
                    $tipo_mensaje = 'warning';
                    break;
                }
                
                $stmt = $pdo->prepare("UPDATE productos SET codigo = ?, descripcion = ?, precio = ?, grupo_id = ? WHERE id = ?");
                $stmt->execute([
                    $productoData['codigo'],
                    $productoData['descripcion'],
                    $productoData['precio'],
                    $productoData['grupo_id'],
                    intval($_POST['id'])
                ]);
                $mensaje = "Producto actualizado exitosamente";
                $tipo_mensaje = "success";
            } catch (PDOException $e) {
                error_log("Error al actualizar producto: " . $e->getMessage());
                $mensaje = "Error al actualizar producto. Por favor, inténtelo de nuevo.";
                $tipo_mensaje = "danger";
            }
            break;
            
        case 'delete':
            try {
                if (empty($_POST['id']) || !is_numeric($_POST['id'])) {
                    $mensaje = 'ID de producto inválido';
                    $tipo_mensaje = 'danger';
                    break;
                }
                
                $productoId = intval($_POST['id']);
                
                // Verificar si el producto tiene pedidos asociados
                $check_stmt = $pdo->prepare("SELECT COUNT(*) as count FROM detalle_pedidos WHERE producto_id = ?");
                $check_stmt->execute([$productoId]);
                $has_orders = $check_stmt->fetch()['count'] > 0;
                
                if ($has_orders) {
                    $mensaje = "No se puede eliminar el producto porque tiene pedidos asociados";
                    $tipo_mensaje = "warning";
                } else {
                    $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
                    $stmt->execute([$productoId]);
                    $mensaje = "Producto eliminado exitosamente";
                    $tipo_mensaje = "success";
                }
            } catch (PDOException $e) {
                error_log("Error al eliminar producto: " . $e->getMessage());
                $mensaje = "Error al eliminar producto. Por favor, inténtelo de nuevo.";
                $tipo_mensaje = "danger";
            }
            break;
    }
}

// Filtros y paginación
$search = $_GET['search'] ?? '';
$grupos_filter = $_GET['grupos'] ?? [];
$precio_min = $_GET['precio_min'] ?? '';
$precio_max = $_GET['precio_max'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Construir WHERE clause
$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(p.codigo LIKE ? OR p.descripcion LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($grupos_filter) && is_array($grupos_filter)) {
    $placeholders = str_repeat('?,', count($grupos_filter) - 1) . '?';
    $where_conditions[] = "p.grupo_id IN ($placeholders)";
    $params = array_merge($params, $grupos_filter);
}

if ($precio_min !== '' && is_numeric($precio_min)) {
    $where_conditions[] = "p.precio >= ?";
    $params[] = $precio_min;
}

if ($precio_max !== '' && is_numeric($precio_max)) {
    $where_conditions[] = "p.precio <= ?";
    $params[] = $precio_max;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Contar total de productos
$count_sql = "SELECT COUNT(*) as total FROM productos p $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_productos = $count_stmt->fetch()['total'];
$total_pages = ceil($total_productos / $limit);

// Obtener productos paginados
$sql = "SELECT p.*, g.nombre as grupo_nombre 
        FROM productos p 
        LEFT JOIN grupos_productos g ON p.grupo_id = g.id 
        $where_clause 
        ORDER BY p.created_at DESC 
        LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll();

// Obtener grupos para el select
$grupos_stmt = $pdo->query("SELECT id, nombre FROM grupos_productos WHERE activo = 1 ORDER BY nombre");
$grupos = $grupos_stmt->fetchAll();

// Preparar contenido de la página
ob_start();
?>

<?php if ($mensaje): ?>
    <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($mensaje); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Header y contenido principal -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestión de Productos</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal">
        <i class="fas fa-plus"></i> Nuevo Producto
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
                       placeholder="Código o descripción">
            </div>
            <div class="col-md-2">
                <label for="precio_min" class="form-label">Precio Min</label>
                <input type="number" class="form-control" id="precio_min" name="precio_min" 
                       value="<?php echo htmlspecialchars($precio_min); ?>" step="0.01">
            </div>
            <div class="col-md-2">
                <label for="precio_max" class="form-label">Precio Max</label>
                <input type="number" class="form-control" id="precio_max" name="precio_max" 
                       value="<?php echo htmlspecialchars($precio_max); ?>" step="0.01">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Filtrar
                </button>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <a href="?" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-eraser"></i> Limpiar
                </a>
            </div>

            <!-- Filtro de grupos -->
            <div class="col-12 mt-4">
                <label class="form-label fw-bold">Filtrar por grupos (selección múltiple):</label>
                <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                    <div class="row g-2">
                        <?php foreach ($grupos as $grupo): ?>
                            <div class="col-md-4 col-lg-3">
                                <div class="form-check">
                                    <input class="form-check-input grupo-checkbox" type="checkbox" 
                                           name="grupos[]" value="<?php echo $grupo['id']; ?>" 
                                           id="grupo_<?php echo $grupo['id']; ?>"
                                           <?php echo in_array($grupo['id'], $grupos_filter) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="grupo_<?php echo $grupo['id']; ?>">
                                        <?php echo htmlspecialchars($grupo['nombre']); ?>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="seleccionarTodosGrupos()">
                            <i class="fas fa-check-double"></i> Seleccionar todos
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="limpiarGrupos()">
                            <i class="fas fa-times"></i> Deseleccionar todos
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de productos -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>Grupo</th>
                        <th>Precio</th>
                        <th>Fecha Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($productos)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <p class="text-muted">No hay productos disponibles</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($productos as $producto): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($producto['codigo']); ?></td>
                            <td><?php echo htmlspecialchars($producto['descripcion']); ?></td>
                            <td><?php echo htmlspecialchars($producto['grupo_nombre'] ?? 'Sin grupo'); ?></td>
                            <td>$<?php echo number_format($producto['precio'], 2); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($producto['created_at'])); ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                        onclick="editProduct(<?php echo $producto['id']; ?>, '<?php echo addslashes($producto['codigo']); ?>', '<?php echo addslashes($producto['descripcion']); ?>', <?php echo $producto['precio']; ?>, <?php echo $producto['grupo_id'] ?? 'null'; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                        onclick="deleteProduct(<?php echo $producto['id']; ?>)">
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
                <?php
                    // Construir array de parámetros base para la paginación
                    $params = array(
                        'search' => $search,
                        'precio_min' => $precio_min,
                        'precio_max' => $precio_max
                    );
                    
                    if (!empty($grupos_filter)) {
                        $params['grupos'] = $grupos_filter;
                    }

                    // Función helper para generar URL
                    function generatePageUrl($page, $params) {
                        $params['page'] = $page;
                        $url_params = array();
                        foreach ($params as $key => $value) {
                            if (is_array($value)) {
                                foreach ($value as $val) {
                                    $url_params[] = urlencode($key) . '[]=' . urlencode($val);
                                }
                            } else if ($value !== '') {
                                $url_params[] = urlencode($key) . '=' . urlencode($value);
                            }
                        }
                        return '?' . implode('&', $url_params);
                    }

                    // Botón Primera página
                    $firstDisabled = $page <= 1;
                    echo '<li class="page-item ' . ($firstDisabled ? 'disabled' : '') . '">
                            <a class="page-link" href="' . ($firstDisabled ? '#' : generatePageUrl(1, $params)) . '">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                          </li>';

                    // Botón Anterior
                    echo '<li class="page-item ' . ($firstDisabled ? 'disabled' : '') . '">
                            <a class="page-link" href="' . ($firstDisabled ? '#' : generatePageUrl($page - 1, $params)) . '">
                                <i class="fas fa-angle-left"></i>
                            </a>
                          </li>';

                    // Calcular rango de páginas a mostrar
                    $range = 2; // Número de páginas a cada lado
                    $initial = max(1, $page - $range);
                    $limit = min($total_pages, $page + $range);

                    // Mostrar primera página si hay gap
                    if ($initial > 1) {
                        echo '<li class="page-item"><a class="page-link" href="' . generatePageUrl(1, $params) . '">1</a></li>';
                        if ($initial > 2) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                    }

                    // Páginas numeradas
                    for ($i = $initial; $i <= $limit; $i++) {
                        echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '">
                                <a class="page-link" href="' . generatePageUrl($i, $params) . '">' . $i . '</a>
                              </li>';
                    }

                    // Mostrar última página si hay gap
                    if ($limit < $total_pages) {
                        if ($limit < $total_pages - 1) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                        echo '<li class="page-item">
                                <a class="page-link" href="' . generatePageUrl($total_pages, $params) . '">' . $total_pages . '</a>
                              </li>';
                    }

                    // Botón Siguiente
                    $lastDisabled = $page >= $total_pages;
                    echo '<li class="page-item ' . ($lastDisabled ? 'disabled' : '') . '">
                            <a class="page-link" href="' . ($lastDisabled ? '#' : generatePageUrl($page + 1, $params)) . '">
                                <i class="fas fa-angle-right"></i>
                            </a>
                          </li>';

                    // Botón Última página
                    echo '<li class="page-item ' . ($lastDisabled ? 'disabled' : '') . '">
                            <a class="page-link" href="' . ($lastDisabled ? '#' : generatePageUrl($total_pages, $params)) . '">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                          </li>';
                ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para crear/editar producto -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalLabel">Nuevo Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="productForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" id="productAction" value="create">
                    <input type="hidden" name="id" id="productId">
                    
                    <div class="mb-3">
                        <label for="codigo" class="form-label">Código *</label>
                        <input type="text" class="form-control" id="codigo" name="codigo" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción *</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="precio" class="form-label">Precio *</label>
                        <input type="number" class="form-control" id="precio" name="precio" step="0.01" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="grupo_id" class="form-label">Grupo</label>
                        <select class="form-select" id="grupo_id" name="grupo_id">
                            <option value="">Seleccionar grupo</option>
                            <?php foreach ($grupos as $grupo): ?>
                                <option value="<?php echo $grupo['id']; ?>"><?php echo htmlspecialchars($grupo['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Producto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// JavaScript adicional
$additionalJS = <<<EOT
<script>
    // Función auxiliar para escapar HTML
    function escapeHtml(text) {
        var element = document.createElement('div');
        element.textContent = text;
        return element.innerHTML;
    }

    function editProduct(id, codigo, descripcion, precio, grupoId) {
        document.getElementById("productAction").value = "update";
        document.getElementById("productId").value = id;
        document.getElementById("codigo").value = escapeHtml(codigo);
        document.getElementById("descripcion").value = escapeHtml(descripcion);
        document.getElementById("precio").value = precio;
        document.getElementById("grupo_id").value = grupoId || "";
        
        document.getElementById("productModalLabel").textContent = "Editar Producto";
        new bootstrap.Modal(document.getElementById("productModal")).show();
    }

    function deleteProduct(id) {
        if (confirm("¿Estás seguro de que quieres eliminar este producto?")) {
            const form = document.createElement("form");
            form.method = "POST";
            form.innerHTML = `
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="\${id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Funciones para manejo de grupos
    function seleccionarTodosGrupos() {
        document.querySelectorAll('.grupo-checkbox').forEach(cb => cb.checked = true);
    }

    function limpiarGrupos() {
        document.querySelectorAll('.grupo-checkbox').forEach(cb => cb.checked = false);
    }

    function aplicarFiltroGrupos() {
        const form = document.createElement('form');
        form.method = 'GET';
        form.style.display = 'none';
        
        const currentUrl = new URLSearchParams(window.location.search);
        for (const [key, value] of currentUrl.entries()) {
            if (key !== 'grupos[]') {
                const input = document.createElement('input');
                input.name = key;
                input.value = value;
                form.appendChild(input);
            }
        }
        
        document.querySelectorAll('.grupo-checkbox:checked').forEach(cb => {
            const input = document.createElement('input');
            input.name = 'grupos[]';
            input.value = cb.value;
            form.appendChild(input);
        });
        
        document.body.appendChild(form);
        form.submit();
    }

    // Resetear modal al cerrarse
    document.getElementById("productModal").addEventListener("hidden.bs.modal", function() {
        document.getElementById("productForm").reset();
        document.getElementById("productAction").value = "create";
        document.getElementById("productId").value = "";
        document.getElementById("productModalLabel").textContent = "Nuevo Producto";
    });
</script>
EOT;

// Renderizar la página
LayoutManager::renderAdminPage('Gestión de Productos', $content, '', $additionalJS);
?>
