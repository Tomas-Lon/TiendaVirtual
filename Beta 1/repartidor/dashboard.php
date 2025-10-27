<?php
session_start();

// Verificar que el usuario esté logueado y sea funcionario (repartidor)
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'empleado') {
    header('Location: ../index.php');
    exit();
}

// Verificar que no sea admin (los admin tienen su propio dashboard)
if ($_SESSION['cargo'] === 'admin') {
    header('Location: ../admin/dashboard.php');
    exit();
}

require_once '../config/database.php';

try {
    $pdo = getConnection();
    $empleado_id = $_SESSION['empleado_id'];
    
    // Obtener entregas asignadas al repartidor
    $stmt = $pdo->prepare("
        SELECT e.*, p.numero_pedido, c.nombre as cliente_nombre, c.telefono
        FROM entregas e
        LEFT JOIN pedidos p ON e.pedido_id = p.id
        LEFT JOIN facturas f ON e.factura_id = f.id
        LEFT JOIN clientes c ON p.cliente_id = c.id OR f.cliente_id = c.id
        WHERE e.repartidor_id = ?
        ORDER BY e.fecha_entrega ASC, e.created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$empleado_id]);
    $entregas = $stmt->fetchAll();
    
    // Obtener estadísticas
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as entregas_programadas 
        FROM entregas 
        WHERE repartidor_id = ? AND estado = 'programada'
    ");
    $stmt->execute([$empleado_id]);
    $entregas_programadas = $stmt->fetch()['entregas_programadas'];
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as entregas_en_transito 
        FROM entregas 
        WHERE repartidor_id = ? AND estado = 'en_transito'
    ");
    $stmt->execute([$empleado_id]);
    $entregas_en_transito = $stmt->fetch()['entregas_en_transito'];
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as entregas_hoy 
        FROM entregas 
        WHERE repartidor_id = ? AND DATE(fecha_entrega) = CURDATE()
    ");
    $stmt->execute([$empleado_id]);
    $entregas_hoy = $stmt->fetch()['entregas_hoy'];
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as entregas_completadas_mes 
        FROM entregas 
        WHERE repartidor_id = ? AND estado = 'entregada' 
        AND MONTH(fecha_entrega_real) = MONTH(CURRENT_DATE())
        AND YEAR(fecha_entrega_real) = YEAR(CURRENT_DATE())
    ");
    $stmt->execute([$empleado_id]);
    $entregas_mes = $stmt->fetch()['entregas_completadas_mes'];
    
} catch (PDOException $e) {
    error_log("Error en dashboard repartidor: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Repartidor - SolTecnInd</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #27ae60;
        }
        .sidebar .nav-link {
            color: #ecf0f1;
            border-radius: 5px;
            margin: 2px 0;
        }
        .sidebar .nav-link:hover {
            background: #2ecc71;
            color: white;
        }
        .sidebar .nav-link.active {
            background: #e67e22;
            color: white;
        }
        .main-content {
            background: #f8f9fa;
            min-height: 100vh;
        }
        .stat-card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }
        .delivery-card {
            border-left: 4px solid #27ae60;
            margin-bottom: 1rem;
            transition: transform 0.3s;
        }
        .delivery-card:hover {
            transform: translateX(5px);
        }
        .delivery-card.en_transito {
            border-left-color: #3498db;
        }
        .delivery-card.programada {
            border-left-color: #f39c12;
        }
        .delivery-card.entregada {
            border-left-color: #2ecc71;
        }
        .delivery-card.fallida {
            border-left-color: #e74c3c;
        }
        .navbar-brand {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-3">
                <div class="text-center mb-4">
                    <h4 class="text-white"><i class="fas fa-truck"></i> SolTecnInd</h4>
                    <small class="text-light">Panel Repartidor</small>
                </div>
                
                <nav class="nav flex-column">
                    <a class="nav-link active" href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a class="nav-link" href="entregas.php">
                        <i class="fas fa-list"></i> Mis Entregas
                    </a>
                    <a class="nav-link" href="ruta.php">
                        <i class="fas fa-route"></i> Planificar Ruta
                    </a>
                    <a class="nav-link" href="historial.php">
                        <i class="fas fa-history"></i> Historial
                    </a>
                    <a class="nav-link" href="perfil.php">
                        <i class="fas fa-user-circle"></i> Mi Perfil
                    </a>
                    <hr class="text-light">
                    <a class="nav-link" href="../auth/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                    </a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <!-- Top Navbar -->
                <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
                    <div class="container-fluid">
                        <span class="navbar-brand mb-0 h1">Panel de Entregas</span>
                        <div class="navbar-nav ms-auto">
                            <div class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['nombre']); ?>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="perfil.php">Mi Perfil</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="../auth/logout.php">Cerrar Sesión</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>
                
                <!-- Content -->
                <div class="container-fluid px-4">
                    <!-- Welcome Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-success d-flex align-items-center" role="alert">
                                <i class="fas fa-truck fa-2x me-3"></i>
                                <div>
                                    <h5 class="alert-heading">¡Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?>!</h5>
                                    <p class="mb-0">Gestiona tus entregas y mantén informados a tus clientes sobre el estado de sus pedidos.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3><?php echo number_format($entregas_programadas); ?></h3>
                                            <p class="mb-0">Programadas</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-clock fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3><?php echo number_format($entregas_en_transito); ?></h3>
                                            <p class="mb-0">En Tránsito</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-shipping-fast fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3><?php echo number_format($entregas_hoy); ?></h3>
                                            <p class="mb-0">Para Hoy</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-calendar-day fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3><?php echo number_format($entregas_mes); ?></h3>
                                            <p class="mb-0">Este Mes</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-check-circle fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Lista de Entregas -->
                        <div class="col-md-8 mb-4">
                            <div class="card">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-list"></i> Entregas Asignadas
                                    </h5>
                                    <button class="btn btn-success btn-sm" onclick="actualizarEntregas()">
                                        <i class="fas fa-sync-alt"></i> Actualizar
                                    </button>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($entregas)): ?>
                                        <div class="text-center text-muted py-5">
                                            <i class="fas fa-inbox fa-3x mb-3"></i>
                                            <h5>No tienes entregas asignadas</h5>
                                            <p>Cuando te asignen entregas, aparecerán aquí.</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($entregas as $entrega): ?>
                                            <div class="card delivery-card <?php echo $entrega['estado']; ?>">
                                                <div class="card-body">
                                                    <div class="row align-items-center">
                                                        <div class="col-md-8">
                                                            <h6 class="mb-1">
                                                                <i class="fas fa-box"></i> 
                                                                <?php echo htmlspecialchars($entrega['numero_pedido'] ?: 'Factura #' . $entrega['id']); ?>
                                                            </h6>
                                                            <p class="mb-1">
                                                                <strong>Cliente:</strong> <?php echo htmlspecialchars($entrega['cliente_nombre'] ?: 'No especificado'); ?>
                                                            </p>
                                                            <p class="mb-1">
                                                                <strong>Dirección:</strong> <?php echo htmlspecialchars($entrega['direccion_entrega'] ?: 'No especificada'); ?>
                                                            </p>
                                                            <?php if ($entrega['telefono']): ?>
                                                                <p class="mb-1">
                                                                    <strong>Teléfono:</strong> 
                                                                    <a href="tel:<?php echo htmlspecialchars($entrega['telefono']); ?>">
                                                                        <?php echo htmlspecialchars($entrega['telefono']); ?>
                                                                    </a>
                                                                </p>
                                                            <?php endif; ?>
                                                            <small class="text-muted">
                                                                <i class="fas fa-calendar"></i> 
                                                                Programada para: <?php echo date('d/m/Y', strtotime($entrega['fecha_entrega'])); ?>
                                                            </small>
                                                        </div>
                                                        <div class="col-md-4 text-end">
                                                            <span class="badge <?php 
                                                                switch($entrega['estado']) {
                                                                    case 'programada': echo 'bg-warning'; break;
                                                                    case 'en_transito': echo 'bg-primary'; break;
                                                                    case 'entregada': echo 'bg-success'; break;
                                                                    case 'fallida': echo 'bg-danger'; break;
                                                                    default: echo 'bg-secondary';
                                                                }
                                                            ?> mb-2 d-block"><?php echo ucfirst(str_replace('_', ' ', $entrega['estado'])); ?></span>
                                                            
                                                            <div class="btn-group-vertical d-grid gap-1">
                                                                <?php if ($entrega['estado'] === 'programada'): ?>
                                                                    <button class="btn btn-primary btn-sm" onclick="iniciarEntrega(<?php echo $entrega['id']; ?>)">
                                                                        <i class="fas fa-play"></i> Iniciar
                                                                    </button>
                                                                <?php elseif ($entrega['estado'] === 'en_transito'): ?>
                                                                    <button class="btn btn-success btn-sm" onclick="completarEntrega(<?php echo $entrega['id']; ?>)">
                                                                        <i class="fas fa-check"></i> Completar
                                                                    </button>
                                                                    <button class="btn btn-warning btn-sm" onclick="marcarFallida(<?php echo $entrega['id']; ?>)">
                                                                        <i class="fas fa-exclamation-triangle"></i> Fallida
                                                                    </button>
                                                                <?php endif; ?>
                                                                <button class="btn btn-info btn-sm" onclick="verDetalles(<?php echo $entrega['id']; ?>)">
                                                                    <i class="fas fa-eye"></i> Detalles
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Panel de Acciones -->
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-tools"></i> Herramientas
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="entregas.php" class="btn btn-outline-primary">
                                            <i class="fas fa-list"></i> Ver Todas las Entregas
                                        </a>
                                        <a href="ruta.php" class="btn btn-outline-success">
                                            <i class="fas fa-route"></i> Planificar Ruta
                                        </a>
                                        <a href="historial.php" class="btn btn-outline-info">
                                            <i class="fas fa-history"></i> Historial de Entregas
                                        </a>
                                        <button class="btn btn-outline-warning" onclick="reportarIncidente()">
                                            <i class="fas fa-exclamation-triangle"></i> Reportar Incidente
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Tips del día -->
                            <div class="card mt-3">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-lightbulb"></i> Tip del Día
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info mb-0">
                                        <small>
                                            <strong>💡 Recuerda:</strong> Siempre confirma la dirección con el cliente antes de salir para la entrega. Esto evita retrasos y mejora la satisfacción del cliente.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal para completar entrega -->
    <div class="modal fade" id="completarEntregaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Completar Entrega</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="completarEntregaForm">
                        <input type="hidden" id="entregaId" name="entregaId">
                        <div class="mb-3">
                            <label for="receptor" class="form-label">Nombre de quien recibe</label>
                            <input type="text" class="form-control" id="receptor" name="receptor" required>
                        </div>
                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" onclick="confirmarEntrega()">Confirmar Entrega</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function iniciarEntrega(id) {
            if (confirm('¿Iniciar entrega? Esto cambiará el estado a "En tránsito"')) {
                // Aquí iría la llamada AJAX para actualizar el estado
                alert('Entrega iniciada - Funcionalidad por implementar');
                location.reload();
            }
        }
        
        function completarEntrega(id) {
            document.getElementById('entregaId').value = id;
            var modal = new bootstrap.Modal(document.getElementById('completarEntregaModal'));
            modal.show();
        }
        
        function confirmarEntrega() {
            // Aquí iría la lógica para confirmar la entrega
            alert('Entrega completada - Funcionalidad por implementar');
            var modal = bootstrap.Modal.getInstance(document.getElementById('completarEntregaModal'));
            modal.hide();
            location.reload();
        }
        
        function marcarFallida(id) {
            var motivo = prompt('Motivo de la entrega fallida:');
            if (motivo) {
                // Aquí iría la llamada AJAX
                alert('Entrega marcada como fallida - Funcionalidad por implementar');
                location.reload();
            }
        }
        
        function verDetalles(id) {
            // Abrir modal o página con detalles de la entrega
            alert('Ver detalles de entrega #' + id + ' - Funcionalidad por implementar');
        }
        
        function actualizarEntregas() {
            location.reload();
        }
        
        function reportarIncidente() {
            alert('Sistema de reportes de incidentes - Funcionalidad por implementar');
        }
    </script>
</body>
</html>
