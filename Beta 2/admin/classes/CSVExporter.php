<?php
/**
 * Exportador CSV - Clase de Utilidades
 * 
 * Genera archivos CSV con formato estándar compatible con Excel
 * Incluye BOM UTF-8 y separadores configurables
 */

class CSVExporter {
    
    /**
     * Exporta datos a formato CSV
     */
    public static function exportar($datos, $tipo, $nombre_archivo = null) {
        if (empty($datos)) {
            throw new Exception("No hay datos para exportar");
        }
        
        $nombre_archivo = $nombre_archivo ?: "reporte_" . $tipo . "_" . date('Y-m-d_H-i-s') . ".csv";
        
        // Configurar headers para descarga
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        
        // Crear archivo CSV en memoria
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8 (para que Excel reconozca acentos)
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Generar headers según el tipo de reporte
        $headers = self::getHeaders($tipo, $datos);
        fputcsv($output, $headers, ';');
        
        // Escribir datos
        foreach ($datos as $fila) {
            $fila_csv = self::formatearFila($fila, $tipo);
            fputcsv($output, $fila_csv, ';');
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Obtiene los headers según el tipo de reporte
     */
    private static function getHeaders($tipo, $datos = []) {
        switch ($tipo) {
            case 'productos':
                return [
                    'Código',
                    'Descripción', 
                    'Categoría',
                    'Cantidad Vendida',
                    'Ingresos Totales',
                    'Precio Promedio',
                    'Pedidos Incluidos',
                    'Clientes Distintos'
                ];
            
            case 'clientes':
                return [
                    'Nombre',
                    'Email',
                    'Teléfono',
                    'Total Pedidos',
                    'Total Gastado',
                    'Promedio Gasto',
                    'Última Compra'
                ];
            
            case 'ventas_diarias':
                return [
                    'Fecha',
                    'Total Pedidos',
                    'Ventas Totales',
                    'Promedio Venta',
                    'Clientes Únicos'
                ];
            
            case 'estados':
                return [
                    'Estado',
                    'Cantidad Pedidos',
                    'Valor Total',
                    'Promedio Valor'
                ];
            
            case 'categorias':
                return [
                    'Categoría',
                    'Productos Vendidos',
                    'Cantidad Total',
                    'Ingresos Categoría',
                    'Precio Promedio'
                ];
            
            case 'empleados':
                return [
                    'Empleado',
                    'Total Ventas',
                    'Ventas Totales',
                    'Promedio Venta'
                ];
            
            case 'ventas_diarias':
                return [
                    'Fecha',
                    'Total Pedidos',
                    'Ventas Totales',
                    'Promedio Venta',
                    'Clientes Únicos',
                    'Venta Mínima',
                    'Venta Máxima'
                ];
            
            case 'trimestral':
                return [
                    'Período',
                    'Mes',
                    'Total Pedidos',
                    'Ventas Totales',
                    'Promedio Venta',
                    'Clientes Únicos'
                ];
            
            default:
                // Headers genéricos basados en las claves del primer elemento
                return array_keys($datos[0] ?? []);
        }
    }
    
    /**
     * Formatea una fila según el tipo de reporte
     */
    private static function formatearFila($fila, $tipo) {
        switch ($tipo) {
            case 'productos':
                return [
                    $fila['codigo'] ?? '',
                    $fila['descripcion'] ?? '',
                    $fila['categoria'] ?? 'Sin categoría',
                    $fila['cantidad_vendida'] ?? 0,
                    '$' . number_format($fila['ingresos_totales'] ?? 0, 0, '.', ','),
                    '$' . number_format($fila['precio_promedio'] ?? 0, 0, '.', ','),
                    $fila['pedidos_incluidos'] ?? 0,
                    $fila['clientes_distintos'] ?? 0
                ];
            
            case 'clientes':
                return [
                    $fila['nombre'] ?? '',
                    $fila['email'] ?? '',
                    $fila['telefono'] ?? '',
                    $fila['total_pedidos'] ?? 0,
                    '$' . number_format($fila['total_gastado'] ?? 0, 0, '.', ','),
                    '$' . number_format($fila['promedio_gasto'] ?? 0, 0, '.', ','),
                    $fila['ultima_compra'] ?? ''
                ];
            
            case 'ventas_diarias':
                return [
                    $fila['fecha'] ?? '',
                    $fila['total_pedidos'] ?? 0,
                    '$' . number_format($fila['ventas_totales'] ?? 0, 0, '.', ','),
                    '$' . number_format($fila['promedio_venta'] ?? 0, 0, '.', ','),
                    $fila['clientes_unicos'] ?? 0,
                    '$' . number_format($fila['venta_minima'] ?? 0, 0, '.', ','),
                    '$' . number_format($fila['venta_maxima'] ?? 0, 0, '.', ',')
                ];
            
            case 'estados':
                return [
                    $fila['estado'] ?? '',
                    $fila['cantidad_pedidos'] ?? 0,
                    '$' . number_format($fila['valor_total'] ?? 0, 0, '.', ','),
                    '$' . number_format($fila['promedio_valor'] ?? 0, 0, '.', ',')
                ];
            
            case 'categorias':
                return [
                    $fila['categoria'] ?? '',
                    $fila['productos_vendidos'] ?? 0,
                    $fila['cantidad_total'] ?? 0,
                    '$' . number_format($fila['ingresos_categoria'] ?? 0, 0, '.', ','),
                    '$' . number_format($fila['precio_promedio'] ?? 0, 0, '.', ',')
                ];
            
            case 'empleados':
                return [
                    $fila['empleado'] ?? '',
                    $fila['total_ventas'] ?? 0,
                    '$' . number_format($fila['ventas_totales'] ?? 0, 0, '.', ','),
                    '$' . number_format($fila['promedio_venta'] ?? 0, 0, '.', ',')
                ];
            
            case 'trimestral':
                return [
                    $fila['periodo'] ?? '',
                    $fila['nombre_mes'] ?? $fila['mes'] ?? '',
                    $fila['total_pedidos'] ?? 0,
                    '$' . number_format($fila['ventas_totales'] ?? 0, 0, '.', ','),
                    '$' . number_format($fila['promedio_venta'] ?? 0, 0, '.', ','),
                    $fila['clientes_unicos'] ?? 0
                ];
            
            default:
                // Formateo genérico
                $fila_formateada = [];
                foreach ($fila as $valor) {
                    if (is_numeric($valor) && strpos($valor, '.') !== false) {
                        $fila_formateada[] = '$' . number_format($valor, 0, '.', ',');
                    } else {
                        $fila_formateada[] = $valor;
                    }
                }
                return $fila_formateada;
        }
    }
}
