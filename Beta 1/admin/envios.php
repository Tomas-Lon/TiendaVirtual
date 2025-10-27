<?php
session_start();
require_once '../includes/LayoutManager.php';
require_once '../config/database.php';

$pdo = getConnection();

// Helpers para compatibilidad con esquemas antiguos
function getEnviosColumns(PDO $pdo) {
    static $columns = null;
    if ($columns !== null) {
        return $columns;
    }
    $columns = [];
    try {
        $stmt = $pdo->query('DESCRIBE envios');
        foreach ($stmt as $row) {
            $columns[strtolower($row['Field'])] = true;
        }
    } catch (Exception $e) {
        // Si no se puede describir, dejar columns vacío para no añadir campos opcionales
    }
    return $columns;
}

function enviosHasColumn(PDO $pdo, string $name): bool {
    $cols = getEnviosColumns($pdo);
    return isset($cols[strtolower($name)]);
}

// Manejo de acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $response = ['success' => false, 'message' => ''];

    // Estados válidos (unificados en toda la vista)
    $estadosPermitidos = ['programado', 'en_preparacion', 'en_transito', 'entregado', 'fallido', 'devuelto'];

    try {
        if ($_POST['action'] === 'crear') {
            // Validaciones básicas
            $pedidoId = isset($_POST['pedido_id']) ? (int)$_POST['pedido_id'] : 0;
            $transportista = trim($_POST['transportista'] ?? '');
            // El número de guía será el mismo número de documento del pedido seleccionado
            $numeroGuia = '';
            $nStmt = $pdo->prepare('SELECT numero_documento FROM pedidos WHERE id = ?');
            $nStmt->execute([$pedidoId]);
            $numeroGuia = (string)($nStmt->fetchColumn() ?: '');
            $estado = trim($_POST['estado'] ?? '');
            $fechaProgramada = trim($_POST['fecha_programada'] ?? '');
            $telefonoContacto = trim($_POST['telefono_contacto'] ?? '');
            $direccionEntrega = trim($_POST['direccion_entrega'] ?? '');
            $observaciones = trim($_POST['observaciones'] ?? '');

            $requireDireccion = enviosHasColumn($pdo, 'direccion_entrega');
            if ($pedidoId <= 0 || $transportista === '' || $fechaProgramada === '' || ($requireDireccion && $direccionEntrega === '')) {
                throw new Exception('Complete los campos obligatorios.');
            }
            if (!in_array($estado, $estadosPermitidos, true)) {
                throw new Exception('Estado no válido.');
            }

            // Evitar número de guía duplicado
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM envios WHERE numero_guia = ?');
            $stmt->execute([$numeroGuia]);
            if ((int)$stmt->fetchColumn() > 0) {
                throw new Exception('El número de guía ya existe.');
            }

            // Construir INSERT dinámicamente según columnas disponibles
            $cols = ['pedido_id','transportista','numero_guia','estado','fecha_programada'];
            $vals = [$pedidoId, $transportista, $numeroGuia, $estado, $fechaProgramada];

            if (enviosHasColumn($pdo, 'telefono_contacto')) {
                $cols[] = 'telefono_contacto';
                $vals[] = ($telefonoContacto !== '' ? $telefonoContacto : null);
            }
            if (enviosHasColumn($pdo, 'direccion_entrega')) {
                $cols[] = 'direccion_entrega';
                $vals[] = $direccionEntrega !== '' ? $direccionEntrega : null;
            }
            if (enviosHasColumn($pdo, 'observaciones')) {
                $cols[] = 'observaciones';
                $vals[] = ($observaciones !== '' ? $observaciones : null);
            }
            if (enviosHasColumn($pdo, 'fecha_entrega_real')) {
                // Si se crea como entregado, registrar fecha de entrega real
                $cols[] = 'fecha_entrega_real';
                $vals[] = ($estado === 'entregado') ? date('Y-m-d H:i:s') : null;
            }

            $placeholders = implode(', ', array_fill(0, count($cols), '?'));
            $columnsSql = implode(', ', $cols);
            $stmt = $pdo->prepare("INSERT INTO envios ($columnsSql) VALUES ($placeholders)");
            $stmt->execute($vals);
            $response = ['success' => true, 'message' => 'Envío creado correctamente'];
        }
        
        elseif ($_POST['action'] === 'editar') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($id <= 0) {
                throw new Exception('ID de envío inválido.');
            }

            $transportista = trim($_POST['transportista'] ?? '');
            $estado = trim($_POST['estado'] ?? '');
            $fechaProgramada = trim($_POST['fecha_programada'] ?? '');
            $telefonoContacto = trim($_POST['telefono_contacto'] ?? '');
            $direccionEntrega = trim($_POST['direccion_entrega'] ?? '');
            $observaciones = trim($_POST['observaciones'] ?? '');

            $requireDireccion = enviosHasColumn($pdo, 'direccion_entrega');
            if ($transportista === '' || $fechaProgramada === '' || ($requireDireccion && $direccionEntrega === '')) {
                throw new Exception('Complete los campos obligatorios.');
            }
            if (!in_array($estado, $estadosPermitidos, true)) {
                throw new Exception('Estado no válido.');
            }

            // Obtener estado previo para decidir fecha_entrega_real
            $prevStmt = $pdo->prepare('SELECT estado, fecha_entrega_real FROM envios WHERE id = ?');
            $prevStmt->execute([$id]);
            $prev = $prevStmt->fetch();
            if (!$prev) {
                throw new Exception('Envío no encontrado.');
            }

            // Construir UPDATE dinámicamente
            $setParts = ['transportista = ?','estado = ?','fecha_programada = ?'];
            $vals = [$transportista, $estado, $fechaProgramada];

            if (enviosHasColumn($pdo, 'telefono_contacto')) {
                $setParts[] = 'telefono_contacto = ?';
                $vals[] = ($telefonoContacto !== '' ? $telefonoContacto : null);
            }
            if (enviosHasColumn($pdo, 'direccion_entrega')) {
                $setParts[] = 'direccion_entrega = ?';
                $vals[] = $direccionEntrega !== '' ? $direccionEntrega : null;
            }
            if (enviosHasColumn($pdo, 'observaciones')) {
                $setParts[] = 'observaciones = ?';
                $vals[] = ($observaciones !== '' ? $observaciones : null);
            }
            if (enviosHasColumn($pdo, 'fecha_entrega_real')) {
                // Conservar si ya tenía fecha de entrega, de lo contrario establecer ahora si pasa a entregado
                $fechaEntregaReal = ($estado === 'entregado') ? ($prev['fecha_entrega_real'] ?: date('Y-m-d H:i:s')) : null;
                $setParts[] = 'fecha_entrega_real = ?';
                $vals[] = $fechaEntregaReal;
            }

            $setSql = implode(', ', $setParts);
            $vals[] = $id;
            $stmt = $pdo->prepare("UPDATE envios SET $setSql WHERE id = ?");
            $stmt->execute($vals);
            $response = ['success' => true, 'message' => 'Envío actualizado correctamente (número de guía no modificable)'];
        }
        
        elseif ($_POST['action'] === 'eliminar') {
            $stmt = $pdo->prepare("DELETE FROM envios WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $response = ['success' => true, 'message' => 'Envío eliminado correctamente'];
        }
        
        elseif ($_POST['action'] === 'cambiar_estado') {
            if (!in_array(($_POST['estado'] ?? ''), $estadosPermitidos, true)) {
                throw new Exception('Estado no válido.');
            }
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($id <= 0) {
                throw new Exception('ID de envío inválido.');
            }
            if (enviosHasColumn($pdo, 'fecha_entrega_real')) {
                $stmt = $pdo->prepare("UPDATE envios SET estado = ?, fecha_entrega_real = ? WHERE id = ?");
                $fecha_entrega_real = ($_POST['estado'] === 'entregado') ? date('Y-m-d H:i:s') : null;
                $stmt->execute([$_POST['estado'], $fecha_entrega_real, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE envios SET estado = ? WHERE id = ?");
                $stmt->execute([$_POST['estado'], $id]);
            }
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
$filtro_transportista = $_GET['transportista'] ?? '';
$search = $_GET['search'] ?? '';

// Construir consulta con filtros
$where_conditions = [];
$params = [];

if (!empty($filtro_estado)) {
    $where_conditions[] = "e.estado = ?";
    $params[] = $filtro_estado;
}

if (!empty($filtro_transportista)) {
    $where_conditions[] = "e.transportista LIKE ?";
    $params[] = "%{$filtro_transportista}%";
}

if (!empty($search)) {
    $where_conditions[] = "(e.numero_guia LIKE ? OR c.nombre LIKE ? OR e.direccion_entrega LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Obtener envíos
$sql = "SELECT e.*, 
         p.numero_documento as pedido_numero,
               c.nombre as cliente_nombre,
               c.email as cliente_email,
               p.total as pedido_total
        FROM envios e
        INNER JOIN pedidos p ON e.pedido_id = p.id
        INNER JOIN clientes c ON p.cliente_id = c.id
        {$where_clause}
        ORDER BY e.fecha_programada DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$envios = $stmt->fetchAll();

// Obtener pedidos sin envío para el modal de crear
$pedidos_sin_envio_sql = "SELECT p.id, p.numero_documento as numero, c.nombre as cliente 
                          FROM pedidos p 
                          INNER JOIN clientes c ON p.cliente_id = c.id 
                          WHERE p.id NOT IN (SELECT pedido_id FROM envios WHERE pedido_id IS NOT NULL)
                          AND p.estado IN ('confirmado', 'preparando')
                          ORDER BY p.id DESC";
$pedidos_sin_envio_stmt = $pdo->prepare($pedidos_sin_envio_sql);
$pedidos_sin_envio_stmt->execute();
$pedidos_sin_envio = $pedidos_sin_envio_stmt->fetchAll();

// Obtener transportistas únicos
$transportistas_sql = "SELECT DISTINCT transportista FROM envios WHERE transportista IS NOT NULL AND transportista != '' ORDER BY transportista";
$transportistas_stmt = $pdo->prepare($transportistas_sql);
$transportistas_stmt->execute();
$transportistas = $transportistas_stmt->fetchAll();

// Preparar contenido de la página
ob_start();
?>

<!-- Header con botón de nuevo envío -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestión de Envíos</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalEnvio">
        <i class="fas fa-plus"></i> Nuevo Envío
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
                    <option value="programado" <?php echo $filtro_estado === 'programado' ? 'selected' : ''; ?>>Programado</option>
                    <option value="en_preparacion" <?php echo $filtro_estado === 'en_preparacion' ? 'selected' : ''; ?>>En Preparación</option>
                    <option value="en_transito" <?php echo $filtro_estado === 'en_transito' ? 'selected' : ''; ?>>En Tránsito</option>
                    <option value="entregado" <?php echo $filtro_estado === 'entregado' ? 'selected' : ''; ?>>Entregado</option>
                    <option value="fallido" <?php echo $filtro_estado === 'fallido' ? 'selected' : ''; ?>>Fallido</option>
                    <option value="devuelto" <?php echo $filtro_estado === 'devuelto' ? 'selected' : ''; ?>>Devuelto</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="transportista" class="form-label">Transportista</label>
                <select class="form-select" id="transportista" name="transportista">
                    <option value="">Todos los transportistas</option>
                    <?php foreach ($transportistas as $transportista): ?>
                        <option value="<?php echo htmlspecialchars($transportista['transportista']); ?>" 
                                <?php echo $filtro_transportista === $transportista['transportista'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($transportista['transportista']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="search" class="form-label">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Número de guía, cliente, dirección...">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary me-2">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <a href="envios.php" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de envíos -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Pedido / Cliente</th>
                        <th>Envío</th>
                        <th>Estado / Fechas</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($envios)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                No se encontraron envíos
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($envios as $envio): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div>
                                        <strong>Pedido #<?php echo $envio['pedido_numero']; ?></strong>
                                        <span class="badge bg-secondary ms-2">$<?php echo number_format($envio['pedido_total'], 2); ?></span>
                                        <br>
                                        <small class="text-primary"><?php echo htmlspecialchars($envio['cliente_nombre']); ?></small>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($envio['cliente_email']); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <strong><?php echo htmlspecialchars($envio['transportista']); ?></strong>
                                    <br>
                                    <span class="badge bg-info"><?php echo htmlspecialchars($envio['numero_guia']); ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="text-center">
                                    <?php
                                    $badge_class = '';
                                    switch ($envio['estado']) {
                                        case 'programado': $badge_class = 'bg-secondary'; break;
                                        case 'en_preparacion': $badge_class = 'bg-warning'; break;
                                        case 'en_transito': $badge_class = 'bg-primary'; break;
                                        case 'entregado': $badge_class = 'bg-success'; break;
                                        case 'fallido': $badge_class = 'bg-danger'; break;
                                        case 'devuelto': $badge_class = 'bg-dark'; break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?> mb-2">
                                        <?php echo ucfirst(str_replace('_', ' ', $envio['estado'])); ?>
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar"></i> 
                                        <?php echo $envio['fecha_programada'] ? date('d/m/Y', strtotime($envio['fecha_programada'])) : '-'; ?>
                                    </small>
                                    <?php if ($envio['fecha_entrega_real']): ?>
                                        <br>
                                        <small class="text-success">
                                            <i class="fas fa-check"></i> 
                                            <?php echo date('d/m/Y H:i', strtotime($envio['fecha_entrega_real'])); ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="editarEnvio(<?php echo htmlspecialchars(json_encode($envio)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-info" onclick="verDetalles(<?php echo htmlspecialchars(json_encode($envio)); ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-success" onclick="cambiarEstado(<?php echo $envio['id']; ?>, '<?php echo $envio['estado']; ?>')">
                                        <i class="fas fa-truck"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="eliminarEnvio(<?php echo $envio['id']; ?>)">
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
    </div>
</div>

<!-- Modal para crear/editar envío -->
<div class="modal fade" id="modalEnvio" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formEnvio">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEnvioTitle">Nuevo Envío</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="envio_id" name="id">
                    <input type="hidden" id="action" name="action" value="crear">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="pedido_id" class="form-label">Pedido</label>
                            <select class="form-select" id="pedido_id" name="pedido_id" required>
                                <option value="">Seleccionar pedido</option>
                                <?php foreach ($pedidos_sin_envio as $pedido): ?>
                                    <option value="<?php echo $pedido['id']; ?>" data-numero-documento="<?php echo htmlspecialchars($pedido['numero']); ?>">
                                        #<?php echo htmlspecialchars($pedido['numero']); ?> - <?php echo htmlspecialchars($pedido['cliente']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="transportista" class="form-label">Transportista</label>
                            <input type="text" class="form-control" id="transportista_input" name="transportista" required 
                                   list="transportistas_list" placeholder="Ej: DHL, FedEx, Correos...">
                            <datalist id="transportistas_list">
                                <?php foreach ($transportistas as $transportista): ?>
                                    <option value="<?php echo htmlspecialchars($transportista['transportista']); ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="col-md-6">
                            <label for="numero_guia" class="form-label">Número de Guía</label>
                            <input type="text" class="form-control" id="numero_guia" name="numero_guia" required readonly placeholder="Se asigna igual al número de pedido">
                        </div>
                        <div class="col-md-6">
                            <label for="estado_select" class="form-label">Estado</label>
                            <select class="form-select" id="estado_select" name="estado" required>
                                <option value="programado">Programado</option>
                                <option value="en_preparacion">En Preparación</option>
                                <option value="en_transito">En Tránsito</option>
                                <option value="entregado">Entregado</option>
                                <option value="fallido">Fallido</option>
                                <option value="devuelto">Devuelto</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="fecha_programada" class="form-label">Fecha Programada</label>
                            <input type="date" class="form-control" id="fecha_programada" name="fecha_programada" required>
                        </div>
                        <div class="col-md-6">
                            <label for="telefono_contacto" class="form-label">Teléfono de Contacto</label>
                            <input type="tel" class="form-control" id="telefono_contacto" name="telefono_contacto">
                        </div>
                        <div class="col-12">
                            <label for="direccion_entrega" class="form-label">Dirección de Entrega</label>
                            <textarea class="form-control" id="direccion_entrega" name="direccion_entrega" rows="2" required></textarea>
                        </div>
                        <div class="col-12">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="2"></textarea>
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

<!-- Modal para cambiar estado -->
<div class="modal fade" id="modalEstado" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formEstado">
                <div class="modal-header">
                    <h5 class="modal-title">Cambiar Estado de Envío</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="estado_envio_id" name="id">
                    <input type="hidden" name="action" value="cambiar_estado">
                    
                    <div class="mb-3">
                        <label for="nuevo_estado" class="form-label">Nuevo Estado</label>
                        <select class="form-select" id="nuevo_estado" name="estado" required>
                            <option value="programado">Programado</option>
                            <option value="en_preparacion">En Preparación</option>
                            <option value="en_transito">En Tránsito</option>
                            <option value="entregado">Entregado</option>
                            <option value="fallido">Fallido</option>
                            <option value="devuelto">Devuelto</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
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
// Configurar fecha por defecto
document.getElementById("fecha_programada").value = new Date().toISOString().split("T")[0];

// Sincronizar número de guía con pedido seleccionado al crear
const pedidoSelectEl = document.getElementById("pedido_id");
const numeroGuiaEl = document.getElementById("numero_guia");
if (pedidoSelectEl && numeroGuiaEl) {
    const syncNumeroGuia = () => {
        const opt = pedidoSelectEl.options[pedidoSelectEl.selectedIndex];
    const numeroDoc = opt ? opt.getAttribute("data-numero-documento") : "";
    numeroGuiaEl.value = numeroDoc || "";
    };
    pedidoSelectEl.addEventListener("change", syncNumeroGuia);
    // Inicializar por si ya hay valor seleccionado
    syncNumeroGuia();
}

// Manejar formulario de envío
document.getElementById("formEnvio").addEventListener("submit", function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch("envios.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById("modalEnvio")).hide();
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

// Manejar formulario de cambio de estado
document.getElementById("formEstado").addEventListener("submit", function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch("envios.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById("modalEstado")).hide();
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

function editarEnvio(envio) {
    document.getElementById("modalEnvioTitle").textContent = "Editar Envío";
    document.getElementById("action").value = "editar";
    document.getElementById("envio_id").value = envio.id;
    // Asegurar que el pedido actual esté presente en el select
    const pedidoSelect = document.getElementById("pedido_id");
    if (![...pedidoSelect.options].some(o => o.value == envio.pedido_id)) {
        const opt = document.createElement("option");
        opt.value = envio.pedido_id;
        opt.textContent = `#${envio.pedido_numero || envio.pedido_id}`;
        if (envio.pedido_numero) {
            opt.setAttribute("data-numero-documento", envio.pedido_numero);
        }
        pedidoSelect.appendChild(opt);
    }
    pedidoSelect.value = envio.pedido_id;
    document.getElementById("transportista_input").value = envio.transportista;
    document.getElementById("numero_guia").value = envio.numero_guia;
    document.getElementById("estado_select").value = envio.estado;
    document.getElementById("fecha_programada").value = envio.fecha_programada;
    document.getElementById("telefono_contacto").value = envio.telefono_contacto || "";
    document.getElementById("direccion_entrega").value = envio.direccion_entrega;
    document.getElementById("observaciones").value = envio.observaciones || "";
    
    // Deshabilitar cambio de pedido y número de guía en edición
    document.getElementById("pedido_id").disabled = true;
    document.getElementById("numero_guia").readOnly = true;
    document.getElementById("numero_guia").style.backgroundColor = "#f8f9fa";
    
    new bootstrap.Modal(document.getElementById("modalEnvio")).show();
}

function cambiarEstado(id, estadoActual) {
    document.getElementById("estado_envio_id").value = id;
    document.getElementById("nuevo_estado").value = estadoActual;
    
    new bootstrap.Modal(document.getElementById("modalEstado")).show();
}

function eliminarEnvio(id) {
    if (confirm("¿Está seguro de que desea eliminar este envío?")) {
        const formData = new FormData();
        formData.append("action", "eliminar");
        formData.append("id", id);
        
        fetch("envios.php", {
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

function verDetalles(envio) {
    // Crear modal dinámico para mostrar detalles
    const modalHtml = 
        \'<div class="modal fade" id="modalDetallesEnvio" tabindex="-1">\' +
            \'<div class="modal-dialog modal-lg">\' +
                \'<div class="modal-content">\' +
                    \'<div class="modal-header">\' +
                        \'<h5 class="modal-title">\' +
                            \'<i class="fas fa-info-circle"></i> Detalles del Envío #\' + envio.numero_guia +
                        \'</h5>\' +
                        \'<button type="button" class="btn-close" data-bs-dismiss="modal"></button>\' +
                    \'</div>\' +
                    \'<div class="modal-body">\' +
                        \'<div class="row">\' +
                            \'<div class="col-md-6">\' +
                                \'<div class="card h-100">\' +
                                    \'<div class="card-header">\' +
                                        \'<h6 class="card-title mb-0">\' +
                                            \'<i class="fas fa-shipping-fast"></i> Información del Envío\' +
                                        \'</h6>\' +
                                    \'</div>\' +
                                    \'<div class="card-body">\' +
                                        \'<table class="table table-sm">\' +
                                            \'<tr>\' +
                                                \'<td><strong>Número de Guía:</strong></td>\' +
                                                \'<td><span class="badge bg-primary">\' + envio.numero_guia + \'</span></td>\' +
                                            \'</tr>\' +
                                            \'<tr>\' +
                                                \'<td><strong>Estado:</strong></td>\' +
                                                \'<td>\' + getEstadoBadge(envio.estado) + \'</td>\' +
                                            \'</tr>\' +
                                            \'<tr>\' +
                                                \'<td><strong>Transportista:</strong></td>\' +
                                                \'<td>\' + envio.transportista + \'</td>\' +
                                            \'</tr>\' +
                                            \'<tr>\' +
                                                \'<td><strong>Fecha Programada:</strong></td>\' +
                                                \'<td>\' + formatearFecha(envio.fecha_programada) + \'</td>\' +
                                            \'</tr>\' +
                                            (envio.fecha_entrega_real ? 
                                                \'<tr>\' +
                                                    \'<td><strong>Fecha Entrega Real:</strong></td>\' +
                                                    \'<td class="text-success">\' + formatearFecha(envio.fecha_entrega_real) + \'</td>\' +
                                                \'</tr>\' : \'\') +
                                        \'</table>\' +
                                    \'</div>\' +
                                \'</div>\' +
                            \'</div>\' +
                            \'<div class="col-md-6">\' +
                                \'<div class="card h-100">\' +
                                    \'<div class="card-header">\' +
                                        \'<h6 class="card-title mb-0">\' +
                                            \'<i class="fas fa-user"></i> Información del Cliente\' +
                                        \'</h6>\' +
                                    \'</div>\' +
                                    \'<div class="card-body">\' +
                                        \'<table class="table table-sm">\' +
                                            \'<tr>\' +
                                                \'<td><strong>Cliente:</strong></td>\' +
                                                \'<td>\' + envio.cliente_nombre + \'</td>\' +
                                            \'</tr>\' +
                                            \'<tr>\' +
                                                \'<td><strong>Email:</strong></td>\' +
                                                \'<td>\' + envio.cliente_email + \'</td>\' +
                                            \'</tr>\' +
                                            \'<tr>\' +
                                                \'<td><strong>Pedido #:</strong></td>\' +
                                                \'<td><span class="badge bg-info">\' + envio.pedido_numero + \'</span></td>\' +
                                            \'</tr>\' +
                                            \'<tr>\' +
                                                \'<td><strong>Total Pedido:</strong></td>\' +
                                                \'<td class="text-success fw-bold">$\' + parseFloat(envio.pedido_total).toLocaleString() + \'</td>\' +
                                            \'</tr>\' +
                                        \'</table>\' +
                                    \'</div>\' +
                                \'</div>\' +
                            \'</div>\' +
                        \'</div>\' +
                        (envio.direccion_entrega ? 
                            \'<div class="row mt-3">\' +
                                \'<div class="col-12">\' +
                                    \'<div class="card">\' +
                                        \'<div class="card-header">\' +
                                            \'<h6 class="card-title mb-0">\' +
                                                \'<i class="fas fa-map-marker-alt"></i> Dirección de Entrega\' +
                                            \'</h6>\' +
                                        \'</div>\' +
                                        \'<div class="card-body">\' +
                                            \'<p class="mb-0">\' + envio.direccion_entrega + \'</p>\' +
                                        \'</div>\' +
                                    \'</div>\' +
                                \'</div>\' +
                            \'</div>\' : \'\') +
                        (envio.observaciones ? 
                            \'<div class="row mt-3">\' +
                                \'<div class="col-12">\' +
                                    \'<div class="card">\' +
                                        \'<div class="card-header">\' +
                                            \'<h6 class="card-title mb-0">\' +
                                                \'<i class="fas fa-sticky-note"></i> Observaciones\' +
                                            \'</h6>\' +
                                        \'</div>\' +
                                        \'<div class="card-body">\' +
                                            \'<p class="mb-0">\' + envio.observaciones + \'</p>\' +
                                        \'</div>\' +
                                    \'</div>\' +
                                \'</div>\' +
                            \'</div>\' : \'\') +
                    \'</div>\' +
                    \'<div class="modal-footer">\' +
                        \'<button type="button" class="btn btn-outline-primary" onclick="imprimirGuia(\' + envio.id + \')">\' +
                            \'<i class="fas fa-print"></i> Imprimir Guía\' +
                        \'</button>\' +
                        \'<button type="button" class="btn btn-outline-success" onclick="copiarNumeroGuia(\\\'\' + envio.numero_guia + \'\\\');">\' +
                            \'<i class="fas fa-copy"></i> Copiar Número\' +
                        \'</button>\' +
                        \'<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">\' +
                            \'<i class="fas fa-times"></i> Cerrar\' +
                        \'</button>\' +
                    \'</div>\' +
                \'</div>\' +
            \'</div>\' +
        \'</div>\';
    
    // Eliminar modal anterior si existe
    const modalAnterior = document.getElementById(\'modalDetallesEnvio\');
    if (modalAnterior) {
        modalAnterior.remove();
    }
    
    // Agregar modal al DOM
    document.body.insertAdjacentHTML(\'beforeend\', modalHtml);
    
    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById(\'modalDetallesEnvio\'));
    modal.show();
    
    // Eliminar modal del DOM cuando se cierre
    document.getElementById(\'modalDetallesEnvio\').addEventListener(\'hidden.bs.modal\', function() {
        this.remove();
    });
}

function getEstadoBadge(estado) {
    const estados = {
        \'programado\': \'<span class="badge bg-secondary">Programado</span>\',
        \'en_preparacion\': \'<span class="badge bg-warning">En Preparación</span>\',
        \'en_transito\': \'<span class="badge bg-primary">En Tránsito</span>\',
        \'entregado\': \'<span class="badge bg-success">Entregado</span>\',
        \'fallido\': \'<span class="badge bg-danger">Fallido</span>\',
        \'devuelto\': \'<span class="badge bg-dark">Devuelto</span>\'
    };
    return estados[estado] || \'<span class="badge bg-secondary">\' + (estado || \'Desconocido\') + \'</span>\';
}

function formatearFecha(fecha) {
    if (!fecha) return \'No definida\';
    const date = new Date(fecha);
    return date.toLocaleDateString(\'es-CO\', {
        year: \'numeric\',
        month: \'long\',
        day: \'numeric\',
        hour: \'2-digit\',
        minute: \'2-digit\'
    });
}

function imprimirGuia(envioId) {
    // Abrir ventana de impresión
    const url = \'imprimir_guia.php?id=\' + envioId;
    window.open(url, \'_blank\', \'width=800,height=600\');
}

function copiarNumeroGuia(numeroGuia) {
    navigator.clipboard.writeText(numeroGuia).then(function() {
        // Mostrar notificación de éxito
        const toast = document.createElement(\'div\');
        toast.className = \'alert alert-success alert-dismissible position-fixed\';
        toast.style.cssText = \'top: 20px; right: 20px; z-index: 9999; max-width: 300px;\';
        toast.innerHTML = 
            \'<small><i class="fas fa-check"></i> Número de guía copiado: \' + numeroGuia + \'</small>\' +
            \'<button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>\';
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }).catch(function() {
        alert(\'Número de guía: \' + numeroGuia);
    });
}

// Limpiar modal al cerrarse
document.getElementById("modalEnvio").addEventListener("hidden.bs.modal", function() {
    document.getElementById("formEnvio").reset();
    document.getElementById("modalEnvioTitle").textContent = "Nuevo Envío";
    document.getElementById("action").value = "crear";
    document.getElementById("envio_id").value = "";
    document.getElementById("pedido_id").disabled = false;
    document.getElementById("numero_guia").readOnly = false;
    document.getElementById("numero_guia").style.backgroundColor = "";
    document.getElementById("fecha_programada").value = new Date().toISOString().split("T")[0];
});
</script>';

// Renderizar la página
LayoutManager::renderAdminPage('Gestión de Envíos', $content, '', $additionalJS);
?>
