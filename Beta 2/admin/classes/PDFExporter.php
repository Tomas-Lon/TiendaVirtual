<?php
/**
 * Exportador PDF - Clase Especializada FPDF
 * 
 * Genera PDFs profesionales utilizando la biblioteca FPDF
 * Incluye templates para productos, clientes y reportes trimestrales
 */

require_once __DIR__ . '/../../fpdf/fpdf.php';

class PDFExporter extends FPDF {
    private $titulo_reporte;
    private $fecha_generacion;
    
    public function __construct($titulo = 'Reporte de Ventas') {
        parent::__construct();
        $this->titulo_reporte = $titulo;
        $this->fecha_generacion = date('d/m/Y H:i:s');
    }
    
    // Header del PDF
    function Header() {
        // Logo (si existe)
        $logo_path = __DIR__ . '/../../assets/logo.png';
        if (file_exists($logo_path)) {
            $this->Image($logo_path, 10, 6, 30);
        }
        
        // Título
        $this->SetFont('Arial', 'B', 16);
        $this->SetX(50);
        $this->Cell(0, 10, $this->titulo_reporte, 0, 1, 'L');
        
        // Fecha de generación
        $this->SetFont('Arial', '', 10);
        $this->SetX(50);
        $this->Cell(0, 10, 'Generado el: ' . $this->fecha_generacion, 0, 1, 'L');
        
        // Línea separadora
        $this->Ln(5);
        $this->SetDrawColor(200, 200, 200);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(10);
    }
    
