<?php
/**
 * Gestor de Reportes - Clase Principal
 * 
 * Maneja toda la lógica de negocio para generación de reportes
 * Incluye consultas optimizadas y análisis estadísticos
 */

require_once __DIR__ . '/../../config/database.php';

class ReporteManager {
    private $db;
    
    public function __construct() {
        $this->db = getConnection();
    }
    
    // Productos más vendidos
    public function productosMasVendidos($fecha_inicio, $fecha_fin, $limite = 10) {
        try {
            $sql = "SELECT 
                        pr.id as producto_id,
                        pr.codigo,
                        pr.descripcion,
                        pr.precio as precio_actual,
                        gp.nombre as categoria,
                        SUM(dp.cantidad) as cantidad_vendida,
                        SUM(dp.subtotal) as ingresos_totales,
                        AVG(dp.precio_unitario) as precio_promedio,
                        COUNT(DISTINCT p.id) as pedidos_incluidos,
                        COUNT(DISTINCT p.cliente_id) as clientes_distintos
                    FROM detalle_pedidos dp
                    INNER JOIN productos pr ON dp.producto_id = pr.id
                    INNER JOIN pedidos p ON dp.pedido_id = p.id
                    LEFT JOIN grupos_productos gp ON pr.grupo_id = gp.id
                    WHERE p.fecha_pedido BETWEEN ? AND ?
                    AND p.estado NOT IN ('cancelado', 'borrador')
                    GROUP BY pr.id, pr.codigo, pr.descripcion, pr.precio, gp.nombre
                    ORDER BY cantidad_vendida DESC, ingresos_totales DESC
                    LIMIT ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$fecha_inicio, $fecha_fin, intval($limite)]);
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Agregar información adicional
            foreach ($resultado as &$producto) {
                $producto['participacion_ingresos'] = 0;
                $producto['ranking'] = 0;
                $producto['stock_actual'] = 'N/A';
                $producto['estado_stock'] = 'no_disponible';
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            error_log("Error en productosMasVendidos: " . $e->getMessage());
            return [];
        }
    }
    
    // Clientes con más compras
    public function clientesTopCompradores($fecha_inicio, $fecha_fin, $limite = 10) {
        $sql = "SELECT 
                    c.nombre,
                    c.email,
                    c.telefono,
                    COUNT(*) as total_pedidos,
                    SUM(p.total) as total_gastado,
                    AVG(p.total) as promedio_gasto,
                    MAX(p.fecha_pedido) as ultima_compra
                FROM pedidos p
                INNER JOIN clientes c ON p.cliente_id = c.id
                WHERE p.fecha_pedido BETWEEN ? AND ?
                AND p.estado NOT IN ('cancelado', 'borrador')
                GROUP BY c.id, c.nombre, c.email, c.telefono
                ORDER BY total_gastado DESC, total_pedidos DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$fecha_inicio, $fecha_fin, intval($limite)]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Estadísticas generales
    public function estadisticasGenerales($fecha_inicio, $fecha_fin) {
        $sql = "SELECT 
                    COUNT(*) as total_pedidos,
                    SUM(total) as ventas_totales,
                    AVG(total) as promedio_venta,
                    COUNT(DISTINCT cliente_id) as clientes_unicos,
                    MIN(fecha_pedido) as primera_venta,
                    MAX(fecha_pedido) as ultima_venta
                FROM pedidos 
                WHERE fecha_pedido BETWEEN ? AND ?
                AND estado NOT IN ('cancelado', 'borrador')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$fecha_inicio, $fecha_fin]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Resumen de estados de pedidos
    public function resumenEstadoPedidos($fecha_inicio, $fecha_fin) {
        $sql = "SELECT 
                    estado,
                    COUNT(*) as cantidad_pedidos,
                    SUM(total) as valor_total,
                    AVG(total) as promedio_valor
                FROM pedidos 
                WHERE fecha_pedido BETWEEN ? AND ?
                GROUP BY estado
                ORDER BY cantidad_pedidos DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$fecha_inicio, $fecha_fin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Ventas por empleado - CORREGIDO
    public function ventasPorEmpleado($fecha_inicio, $fecha_fin) {
        try {
            $sql = "SELECT 
                        e.nombre as empleado,
                        e.id as empleado_id,
                        COUNT(DISTINCT p.id) as total_ventas,
                        SUM(p.total) as ventas_totales,
                        AVG(p.total) as promedio_venta,
                        COUNT(DISTINCT p.cliente_id) as clientes_atendidos,
                        MIN(p.fecha_pedido) as primera_venta,
                        MAX(p.fecha_pedido) as ultima_venta
                    FROM pedidos p
                    INNER JOIN empleados e ON p.empleado_id = e.id
                    WHERE p.fecha_pedido BETWEEN ? AND ?
                    AND p.estado NOT IN ('cancelado', 'borrador')
                    GROUP BY e.id, e.nombre
                    ORDER BY ventas_totales DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$fecha_inicio, $fecha_fin]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en ventasPorEmpleado: " . $e->getMessage());
            return [];
        }
    }
    
    // Estados de pedidos - CORREGIDO
    public function estadosPedidos($fecha_inicio, $fecha_fin) {
        try {
            $sql = "SELECT 
                        estado,
                        COUNT(*) as cantidad_pedidos,
                        SUM(total) as valor_total,
                        AVG(total) as promedio_valor,
                        MIN(fecha_pedido) as fecha_primer_pedido,
                        MAX(fecha_pedido) as fecha_ultimo_pedido
                    FROM pedidos 
                    WHERE fecha_pedido BETWEEN ? AND ?
                    GROUP BY estado
                    ORDER BY cantidad_pedidos DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$fecha_inicio, $fecha_fin]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en estadosPedidos: " . $e->getMessage());
            return [];
        }
    }
    
    // Reporte trimestral completo
    public function reporteTrimestral($año, $trimestre) {
        $trimestres = [
            1 => ['01-01', '03-31'],
            2 => ['04-01', '06-30'], 
            3 => ['07-01', '09-30'],
            4 => ['10-01', '12-31']
        ];
        
        if (!isset($trimestres[$trimestre])) {
            throw new Exception("Trimestre inválido");
        }
        
        $fecha_inicio = $año . '-' . $trimestres[$trimestre][0];
        $fecha_fin = $año . '-' . $trimestres[$trimestre][1];
        
        $reporte = [];
        
        // 1. Resumen ejecutivo
        $reporte['resumen'] = $this->estadisticasGenerales($fecha_inicio, $fecha_fin);
        
        // 2. Ventas mensuales del trimestre
        $sql = "SELECT 
                    MONTH(p.fecha_pedido) as mes,
                    MONTHNAME(p.fecha_pedido) as nombre_mes,
                    COUNT(*) as total_pedidos,
                    SUM(p.total) as ventas_totales,
                    AVG(p.total) as promedio_venta,
                    COUNT(DISTINCT p.cliente_id) as clientes_unicos
                FROM pedidos p 
                WHERE p.fecha_pedido BETWEEN ? AND ?
                AND p.estado NOT IN ('cancelado', 'borrador')
                GROUP BY MONTH(p.fecha_pedido), MONTHNAME(p.fecha_pedido)
                ORDER BY mes";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$fecha_inicio, $fecha_fin]);
        $reporte['ventas_mensuales'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 3. Top 10 productos del trimestre
        $reporte['top_productos'] = $this->productosMasVendidos($fecha_inicio, $fecha_fin, 10);
        
        // 4. Top 10 clientes del trimestre
        $reporte['top_clientes'] = $this->clientesTopCompradores($fecha_inicio, $fecha_fin, 10);
        
        // 5. Análisis de estados de pedidos
        $reporte['estados_pedidos'] = $this->estadosPedidos($fecha_inicio, $fecha_fin);
        
        // 6. Performance de empleados
        $reporte['performance_empleados'] = $this->ventasPorEmpleado($fecha_inicio, $fecha_fin);
        
        // 7. Análisis de categorías de productos
        $sql = "SELECT 
                    gp.nombre as categoria,
                    COUNT(DISTINCT pr.id) as productos_vendidos,
                    SUM(dp.cantidad) as cantidad_total,
                    SUM(dp.subtotal) as ingresos_categoria,
                    AVG(dp.precio_unitario) as precio_promedio
                FROM detalle_pedidos dp
                INNER JOIN productos pr ON dp.producto_id = pr.id
                INNER JOIN grupos_productos gp ON pr.grupo_id = gp.id
                INNER JOIN pedidos p ON dp.pedido_id = p.id
                WHERE p.fecha_pedido BETWEEN ? AND ?
                AND p.estado NOT IN ('cancelado', 'borrador')
                GROUP BY gp.id, gp.nombre
                ORDER BY ingresos_categoria DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$fecha_inicio, $fecha_fin]);
        $reporte['analisis_categorias'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 8. Tendencias diarias promedio
        $sql = "SELECT 
                    DAYNAME(p.fecha_pedido) as dia_semana,
                    DAYOFWEEK(p.fecha_pedido) as dia_numero,
                    COUNT(*) as total_pedidos,
                    SUM(p.total) as ventas_totales,
                    AVG(p.total) as promedio_venta,
                    COUNT(DISTINCT p.cliente_id) as clientes_unicos
                FROM pedidos p 
                WHERE p.fecha_pedido BETWEEN ? AND ?
                AND p.estado NOT IN ('cancelado', 'borrador')
                GROUP BY DAYOFWEEK(p.fecha_pedido), DAYNAME(p.fecha_pedido)
                ORDER BY dia_numero";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$fecha_inicio, $fecha_fin]);
        $reporte['tendencias_diarias'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 9. Análisis de crecimiento comparativo
        $trimestre_anterior = $trimestre - 1;
        $año_anterior = $año;
        if ($trimestre_anterior < 1) {
            $trimestre_anterior = 4;
            $año_anterior = $año - 1;
        }
        
        $fecha_inicio_anterior = $año_anterior . '-' . $trimestres[$trimestre_anterior][0];
        $fecha_fin_anterior = $año_anterior . '-' . $trimestres[$trimestre_anterior][1];
        
        $sql_anterior = "SELECT 
                    COUNT(*) as pedidos_anteriores,
                    SUM(total) as ventas_anteriores,
                    AVG(total) as promedio_anterior,
                    COUNT(DISTINCT cliente_id) as clientes_anteriores
                FROM pedidos 
                WHERE fecha_pedido BETWEEN ? AND ?
                AND estado NOT IN ('cancelado', 'borrador')";
        
        $stmt = $this->db->prepare($sql_anterior);
        $stmt->execute([$fecha_inicio_anterior, $fecha_fin_anterior]);
        $anterior = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $actual = $reporte['resumen'];
        $reporte['crecimiento'] = [
            'pedidos' => $this->calcularCrecimiento($actual['total_pedidos'] ?? 0, $anterior['pedidos_anteriores'] ?? 0),
            'ventas' => $this->calcularCrecimiento($actual['ventas_totales'] ?? 0, $anterior['ventas_anteriores'] ?? 0),
            'promedio' => $this->calcularCrecimiento($actual['promedio_venta'] ?? 0, $anterior['promedio_anterior'] ?? 0),
            'clientes' => $this->calcularCrecimiento($actual['clientes_unicos'] ?? 0, $anterior['clientes_anteriores'] ?? 0)
        ];
        
        $reporte['periodo'] = [
            'año' => $año,
            'trimestre' => $trimestre,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'dias_transcurridos' => (strtotime($fecha_fin) - strtotime($fecha_inicio)) / (60 * 60 * 24) + 1
        ];
        
        return $reporte;
    }
    
    // Función auxiliar para calcular crecimiento porcentual
    private function calcularCrecimiento($actual, $anterior) {
        if ($anterior == 0) return $actual > 0 ? 100 : 0;
        return round((($actual - $anterior) / $anterior) * 100, 2);
    }
    
    /**
     * Ventas por día en un período específico
     */
    public function ventasDiarias($fecha_inicio, $fecha_fin) {
        try {
            $sql = "SELECT 
                        DATE(fecha_pedido) as fecha,
                        COUNT(*) as total_pedidos,
                        SUM(total) as ventas_totales,
                        AVG(total) as promedio_venta,
                        COUNT(DISTINCT cliente_id) as clientes_unicos,
                        MIN(total) as venta_minima,
                        MAX(total) as venta_maxima
                    FROM pedidos 
                    WHERE fecha_pedido BETWEEN ? AND ?
                    AND estado NOT IN ('cancelado', 'borrador')
                    GROUP BY DATE(fecha_pedido)
                    ORDER BY fecha DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$fecha_inicio, $fecha_fin]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en ventasDiarias: " . $e->getMessage());
            return [];
        }
    }
}
