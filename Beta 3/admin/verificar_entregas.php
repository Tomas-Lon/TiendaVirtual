<?php
/**
 * Script de verificación de instalación del sistema de entregas
 * 
 * Verifica que todo esté correctamente configurado
 */

session_start();

// Verificar acceso
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'empleado' || $_SESSION['cargo'] !== 'admin') {
    die('Acceso denegado. Solo administradores.');
}

require_once 'config/database.php';

$checks = [];

// 1. Verificar tablas
try {
    $pdo = getConnection();
    
    $tables = ['entregas', 'comprobantes_entrega', 'historial_entregas'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        $checks['Tabla ' . $table] = $stmt->rowCount() > 0 ? '✓ OK' : '✗ NO EXISTE';
    }
} catch (Exception $e) {
    $checks['Conexión BD'] = '✗ ERROR: ' . $e->getMessage();
}

// 2. Verificar directorios
$dirs = [
    'uploads/entregas/fotos/',
    'uploads/entregas/firmas/',
    'uploads/comprobantes/',
];

foreach ($dirs as $dir) {
    $full_path = __DIR__ . '/' . $dir;
    $checks['Directorio ' . $dir] = is_dir($full_path) ? '✓ OK' : '✗ NO EXISTE';
}

// 3. Verificar archivos
$files = [
    'admin/classes/ComprobantePDF.php',
    'admin/classes/EmailService.php',
    'config/email_config.php',
    'repartidor/entrega.php',
    'repartidor/ajax/procesar_entrega.php',
    'cliente/comprobante.php',
];

foreach ($files as $file) {
    $full_path = __DIR__ . '/' . $file;
    $checks['Archivo ' . $file] = file_exists($full_path) ? '✓ OK' : '✗ NO EXISTE';
}

// 4. Verificar configuración de email
$email_config_file = __DIR__ . '/config/email_config.php';
if (file_exists($email_config_file)) {
    $config = require $email_config_file;
    $email = $config['email'] ?? [];
    $has_credentials = !empty($email['username']) && !empty($email['password']);
    $checks['Email configurado'] = $has_credentials ? '✓ OK' : '⚠ PENDIENTE CONFIGURAR';
} else {
    $checks['Config email'] = '✗ NO EXISTE';
}

// 5. Verificar FPDF
$checks['FPDF disponible'] = file_exists(__DIR__ . '/fpdf/fpdf.php') ? '✓ OK' : '✗ NO EXISTE';

// 6. Verificar PHP version
$php_version = phpversion();
$checks['PHP version'] = version_compare($php_version, '7.4', '>=') ? '✓ ' . $php_version : '✗ ' . $php_version;

// 7. Verificar extensiones
$extensions = ['gd', 'fileinfo'];
foreach ($extensions as $ext) {
    $checks['Extensión ' . $ext] = extension_loaded($ext) ? '✓ Habilitada' : '✗ Deshabilitada';
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Instalación - SolTecnInd</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; padding: 20px; }
        .container { max-width: 800px; }
        .check-item { padding: 12px 0; border-bottom: 1px solid #eee; }
        .check-item:last-child { border-bottom: none; }
        .check-label { font-weight: 500; color: #333; }
        .check-status { text-align: right; }
        .status-ok { color: #27ae60; }
        .status-error { color: #e74c3c; }
        .status-warning { color: #f39c12; }
        .header { margin-bottom: 30px; }
        .card-header { background-color: #27ae60; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-check-circle text-success"></i> Verificación del Sistema</h1>
            <p class="text-muted">Estado de instalación del sistema de entregas</p>
        </div>

        <div class="card">
            <div class="card-header text-white">
                <h5 class="mb-0"><i class="fas fa-tools"></i> Checklist de Instalación</h5>
            </div>
            <div class="card-body">
                <?php foreach ($checks as $name => $status): ?>
                    <div class="check-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="check-label"><?php echo htmlspecialchars($name); ?></span>
                            <span class="check-status <?php 
                                if (strpos($status, '✓') === 0) echo 'status-ok';
                                elseif (strpos($status, '✗') === 0) echo 'status-error';
                                else echo 'status-warning';
                            ?>">
                                <?php echo htmlspecialchars($status); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="mt-4 alert alert-info">
            <h6><i class="fas fa-lightbulb"></i> Próximos pasos:</h6>
            <ol class="mb-0">
                <li>Configura tus credenciales de email en <code>config/email_config.php</code></li>
                <li>Crea algunos datos de prueba ejecutando <code>Database/test_data_entregas.sql</code></li>
                <li>Accede a <code>/repartidor/dashboard.php</code> para probar el sistema</li>
                <li>Lee la documentación en <code>ENTREGAS_README.md</code></li>
            </ol>
        </div>

        <div class="mt-3">
            <a href="admin/dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver al Dashboard
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
