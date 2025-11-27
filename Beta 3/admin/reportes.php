<?php
session_start();

require_once '../includes/LayoutManager.php';
require_once '../config/database.php';
require_once __DIR__ . '/classes/ReporteController.php';

// Verificar autenticación de administrador
if (!isset($_SESSION['empleado_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Manejar solicitudes AJAX del sistema de reportes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    $controller = new ReporteController();
    $response = $controller->manejarSolicitud();
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// CSS adicional para el sistema de reportes
$additionalCSS = '
<style>
    .stat-card {
        transition: transform 0.3s ease;
        border: none;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.2);
    }
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.7);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }
    .table-container {
        max-height: 600px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
    }
    .export-btn {
        background: linear-gradient(45deg, #28a745, #20c997);
        border: none;
        border-radius: 25px;
    }
    .filter-card {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        border: none;
    }
</style>
';

// JavaScript adicional para el sistema de reportes
$additionalJS = '
<script src="js/reportes.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById("tipo_reporte").addEventListener("change", function() {
            const trimestralFields = document.getElementById("campos_trimestral");
            if (this.value === "trimestral") {
                trimestralFields.style.display = "block";
            } else {
                trimestralFields.style.display = "none";
            }
        });

        const mesActual = new Date().getMonth() + 1;
        const trimestreActual = Math.ceil(mesActual / 3);
        document.getElementById("numero_trimestre").value = trimestreActual;
    });
</script>
';

// Contenido de la página
$content = '
<!-- Loading Overlay -->
<div id="loading" class="loading-overlay">
    <div class="text-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
        <p class="text-white mt-3">Generando reporte...</p>
    </div>
</div>

<!-- Header Principal -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0 text-primary">
                <i class="fas fa-chart-bar me-2"></i> Sistema de Reportes
            </h1>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary" onclick="reportes.refrescarDatos()">
                    <i class="fas fa-sync-alt me-1"></i> Actualizar
                </button>
                <div class="dropdown">
                    <button class="btn export-btn text-white dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-download me-1"></i> Exportar
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="reportes.exportar(\'csv\')">
                            <i class="fas fa-file-csv me-2 text-success"></i> Exportar CSV
                        </a></li>
                        <li><a class="dropdown-item" href="#" onclick="reportes.exportar(\'pdf\')">
                            <i class="fas fa-file-pdf me-2 text-danger"></i> Exportar PDF
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Panel de Filtros -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card filter-card">
            <div class="card-header bg-transparent">
                <h5 class="mb-0">
                    <i class="fas fa-filter me-2"></i> Configuración de Reportes
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Tipo de Reporte</label>
                        <select class="form-select" id="tipo_reporte">
                            <option value="productos">📊 Productos Más Vendidos</option>
                            <option value="clientes">👥 Mejores Clientes</option>
                            <option value="estados">📋 Estados de Pedidos</option>
                            <option value="empleados">🏢 Ventas por Empleado</option>
                            <option value="ventas_diarias">📅 Ventas Diarias</option>
                            <option value="trimestral">📈 Reporte Trimestral</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Fecha Inicio</label>
                        <input type="date" class="form-control" id="fecha_inicio" value="' . date('Y-m-01') . '">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Fecha Fin</label>
                        <input type="date" class="form-control" id="fecha_fin" value="' . date('Y-m-d') . '">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Límite</label>
                        <select class="form-select" id="limite">
                            <option value="10">Top 10</option>
                            <option value="25">Top 25</option>
                            <option value="50">Top 50</option>
                            <option value="100">Top 100</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <button class="btn btn-primary w-100" onclick="reportes.generarReporte()">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Campos específicos para reporte trimestral -->
                <div class="row mt-3" id="campos_trimestral" style="display: none;">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Año</label>
                        <select class="form-select" id="año_trimestre">';

for($i = date('Y'); $i >= date('Y')-5; $i--) {
    $content .= '<option value="' . $i . '">' . $i . '</option>';
}

$content .= '
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Trimestre</label>
                        <select class="form-select" id="numero_trimestre">
                            <option value="1">Q1 (Enero - Marzo)</option>
                            <option value="2">Q2 (Abril - Junio)</option>
                            <option value="3">Q3 (Julio - Septiembre)</option>
                            <option value="4">Q4 (Octubre - Diciembre)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Área de Resultados -->
<div class="row">
    <!-- Estadísticas Generales (Siempre Visibles) -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i> Estadísticas Generales del Mes Actual
                </h5>
            </div>
            <div class="card-body">
                <div class="row" id="estadisticas_generales">
                    <div class="col-md-3">
                        <div class="stat-card card h-100 text-center bg-light">
                            <div class="card-body">
                                <i class="fas fa-shopping-cart fa-2x text-primary mb-2"></i>
                                <h4 class="text-primary mb-1" id="stat_pedidos">-</h4>
                                <p class="text-muted mb-0">Pedidos Totales</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card card h-100 text-center bg-light">
                            <div class="card-body">
                                <i class="fas fa-dollar-sign fa-2x text-success mb-2"></i>
                                <h4 class="text-success mb-1" id="stat_ventas">-</h4>
                                <p class="text-muted mb-0">Ventas Totales</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card card h-100 text-center bg-light">
                            <div class="card-body">
                                <i class="fas fa-users fa-2x text-info mb-2"></i>
                                <h4 class="text-info mb-1" id="stat_clientes">-</h4>
                                <p class="text-muted mb-0">Clientes Únicos</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card card h-100 text-center bg-light">
                            <div class="card-body">
                                <i class="fas fa-chart-bar fa-2x text-warning mb-2"></i>
                                <h4 class="text-warning mb-1" id="stat_promedio">-</h4>
                                <p class="text-muted mb-0">Venta Promedio</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 id="titulo_reporte" class="mb-0">
                    <i class="fas fa-chart-line me-2"></i> Seleccione un tipo de reporte
                </h5>
                <small class="text-muted" id="info_reporte"></small>
            </div>
            <div class="card-body">
                <div id="contenido_reporte">
                    <div class="text-center py-5">
                        <i class="fas fa-chart-bar fa-4x text-muted mb-4"></i>
                        <h4 class="text-muted mb-3">Bienvenido al Sistema de Reportes</h4>
                        <p class="text-muted mb-4">Configure los filtros arriba y haga clic en el botón de búsqueda para generar su reporte</p>
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Sistema de Reportes Completo v2.0:</strong>
                                    <ul class="list-unstyled mt-2 mb-0">
                                        <li>📊 • Productos más vendidos con análisis detallado</li>
                                        <li>👥 • Ranking de mejores clientes con historial</li>
                                        <li>📋 • Análisis de estados de pedidos</li>
                                        <li>🏢 • Performance de ventas por empleado</li>
                                        <li>📅 • Análisis de ventas diarias con métricas</li>
                                        <li>📈 • Reportes trimestrales con comparativos</li>
                                        <li>📊 • Estadísticas generales en tiempo real</li>
                                        <li>💾 • Exportación en CSV y PDF profesional</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
';

// Renderizar la página usando LayoutManager
LayoutManager::renderAdminPage('Sistema de Reportes', $content, $additionalCSS, $additionalJS);
?>
