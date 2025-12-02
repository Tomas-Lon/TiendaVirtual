<?php
/**
 * Controlador de Reportes - API REST
 * 
 * Maneja todas las solicitudes AJAX del sistema de reportes
 * Incluye validación, procesamiento y respuestas JSON
 */

require_once __DIR__ . '/ReporteManager.php';
require_once __DIR__ . '/CSVExporter.php';
require_once __DIR__ . '/PDFExporter.php';

class ReporteController {
    private $reporteManager;
    
    public function __construct() {
        $this->reporteManager = new ReporteManager();
    }
    
    /**
     * Maneja las solicitudes AJAX
     */
    public function manejarSolicitud() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $action = $_POST['action'] ?? '';
            
            switch ($action) {
                case 'productos_mas_vendidos':
                    return $this->obtenerProductosMasVendidos();
                
                case 'clientes_top':
                    return $this->obtenerClientesTop();
                
                case 'ventas_diarias':
                    return $this->obtenerVentasDiarias();
                
                case 'reporte_trimestral':
                    return $this->obtenerReporteTrimestral();
                
                case 'estadisticas_generales':
                    return $this->obtenerEstadisticasGenerales();
                
                case 'estados_pedidos':
                    return $this->obtenerEstadosPedidos();
                
                case 'ventas_empleados':
                    return $this->obtenerVentasEmpleados();
                
                case 'exportar_csv':
                    return $this->exportarCSV();
                
                case 'exportar_pdf':
                    return $this->exportarPDF();
                
                default:
                    throw new Exception('Acción no válida');
            }
            
        } catch (Exception $e) {
            return $this->respuestaError($e->getMessage());
        }
    }
    
    private function obtenerProductosMasVendidos() {
        $fecha_inicio = $_POST['fecha_inicio'] ?? date('Y-m-01');
        $fecha_fin = $_POST['fecha_fin'] ?? date('Y-m-d');
        $limite = intval($_POST['limite'] ?? 10);
        
        $datos = $this->reporteManager->productosMasVendidos($fecha_inicio, $fecha_fin, $limite);
        
        return $this->respuestaExito($datos);
    }
    
    private function obtenerClientesTop() {
        $fecha_inicio = $_POST['fecha_inicio'] ?? date('Y-m-01');
        $fecha_fin = $_POST['fecha_fin'] ?? date('Y-m-d');
        $limite = intval($_POST['limite'] ?? 10);
        
        $datos = $this->reporteManager->clientesTopCompradores($fecha_inicio, $fecha_fin, $limite);
        
        return $this->respuestaExito($datos);
    }
    
    private function obtenerVentasDiarias() {
        $fecha_inicio = $_POST['fecha_inicio'] ?? date('Y-m-01');
        $fecha_fin = $_POST['fecha_fin'] ?? date('Y-m-d');
        
        $datos = $this->reporteManager->ventasDiarias($fecha_inicio, $fecha_fin);
        
        return $this->respuestaExito($datos);
    }
    
    private function obtenerReporteTrimestral() {
        $año = intval($_POST['año'] ?? date('Y'));
        $trimestre = intval($_POST['trimestre'] ?? ceil(date('n') / 3));
        
        $datos = $this->reporteManager->reporteTrimestral($año, $trimestre);
        
        return $this->respuestaExito($datos);
    }
    
    private function obtenerEstadisticasGenerales() {
        $fecha_inicio = $_POST['fecha_inicio'] ?? date('Y-m-01');
        $fecha_fin = $_POST['fecha_fin'] ?? date('Y-m-d');
        
        $datos = $this->reporteManager->estadisticasGenerales($fecha_inicio, $fecha_fin);
        
        return $this->respuestaExito($datos);
    }
    
    private function obtenerEstadosPedidos() {
        $fecha_inicio = $_POST['fecha_inicio'] ?? date('Y-m-01');
        $fecha_fin = $_POST['fecha_fin'] ?? date('Y-m-d');
        
        $datos = $this->reporteManager->estadosPedidos($fecha_inicio, $fecha_fin);
        
        return $this->respuestaExito($datos);
    }
    
    private function obtenerVentasEmpleados() {
        $fecha_inicio = $_POST['fecha_inicio'] ?? date('Y-m-01');
        $fecha_fin = $_POST['fecha_fin'] ?? date('Y-m-d');
        
        $datos = $this->reporteManager->ventasPorEmpleado($fecha_inicio, $fecha_fin);
        
        return $this->respuestaExito($datos);
    }
    
    private function exportarCSV() {
        $tipo = $_POST['tipo_reporte'] ?? '';
        $fecha_inicio = $_POST['fecha_inicio'] ?? date('Y-m-01');
        $fecha_fin = $_POST['fecha_fin'] ?? date('Y-m-d');
        $limite = intval($_POST['limite'] ?? 50);
        
        // Obtener datos según el tipo
        $datos = $this->obtenerDatosParaExportacion($tipo, $fecha_inicio, $fecha_fin, $limite);
        
        if (empty($datos)) {
            throw new Exception('No hay datos para exportar');
        }
        
        // Exportar (esto termina la ejecución)
        CSVExporter::exportar($datos, $tipo);
    }
    
    private function exportarPDF() {
        $tipo = $_POST['tipo_reporte'] ?? '';
        $fecha_inicio = $_POST['fecha_inicio'] ?? date('Y-m-01');
        $fecha_fin = $_POST['fecha_fin'] ?? date('Y-m-d');
        $limite = intval($_POST['limite'] ?? 50);
        
        // Obtener datos según el tipo
        $datos = $this->obtenerDatosParaExportacion($tipo, $fecha_inicio, $fecha_fin, $limite);
        
        if (empty($datos)) {
            throw new Exception('No hay datos para exportar');
        }
        
        $periodo = "$fecha_inicio al $fecha_fin";
        
        // Generar PDF (esto termina la ejecución)
        PDFExporter::generar($tipo, $datos, null, $periodo);
    }
    
    private function obtenerDatosParaExportacion($tipo, $fecha_inicio, $fecha_fin, $limite) {
        switch ($tipo) {
            case 'productos':
                return $this->reporteManager->productosMasVendidos($fecha_inicio, $fecha_fin, $limite);
            
            case 'clientes':
                return $this->reporteManager->clientesTopCompradores($fecha_inicio, $fecha_fin, $limite);
            
            case 'estados':
                return $this->reporteManager->estadosPedidos($fecha_inicio, $fecha_fin);
            
            case 'empleados':
                return $this->reporteManager->ventasPorEmpleado($fecha_inicio, $fecha_fin);
            
            case 'ventas_diarias':
                return $this->reporteManager->ventasDiarias($fecha_inicio, $fecha_fin);
            
            case 'trimestral':
                $año = intval($_POST['año'] ?? date('Y'));
                $trimestre = intval($_POST['trimestre'] ?? ceil(date('n') / 3));
                return $this->reporteManager->reporteTrimestral($año, $trimestre);
            
            default:
                throw new Exception("Tipo de reporte no válido: $tipo");
        }
    }
    
    private function respuestaExito($data) {
        return [
            'success' => true,
            'data' => $data
        ];
    }
    
    private function respuestaError($mensaje) {
        return [
            'success' => false,
            'error' => $mensaje
        ];
    }
}
