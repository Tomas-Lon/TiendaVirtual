<?php
session_start();

// Verificar autenticación y cargo
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'empleado') {
    header('Location: ../index.php?err=not_empleado');
    exit();
}

$_SESSION['cargo'] = strtolower($_SESSION['cargo'] ?? '');
if ($_SESSION['cargo'] === 'admin') {
    header('Location: ../admin/dashboard.php');
    exit();
}
if ($_SESSION['cargo'] !== 'repartidor') {
    header('Location: ../index.php?err=no_repartidor');
    exit();
}

// Asegurar que el Layout use el menú de repartidor
$_SESSION['user_role'] = 'repartidor';

$user_name = $_SESSION['nombre'] ?? $_SESSION['usuario'] ?? 'Repartidor';

require_once '../includes/LayoutManager.php';
require_once '../config/database.php';
require_once '../config/paths.php';

$pdo = getConnection();
$empleado_id = $_SESSION['empleado_id'] ?? 0;

// Filtros
$filtro_estado = $_GET['estado'] ?? 'todas';
$busqueda = $_GET['q'] ?? '';
$fecha_desde = $_GET['fecha_desde'] ?? '';
$fecha_hasta = $_GET['fecha_hasta'] ?? '';

// Construir query base
$where_conditions = ['e.repartidor_id = ?'];
$params = [$empleado_id];

if ($filtro_estado !== 'todas' && in_array($filtro_estado, ['programado', 'en_preparacion', 'en_transito', 'entregado', 'fallido', 'devuelto'])) {
    $where_conditions[] = 'e.estado = ?';
    $params[] = $filtro_estado;
}

if ($busqueda) {
    $where_conditions[] = "(c.nombre LIKE ? OR p.numero_documento LIKE ? OR dir.direccion LIKE ?)";
    $busqueda_param = "%{$busqueda}%";
    $params[] = $busqueda_param;
    $params[] = $busqueda_param;
    $params[] = $busqueda_param;
}

if ($fecha_desde) {
    $where_conditions[] = 'DATE(e.fecha_programada) >= ?';
    $params[] = $fecha_desde;
}

if ($fecha_hasta) {
    $where_conditions[] = 'DATE(e.fecha_programada) <= ?';
    $params[] = $fecha_hasta;
}

$where_clause = implode(' AND ', $where_conditions);

