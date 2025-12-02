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
     * Exporta un CSV 'raw' dado un array de encabezados y filas.
     * Usar cuando los datos no sigan las claves esperadas por getHeaders/formatearFila.
     */
    public static function rawExport(array $headers, array $rows, $nombre_archivo = null) {
        $nombre_archivo = $nombre_archivo ?: 'export_' . date('Y-m-d_H-i-s') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        // BOM UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, $headers, ';');

        foreach ($rows as $row) {
            // Formatear cada valor de la fila según su tipo
            $outRow = [];
            foreach ($row as $val) {
                if (is_null($val)) {
                    $outRow[] = '';
                } elseif (is_numeric($val)) {
                    // Si es entero, mantener como entero; si tiene decimales, dos decimales con coma
                    if (floor($val) == $val) {
                        $outRow[] = (string) intval($val);
                    } else {
                        $outRow[] = number_format((float)$val, 2, ',', '');
                    }
                } elseif (self::looksLikeDate($val)) {
                    $outRow[] = self::formatDateForCSV($val);
                } else {
                    $outRow[] = $val;
                }
            }
            fputcsv($output, $outRow, ';');
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
                    isset($fila['cantidad_vendida']) ? intval($fila['cantidad_vendida']) : 0,
                    isset($fila['ingresos_totales']) ? number_format((float)$fila['ingresos_totales'], 2, ',', '') : '',
                    isset($fila['precio_promedio']) ? number_format((float)$fila['precio_promedio'], 2, ',', '') : '',
                    $fila['pedidos_incluidos'] ?? 0,
                    $fila['clientes_distintos'] ?? 0
                ];

            case 'clientes':
                return [
                    $fila['nombre'] ?? '',
                    $fila['email'] ?? '',
                    $fila['telefono'] ?? '',
                    $fila['total_pedidos'] ?? 0,
                    isset($fila['total_gastado']) ? number_format((float)$fila['total_gastado'], 2, ',', '') : '',
                    isset($fila['promedio_gasto']) ? number_format((float)$fila['promedio_gasto'], 2, ',', '') : '',
                    $fila['ultima_compra'] ?? ''
                ];

            case 'ventas_diarias':
                return [
                    $fila['fecha'] ?? '',
                    $fila['total_pedidos'] ?? 0,
                    isset($fila['ventas_totales']) ? number_format((float)$fila['ventas_totales'], 2, ',', '') : '',
                    isset($fila['promedio_venta']) ? number_format((float)$fila['promedio_venta'], 2, ',', '') : '',
                    $fila['clientes_unicos'] ?? 0,
                    isset($fila['venta_minima']) ? number_format((float)$fila['venta_minima'], 2, ',', '') : '',
                    isset($fila['venta_maxima']) ? number_format((float)$fila['venta_maxima'], 2, ',', '') : ''
                ];

            case 'estados':
                return [
                    $fila['estado'] ?? '',
                    $fila['cantidad_pedidos'] ?? 0,
                    isset($fila['valor_total']) ? number_format((float)$fila['valor_total'], 2, ',', '') : '',
                    isset($fila['promedio_valor']) ? number_format((float)$fila['promedio_valor'], 2, ',', '') : ''
                ];

            case 'categorias':
                return [
                    $fila['categoria'] ?? '',
                    $fila['productos_vendidos'] ?? 0,
                    $fila['cantidad_total'] ?? 0,
                    isset($fila['ingresos_categoria']) ? number_format((float)$fila['ingresos_categoria'], 2, ',', '') : '',
                    isset($fila['precio_promedio']) ? number_format((float)$fila['precio_promedio'], 2, ',', '') : ''
                ];

            case 'empleados':
                return [
                    $fila['empleado'] ?? '',
                    $fila['total_ventas'] ?? 0,
                    isset($fila['ventas_totales']) ? number_format((float)$fila['ventas_totales'], 2, ',', '') : '',
                    isset($fila['promedio_venta']) ? number_format((float)$fila['promedio_venta'], 2, ',', '') : ''
                ];

            case 'trimestral':
                return [
                    $fila['periodo'] ?? '',
                    $fila['nombre_mes'] ?? $fila['mes'] ?? '',
                    $fila['total_pedidos'] ?? 0,
                    isset($fila['ventas_totales']) ? number_format((float)$fila['ventas_totales'], 2, ',', '') : '',
                    isset($fila['promedio_venta']) ? number_format((float)$fila['promedio_venta'], 2, ',', '') : '',
                    $fila['clientes_unicos'] ?? 0
                ];

            default:
                // Formateo genérico: números con 2 decimales (coma decimal), fechas en d/m/Y H:i si se detectan
                $fila_formateada = [];
                foreach ($fila as $clave => $valor) {
                    if (is_null($valor)) {
                        $fila_formateada[] = '';
                    } elseif (is_numeric($valor)) {
                        // Enteros sin decimales
                        if (floor($valor) == $valor) {
                            $fila_formateada[] = (string) intval($valor);
                        } else {
                            $fila_formateada[] = number_format((float)$valor, 2, ',', '');
                        }
                    } elseif (self::looksLikeDate($valor)) {
                        $fila_formateada[] = self::formatDateForCSV($valor);
                    } else {
                        $fila_formateada[] = $valor;
                    }
                }
                return $fila_formateada;
        }
    }

    /**
     * Detecta si un valor parece una fecha ISO o datetime
     */
    private static function looksLikeDate($val) {
        if (!is_string($val)) return false;
        // YYYY-MM-DD o YYYY-MM-DD HH:MM:SS
        return preg_match('/^\d{4}-\d{2}-\d{2}(?:[ T]\d{2}:\d{2}(:\d{2})?)?$/', $val);
    }

    /**
     * Formatea una fecha para CSV: d/m/Y H:i si tiene tiempo, sino d/m/Y
     */
    private static function formatDateForCSV($val) {
        $ts = strtotime($val);
        if ($ts === false) return $val;
        // Si contiene hora distinta de 00:00:00, incluir hora
        if (preg_match('/\d{2}:\d{2}:\d{2}/', $val)) {
            return date('d/m/Y H:i', $ts);
        }
        return date('d/m/Y', $ts);
    }
}
