<?php
/**
 * Utilidad para verificar y corregir rutas de comprobantes
 * Ejecutar solo una vez en producción para migrar las rutas
 */
session_start();

// Solo admin puede ejecutar esto
if (!isset($_SESSION['user_id']) || $_SESSION['cargo'] !== 'admin') {
    die('Acceso denegado. Solo administradores.');
}

require_once '../config/database.php';
require_once '../config/paths.php';

$pdo = getConnection();
$action = $_GET['action'] ?? 'verificar';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar y Corregir Rutas - SolTecnInd</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3><i class="fas fa-tools"></i> Utilidad de Corrección de Rutas</h3>
                <p class="mb-0">Sistema de verificación y corrección de URLs de comprobantes</p>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>Configuración Actual:</strong><br>
                    <code>BASE_URL: <?= BASE_URL ?></code><br>
                    <code>COMPROBANTES_URL: <?= COMPROBANTES_URL ?></code><br>
                    <code>Environment: <?= (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) ? 'LOCAL' : 'PRODUCCIÓN' ?></code>
                </div>

                <?php if ($action === 'verificar'): ?>
                    <h4>Estado de las Rutas en la Base de Datos</h4>
                    <?php
                    $stmt = $pdo->query("
                        SELECT id, pdf_path, created_at
                        FROM comprobantes_entrega
                        WHERE pdf_path IS NOT NULL
                        ORDER BY id DESC
                        LIMIT 20
                    ");
                    $comprobantes = $stmt->fetchAll();
                    ?>
                    
                    <?php if (empty($comprobantes)): ?>
                        <div class="alert alert-warning">No hay comprobantes en la base de datos.</div>
                    <?php else: ?>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Ruta Actual en BD</th>
                                    <th>Ruta Normalizada</th>
                                    <th>Estado</th>
                                    <th>URL Completa</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($comprobantes as $comp): ?>
                                <?php
                                $ruta_actual = $comp['pdf_path'];
                                $ruta_normalizada = normalizar_url($ruta_actual);
                                $necesita_correccion = (strpos($ruta_actual, '/projectTiendaVirtual') !== false);
                                ?>
                                <tr class="<?= $necesita_correccion ? 'table-warning' : 'table-success' ?>">
                                    <td><?= $comp['id'] ?></td>
                                    <td><code><?= htmlspecialchars($ruta_actual) ?></code></td>
                                    <td><code><?= htmlspecialchars($ruta_normalizada) ?></code></td>
                                    <td>
                                        <?php if ($necesita_correccion): ?>
                                            <span class="badge bg-warning">Necesita corrección</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">OK</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= htmlspecialchars($ruta_normalizada) ?>" target="_blank" class="btn btn-sm btn-primary">
                                            <i class="fas fa-external-link-alt"></i> Probar
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <div class="alert alert-warning mt-4">
                            <h5>¿Qué hacer ahora?</h5>
                            <ol>
                                <li>Verifica que las rutas normalizadas sean correctas haciendo clic en "Probar"</li>
                                <li>Si funcionan correctamente, haz clic en el botón de abajo para corregir TODAS las rutas en la BD</li>
                                <li>Esta operación es segura y se puede ejecutar múltiples veces sin problemas</li>
                            </ol>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <a href="?action=corregir" class="btn btn-warning btn-lg">
                                <i class="fas fa-wrench"></i> Corregir TODAS las Rutas en la Base de Datos
                            </a>
                            <a href="../dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver al Dashboard
                            </a>
                        </div>
                    <?php endif; ?>
                    
                <?php elseif ($action === 'corregir'): ?>
                    <h4>Corrigiendo Rutas...</h4>
                    <?php
                    try {
                        // Actualizar rutas que empiezan con /projectTiendaVirtual
                        $stmt = $pdo->prepare("
                            UPDATE comprobantes_entrega
                            SET pdf_path = REPLACE(pdf_path, '/projectTiendaVirtual', '')
                            WHERE pdf_path LIKE '/projectTiendaVirtual%'
                        ");
                        $stmt->execute();
                        $actualizados1 = $stmt->rowCount();
                        
                        // Asegurar que todas las rutas empiecen con /
                        $stmt = $pdo->prepare("
                            UPDATE comprobantes_entrega
                            SET pdf_path = CONCAT('/', pdf_path)
                            WHERE pdf_path IS NOT NULL 
                              AND pdf_path != ''
                              AND LEFT(pdf_path, 1) != '/'
                        ");
                        $stmt->execute();
                        $actualizados2 = $stmt->rowCount();
                        
                        echo "<div class='alert alert-success'>";
                        echo "<h5>✓ Corrección Completada</h5>";
                        echo "<p>Se actualizaron:<br>";
                        echo "- <strong>$actualizados1</strong> registros con prefijo /projectTiendaVirtual<br>";
                        echo "- <strong>$actualizados2</strong> registros sin barra inicial</p>";
                        echo "</div>";
                        
                        // Mostrar estado después de la corrección
                        $stmt = $pdo->query("
                            SELECT id, pdf_path
                            FROM comprobantes_entrega
                            WHERE pdf_path IS NOT NULL
                            ORDER BY id DESC
                            LIMIT 10
                        ");
                        $comprobantes = $stmt->fetchAll();
                        
                        if (!empty($comprobantes)) {
                            echo "<h5>Estado Después de la Corrección (últimos 10):</h5>";
                            echo "<table class='table table-sm'>";
                            echo "<thead><tr><th>ID</th><th>Ruta Corregida</th><th>Prueba</th></tr></thead>";
                            echo "<tbody>";
                            foreach ($comprobantes as $comp) {
                                $url = normalizar_url($comp['pdf_path']);
                                echo "<tr>";
                                echo "<td>{$comp['id']}</td>";
                                echo "<td><code>" . htmlspecialchars($comp['pdf_path']) . "</code></td>";
                                echo "<td><a href='" . htmlspecialchars($url) . "' target='_blank' class='btn btn-sm btn-primary'>Probar</a></td>";
                                echo "</tr>";
                            }
                            echo "</tbody></table>";
                        }
                        
                    } catch (Exception $e) {
                        echo "<div class='alert alert-danger'>";
                        echo "<h5>✗ Error</h5>";
                        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
                        echo "</div>";
                    }
                    ?>
                    
                    <div class="d-grid gap-2 mt-4">
                        <a href="?action=verificar" class="btn btn-primary">
                            <i class="fas fa-check"></i> Verificar Nuevamente
                        </a>
                        <a href="../dashboard.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver al Dashboard
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>