// Obtener envios asignados con información del comprobante
$stmt = $pdo->prepare("
    SELECT e.id, e.estado, e.fecha_programada as fecha_entrega, e.fecha_entrega_real,
           dir.direccion as direccion_entrega, dir.ciudad,
           p.numero_documento, c.nombre AS cliente_nombre, c.telefono, c.email,
           ent.id as entrega_realizada_id,
           comp.pdf_path as comprobante_pdf,
           comp.codigo_qr as codigo_comprobante
    FROM envios e
    INNER JOIN pedidos p ON e.pedido_id = p.id
    INNER JOIN clientes c ON p.cliente_id = c.id
    LEFT JOIN direcciones_clientes dir ON e.direccion_entrega_id = dir.id
    LEFT JOIN entregas ent ON ent.envio_id = e.id
    LEFT JOIN comprobantes_entrega comp ON comp.entrega_id = ent.id
    WHERE {$where_clause}
    ORDER BY 
        CASE e.estado 
            WHEN 'programado' THEN 1
            WHEN 'en_preparacion' THEN 2
            WHEN 'en_transito' THEN 3
            ELSE 4
        END,
        e.fecha_programada ASC
");
$stmt->execute($params);
$entregas = $stmt->fetchAll();

// Contadores por estado
$stmt = $pdo->prepare("
    SELECT 
        SUM(CASE WHEN estado = 'programado' THEN 1 ELSE 0 END) as programados,
        SUM(CASE WHEN estado = 'en_transito' THEN 1 ELSE 0 END) as en_transito,
        SUM(CASE WHEN estado = 'entregado' THEN 1 ELSE 0 END) as entregados,
        SUM(CASE WHEN estado = 'fallido' THEN 1 ELSE 0 END) as fallidos
    FROM envios
    WHERE repartidor_id = ?
");
$stmt->execute([$empleado_id]);
$contadores = $stmt->fetch();

function getEstadoBadge(string $estado): string {
    $badges = [
        'programado' => 'bg-warning text-dark',
        'en_preparacion' => 'bg-info text-dark',
        'en_transito' => 'bg-primary',
        'entregado' => 'bg-success',
        'fallido' => 'bg-danger',
        'devuelto' => 'bg-secondary'
    ];
    return $badges[$estado] ?? 'bg-secondary';
}

function getEstadoTexto(string $estado): string {
    $textos = [
        'programado' => 'Programado',
        'en_preparacion' => 'En Preparación',
        'en_transito' => 'En Tránsito',
        'entregado' => 'Entregado',
        'fallido' => 'Fallido',
        'devuelto' => 'Devuelto'
    ];
    return $textos[$estado] ?? ucfirst(str_replace('_', ' ', $estado));
}

// Construir contenido
ob_start();
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Buscar</label>
                        <input type="text" name="q" class="form-control" placeholder="Cliente, pedido, dirección..." value="<?= htmlspecialchars($busqueda) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Desde</label>
                        <input type="date" name="fecha_desde" class="form-control" value="<?= htmlspecialchars($fecha_desde) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Hasta</label>
                        <input type="date" name="fecha_hasta" class="form-control" value="<?= htmlspecialchars($fecha_hasta) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select">
                            <option value="todas" <?= $filtro_estado === 'todas' ? 'selected' : '' ?>>Todas</option>
                            <option value="programado" <?= $filtro_estado === 'programado' ? 'selected' : '' ?>>Programados</option>
                            <option value="en_preparacion" <?= $filtro_estado === 'en_preparacion' ? 'selected' : '' ?>>En Preparación</option>
                            <option value="en_transito" <?= $filtro_estado === 'en_transito' ? 'selected' : '' ?>>En Tránsito</option>
                            <option value="entregado" <?= $filtro_estado === 'entregado' ? 'selected' : '' ?>>Entregados</option>
                            <option value="fallido" <?= $filtro_estado === 'fallido' ? 'selected' : '' ?>>Fallidos</option>
                            <option value="devuelto" <?= $filtro_estado === 'devuelto' ? 'selected' : '' ?>>Devueltos</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Programados</h6>
                        <h3 class="mb-0 text-warning"><?= number_format($contadores['programados']) ?></h3>
                    </div>
                    <div class="text-warning">
                        <i class="fas fa-clock fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">En Tránsito</h6>
                        <h3 class="mb-0 text-primary"><?= number_format($contadores['en_transito']) ?></h3>
                    </div>
                    <div class="text-primary">
                        <i class="fas fa-truck-fast fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Entregados</h6>
                        <h3 class="mb-0 text-success"><?= number_format($contadores['entregados']) ?></h3>
                    </div>
                    <div class="text-success">
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Fallidos</h6>
                        <h3 class="mb-0 text-danger"><?= number_format($contadores['fallidos']) ?></h3>
                    </div>
                    <div class="text-danger">
                        <i class="fas fa-exclamation-triangle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-truck me-2"></i>Mis Entregas (<?= count($entregas) ?>)</h5>
            <?php
                $exportUrl = 'ajax/export_entregas.php?fecha_desde=' . urlencode($fecha_desde) .
                             '&fecha_hasta=' . urlencode($fecha_hasta) .
                             '&estado=' . urlencode($filtro_estado) .
                             '&q=' . urlencode($busqueda);
            ?>
            <div class="d-flex gap-2">
                <a href="<?= $exportUrl ?>" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-file-csv"></i> Exportar CSV
                </a>
                <a href="dashboard.php" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Dashboard
                </a>
            </div>
        </div>
    <div class="card-body">
        <?php if (empty($entregas)): ?>
            <div class="text-center text-muted py-5">
                <i class="fas fa-inbox fa-3x mb-3"></i>
                <h5>No se encontraron entregas</h5>
                <p class="mb-0">Ajusta los filtros o verifica tu asignación de entregas.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Pedido</th>
                            <th>Cliente</th>
                            <th>Dirección</th>
                            <th>Fecha Entrega</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($entregas as $entrega): ?>
                        <tr>
                            <td><strong>#<?= $entrega['id'] ?></strong></td>
                            <td><?= htmlspecialchars($entrega['numero_documento'] ?: 'Sin pedido') ?></td>
                            <td>
                                <?= htmlspecialchars($entrega['cliente_nombre'] ?: 'No especificado') ?>
                                <?php if ($entrega['telefono']): ?>
                                    <br><small class="text-muted">
                                        <i class="fas fa-phone"></i> 
                                        <a href="tel:<?= htmlspecialchars($entrega['telefono']) ?>"><?= htmlspecialchars($entrega['telefono']) ?></a>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small><?= htmlspecialchars($entrega['direccion_entrega'] ?: 'No especificada') ?></small>
                                <?php if ($entrega['ciudad']): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($entrega['ciudad']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= date('d/m/Y', strtotime($entrega['fecha_entrega'])) ?>
                                <?php if ($entrega['fecha_entrega_real']): ?>
                                    <br><small class="text-success">Entregado: <?= date('d/m/Y H:i', strtotime($entrega['fecha_entrega_real'])) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= getEstadoBadge($entrega['estado']) ?>">
                                    <?= getEstadoTexto($entrega['estado']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <?php if ($entrega['estado'] === 'programado'): ?>
                                        <button class="btn btn-primary" onclick="iniciarEntrega(<?= $entrega['id'] ?>)" title="Iniciar entrega">
                                            <i class="fas fa-play"></i>
                                        </button>
                                        <a href="entrega.php?id=<?= $entrega['id'] ?>" class="btn btn-success" title="Completar entrega">
                                            <i class="fas fa-check"></i>
                                        </a>
                                        <button class="btn btn-warning" onclick="marcarFallida(<?= $entrega['id'] ?>)" title="Marcar fallida">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php elseif ($entrega['estado'] === 'en_transito'): ?>
                                        <a href="entrega.php?id=<?= $entrega['id'] ?>" class="btn btn-success" title="Completar entrega">
                                            <i class="fas fa-check-circle"></i>
                                        </a>
                                        <button class="btn btn-warning" onclick="marcarFallida(<?= $entrega['id'] ?>)" title="Marcar fallida">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php elseif ($entrega['estado'] === 'entregado'): ?>
                                        <?php if ($entrega['comprobante_pdf']): ?>
                                            <?php
                                            // Preparar datos para JavaScript con URL normalizada
                                            $entrega_data = $entrega;
                                            $entrega_data['comprobante_pdf_url'] = normalizar_url($entrega['comprobante_pdf']);
                                            ?>
                                            <button class="btn btn-success" onclick="verComprobante('<?= addslashes(normalizar_url($entrega['comprobante_pdf'])) ?>')" title="Ver comprobante">
                                                <i class="fas fa-file-pdf"></i> PDF
                                            </button>
                                        <?php else: ?>
                                            <?php
                                            $entrega_data = $entrega;
                                            $entrega_data['comprobante_pdf_url'] = '';
                                            ?>
                                            <button class="btn btn-outline-secondary" disabled title="Comprobante no disponible">
                                                <i class="fas fa-file"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn btn-info" onclick="verDetallesEntrega(<?= htmlspecialchars(json_encode($entrega_data), ENT_QUOTES) ?>)" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    <?php elseif ($entrega['estado'] === 'fallido'): ?>
                                        <button class="btn btn-secondary" onclick="verDetallesEntrega(<?= htmlspecialchars(json_encode($entrega), ENT_QUOTES) ?>)" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="entrega.php?id=<?= $entrega['id'] ?>" class="btn btn-warning" title="Reintentar entrega">
                                            <i class="fas fa-redo"></i>
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-info" onclick="verDetallesEntrega(<?= htmlspecialchars(json_encode($entrega), ENT_QUOTES) ?>)" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para marcar fallida -->
<div class="modal fade" id="modalFallida" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Marcar Entrega como Fallida</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="entrega_fallida_id">
                <div class="mb-3">
                    <label class="form-label">Motivo *</label>
                    <select class="form-select" id="motivo_fallida">
                        <option value="">Seleccionar...</option>
                        <option value="Cliente ausente">Cliente ausente</option>
                        <option value="Dirección incorrecta">Dirección incorrecta</option>
                        <option value="Cliente rechazó pedido">Cliente rechazó pedido</option>
                        <option value="Zona peligrosa">Zona peligrosa</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Observaciones</label>
                    <textarea class="form-control" id="observaciones_fallida" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmarFallida()">Marcar como Fallida</button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

$additionalJS = <<<JS
function iniciarEntrega(entregaId) {
    if (!confirm('¿Iniciar esta entrega? El estado cambiará a "En Tránsito".')) return;
    
    fetch('ajax/cambiar_estado.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({entrega_id: entregaId, estado: 'en_transito'})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✓ Entrega iniciada correctamente');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error de conexión: ' + error.message);
    });
}

function marcarFallida(entregaId) {
    document.getElementById('entrega_fallida_id').value = entregaId;
    document.getElementById('motivo_fallida').value = '';
    document.getElementById('observaciones_fallida').value = '';
    new bootstrap.Modal(document.getElementById('modalFallida')).show();
}

function confirmarFallida() {
    const entregaId = document.getElementById('entrega_fallida_id').value;
    const motivo = document.getElementById('motivo_fallida').value;
    const observaciones = document.getElementById('observaciones_fallida').value;
    
    if (!motivo) {
        alert('Selecciona un motivo');
        return;
    }
    
    fetch('ajax/cambiar_estado.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            entrega_id: entregaId,
            estado: 'fallido',
            motivo: motivo,
            observaciones: observaciones
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✓ Entrega marcada como fallida');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error de conexión: ' + error.message);
    });
}

