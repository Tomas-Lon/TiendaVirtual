<?php
session_start();

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../includes/LayoutManager.php';
require_once '../config/database.php';
require_once '../config/paths.php';
require_once '../includes/PermissionManager.php';

$pdo = getConnection();
$mensaje = '';
$tipo_mensaje = '';

/**
 * Valida y sanitiza los datos del pedido
 */
function validatePedidoData($data) {
    $errors = [];
    
    if (empty($data['cliente_id']) || !is_numeric($data['cliente_id'])) {
        $errors[] = 'Debe seleccionar un cliente válido';
    }
    
    if (empty($data['fecha_pedido'])) {
        $errors[] = 'La fecha del pedido es obligatoria';
    }
    
    if (empty($data['estado'])) {
        $errors[] = 'El estado del pedido es obligatorio';
    }
    
    if (!isset($data['total']) || floatval($data['total']) < 0) {
        $errors[] = 'El total del pedido debe ser mayor o igual a 0';
    }
    
    return $errors;
}

// Manejar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'cambiar_estado':
            try {
                // Determinar qué estados puede imponer el usuario
                if (PermissionManager::hasPermission('admin')) {
                    $allowedTargets = ['confirmado','listo_envio','enviado'];
                } elseif (PermissionManager::hasPermission('repartidor')) {
                    $allowedTargets = ['entregado','devuelto'];
                } else {
                    $mensaje = 'No autorizado para cambiar estados';
                    $tipo_mensaje = 'danger';
                    break;
                }
                if (empty($_POST['id']) || !is_numeric($_POST['id'])) {
                    $mensaje = 'ID de pedido inválido';
                    $tipo_mensaje = 'danger';
                    break;
                }
                
                if (empty($_POST['estado'])) {
                    $mensaje = 'El estado es obligatorio';
                    $tipo_mensaje = 'danger';
                    break;
                }

                // Validar que el estado objetivo esté dentro de los permitidos para este usuario
                $targetState = trim($_POST['estado']);
                if (!in_array($targetState, $allowedTargets, true)) {
                    $mensaje = 'No autorizado para cambiar al estado solicitado';
                    $tipo_mensaje = 'danger';
                    break;
                }
                
                $pedidoId = intval($_POST['id']);
                $estado = trim($_POST['estado']);

                // Obtener estado anterior
                $select = $pdo->prepare("SELECT estado FROM pedidos WHERE id = ?");
                $select->execute([$pedidoId]);
                $estadoAnterior = $select->fetchColumn();

                if ($estadoAnterior === false) {
                    $mensaje = 'Pedido no encontrado';
                    $tipo_mensaje = 'danger';
                    break;
                }

                // Actualizar estado dentro de una transacción y registrar historial
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("UPDATE pedidos SET estado = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$estado, $pedidoId]);

                // Insertar registro en pedido_historial si la tabla existe (no interromper flujo si falla)
                $usuario_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
                $usuario_tipo = PermissionManager::hasPermission('admin') ? 'admin' : (PermissionManager::hasPermission('repartidor') ? 'repartidor' : 'usuario');
                $motivo = !empty($_POST['motivo']) ? trim($_POST['motivo']) : null;
                try {
                    $hist = $pdo->prepare("INSERT INTO pedido_historial (pedido_id, usuario_id, usuario_tipo, estado_anterior, estado_nuevo, motivo, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                    $hist->execute([$pedidoId, $usuario_id, $usuario_tipo, $estadoAnterior, $estado, $motivo]);
                } catch (PDOException $e) {
                    error_log("No se pudo insertar en pedido_historial: " . $e->getMessage());
                    // continuar sin fallar la operación principal
                }

                $pdo->commit();

                $mensaje = "Estado del pedido actualizado exitosamente";
                $tipo_mensaje = "success";
            } catch (PDOException $e) {
                error_log("Error al actualizar estado de pedido: " . $e->getMessage());
                $mensaje = "Error al actualizar estado. Por favor, inténtelo de nuevo.";
                $tipo_mensaje = "danger";
            }
            break;
            
        case 'create':
            try {
                $validationErrors = validatePedidoData($_POST);
                
                if (!empty($validationErrors)) {
                    $mensaje = 'Errores de validación: ' . implode(', ', $validationErrors);
                    $tipo_mensaje = 'danger';
                    break;
                }
                
                $pdo->beginTransaction();
                
                // Crear pedido
                $stmt = $pdo->prepare("INSERT INTO pedidos (numero_documento, cliente_id, fecha_pedido, estado, total) VALUES (?, ?, ?, ?, ?)");
                $numero_documento = 'PED' . date('YmdHis') . rand(100, 999);
                $stmt->execute([
                    $numero_documento,
                    $_POST['cliente_id'],
                    $_POST['fecha_pedido'],
                    'borrador',
                    0 // Se calculará después
                ]);
                
                $pedido_id = $pdo->lastInsertId();
                
                // Agregar productos si se especificaron
                if (!empty($_POST['productos'])) {
                    $total = 0;
                    foreach ($_POST['productos'] as $producto_data) {
                        $stmt = $pdo->prepare("INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
                        $stmt->execute([
                            $pedido_id,
                            $producto_data['producto_id'],
                            $producto_data['cantidad'],
                            $producto_data['precio']
                        ]);
                        $total += $producto_data['cantidad'] * $producto_data['precio'];
                    }
                    
                    // Actualizar total del pedido
                    $stmt = $pdo->prepare("UPDATE pedidos SET total = ? WHERE id = ?");
                    $stmt->execute([$total, $pedido_id]);
                }
                
                $pdo->commit();
                $mensaje = "Pedido creado exitosamente con número: $numero_documento";
                $tipo_mensaje = "success";
            } catch (PDOException $e) {
                $pdo->rollBack();
                $mensaje = "Error al crear pedido: " . $e->getMessage();
                $tipo_mensaje = "danger";
            }
            break;
    }
}

// Filtros y paginación
$search = $_GET['search'] ?? '';
$estado_filter = $_GET['estado'] ?? '';
$cliente_filter = $_GET['cliente'] ?? '';
$fecha_desde = $_GET['fecha_desde'] ?? '';
$fecha_hasta = $_GET['fecha_hasta'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Construir WHERE clause
$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(p.numero_documento LIKE ? OR c.nombre LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($estado_filter) {
    $where_conditions[] = "p.estado = ?";
    $params[] = $estado_filter;
}

if ($cliente_filter) {
    $where_conditions[] = "p.cliente_id = ?";
    $params[] = $cliente_filter;
}

if ($fecha_desde) {
    $where_conditions[] = "p.fecha_pedido >= ?";
    $params[] = $fecha_desde;
}

if ($fecha_hasta) {
    $where_conditions[] = "p.fecha_pedido <= ?";
    $params[] = $fecha_hasta;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Contar total de pedidos
$count_sql = "SELECT COUNT(*) as total FROM pedidos p JOIN clientes c ON p.cliente_id = c.id $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_pedidos = $count_stmt->fetch()['total'];
$total_pages = ceil($total_pedidos / $limit);

// Obtener pedidos paginados con información de comprobante de entrega
$sql = "SELECT p.*, c.nombre as cliente_nombre, c.email as cliente_email,
        comp.pdf_path as comprobante_pdf, comp.codigo_qr as codigo_comprobante
        FROM pedidos p 
        JOIN clientes c ON p.cliente_id = c.id 
        LEFT JOIN entregas ent ON ent.pedido_id = p.id
        LEFT JOIN comprobantes_entrega comp ON comp.entrega_id = ent.id
        $where_clause 
        ORDER BY p.created_at DESC 
        LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pedidos = $stmt->fetchAll();

// Obtener clientes para el filtro
$clientes_stmt = $pdo->query("SELECT id, nombre FROM clientes WHERE activo = 1 ORDER BY nombre");
$clientes = $clientes_stmt->fetchAll();

// Estados disponibles
$estados = [
    'borrador' => 'Borrador',
    'confirmado' => 'Confirmado',
    'en_preparacion' => 'En Preparación',
    'listo_envio' => 'Listo para Envío',
    'enviado' => 'Enviado',
    'entregado' => 'Entregado',
    'cancelado' => 'Cancelado'
];

// Estados permitidos por tipo de usuario (policy)
$allowedForUser = [];
if (PermissionManager::hasPermission('admin')) {
    // Admin puede mover a estos estados (confirmado, listo para envio, enviado)
    $allowedForUser = ['confirmado','listo_envio','enviado'];
} elseif (PermissionManager::hasPermission('repartidor')) {
    // Repartidor solo marca entregado o devuelto
    $allowedForUser = ['entregado','devuelto'];
} else {
    // Otros roles no pueden cambiar estados desde este panel
    $allowedForUser = [];
}

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
    <div>
        <h2 class="mb-1">Gestión de Pedidos</h2>
        <p class="text-muted mb-0">Administra y controla todos los pedidos del sistema</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary" onclick="openExportModal()">
            <i class="fas fa-print"></i> Exportar / Imprimir
        </button>
        <a href="nueva_compra.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Pedido
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Número de pedido o cliente">
            </div>
            <div class="col-md-2">
                <label for="estado" class="form-label">Estado</label>
                <select class="form-select" id="estado" name="estado">
                    <option value="">Todos los estados</option>
                    <?php foreach ($estados as $valor => $texto): ?>
                        <option value="<?php echo $valor; ?>" 
                                <?php echo $estado_filter === $valor ? 'selected' : ''; ?>>
                            <?php echo $texto; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="cliente" class="form-label">Cliente</label>
                <select class="form-select" id="cliente" name="cliente">
                    <option value="">Todos los clientes</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?php echo $cliente['id']; ?>" 
                                <?php echo $cliente_filter == $cliente['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cliente['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="fecha_desde" class="form-label">Desde</label>
                <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" 
                       value="<?php echo htmlspecialchars($fecha_desde); ?>">
            </div>
            <div class="col-md-2">
                <label for="fecha_hasta" class="form-label">Hasta</label>
                <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" 
                       value="<?php echo htmlspecialchars($fecha_hasta); ?>">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de pedidos -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pedidos)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <p class="text-muted">No hay pedidos disponibles</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pedidos as $pedido): ?>
                        <?php
                        $estado_color = [
                            'borrador' => 'secondary',
                            'confirmado' => 'info',
                            'en_preparacion' => 'warning',
                            'listo_envio' => 'primary',
                            'enviado' => 'success',
                            'entregado' => 'success',
                            'cancelado' => 'danger'
                        ];
                        ?>
                        <tr>
                            <td>
                                <strong>#<?php echo htmlspecialchars($pedido['numero_documento']); ?></strong><br>
                                <small class="text-muted">ID: <?php echo $pedido['id']; ?></small>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($pedido['cliente_nombre']); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars($pedido['cliente_email']); ?></small>
                            </td>
                            <td>
                                <?php echo date('d/m/Y', strtotime($pedido['fecha_pedido'])); ?><br>
                                <small class="text-muted"><?php echo date('H:i', strtotime($pedido['fecha_pedido'])); ?></small>
                            </td>
                            <td><span class="badge bg-<?php echo $estado_color[$pedido['estado']]; ?>"><?php echo $estados[$pedido['estado']]; ?></span></td>
                            <td>
                                <strong>$<?php echo number_format($pedido['total'], 2); ?></strong>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            onclick="verDetalle(<?php echo $pedido['id']; ?>)" title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-warning" 
                                            onclick="cambiarEstado(<?php echo $pedido['id']; ?>, '<?php echo $pedido['estado']; ?>')" title="Cambiar estado">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if (in_array($pedido['estado'], ['confirmado', 'en_preparacion', 'listo_envio'])): ?>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" 
                                            onclick="crearEnvio(<?php echo $pedido['id']; ?>, '<?php echo htmlspecialchars($pedido['numero_documento'], ENT_QUOTES); ?>')" 
                                            title="Crear envío">
                                        <i class="fas fa-truck"></i>
                                    </button>
                                    <?php endif; ?>
                                    <?php if ($pedido['estado'] === 'entregado' && !empty($pedido['comprobante_pdf'])): ?>
                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                            onclick="window.open('<?php echo htmlspecialchars(normalizar_url($pedido['comprobante_pdf'])); ?>', '_blank')" 
                                            title="Ver comprobante de entrega">
                                        <i class="fas fa-file-pdf"></i>
                                    </button>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-sm btn-outline-success" 
                                            onclick="imprimirPedido(<?php echo $pedido['id']; ?>)" title="Imprimir">
                                        <i class="fas fa-print"></i>
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
        <nav aria-label="Paginación">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&estado=<?php echo urlencode($estado_filter); ?>&cliente=<?php echo urlencode($cliente_filter); ?>&fecha_desde=<?php echo urlencode($fecha_desde); ?>&fecha_hasta=<?php echo urlencode($fecha_hasta); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para cambiar estado -->
<div class="modal fade" id="estadoModal" tabindex="-1" aria-labelledby="estadoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="estadoModalLabel">Cambiar Estado del Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="estadoForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="cambiar_estado">
                    <input type="hidden" name="id" id="pedidoId">
                    
                    <div class="mb-3">
                        <label for="nuevoEstado" class="form-label">Nuevo Estado</label>
                        <select class="form-select" id="nuevoEstado" name="estado" required>
                            <?php foreach ($estados as $valor => $texto): ?>
                                <?php $disabled = !in_array($valor, $allowedForUser, true) ? 'disabled' : ''; ?>
                                <option value="<?php echo $valor; ?>" <?php echo $disabled; ?>><?php echo $texto; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="alert alert-info">
                        <small><i class="fas fa-info-circle"></i> Este cambio afectará el seguimiento del pedido.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Cambiar Estado</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para ver detalle -->
<div class="modal fade" id="detalleModal" tabindex="-1" aria-labelledby="detalleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detalleModalLabel">Detalle del Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="detalleContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                </div>
            </div>

                        
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// JavaScript adicional
$additionalJS = <<<'JS'
<script>
function cambiarEstado(id, estadoActual) {
    document.getElementById("pedidoId").value = id;
    document.getElementById("nuevoEstado").value = estadoActual;
    new bootstrap.Modal(document.getElementById("estadoModal")).show();
}

function verDetalle(id) {
    document.getElementById("detalleContent").innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>
    `;
    
    new bootstrap.Modal(document.getElementById("detalleModal")).show();
    
    // Cargar detalle via AJAX (usa endpoint admin/ajax/pedido_detalle.php)
    fetch(`ajax/pedido_detalle.php?id=${id}`, { credentials: 'same-origin' })
        .then(response => {
            if (!response.ok) throw new Error('HTTP ' + response.status);
            return response.text();
        })
        .then(html => {
            document.getElementById("detalleContent").innerHTML = html;
        })
        .catch(error => {
            console.error('Error cargando detalle:', error);
            document.getElementById("detalleContent").innerHTML = `
                <div class="alert alert-warning">
                    No se pudo cargar el detalle del pedido. Verifique que el pedido exista o consulte el registro de errores.
                </div>
            `;
        });
}

function imprimirListaPedidos() {
    const printWindow = window.open("", "_blank");
    const fecha = new Date().toLocaleDateString("es-CO");
    
    let html = `
        <html>
        <head>
            <title>Lista de Pedidos - SolTecnInd</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; font-size: 12px; }
                .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f5f5f5; font-weight: bold; font-size: 11px; }
                .text-end { text-align: right; }
                @media print {
                    body { margin: 10px; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>SolTecnInd</h1>
                <h2>Lista de Pedidos</h2>
                <p>Generado el: ${fecha}</p>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>`;
    
    // Obtener datos de la tabla
    const rows = document.querySelectorAll("tbody tr");
    let totalRegistros = 0;
    
    rows.forEach(row => {
        const cells = row.querySelectorAll("td");
        if (cells.length >= 5) {
            const numeroText = cells[0].querySelector('strong')?.textContent.trim() || cells[0].textContent.trim();
            const clienteText = cells[1].querySelector('strong')?.textContent.trim() || cells[1].textContent.trim();
            const fechaText = cells[2].textContent.trim().split('\n')[0].trim();
            const estadoText = cells[3].querySelector('.badge')?.textContent.trim() || cells[3].textContent.trim();
            const totalText = cells[4].querySelector('strong')?.textContent.trim() || cells[4].textContent.trim();
            
            if (numeroText && numeroText !== "No hay pedidos disponibles") {
                html += `
                    <tr>
                        <td>${numeroText}</td>
                        <td>${clienteText}</td>
                        <td>${fechaText}</td>
                        <td>${estadoText}</td>
                        <td class="text-end">${totalText}</td>
                    </tr>`;
                totalRegistros++;
            }
        }
    });
    
    if (totalRegistros === 0) {
        html += `
            <tr>
                <td colspan="5" style="text-align: center;">No hay pedidos para mostrar</td>
            </tr>`;
    }
    
    html += `
                </tbody>
            </table>
            <div style="margin-top: 30px; font-size: 10px; color: #666;">
                <p><strong>Total de pedidos en esta página:</strong> ${totalRegistros}</p>
            </div>
        </body>
        </html>`;
    
    printWindow.document.write(html);
    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => printWindow.print(), 250);
}

function imprimirPedido(id) {
    window.open(`reportes/pedido_print.php?id=${id}`, "_blank");
}

function crearEnvio(pedidoId, numeroPedido) {
    // Guardar en sessionStorage para que envios.php pueda acceder
    sessionStorage.setItem('pedido_crear_envio', JSON.stringify({
        pedido_id: pedidoId,
        numero_pedido: numeroPedido,
        timestamp: Date.now()
    }));
    
    // Redirigir a envios.php
    window.location.href = 'envios.php?crear_envio=1&pedido_id=' + pedidoId;
}
</script>
JS;

$additionalJS = preg_replace('/<\/script>$/', "\nfunction openExportModal() { new bootstrap.Modal(document.getElementById('exportModal')).show(); }\nfunction exportCSV() {\n    const desde = document.getElementById('exp_fecha_desde').value;\n    const hasta = document.getElementById('exp_fecha_hasta').value;\n    const limit = document.getElementById('exp_limit').value;\n    const estado = document.getElementById('exp_estado').value;\n    let url = 'ajax/export_pedidos.php?';\n    const params = new URLSearchParams();\n    if (desde) params.set('fecha_desde', desde);\n    if (hasta) params.set('fecha_hasta', hasta);\n    if (estado) params.set('estado', estado);\n    if (limit) params.set('limit', limit);\n    url += params.toString();\n    window.open(url, '_blank');\n}\nfunction exportPDF() {\n    const desde = document.getElementById('exp_fecha_desde').value;\n    const hasta = document.getElementById('exp_fecha_hasta').value;\n    const limit = document.getElementById('exp_limit').value;\n    const estado = document.getElementById('exp_estado').value;\n    let url = 'reportes/pedidos_report.php?';\n    const params = new URLSearchParams();\n    if (desde) params.set('fecha_desde', desde);\n    if (hasta) params.set('fecha_hasta', hasta);\n    if (estado) params.set('estado', estado);\n    if (limit) params.set('limit', limit);\n    url += params.toString();\n    window.open(url, '_blank');\n}\n</script>", $additionalJS, 1);

// Insert export modal at page level (outside other modals)
// Build estado options dynamically so PHP variables are rendered correctly
$estadoOptions = '';
foreach ($estados as $valor => $texto) {
    $estadoOptions .= '<option value="' . htmlspecialchars($valor, ENT_QUOTES) . '">' . htmlspecialchars($texto) . '</option>';
}

$content .= '<!-- Modal para exportar/imprimir lista similar a Reportes -->'
    . '<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">'
    . '<div class="modal-dialog">'
    . '<div class="modal-content">'
    . '<div class="modal-header">'
    . '<h5 class="modal-title" id="exportModalLabel">Exportar / Imprimir Pedidos</h5>'
    . '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>'
    . '</div>'
    . '<form id="exportForm" method="GET" target="_blank">'
    . '<div class="modal-body">'
    . '<div class="row g-3">'
    . '<div class="col-md-6">'
    . '<label for="exp_fecha_desde" class="form-label">Fecha desde</label>'
    . '<input type="date" id="exp_fecha_desde" name="fecha_desde" class="form-control">'
    . '</div>'
    . '<div class="col-md-6">'
    . '<label for="exp_fecha_hasta" class="form-label">Fecha hasta</label>'
    . '<input type="date" id="exp_fecha_hasta" name="fecha_hasta" class="form-control">'
    . '</div>'
    . '<div class="col-md-6">'
    . '<label for="exp_limit" class="form-label">Cantidad (líneas)</label>'
    . '<select id="exp_limit" name="limit" class="form-select">'
    . '<option value="">Sin límite</option>'
    . '<option value="10">10</option>'
    . '<option value="25">25</option>'
    . '<option value="50">50</option>'
    . '<option value="100">100</option>'
    . '</select>'
    . '</div>'
    . '<div class="col-md-6">'
    . '<label for="exp_estado" class="form-label">Estado</label>'
    . '<select id="exp_estado" name="estado" class="form-select">'
    . '<option value="">Todos</option>'
    . $estadoOptions
    . '</select>'
    . '</div>'
    . '</div>'
    . '<div class="mt-3">'
    . '<small class="text-muted">Elija CSV para obtener un archivo delimitado para Excel, o PDF para una vista imprimible.</small>'
    . '</div>'
    . '</div>'
    . '<div class="modal-footer">'
    . '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>'
    . '<button type="button" class="btn btn-outline-primary" onclick="exportCSV()">Exportar CSV</button>'
    . '<button type="button" class="btn btn-primary" onclick="exportPDF()">Exportar PDF</button>'
    . '</div>'
    . '</form>'
    . '</div>'
    . '</div>'
    . '</div>';

// Renderizar la página
LayoutManager::renderAdminPage('Gestión de Pedidos', $content, '', $additionalJS);
?>
