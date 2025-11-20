<?php
session_start();

// Verificar que el usuario sea cliente
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'cliente') {
    header('Location: ../index.php');
    exit();
}

require_once '../config/database.php';

$codigo_qr = $_GET['codigo'] ?? null;

if (!$codigo_qr) {
    header('Location: dashboard.php');
    exit();
}

try {
    $pdo = getConnection();
    
    // Obtener comprobante por código QR
    $stmt = $pdo->prepare("
        SELECT c.*, e.cliente_id, e.numero_pedido, e.fecha_entrega_real
        FROM comprobantes_entrega c
        LEFT JOIN entregas e ON c.entrega_id = e.id
        WHERE c.codigo_qr = ?
    ");
    $stmt->execute([$codigo_qr]);
    $comprobante = $stmt->fetch();
    
    if (!$comprobante) {
        $error = "Comprobante no encontrado";
    } elseif ($comprobante['cliente_id'] != $_SESSION['cliente_id']) {
        // Verificar que el cliente sea el propietario
        $error = "No tienes acceso a este comprobante";
    }
    
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    $error = "Error al cargar el comprobante";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Entrega</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .comprobante-container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-top: 30px; }
        .header { border-bottom: 3px solid #27ae60; padding-bottom: 20px; margin-bottom: 20px; }
        .header h1 { color: #27ae60; }
        .section { margin-bottom: 20px; }
        .section-title { background-color: #f0f8f5; border-left: 4px solid #27ae60; padding: 10px 15px; margin-bottom: 15px; font-weight: bold; }
        .row-info { display: flex; padding: 10px 0; border-bottom: 1px solid #eee; }
        .label { font-weight: 600; color: #333; width: 200px; }
        .value { color: #666; flex: 1; }
        .firma-section { text-align: center; margin-top: 30px; }
        .firma-image { max-width: 200px; border: 1px solid #ddd; border-radius: 5px; margin: 10px 0; }
        .codigo-qr { background-color: #f0f8f5; padding: 15px; border-radius: 5px; text-align: center; margin: 20px 0; }
        .codigo-qr code { font-size: 18px; color: #27ae60; font-weight: bold; }
        .acciones { margin-top: 30px; }
        .error-box { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin-top: 30px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php"><i class="fas fa-arrow-left"></i> Volver</a>
            <span class="navbar-text text-light">Comprobante de Entrega</span>
        </div>
    </nav>

    <div class="container">
        <?php if (!empty($error)): ?>
            <div class="error-box">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php elseif ($comprobante): ?>
            <div class="comprobante-container">
                <div class="header">
                    <h1><i class="fas fa-check-circle text-success"></i> Comprobante de Entrega</h1>
                    <p class="text-muted">Documento oficial de entrega de mercancía</p>
                </div>

                <!-- Información General -->
                <div class="section">
                    <div class="section-title">Información de Entrega</div>
                    <div class="row-info">
                        <div class="label">Fecha de Entrega:</div>
                        <div class="value"><?php echo date('d/m/Y H:i', strtotime($comprobante['fecha_generacion'])); ?></div>
                    </div>
                    <div class="row-info">
                        <div class="label">Recibido por:</div>
                        <div class="value"><strong><?php echo htmlspecialchars($comprobante['receptor_nombre']); ?></strong></div>
                    </div>
                    <?php if ($comprobante['receptor_documento']): ?>
                        <div class="row-info">
                            <div class="label">Documento:</div>
                            <div class="value"><?php echo htmlspecialchars($comprobante['receptor_documento']); ?></div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Observaciones -->
                <?php if ($comprobante['observaciones']): ?>
                    <div class="section">
                        <div class="section-title">Observaciones</div>
                        <p><?php echo nl2br(htmlspecialchars($comprobante['observaciones'])); ?></p>
                    </div>
                <?php endif; ?>

                <!-- Ubicación GPS -->
                <?php if ($comprobante['latitud'] && $comprobante['longitud']): ?>
                    <div class="section">
                        <div class="section-title">Ubicación de Entrega</div>
                        <div class="row-info">
                            <div class="label">Latitud:</div>
                            <div class="value"><?php echo $comprobante['latitud']; ?></div>
                        </div>
                        <div class="row-info">
                            <div class="label">Longitud:</div>
                            <div class="value"><?php echo $comprobante['longitud']; ?></div>
                        </div>
                        <div class="row-info">
                            <a href="https://maps.google.com/?q=<?php echo $comprobante['latitud']; ?>,<?php echo $comprobante['longitud']; ?>" 
                               target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-map"></i> Ver en Maps
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Fotos -->
                <?php if ($comprobante['foto_cliente']): ?>
                    <div class="section">
                        <div class="section-title">Evidencia Fotográfica</div>
                        <img src="<?php echo htmlspecialchars($comprobante['foto_cliente']); ?>" 
                             alt="Foto de entrega" class="firma-image" style="max-width: 300px;">
                    </div>
                <?php endif; ?>

                <!-- Firma -->
                <?php if ($comprobante['firma_cliente']): ?>
                    <div class="section">
                        <div class="section-title">Firma del Receptor</div>
                        <div class="firma-section">
                            <img src="<?php echo htmlspecialchars($comprobante['firma_cliente']); ?>" 
                                 alt="Firma" class="firma-image">
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Código QR -->
                <div class="codigo-qr">
                    <div>Código de Referencia:</div>
                    <code><?php echo htmlspecialchars($comprobante['codigo_qr']); ?></code>
                </div>

                <!-- Acciones -->
                <div class="acciones">
                    <div class="d-grid gap-2">
                        <?php if ($comprobante['pdf_path']): ?>
                            <a href="<?php echo htmlspecialchars($comprobante['pdf_path']); ?>" 
                               class="btn btn-lg btn-success" target="_blank">
                                <i class="fas fa-file-pdf"></i> Descargar Comprobante PDF
                            </a>
                        <?php endif; ?>
                        <a href="dashboard.php" class="btn btn-lg btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver a Mis Entregas
                        </a>
                    </div>
                </div>

                <!-- Pie de página -->
                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #999; font-size: 12px;">
                    <p>Este comprobante es un documento oficial de entrega.</p>
                    <p>Para cualquier reclamo o duda, contacta con nuestro servicio al cliente.</p>
                    <p>&copy; 2025 SolTecnInd. Todos los derechos reservados.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