function verComprobante(pdfPath) {
    if (!pdfPath) {
        alert('Comprobante no disponible');
        return;
    }
    // La URL ya viene normalizada desde PHP
    window.open(pdfPath, '_blank');
}

function verDetallesEntrega(entrega) {
    const estadoTextos = {
        'programado': 'Programado',
        'en_preparacion': 'En Preparación',
        'en_transito': 'En Tránsito',
        'entregado': 'Entregado',
        'fallido': 'Fallido',
        'devuelto': 'Devuelto'
    };
    
    const estadoBadges = {
        'programado': 'badge bg-warning text-dark',
        'en_preparacion': 'badge bg-info text-dark',
        'en_transito': 'badge bg-primary',
        'entregado': 'badge bg-success',
        'fallido': 'badge bg-danger',
        'devuelto': 'badge bg-secondary'
    };
    
    let htmlDetalles = '<div class="row">' +
        '<div class="col-md-6">' +
            '<h6 class="fw-bold mb-3">Información de Entrega</h6>' +
            '<table class="table table-sm table-borderless">' +
                '<tr>' +
                    '<td class="text-muted" style="width: 40%;">ID:</td>' +
                    '<td><strong>#' + entrega.id + '</strong></td>' +
                '</tr>' +
                '<tr>' +
                    '<td class="text-muted">Pedido:</td>' +
                    '<td>' + (entrega.numero_documento ? entrega.numero_documento : 'N/A') + '</td>' +
                '</tr>' +
                '<tr>' +
                    '<td class="text-muted">Estado:</td>' +
                    '<td><span class="' + (estadoBadges[entrega.estado] ? estadoBadges[entrega.estado] : 'badge bg-secondary') + '">' + (estadoTextos[entrega.estado] ? estadoTextos[entrega.estado] : entrega.estado) + '</span></td>' +
                '</tr>' +
                '<tr>' +
                    '<td class="text-muted">Fecha Programada:</td>' +
                    '<td>' + new Date(entrega.fecha_entrega).toLocaleDateString('es-CO') + '</td>' +
                '</tr>' +
                (entrega.fecha_entrega_real ? 
                    '<tr>' +
                        '<td class="text-muted">Fecha Entregado:</td>' +
                        '<td class="text-success fw-bold">' + new Date(entrega.fecha_entrega_real).toLocaleString('es-CO') + '</td>' +
                    '</tr>' 
                : '') +
            '</table>' +
        '</div>' +
        '<div class="col-md-6">' +
            '<h6 class="fw-bold mb-3">Información del Cliente</h6>' +
            '<table class="table table-sm table-borderless">' +
                '<tr>' +
                    '<td class="text-muted" style="width: 40%;">Cliente:</td>' +
                    '<td><strong>' + (entrega.cliente_nombre ? entrega.cliente_nombre : 'N/A') + '</strong></td>' +
                '</tr>' +
                (entrega.telefono ? 
                    '<tr>' +
                        '<td class="text-muted">Teléfono:</td>' +
                        '<td><a href="tel:' + entrega.telefono + '"><i class="fas fa-phone"></i> ' + entrega.telefono + '</a></td>' +
                    '</tr>' 
                : '') +
                (entrega.email ? 
                    '<tr>' +
                        '<td class="text-muted">Email:</td>' +
                        '<td><a href="mailto:' + entrega.email + '">' + entrega.email + '</a></td>' +
                    '</tr>' 
                : '') +
                '<tr>' +
                    '<td class="text-muted">Dirección:</td>' +
                    '<td>' + (entrega.direccion_entrega ? entrega.direccion_entrega : 'N/A') + '</td>' +
                '</tr>' +
                (entrega.ciudad ? 
                    '<tr>' +
                        '<td class="text-muted">Ciudad:</td>' +
                        '<td>' + entrega.ciudad + '</td>' +
                    '</tr>' 
                : '') +
            '</table>' +
        '</div>' +
    '</div>';
    
    if (entrega.estado === 'entregado' && entrega.comprobante_pdf) {
        htmlDetalles += '<div class="alert alert-success mt-3">' +
            '<h6 class="alert-heading"><i class="fas fa-check-circle"></i> Entrega Completada</h6>' +
            '<p class="mb-2">Esta entrega se completó exitosamente y se generó el comprobante.</p>' +
            (entrega.codigo_comprobante ? '<p class="mb-2"><strong>Código:</strong> ' + entrega.codigo_comprobante + '</p>' : '') +
            '<button type="button" class="btn btn-success btn-sm" onclick="verComprobante(\'' + entrega.comprobante_pdf_url.replace(/'/g, "\\'") + '\'); bootstrap.Modal.getInstance(document.getElementById(\'modalDetallesEntrega\')).hide();">' +
                '<i class="fas fa-file-pdf"></i> Ver Comprobante PDF' +
            '</button>' +
        '</div>';
    }
    
    const modalHTML = '<div class="modal fade" id="modalDetallesEntrega" tabindex="-1">' +
        '<div class="modal-dialog modal-lg">' +
            '<div class="modal-content">' +
                '<div class="modal-header">' +
                    '<h5 class="modal-title"><i class="fas fa-info-circle"></i> Detalles de la Entrega #' + entrega.id + '</h5>' +
                    '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>' +
                '</div>' +
                '<div class="modal-body">' +
                    htmlDetalles +
                '</div>' +
                '<div class="modal-footer">' +
                    '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>' +
                '</div>' +
            '</div>' +
        '</div>' +
    '</div>';
    
    // Remover modal previo si existe
    const modalPrevio = document.getElementById('modalDetallesEntrega');
    if (modalPrevio) {
        modalPrevio.remove();
    }
    
    // Agregar modal al DOM
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('modalDetallesEntrega'));
    modal.show();
    
    // Eliminar modal del DOM cuando se cierre
    document.getElementById('modalDetallesEntrega').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}
JS;

// Agregar el JavaScript al contenido
$content .= "\n<script>\n" . $additionalJS . "\n</script>";

LayoutManager::renderAdminPage('Mis Entregas', $content, '');
?>
