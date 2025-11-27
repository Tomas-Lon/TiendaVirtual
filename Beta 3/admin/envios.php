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
            $repartidorId = isset($_POST['repartidor_id']) ? (int)$_POST['repartidor_id'] : null;
            $direccionEntregaId = isset($_POST['direccion_entrega_id']) ? (int)$_POST['direccion_entrega_id'] : null;
            $transportista = 'Transporte Sol Técnica'; // Fijo para todos los envíos
            // El número de guía será el mismo número de documento del pedido seleccionado
            $numeroGuia = '';
            $nStmt = $pdo->prepare('SELECT numero_documento FROM pedidos WHERE id = ?');
            $nStmt->execute([$pedidoId]);
            $numeroGuia = (string)($nStmt->fetchColumn() ?: '');
            $estado = trim($_POST['estado'] ?? 'programado'); // Por defecto programado
            $fechaProgramada = trim($_POST['fecha_programada'] ?? '');
            $receptorNombre = trim($_POST['receptor_nombre'] ?? '');
            $receptorDocumento = trim($_POST['receptor_documento'] ?? '');
            $observaciones = trim($_POST['observaciones'] ?? '');

            if ($pedidoId <= 0 || $fechaProgramada === '') {
                throw new Exception('Complete los campos obligatorios.');
            }
            if (!$direccionEntregaId || $direccionEntregaId <= 0) {
                throw new Exception('Debe seleccionar una dirección de entrega.');
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

            if (enviosHasColumn($pdo, 'repartidor_id')) {
                $cols[] = 'repartidor_id';
                $vals[] = $repartidorId;
            }
            if (enviosHasColumn($pdo, 'direccion_entrega_id')) {
                $cols[] = 'direccion_entrega_id';
                $vals[] = $direccionEntregaId;
            }
            if (enviosHasColumn($pdo, 'receptor_nombre')) {
                $cols[] = 'receptor_nombre';
                $vals[] = ($receptorNombre !== '' ? $receptorNombre : null);
            }
            if (enviosHasColumn($pdo, 'receptor_documento')) {
                $cols[] = 'receptor_documento';
                $vals[] = ($receptorDocumento !== '' ? $receptorDocumento : null);
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

            $repartidorId = isset($_POST['repartidor_id']) ? (int)$_POST['repartidor_id'] : null;
            $direccionEntregaId = isset($_POST['direccion_entrega_id']) ? (int)$_POST['direccion_entrega_id'] : null;
            $estado = trim($_POST['estado'] ?? 'programado');
            $fechaProgramada = trim($_POST['fecha_programada'] ?? '');
            $receptorNombre = trim($_POST['receptor_nombre'] ?? '');
            $receptorDocumento = trim($_POST['receptor_documento'] ?? '');
            $observaciones = trim($_POST['observaciones'] ?? '');

            if ($fechaProgramada === '') {
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
            $setParts = ['estado = ?','fecha_programada = ?'];
            $vals = [$estado, $fechaProgramada];

            if (enviosHasColumn($pdo, 'repartidor_id')) {
                $setParts[] = 'repartidor_id = ?';
                $vals[] = $repartidorId;
            }
            if (enviosHasColumn($pdo, 'direccion_entrega_id')) {
                $setParts[] = 'direccion_entrega_id = ?';
                $vals[] = $direccionEntregaId;
            }
            if (enviosHasColumn($pdo, 'receptor_nombre')) {
                $setParts[] = 'receptor_nombre = ?';
                $vals[] = ($receptorNombre !== '' ? $receptorNombre : null);
            }
            if (enviosHasColumn($pdo, 'receptor_documento')) {
                $setParts[] = 'receptor_documento = ?';
                $vals[] = ($receptorDocumento !== '' ? $receptorDocumento : null);
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
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($id <= 0) {
                throw new Exception('ID de envío inválido.');
            }
            
            // Verificar que no esté entregado
            $stmt = $pdo->prepare('SELECT estado FROM envios WHERE id = ?');
            $stmt->execute([$id]);
            $envio = $stmt->fetch();
            
            if (!$envio) {
                throw new Exception('Envío no encontrado.');
            }
            
            if ($envio['estado'] === 'entregado') {
                throw new Exception('No se puede eliminar un envío que ya fue entregado.');
            }
            
            $stmt = $pdo->prepare("DELETE FROM envios WHERE id = ?");
            $stmt->execute([$id]);
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
            
            // Verificar estado actual
            $stmt = $pdo->prepare('SELECT estado FROM envios WHERE id = ?');
            $stmt->execute([$id]);
            $envioActual = $stmt->fetch();
            
            if (!$envioActual) {
                throw new Exception('Envío no encontrado.');
            }
            
            if ($envioActual['estado'] === 'entregado') {
                throw new Exception('No se puede modificar el estado de un envío que ya fue entregado.');
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
    $where_conditions[] = "(e.numero_guia LIKE ? OR c.nombre LIKE ? OR dir.direccion LIKE ? OR p.numero_documento LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Obtener envíos
$sql = "SELECT e.*, 
               p.numero_documento as pedido_numero,
               p.cliente_id,
               c.nombre as cliente_nombre,
               c.email as cliente_email,
               p.total as pedido_total,
               emp.nombre as repartidor_nombre,
               dir.nombre as direccion_nombre,
               dir.direccion as direccion_texto,
               dir.ciudad as direccion_ciudad,
               ent.id as entrega_realizada_id,
               comp.pdf_path as comprobante_pdf
        FROM envios e
        INNER JOIN pedidos p ON e.pedido_id = p.id
        INNER JOIN clientes c ON p.cliente_id = c.id
        LEFT JOIN empleados emp ON e.repartidor_id = emp.id
        LEFT JOIN direcciones_clientes dir ON e.direccion_entrega_id = dir.id
        LEFT JOIN entregas ent ON ent.envio_id = e.id
        LEFT JOIN comprobantes_entrega comp ON comp.entrega_id = ent.id
        {$where_clause}
        ORDER BY e.fecha_programada DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$envios = $stmt->fetchAll();

// Obtener pedidos sin envío para el modal de crear
$pedidos_sin_envio_sql = "SELECT p.id, p.numero_documento as numero, c.id as cliente_id, c.nombre as cliente 
                          FROM pedidos p 
                          INNER JOIN clientes c ON p.cliente_id = c.id 
                          WHERE p.id NOT IN (SELECT pedido_id FROM envios WHERE pedido_id IS NOT NULL)
                          AND p.estado IN ('confirmado', 'en_preparacion')
                          ORDER BY p.id DESC";
$pedidos_sin_envio_stmt = $pdo->prepare($pedidos_sin_envio_sql);
$pedidos_sin_envio_stmt->execute();
$pedidos_sin_envio = $pedidos_sin_envio_stmt->fetchAll();

// Obtener transportistas únicos
$transportistas_sql = "SELECT DISTINCT transportista FROM envios WHERE transportista IS NOT NULL AND transportista != '' ORDER BY transportista";
$transportistas_stmt = $pdo->prepare($transportistas_sql);
$transportistas_stmt->execute();
$transportistas = $transportistas_stmt->fetchAll();

// Obtener repartidores (empleados con cargo 'repartidor')
$repartidores_sql = "SELECT e.id, e.nombre, e.telefono 
                     FROM empleados e
                     INNER JOIN credenciales c ON c.empleado_id = e.id
                     WHERE LOWER(e.cargo) = 'repartidor' AND e.activo = 1
                     ORDER BY e.nombre";
$repartidores_stmt = $pdo->prepare($repartidores_sql);
$repartidores_stmt->execute();
$repartidores = $repartidores_stmt->fetchAll();

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
                        <tr <?php echo $envio['estado'] === 'entregado' ? 'class="table-success"' : ''; ?>>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div>
                                        <strong>Pedido #<?php echo $envio['pedido_numero']; ?></strong>
                                        <span class="badge bg-secondary ms-2">$<?php echo number_format($envio['pedido_total'], 2); ?></span>
                                        <?php if ($envio['estado'] === 'entregado'): ?>
                                            <span class="badge bg-success ms-1">
                                                <i class="fas fa-check-circle"></i> ENTREGADO
                                            </span>
                                        <?php endif; ?>
                                        <br>
                                        <small class="text-primary"><?php echo htmlspecialchars($envio['cliente_nombre']); ?></small>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($envio['cliente_email']); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <span class="badge bg-info"><?php echo htmlspecialchars($envio['numero_guia']); ?></span>
                                    <?php if ($envio['repartidor_nombre']): ?>
                                        <br><small class="text-primary">
                                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($envio['repartidor_nombre']); ?>
                                        </small>
                                    <?php else: ?>
                                        <br><small class="text-muted">
                                            <i class="fas fa-user-slash"></i> Sin repartidor asignado
                                        </small>
                                    <?php endif; ?>
                                    <?php if ($envio['direccion_nombre']): ?>
                                        <br><small class="text-success">
                                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($envio['direccion_nombre']); ?>
                                        </small>
                                    <?php endif; ?>
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
                                    <?php if ($envio['estado'] !== 'entregado'): ?>
                                        <button class="btn btn-outline-primary" onclick='editarEnvio(<?php echo json_encode($envio); ?>)' title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <button class="btn btn-outline-info" onclick='verDetalles(<?php echo json_encode($envio); ?>)' title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                    <?php if ($envio['estado'] === 'entregado' && $envio['comprobante_pdf']): ?>
                                        <button class="btn btn-outline-success" onclick='verComprobante("<?php echo htmlspecialchars($envio['comprobante_pdf'], ENT_QUOTES); ?>")' title="Ver comprobante">
                                            <i class="fas fa-file-pdf"></i>
                                        </button>
                                    <?php elseif ($envio['estado'] !== 'entregado'): ?>
                                        <button class="btn btn-outline-warning" onclick='cambiarEstado(<?php echo $envio['id']; ?>, "<?php echo $envio['estado']; ?>")' title="Cambiar estado">
                                            <i class="fas fa-truck"></i>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($envio['estado'] !== 'entregado'): ?>
                                        <button class="btn btn-outline-danger" onclick="eliminarEnvio(<?php echo $envio['id']; ?>)" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-outline-secondary" disabled title="No se puede eliminar una entrega completada">
                                            <i class="fas fa-lock"></i>
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
                            <label for="pedido_id" class="form-label">Pedido *</label>
                            <select class="form-select" id="pedido_id" name="pedido_id" required>
                                <option value="">Seleccionar pedido</option>
                                <?php foreach ($pedidos_sin_envio as $pedido): ?>
                                    <option value="<?php echo $pedido['id']; ?>" 
                                            data-numero-documento="<?php echo htmlspecialchars($pedido['numero']); ?>"
                                            data-cliente-id="<?php echo $pedido['cliente_id']; ?>">
                                        #<?php echo htmlspecialchars($pedido['numero']); ?> - <?php echo htmlspecialchars($pedido['cliente']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="repartidor_id" class="form-label">Repartidor</label>
                            <select class="form-select" id="repartidor_id" name="repartidor_id">
                                <option value="">Sin asignar</option>
                                <?php foreach ($repartidores as $repartidor): ?>
                                    <option value="<?php echo $repartidor['id']; ?>">
                                        <?php echo htmlspecialchars($repartidor['nombre']); ?>
                                        <?php if ($repartidor['telefono']): ?>
                                            - <?php echo htmlspecialchars($repartidor['telefono']); ?>
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="numero_guia" class="form-label">Número de Guía</label>
                            <input type="text" class="form-control" id="numero_guia" name="numero_guia" readonly 
                                   placeholder="Se asigna automáticamente" style="background-color: #f8f9fa;">
                        </div>
                        <div class="col-md-6">
                            <label for="fecha_programada" class="form-label">Fecha Programada *</label>
                            <input type="date" class="form-control" id="fecha_programada" name="fecha_programada" required>
                        </div>
                        <div class="col-md-12">
                            <label for="direccion_entrega_id" class="form-label">Dirección de Entrega *</label>
                            <select class="form-select" id="direccion_entrega_id" name="direccion_entrega_id" required>
                                <option value="">Seleccione primero un pedido</option>
                            </select>
                            <small class="text-muted">Al seleccionar una dirección se precargarán los datos del receptor</small>
                        </div>
                        <div class="col-md-6">
                            <label for="receptor_nombre" class="form-label">Receptor</label>
                            <input type="text" class="form-control" id="receptor_nombre" name="receptor_nombre" 
                                   placeholder="Nombre de quien recibe">
                        </div>
                        <div class="col-md-6">
                            <label for="receptor_documento" class="form-label">Documento Receptor</label>
                            <input type="text" class="form-control" id="receptor_documento" name="receptor_documento" 
                                   placeholder="CC/NIT">
                        </div>
                        <div class="col-md-12">
                            <label for="estado_select" class="form-label">Estado</label>
                            <select class="form-select" id="estado_select" name="estado">
                                <option value="programado" selected>Programado</option>
                                <option value="en_preparacion">En Preparación</option>
                                <option value="en_transito">En Tránsito</option>
                                <option value="entregado">Entregado</option>
                                <option value="fallido">Fallido</option>
                                <option value="devuelto">Devuelto</option>
                            </select>
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
$additionalJS = <<<'JAVASCRIPT'
<script>
// Configurar fecha por defecto
document.getElementById("fecha_programada").value = new Date().toISOString().split("T")[0];

// Sincronizar número de guía y cargar direcciones cuando se selecciona pedido
const pedidoSelectEl = document.getElementById("pedido_id");
const numeroGuiaEl = document.getElementById("numero_guia");
const direccionSelectEl = document.getElementById("direccion_entrega_id");

if (pedidoSelectEl && numeroGuiaEl && direccionSelectEl) {
    let direccionesData = [];
    
    pedidoSelectEl.addEventListener("change", function() {
        const opt = this.options[this.selectedIndex];
        const numeroDoc = opt ? opt.getAttribute("data-numero-documento") : "";
        const clienteId = opt ? opt.getAttribute("data-cliente-id") : "";
        
        numeroGuiaEl.value = numeroDoc || "";
        
        document.getElementById("receptor_nombre").value = "";
        document.getElementById("receptor_documento").value = "";
        
        if (clienteId) {
            direccionSelectEl.innerHTML = "<option value=''>Cargando...</option>";
            direccionSelectEl.disabled = true;
            
            fetch("ajax/get_direcciones_cliente.php?cliente_id=" + clienteId)
                .then(response => response.json())
                .then(data => {
                    direccionSelectEl.innerHTML = "<option value=''>Seleccionar dirección</option>";
                    if (data.success && data.direcciones) {
                        direccionesData = data.direcciones;
                        data.direcciones.forEach(dir => {
                            const option = document.createElement("option");
                            option.value = dir.id;
                            option.textContent = dir.nombre + " - " + dir.ciudad;
                            option.dataset.contactoReceptor = dir.contacto_receptor || "";
                            option.dataset.documentoReceptor = dir.documento_receptor || "";
                            direccionSelectEl.appendChild(option);
                        });
                    }
                    direccionSelectEl.disabled = false;
                })
                .catch(error => {
                    console.error("Error:", error);
                    direccionSelectEl.innerHTML = "<option value=''>Error al cargar</option>";
                    direccionSelectEl.disabled = false;
                });
        } else {
            direccionSelectEl.innerHTML = "<option value=''>Seleccione primero un pedido</option>";
            direccionSelectEl.disabled = true;
        }
    });
    
    direccionSelectEl.addEventListener("change", function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption && selectedOption.value) {
            const contactoReceptor = selectedOption.dataset.contactoReceptor || "";
            const documentoReceptor = selectedOption.dataset.documentoReceptor || "";
            
            document.getElementById("receptor_nombre").value = contactoReceptor;
            document.getElementById("receptor_documento").value = documentoReceptor;
        } else {
            document.getElementById("receptor_nombre").value = "";
            document.getElementById("receptor_documento").value = "";
        }
    });
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
    if (envio.estado === "entregado") {
        alert("No se puede editar un envío que ya fue entregado.\n\nPuede ver los detalles y el comprobante, pero no modificar la información.");
        return;
    }
    
    document.getElementById("modalEnvioTitle").textContent = "Editar Envío";
    document.getElementById("action").value = "editar";
    document.getElementById("envio_id").value = envio.id;
    
    const pedidoSelect = document.getElementById("pedido_id");
    if (![...pedidoSelect.options].some(o => o.value == envio.pedido_id)) {
        const opt = document.createElement("option");
        opt.value = envio.pedido_id;
        opt.textContent = "#" + (envio.pedido_numero || envio.pedido_id);
        if (envio.pedido_numero) {
            opt.setAttribute("data-numero-documento", envio.pedido_numero);
        }
        if (envio.cliente_id) {
            opt.setAttribute("data-cliente-id", envio.cliente_id);
        }
        pedidoSelect.appendChild(opt);
    }
    pedidoSelect.value = envio.pedido_id;
    document.getElementById("numero_guia").value = envio.numero_guia;
    document.getElementById("estado_select").value = envio.estado;
    document.getElementById("fecha_programada").value = envio.fecha_programada;
    document.getElementById("observaciones").value = envio.observaciones || "";
    
    document.getElementById("repartidor_id").value = envio.repartidor_id || "";
    document.getElementById("receptor_nombre").value = envio.receptor_nombre || "";
    document.getElementById("receptor_documento").value = envio.receptor_documento || "";
    
    if (envio.cliente_id) {
        const direccionSelect = document.getElementById("direccion_entrega_id");
        direccionSelect.innerHTML = "<option value=''>Cargando...</option>";
        direccionSelect.disabled = true;
        
        fetch("ajax/get_direcciones_cliente.php?cliente_id=" + envio.cliente_id)
            .then(response => response.json())
            .then(data => {
                direccionSelect.innerHTML = "<option value=''>Seleccionar dirección</option>";
                if (data.success && data.direcciones) {
                    data.direcciones.forEach(dir => {
                        const option = document.createElement("option");
                        option.value = dir.id;
                        option.textContent = dir.nombre + " - " + dir.ciudad;
                        option.dataset.contactoReceptor = dir.contacto_receptor || "";
                        option.dataset.documentoReceptor = dir.documento_receptor || "";
                        if (dir.id == envio.direccion_entrega_id) {
                            option.selected = true;
                        }
                        direccionSelect.appendChild(option);
                    });
                }
                direccionSelect.disabled = false;
            })
            .catch(error => {
                console.error("Error:", error);
                direccionSelect.innerHTML = "<option value=''>Error al cargar</option>";
                direccionSelect.disabled = false;
            });
    }
    
    document.getElementById("pedido_id").disabled = true;
    document.getElementById("numero_guia").readOnly = true;
    document.getElementById("numero_guia").style.backgroundColor = "#f8f9fa";
    
    new bootstrap.Modal(document.getElementById("modalEnvio")).show();
}

function cambiarEstado(id, estadoActual) {
    if (estadoActual === "entregado") {
        alert("No se puede cambiar el estado de un envío que ya fue entregado.\n\nLa entrega ha sido completada y el comprobante fue generado.");
        return;
    }
    
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

function verComprobante(pdfPath) {
    if (!pdfPath) {
        alert("No se encontró el comprobante de entrega.");
        return;
    }
    
    window.open(pdfPath, "_blank");
}

function verDetalles(envio) {
    const modalHtml = 
        '<div class="modal fade" id="modalDetallesEnvio" tabindex="-1">' +
            '<div class="modal-dialog modal-lg">' +
                '<div class="modal-content">' +
                    '<div class="modal-header">' +
                        '<h5 class="modal-title">' +
                            '<i class="fas fa-info-circle"></i> Detalles del Envío #' + envio.numero_guia +
                        '</h5>' +
                        '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>' +
                    '</div>' +
                    '<div class="modal-body">' +
                        '<div class="row">' +
                            '<div class="col-md-6">' +
                                '<div class="card h-100">' +
                                    '<div class="card-header">' +
                                        '<h6 class="card-title mb-0">' +
                                            '<i class="fas fa-shipping-fast"></i> Información del Envío' +
                                        '</h6>' +
                                    '</div>' +
                                    '<div class="card-body">' +
                                        '<table class="table table-sm">' +
                                            '<tr>' +
                                                '<td><strong>Número de Guía:</strong></td>' +
                                                '<td><span class="badge bg-primary">' + envio.numero_guia + '</span></td>' +
                                            '</tr>' +
                                            '<tr>' +
                                                '<td><strong>Estado:</strong></td>' +
                                                '<td>' + getEstadoBadge(envio.estado) + '</td>' +
                                            '</tr>' +
                                            '<tr>' +
                                                '<td><strong>Transportista:</strong></td>' +
                                                '<td>' + envio.transportista + '</td>' +
                                            '</tr>' +
                                            '<tr>' +
                                                '<td><strong>Fecha Programada:</strong></td>' +
                                                '<td>' + formatearFecha(envio.fecha_programada) + '</td>' +
                                            '</tr>' +
                                            (envio.fecha_entrega_real ? 
                                                '<tr>' +
                                                    '<td><strong>Fecha Entrega Real:</strong></td>' +
                                                    '<td class="text-success">' + formatearFecha(envio.fecha_entrega_real) + '</td>' +
                                                '</tr>' : '') +
                                        '</table>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="col-md-6">' +
                                '<div class="card h-100">' +
                                    '<div class="card-header">' +
                                        '<h6 class="card-title mb-0">' +
                                            '<i class="fas fa-user"></i> Información del Cliente' +
                                        '</h6>' +
                                    '</div>' +
                                    '<div class="card-body">' +
                                        '<table class="table table-sm">' +
                                            '<tr>' +
                                                '<td><strong>Cliente:</strong></td>' +
                                                '<td>' + envio.cliente_nombre + '</td>' +
                                            '</tr>' +
                                            '<tr>' +
                                                '<td><strong>Email:</strong></td>' +
                                                '<td>' + envio.cliente_email + '</td>' +
                                            '</tr>' +
                                            '<tr>' +
                                                '<td><strong>Pedido #:</strong></td>' +
                                                '<td><span class="badge bg-info">' + envio.pedido_numero + '</span></td>' +
                                            '</tr>' +
                                            '<tr>' +
                                                '<td><strong>Total Pedido:</strong></td>' +
                                                '<td class="text-success fw-bold">$' + parseFloat(envio.pedido_total).toLocaleString() + '</td>' +
                                            '</tr>' +
                                        '</table>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                        (envio.direccion_entrega ? 
                            '<div class="row mt-3">' +
                                '<div class="col-12">' +
                                    '<div class="card">' +
                                        '<div class="card-header">' +
                                            '<h6 class="card-title mb-0">' +
                                                '<i class="fas fa-map-marker-alt"></i> Dirección de Entrega' +
                                            '</h6>' +
                                        '</div>' +
                                        '<div class="card-body">' +
                                            '<p class="mb-0">' + envio.direccion_entrega + '</p>' +
                                        '</div>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' : '') +
                        (envio.observaciones ? 
                            '<div class="row mt-3">' +
                                '<div class="col-12">' +
                                    '<div class="card">' +
                                        '<div class="card-header">' +
                                            '<h6 class="card-title mb-0">' +
                                                '<i class="fas fa-sticky-note"></i> Observaciones' +
                                            '</h6>' +
                                        '</div>' +
                                        '<div class="card-body">' +
                                            '<p class="mb-0">' + envio.observaciones + '</p>' +
                                        '</div>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' : '') +
                        (envio.estado === 'entregado' && envio.comprobante_pdf ? 
                            '<div class="row mt-3">' +
                                '<div class="col-12">' +
                                    '<div class="alert alert-success">' +
                                        '<h6 class="alert-heading">' +
                                            '<i class="fas fa-check-circle"></i> Entrega Completada' +
                                        '</h6>' +
                                        '<p class="mb-2">Esta entrega fue completada exitosamente y se generó el comprobante de entrega.</p>' +
                                        '<hr>' +
                                        '<button type="button" class="btn btn-success btn-sm" onclick="verComprobante(\'' + envio.comprobante_pdf.replace(/'/g, "\\'") + '\')">' +
                                            '<i class="fas fa-file-pdf"></i> Ver Comprobante de Entrega' +
                                        '</button>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' : '') +
                    '</div>' +
                    '<div class="modal-footer">' +
                        (envio.estado === 'entregado' && envio.comprobante_pdf ? 
                            '<button type="button" class="btn btn-success" onclick="verComprobante(\'' + envio.comprobante_pdf.replace(/'/g, "\\'") + '\')">' +
                                '<i class="fas fa-file-pdf"></i> Ver Comprobante' +
                            '</button>' : 
                            '<button type="button" class="btn btn-outline-primary" onclick="imprimirGuia(' + envio.id + ')">' +
                                '<i class="fas fa-print"></i> Imprimir Guía' +
                            '</button>') +
                        '<button type="button" class="btn btn-outline-success" onclick="copiarNumeroGuia(\'' + envio.numero_guia.replace(/'/g, "\\'") + '\')">' +
                            '<i class="fas fa-copy"></i> Copiar Número' +
                        '</button>' +
                        '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' +
                            '<i class="fas fa-times"></i> Cerrar' +
                        '</button>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>';
    
    const modalAnterior = document.getElementById('modalDetallesEnvio');
    if (modalAnterior) {
        modalAnterior.remove();
    }
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    const modal = new bootstrap.Modal(document.getElementById('modalDetallesEnvio'));
    modal.show();
    
    document.getElementById('modalDetallesEnvio').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function getEstadoBadge(estado) {
    const estados = {
        'programado': '<span class="badge bg-secondary">Programado</span>',
        'en_preparacion': '<span class="badge bg-warning">En Preparación</span>',
        'en_transito': '<span class="badge bg-primary">En Tránsito</span>',
        'entregado': '<span class="badge bg-success">Entregado</span>',
        'fallido': '<span class="badge bg-danger">Fallido</span>',
        'devuelto': '<span class="badge bg-dark">Devuelto</span>'
    };
    return estados[estado] || '<span class="badge bg-secondary">' + (estado || 'Desconocido') + '</span>';
}

function formatearFecha(fecha) {
    if (!fecha) return 'No definida';
    const date = new Date(fecha);
    return date.toLocaleDateString('es-CO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function imprimirGuia(envioId) {
    const url = 'imprimir_guia.php?id=' + envioId;
    window.open(url, '_blank', 'width=800,height=600');
}

function copiarNumeroGuia(numeroGuia) {
    navigator.clipboard.writeText(numeroGuia).then(function() {
        const toast = document.createElement('div');
        toast.className = 'alert alert-success alert-dismissible position-fixed';
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 300px;';
        toast.innerHTML = 
            '<small><i class="fas fa-check"></i> Número de guía copiado: ' + numeroGuia + '</small>' +
            '<button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>';
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }).catch(function() {
        alert('Número de guía: ' + numeroGuia);
    });
}

document.getElementById("modalEnvio").addEventListener("hidden.bs.modal", function() {
    document.getElementById("formEnvio").reset();
    document.getElementById("modalEnvioTitle").textContent = "Nuevo Envío";
    document.getElementById("action").value = "crear";
    document.getElementById("envio_id").value = "";
    document.getElementById("pedido_id").disabled = false;
    document.getElementById("numero_guia").readOnly = true;
    document.getElementById("numero_guia").style.backgroundColor = "";
    document.getElementById("direccion_entrega_id").innerHTML = "<option value=''>Seleccione primero un pedido</option>";
    document.getElementById("direccion_entrega_id").disabled = false;
    document.getElementById("fecha_programada").value = new Date().toISOString().split("T")[0];
});
</script>
JAVASCRIPT;

// Renderizar la página
LayoutManager::renderAdminPage('Gestión de Envíos', $content, '', $additionalJS);
