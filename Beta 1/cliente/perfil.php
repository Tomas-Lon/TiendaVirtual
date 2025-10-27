<?php
session_start();

// Verificar que el usuario esté logueado y sea cliente
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'cliente') {
    header('Location: ../index.php');
    exit();
}

require_once '../config/database.php';

$mensaje = '';
$tipo_mensaje = '';

// Procesar actualización de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getConnection();
        $cliente_id = $_SESSION['cliente_id'];
        
        $nombre = trim($_POST['nombre']);
        $telefono = trim($_POST['telefono']);
        $email = trim($_POST['email']);
        $direccion = trim($_POST['direccion']);
        $ciudad = trim($_POST['ciudad']);
        
        $stmt = $pdo->prepare("UPDATE clientes SET nombre = ?, telefono = ?, email = ?, direccion_principal = ?, ciudad = ? WHERE id = ?");
        if ($stmt->execute([$nombre, $telefono, $email, $direccion, $ciudad, $cliente_id])) {
            $mensaje = "Perfil actualizado correctamente";
            $tipo_mensaje = "success";
            $_SESSION['nombre'] = $nombre; // Actualizar nombre en sesión
        } else {
            $mensaje = "Error al actualizar el perfil";
            $tipo_mensaje = "danger";
        }
    } catch (PDOException $e) {
        $mensaje = "Error: " . $e->getMessage();
        $tipo_mensaje = "danger";
    }
}

try {
    $pdo = getConnection();
    $cliente_id = $_SESSION['cliente_id'];
    
    // Obtener información del cliente
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
    $stmt->execute([$cliente_id]);
    $cliente = $stmt->fetch();
    
} catch (PDOException $e) {
    error_log("Error en perfil cliente: " . $e->getMessage());
    $cliente = null;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - SolTecnInd</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #34495e;
        }
        .sidebar .nav-link {
            color: #ecf0f1;
            border-radius: 5px;
            margin: 2px 0;
        }
        .sidebar .nav-link:hover {
            background: #3d566e;
            color: white;
        }
        .sidebar .nav-link.active {
            background: #e74c3c;
            color: white;
        }
        .main-content {
            background: #f8f9fa;
            min-height: 100vh;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar px-3 py-4">
                    <div class="text-center mb-4">
                        <h4 class="text-light fw-bold">SolTecnInd</h4>
                        <small class="text-light">Portal Cliente</small>
                    </div>
                    
                    <nav class="nav flex-column">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Inicio
                        </a>
                        <a class="nav-link" href="productos.php">
                            <i class="fas fa-boxes"></i> Catálogo
                        </a>
                        <a class="nav-link" href="nueva_compra.php">
                            <i class="fas fa-cart-plus"></i> Nueva Compra
                        </a>
                        <a class="nav-link" href="pedidos.php">
                            <i class="fas fa-clipboard-list"></i> Mis Pedidos
                        </a>
                        <a class="nav-link" href="cotizaciones.php">
                            <i class="fas fa-file-invoice-dollar"></i> Cotizaciones
                        </a>
                        <a class="nav-link active" href="perfil.php">
                            <i class="fas fa-user-circle"></i> Mi Perfil
                        </a>
                        <hr class="text-light">
                        <a class="nav-link text-warning" href="../logout.php">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="container-fluid px-4 py-4">
                    <h2 class="mb-4">Mi Perfil</h2>
                    
                    <?php if (!empty($mensaje)): ?>
                        <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
                            <?php echo $mensaje; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($cliente): ?>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Información Personal</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Nombre Completo</label>
                                                    <input type="text" class="form-control" name="nombre" 
                                                           value="<?php echo htmlspecialchars($cliente['nombre']); ?>" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Tipo de Documento</label>
                                                    <input type="text" class="form-control" 
                                                           value="<?php echo htmlspecialchars($cliente['tipo_documento']); ?>" readonly>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Número de Documento</label>
                                                    <input type="text" class="form-control" 
                                                           value="<?php echo htmlspecialchars($cliente['numero_documento']); ?>" readonly>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Teléfono</label>
                                                    <input type="tel" class="form-control" name="telefono" 
                                                           value="<?php echo htmlspecialchars($cliente['telefono']); ?>">
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" name="email" 
                                                       value="<?php echo htmlspecialchars($cliente['email']); ?>">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Dirección</label>
                              <input type="text" class="form-control" name="direccion" 
                                  value="<?php echo htmlspecialchars($cliente['direccion_principal'] ?? ($cliente['direccion'] ?? '')); ?>">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Ciudad</label>
                                                <input type="text" class="form-control" name="ciudad" 
                                                       value="<?php echo htmlspecialchars($cliente['ciudad']); ?>">
                                            </div>
                                            
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save"></i> Guardar Cambios
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Información de Cuenta</h5>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Usuario:</strong> <?php echo htmlspecialchars($_SESSION['usuario']); ?></p>
                                        <p><strong>Estado:</strong> 
                                            <span class="badge bg-<?php echo $cliente['activo'] ? 'success' : 'danger'; ?>">
                                                <?php echo $cliente['activo'] ? 'Activo' : 'Inactivo'; ?>
                                            </span>
                                        </p>
                                        <p><strong>Registro:</strong> <?php echo date('d/m/Y', strtotime($cliente['created_at'])); ?></p>
                                        
                                        <hr>
                                        
                                        <div class="d-grid">
                                            <button class="btn btn-outline-warning mb-2" data-bs-toggle="modal" data-bs-target="#cambiarPasswordModal">
                                                <i class="fas fa-key"></i> Cambiar Contraseña
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger">
                            Error al cargar la información del perfil.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Cambiar Contraseña -->
    <div class="modal fade" id="cambiarPasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cambiar Contraseña</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label class="form-label">Contraseña Actual</label>
                            <input type="password" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nueva Contraseña</label>
                            <input type="password" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirmar Nueva Contraseña</label>
                            <input type="password" class="form-control">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary">Cambiar Contraseña</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