    // Footer del PDF
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 10, 'Página ' . $this->PageNo() . ' - Sistema de Reportes', 0, 0, 'C');
    }
    
    /**
     * Exporta reporte de productos a PDF
     */
    public function exportarProductos($datos, $periodo = '') {
        $this->titulo_reporte = 'Reporte de Productos Más Vendidos' . ($periodo ? " - $periodo" : '');
        $this->AddPage();
        
        if (empty($datos)) {
            $this->SetFont('Arial', '', 12);
            $this->Cell(0, 10, 'No se encontraron datos para mostrar', 0, 1, 'C');
            return;
        }
        
        // Título de la tabla
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Top ' . count($datos) . ' Productos Más Vendidos', 0, 1, 'C');
        $this->Ln(5);
        
        // Headers de la tabla
        $this->SetFont('Arial', 'B', 9);
        $this->SetFillColor(230, 230, 230);
        
        $this->Cell(20, 8, '#', 1, 0, 'C', true);
        $this->Cell(30, 8, 'Código', 1, 0, 'C', true);
        $this->Cell(60, 8, 'Descripción', 1, 0, 'C', true);
        $this->Cell(25, 8, 'Cantidad', 1, 0, 'C', true);
        $this->Cell(30, 8, 'Ingresos $', 1, 0, 'C', true);
        $this->Cell(25, 8, 'Pedidos', 1, 1, 'C', true);
        
        // Datos
        $this->SetFont('Arial', '', 8);
        $contador = 1;
        
        foreach ($datos as $producto) {
            // Verificar si necesitamos nueva página
            if ($this->GetY() > 250) {
                $this->AddPage();
                // Repetir headers
                $this->SetFont('Arial', 'B', 9);
                $this->SetFillColor(230, 230, 230);
                $this->Cell(20, 8, '#', 1, 0, 'C', true);
                $this->Cell(30, 8, 'Código', 1, 0, 'C', true);
                $this->Cell(60, 8, 'Descripción', 1, 0, 'C', true);
                $this->Cell(25, 8, 'Cantidad', 1, 0, 'C', true);
                $this->Cell(30, 8, 'Ingresos', 1, 0, 'C', true);
                $this->Cell(25, 8, 'Pedidos', 1, 1, 'C', true);
                $this->SetFont('Arial', '', 8);
            }
            
            $this->Cell(20, 8, $contador, 1, 0, 'C');
            $this->Cell(30, 8, $producto['codigo'], 1, 0, 'C');
            
            // Descripción (truncar si es muy larga)
            $descripcion = strlen($producto['descripcion']) > 35 ? 
                          substr($producto['descripcion'], 0, 32) . '...' : 
                          $producto['descripcion'];
            $this->Cell(60, 8, $descripcion, 1, 0, 'L');
            
            $this->Cell(25, 8, number_format($producto['cantidad_vendida'], 0, '.', ','), 1, 0, 'R');
            $this->Cell(30, 8, '$' . number_format($producto['ingresos_totales'], 0, '.', ','), 1, 0, 'R');
            $this->Cell(25, 8, $producto['pedidos_incluidos'], 1, 1, 'C');
            
            $contador++;
        }
        
        // Resumen al final
        $this->Ln(10);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 8, 'Resumen:', 0, 1, 'L');
        
        $total_cantidad = array_sum(array_column($datos, 'cantidad_vendida'));
        $total_ingresos = array_sum(array_column($datos, 'ingresos_totales'));
        
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 6, '• Total unidades vendidas: ' . number_format($total_cantidad, 0, '.', ','), 0, 1, 'L');
        $this->Cell(0, 6, '• Total ingresos: $' . number_format($total_ingresos, 0, '.', ','), 0, 1, 'L');
        $this->Cell(0, 6, '• Productos en el ranking: ' . count($datos), 0, 1, 'L');
    }
    
    /**
     * Exporta reporte de clientes a PDF
     */
    public function exportarClientes($datos, $periodo = '') {
        $this->titulo_reporte = 'Reporte de Mejores Clientes' . ($periodo ? " - $periodo" : '');
        $this->AddPage();
        
        if (empty($datos)) {
            $this->SetFont('Arial', '', 12);
            $this->Cell(0, 10, 'No se encontraron datos para mostrar', 0, 1, 'C');
            return;
        }
        
        // Título de la tabla
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Top ' . count($datos) . ' Clientes con Más Compras', 0, 1, 'C');
        $this->Ln(5);
        
        // Headers de la tabla
        $this->SetFont('Arial', 'B', 9);
        $this->SetFillColor(230, 230, 230);
        
        $this->Cell(20, 8, '#', 1, 0, 'C', true);
        $this->Cell(50, 8, 'Cliente', 1, 0, 'C', true);
        $this->Cell(45, 8, 'Email', 1, 0, 'C', true);
        $this->Cell(25, 8, 'Pedidos', 1, 0, 'C', true);
        $this->Cell(30, 8, 'Total Gastado $', 1, 0, 'C', true);
        $this->Cell(20, 8, 'Promedio $', 1, 1, 'C', true);
        
        // Datos
        $this->SetFont('Arial', '', 8);
        $contador = 1;
        
        foreach ($datos as $cliente) {
            if ($this->GetY() > 250) {
                $this->AddPage();
                // Repetir headers
                $this->SetFont('Arial', 'B', 9);
                $this->SetFillColor(230, 230, 230);
                $this->Cell(20, 8, '#', 1, 0, 'C', true);
                $this->Cell(50, 8, 'Cliente', 1, 0, 'C', true);
                $this->Cell(45, 8, 'Email', 1, 0, 'C', true);
                $this->Cell(25, 8, 'Pedidos', 1, 0, 'C', true);
                $this->Cell(30, 8, 'Total Gastado $', 1, 0, 'C', true);
                $this->Cell(20, 8, 'Promedio $', 1, 1, 'C', true);
                $this->SetFont('Arial', '', 8);
            }
            
            $this->Cell(20, 8, $contador, 1, 0, 'C');
            
            // Nombre (truncar si es muy largo)
            $nombre = strlen($cliente['nombre']) > 25 ? 
                     substr($cliente['nombre'], 0, 22) . '...' : 
                     $cliente['nombre'];
            $this->Cell(50, 8, $nombre, 1, 0, 'L');
            
            // Email (truncar si es muy largo)
            $email = strlen($cliente['email']) > 25 ? 
                    substr($cliente['email'], 0, 22) . '...' : 
                    $cliente['email'];
            $this->Cell(45, 8, $email, 1, 0, 'L');
            
            $this->Cell(25, 8, $cliente['total_pedidos'], 1, 0, 'C');
            $this->Cell(30, 8, '$' . number_format($cliente['total_gastado'], 0, '.', ','), 1, 0, 'R');
            $this->Cell(20, 8, '$' . number_format($cliente['promedio_gasto'], 0, '.', ','), 1, 1, 'R');
            
            $contador++;
        }
        
        // Resumen al final
        $this->Ln(10);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 8, 'Resumen:', 0, 1, 'L');
        
        $total_pedidos = array_sum(array_column($datos, 'total_pedidos'));
        $total_ventas = array_sum(array_column($datos, 'total_gastado'));
        
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 6, '• Total pedidos: ' . number_format($total_pedidos, 0, '.', ','), 0, 1, 'L');
        $this->Cell(0, 6, '• Total ventas: $' . number_format($total_ventas, 0, '.', ','), 0, 1, 'L');
        $this->Cell(0, 6, '• Clientes en el ranking: ' . count($datos), 0, 1, 'L');
    }
    
    /**
     * Exporta reporte trimestral completo
     */
    public function exportarTrimestral($datos) {
        $periodo = $datos['periodo'];
        $this->titulo_reporte = "Reporte Trimestral Q{$periodo['trimestre']} {$periodo['año']}";
        
        // Página 1: Resumen Ejecutivo
        $this->AddPage();
        $this->resumenEjecutivo($datos['resumen'], $datos['crecimiento']);
        
        // Página 2: Productos Top
        $this->AddPage();
        $this->exportarProductos($datos['top_productos'], "Q{$periodo['trimestre']} {$periodo['año']}");
        
        // Página 3: Clientes Top
        $this->AddPage();
        $this->exportarClientes($datos['top_clientes'], "Q{$periodo['trimestre']} {$periodo['año']}");
    }
    
    private function resumenEjecutivo($resumen, $crecimiento) {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'Resumen Ejecutivo', 0, 1, 'C');
        $this->Ln(10);
        
        // Métricas principales
        $this->SetFont('Arial', 'B', 12);
        $this->SetFillColor(240, 240, 240);
        
        $this->Cell(95, 15, 'Ventas Totales', 1, 0, 'L', true);
        $this->Cell(95, 15, '$' . number_format($resumen['ventas_totales'] ?? 0, 0, '.', ','), 1, 1, 'R');
        
        $this->Cell(95, 15, 'Total Pedidos', 1, 0, 'L', true);
        $this->Cell(95, 15, number_format($resumen['total_pedidos'] ?? 0, 0, '.', ','), 1, 1, 'R');
        
        $this->Cell(95, 15, 'Promedio por Venta', 1, 0, 'L', true);
        $this->Cell(95, 15, '$' . number_format($resumen['promedio_venta'] ?? 0, 0, '.', ','), 1, 1, 'R');
        
        $this->Cell(95, 15, 'Clientes Únicos', 1, 0, 'L', true);
        $this->Cell(95, 15, number_format($resumen['clientes_unicos'] ?? 0, 0, '.', ','), 1, 1, 'R');
        
        // Crecimiento
        $this->Ln(10);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Crecimiento vs Trimestre Anterior', 0, 1, 'L');
        
        $this->SetFont('Arial', '', 10);
        $this->Cell(95, 10, 'Ventas:', 1, 0, 'L');
        $color = $crecimiento['ventas'] >= 0 ? [0, 128, 0] : [255, 0, 0];
        $this->SetTextColor($color[0], $color[1], $color[2]);
        $simbolo = $crecimiento['ventas'] >= 0 ? '+' : '';
        $this->Cell(95, 10, $simbolo . $crecimiento['ventas'] . '%', 1, 1, 'R');
        
        $this->SetTextColor(0, 0, 0);
        $this->Cell(95, 10, 'Pedidos:', 1, 0, 'L');
        $color = $crecimiento['pedidos'] >= 0 ? [0, 128, 0] : [255, 0, 0];
        $this->SetTextColor($color[0], $color[1], $color[2]);
        $simbolo = $crecimiento['pedidos'] >= 0 ? '+' : '';
        $this->Cell(95, 10, $simbolo . $crecimiento['pedidos'] . '%', 1, 1, 'R');
        
        $this->SetTextColor(0, 0, 0);
    }
    
    /**
     * Exporta reporte de empleados a PDF
     */
    public function exportarEmpleados($datos, $periodo = '') {
        $this->titulo_reporte = 'Reporte de Ventas por Empleado' . ($periodo ? " - $periodo" : '');
        $this->AddPage();
        
        if (empty($datos)) {
            $this->SetFont('Arial', '', 12);
            $this->Cell(0, 10, 'No se encontraron datos de empleados para mostrar', 0, 1, 'C');
            return;
        }
        
        // Título de la tabla
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Performance de Ventas por Empleado', 0, 1, 'C');
        $this->Ln(5);
        
        // Headers de la tabla
        $this->SetFont('Arial', 'B', 9);
        $this->SetFillColor(230, 230, 230);
        
        $this->Cell(20, 8, '#', 1, 0, 'C', true);
        $this->Cell(60, 8, 'Empleado', 1, 0, 'C', true);
        $this->Cell(25, 8, 'Ventas', 1, 0, 'C', true);
        $this->Cell(35, 8, 'Total $', 1, 0, 'C', true);
        $this->Cell(30, 8, 'Promedio $', 1, 0, 'C', true);
        $this->Cell(20, 8, '%', 1, 1, 'C', true);
        
        // Calcular total para porcentajes
        $totalVentas = array_sum(array_column($datos, 'ventas_totales'));
        
        // Datos
        $this->SetFont('Arial', '', 8);
        $contador = 1;
        
        foreach ($datos as $empleado) {
            if ($this->GetY() > 250) {
                $this->AddPage();
                // Repetir headers
                $this->SetFont('Arial', 'B', 9);
                $this->SetFillColor(230, 230, 230);
                $this->Cell(20, 8, '#', 1, 0, 'C', true);
                $this->Cell(60, 8, 'Empleado', 1, 0, 'C', true);
                $this->Cell(25, 8, 'Ventas', 1, 0, 'C', true);
                $this->Cell(35, 8, 'Total $', 1, 0, 'C', true);
                $this->Cell(30, 8, 'Promedio $', 1, 0, 'C', true);
                $this->Cell(20, 8, '%', 1, 1, 'C', true);
                $this->SetFont('Arial', '', 8);
            }
            
            $participacion = $totalVentas > 0 ? round(($empleado['ventas_totales'] / $totalVentas) * 100, 1) : 0;
            
            $this->Cell(20, 6, $contador, 1, 0, 'C');
            $this->Cell(60, 6, substr($empleado['empleado'] ?? 'N/A', 0, 30), 1, 0, 'L');
            $this->Cell(25, 6, $empleado['total_ventas'] ?? 0, 1, 0, 'C');
            $this->Cell(35, 6, '$' . number_format($empleado['ventas_totales'] ?? 0, 0, '.', ','), 1, 0, 'R');
            $this->Cell(30, 6, '$' . number_format($empleado['promedio_venta'] ?? 0, 0, '.', ','), 1, 0, 'R');
            $this->Cell(20, 6, $participacion . '%', 1, 1, 'R');
            
            $contador++;
        }
    }
    
    /**
     * Exporta reporte de estados a PDF
     */
    public function exportarEstados($datos, $periodo = '') {
        $this->titulo_reporte = 'Reporte de Estados de Pedidos' . ($periodo ? " - $periodo" : '');
        $this->AddPage();
        
        if (empty($datos)) {
            $this->SetFont('Arial', '', 12);
            $this->Cell(0, 10, 'No se encontraron datos de estados para mostrar', 0, 1, 'C');
            return;
        }
        
        // Título de la tabla
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Distribución de Estados de Pedidos', 0, 1, 'C');
        $this->Ln(5);
        
        // Headers de la tabla
        $this->SetFont('Arial', 'B', 9);
        $this->SetFillColor(230, 230, 230);
        
        $this->Cell(50, 8, 'Estado', 1, 0, 'C', true);
        $this->Cell(30, 8, 'Cantidad', 1, 0, 'C', true);
        $this->Cell(40, 8, 'Valor Total $', 1, 0, 'C', true);
        $this->Cell(35, 8, 'Promedio $', 1, 0, 'C', true);
        $this->Cell(35, 8, 'Porcentaje', 1, 1, 'C', true);
        
        // Calcular total para porcentajes
        $totalPedidos = array_sum(array_column($datos, 'cantidad_pedidos'));
        
        // Datos
        $this->SetFont('Arial', '', 9);
        
        foreach ($datos as $estado) {
            $porcentaje = $totalPedidos > 0 ? round(($estado['cantidad_pedidos'] / $totalPedidos) * 100, 1) : 0;
            
            $this->Cell(50, 7, ucfirst($estado['estado'] ?? 'N/A'), 1, 0, 'L');
            $this->Cell(30, 7, $estado['cantidad_pedidos'] ?? 0, 1, 0, 'C');
            $this->Cell(40, 7, '$' . number_format($estado['valor_total'] ?? 0, 0, '.', ','), 1, 0, 'R');
            $this->Cell(35, 7, '$' . number_format($estado['promedio_valor'] ?? 0, 0, '.', ','), 1, 0, 'R');
            $this->Cell(35, 7, $porcentaje . '%', 1, 1, 'R');
        }
    }
    
    /**
     * Exporta reporte de ventas diarias a PDF
     */
    public function exportarVentasDiarias($datos, $periodo = '') {
        $this->titulo_reporte = 'Reporte de Ventas Diarias' . ($periodo ? " - $periodo" : '');
        $this->AddPage();
        
        if (empty($datos)) {
            $this->SetFont('Arial', '', 12);
            $this->Cell(0, 10, 'No se encontraron datos de ventas diarias para mostrar', 0, 1, 'C');
            return;
        }
        
        // Título de la tabla
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Análisis de Ventas por Día', 0, 1, 'C');
        $this->Ln(5);
        
        // Headers de la tabla
        $this->SetFont('Arial', 'B', 8);
        $this->SetFillColor(230, 230, 230);
        
        $this->Cell(25, 8, 'Fecha', 1, 0, 'C', true);
        $this->Cell(20, 8, 'Pedidos', 1, 0, 'C', true);
        $this->Cell(30, 8, 'Ventas $', 1, 0, 'C', true);
        $this->Cell(25, 8, 'Promedio $', 1, 0, 'C', true);
        $this->Cell(20, 8, 'Clientes', 1, 0, 'C', true);
        $this->Cell(25, 8, 'Mín $', 1, 0, 'C', true);
        $this->Cell(25, 8, 'Máx $', 1, 1, 'C', true);
        
        // Datos
        $this->SetFont('Arial', '', 7);
        
        foreach ($datos as $dia) {
            if ($this->GetY() > 250) {
                $this->AddPage();
                // Repetir headers
                $this->SetFont('Arial', 'B', 8);
                $this->SetFillColor(230, 230, 230);
                $this->Cell(25, 8, 'Fecha', 1, 0, 'C', true);
                $this->Cell(20, 8, 'Pedidos', 1, 0, 'C', true);
                $this->Cell(30, 8, 'Ventas $', 1, 0, 'C', true);
                $this->Cell(25, 8, 'Promedio $', 1, 0, 'C', true);
                $this->Cell(20, 8, 'Clientes', 1, 0, 'C', true);
                $this->Cell(25, 8, 'Mín $', 1, 0, 'C', true);
                $this->Cell(25, 8, 'Máx $', 1, 1, 'C', true);
                $this->SetFont('Arial', '', 7);
            }
            
            $this->Cell(25, 6, date('d/m/Y', strtotime($dia['fecha'])), 1, 0, 'C');
            $this->Cell(20, 6, $dia['total_pedidos'] ?? 0, 1, 0, 'C');
            $this->Cell(30, 6, '$' . number_format($dia['ventas_totales'] ?? 0, 0, '.', ','), 1, 0, 'R');
            $this->Cell(25, 6, '$' . number_format($dia['promedio_venta'] ?? 0, 0, '.', ','), 1, 0, 'R');
            $this->Cell(20, 6, $dia['clientes_unicos'] ?? 0, 1, 0, 'C');
            $this->Cell(25, 6, '$' . number_format($dia['venta_minima'] ?? 0, 0, '.', ','), 1, 0, 'R');
            $this->Cell(25, 6, '$' . number_format($dia['venta_maxima'] ?? 0, 0, '.', ','), 1, 1, 'R');
        }
    }
    
    /**
     * Exporta reporte genérico a PDF
     */
    public function exportarGenerico($datos, $tipo, $periodo = '') {
        $this->titulo_reporte = 'Reporte ' . ucfirst($tipo) . ($periodo ? " - $periodo" : '');
        $this->AddPage();
        
        if (empty($datos)) {
            $this->SetFont('Arial', '', 12);
            $this->Cell(0, 10, 'No se encontraron datos para mostrar', 0, 1, 'C');
            return;
        }
        
        // Título de la tabla
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Reporte ' . ucfirst($tipo), 0, 1, 'C');
        $this->Ln(5);
        
        // Obtener las claves del primer elemento para headers
        $headers = array_keys($datos[0]);
        $numCols = count($headers);
        $colWidth = $numCols > 0 ? 190 / $numCols : 30;
        
        // Headers de la tabla
        $this->SetFont('Arial', 'B', 8);
        $this->SetFillColor(230, 230, 230);
        
        foreach ($headers as $header) {
            $this->Cell($colWidth, 8, ucfirst(str_replace('_', ' ', $header)), 1, 0, 'C', true);
        }
        $this->Ln();
        
        // Datos
        $this->SetFont('Arial', '', 7);
        
        foreach ($datos as $fila) {
            if ($this->GetY() > 250) {
                $this->AddPage();
                // Repetir headers
                $this->SetFont('Arial', 'B', 8);
                $this->SetFillColor(230, 230, 230);
                foreach ($headers as $header) {
                    $this->Cell($colWidth, 8, ucfirst(str_replace('_', ' ', $header)), 1, 0, 'C', true);
                }
                $this->Ln();
                $this->SetFont('Arial', '', 7);
            }
            
            foreach ($fila as $valor) {
                $valor_mostrar = is_numeric($valor) && strpos($valor, '.') !== false 
                    ? '$' . number_format($valor, 0, '.', ',') 
                    : substr($valor, 0, 20);
                $this->Cell($colWidth, 6, $valor_mostrar, 1, 0, 'C');
            }
            $this->Ln();
        }
    }
    
    /**
     * Método estático para generar y descargar PDF
     */
    public static function generar($tipo, $datos, $titulo = null, $periodo = '') {
        try {
            $pdf = new self($titulo ?: "Reporte " . ucfirst($tipo));
            
            switch ($tipo) {
                case 'productos':
                    $pdf->exportarProductos($datos, $periodo);
                    break;
                case 'clientes':
                    $pdf->exportarClientes($datos, $periodo);
                    break;
                case 'empleados':
                    $pdf->exportarEmpleados($datos, $periodo);
                    break;
                case 'estados':
                    $pdf->exportarEstados($datos, $periodo);
                    break;
                case 'ventas_diarias':
                    $pdf->exportarVentasDiarias($datos, $periodo);
                    break;
                case 'trimestral':
                    $pdf->exportarTrimestral($datos);
                    break;
                default:
                    $pdf->exportarGenerico($datos, $tipo, $periodo);
            }
            
            $nombre_archivo = "reporte_" . $tipo . "_" . date('Y-m-d_H-i-s') . ".pdf";
            
            // Configurar headers para descarga
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Expires: 0');
            
            $pdf->Output('D', $nombre_archivo);
            exit;
        } catch (Exception $e) {
            error_log("Error generando PDF: " . $e->getMessage());
            throw new Exception("Error al generar el PDF: " . $e->getMessage());
        }
    }
}
