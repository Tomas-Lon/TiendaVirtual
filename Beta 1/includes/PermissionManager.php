<?php
/**
 * Sistema de Permisos y Badges Dinámicos
 */

class PermissionManager {
    
    private static $pdo = null;
    
    private static function getDB() {
        if (self::$pdo === null) {
            require_once __DIR__ . '/../config/database.php';
            self::$pdo = getConnection();
        }
        return self::$pdo;
    }
    
    /**
     * Verifica si el usuario tiene permisos para acceder a una funcionalidad
     */
    public static function hasPermission($permission) {
        $userType = $_SESSION['tipo'] ?? '';
        $userRole = $_SESSION['cargo'] ?? '';
        
        // Definir permisos por tipo y cargo
        $permissions = [
            'admin' => ['admin'],
            'vendedor' => ['empleado'],
            'repartidor' => ['empleado'],
            'cliente' => ['cliente']
        ];
        
        if ($userType === 'empleado') {
            return in_array($userRole, $permissions[$permission] ?? []);
        }
        
        return in_array($userType, $permissions[$permission] ?? []);
    }
    
    /**
     * Obtiene badges dinámicos para los elementos del menú
     */
    public static function getDynamicBadges() {
        $badges = [];
        
        try {
            $pdo = self::getDB();
            $userType = $_SESSION['tipo'] ?? '';
            $userId = $_SESSION['cliente_id'] ?? $_SESSION['empleado_id'] ?? 0;
            
            switch ($userType) {
                case 'empleado':
                    if ($_SESSION['cargo'] === 'admin') {
                        // Pedidos pendientes
                        $stmt = $pdo->query("SELECT COUNT(*) as count FROM pedidos WHERE estado IN ('borrador', 'confirmado')");
                        $badges['pending_orders'] = $stmt->fetch()['count'];
                        
                        // Envíos pendientes
                        $stmt = $pdo->query("SELECT COUNT(*) as count FROM envios WHERE estado = 'pendiente'");
                        $badges['pending_shipments'] = $stmt->fetch()['count'];
                    } elseif ($_SESSION['cargo'] === 'repartidor') {
                        // Entregas pendientes para el repartidor
                        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM envios WHERE repartidor_id = ? AND estado IN ('pendiente', 'en_transito')");
                        $stmt->execute([$userId]);
                        $badges['pending_deliveries'] = $stmt->fetch()['count'];
                    }
                    break;
                    
                case 'cliente':
                    // Items en carrito (si implementas carrito en sesión o BD)
                    $badges['cart_items'] = $_SESSION['cart_count'] ?? 0;
                    
                    // Pedidos pendientes del cliente
                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM pedidos WHERE cliente_id = ? AND estado NOT IN ('entregado', 'cancelado')");
                    $stmt->execute([$userId]);
                    $badges['pending_orders'] = $stmt->fetch()['count'];
                    break;
            }
            
            // Agregar contador de descuentos
            $stmt = $pdo->query("SELECT COUNT(*) FROM descuentos_clientes WHERE activo = 1");
            $badges['descuentos_count'] = $stmt->fetchColumn();
            
        } catch (Exception $e) {
            error_log("Error getting dynamic badges: " . $e->getMessage());
        }
        
        return $badges;
    }
    
    /**
     * Renderiza un badge según su tipo
     */
    public static function renderBadge($badgeConfig, $badges) {
        if (strpos($badgeConfig, 'dynamic:') === 0) {
            $badgeKey = str_replace('dynamic:', '', $badgeConfig);
            $count = $badges[$badgeKey] ?? 0;
            
            if ($count > 0) {
                $color = $count > 10 ? 'danger' : ($count > 5 ? 'warning' : 'primary');
                return "<span class='badge bg-{$color} ms-2'>{$count}</span>";
            }
        } else {
            // Badge estático
            return "<span class='badge bg-info ms-2'>{$badgeConfig}</span>";
        }
        
        return '';
    }
    
    /**
     * Filtra elementos del menú según permisos
     */
    public static function filterMenuItems($menuItems) {
        $filtered = [];
        
        foreach ($menuItems as $item) {
            // Si es un separador, siempre incluir
            if (isset($item['type']) && $item['type'] === 'separator') {
                $filtered[] = $item;
                continue;
            }
            
            // Verificar permisos si están definidos
            if (isset($item['permissions'])) {
                $hasPermission = false;
                foreach ($item['permissions'] as $permission) {
                    if (self::hasPermission($permission)) {
                        $hasPermission = true;
                        break;
                    }
                }
                if (!$hasPermission) {
                    continue;
                }
            }
            
            $filtered[] = $item;
        }
        
        return $filtered;
    }
}

/**
 * Sistema de Notificaciones
 */
class NotificationManager {
    
    /**
     * Obtiene notificaciones para el usuario actual
     */
    public static function getUserNotifications() {
        try {
            require_once __DIR__ . '/../config/database.php';
            $pdo = getConnection();
            
            $userType = $_SESSION['tipo'] ?? '';
            $userId = $_SESSION['cliente_id'] ?? $_SESSION['empleado_id'] ?? 0;
            
            $notifications = [];
            
            switch ($userType) {
                case 'empleado':
                    if ($_SESSION['cargo'] === 'admin') {
                        // Notificaciones para admin
                        $stmt = $pdo->query("
                            SELECT 'order' as type, 'Nuevo pedido recibido' as message, created_at 
                            FROM pedidos 
                            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR) 
                            ORDER BY created_at DESC 
                            LIMIT 5
                        ");
                        $notifications = array_merge($notifications, $stmt->fetchAll());
                    }
                    break;
                    
                case 'cliente':
                    // Notificaciones para cliente
                    $stmt = $pdo->prepare("
                        SELECT 'order_update' as type, 
                               CONCAT('Tu pedido #', numero_documento, ' cambió a: ', estado) as message,
                               updated_at as created_at
                        FROM pedidos 
                        WHERE cliente_id = ? 
                        AND updated_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                        ORDER BY updated_at DESC 
                        LIMIT 5
                    ");
                    $stmt->execute([$userId]);
                    $notifications = array_merge($notifications, $stmt->fetchAll());
                    break;
            }
            
            return $notifications;
            
        } catch (Exception $e) {
            error_log("Error getting notifications: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Renderiza el dropdown de notificaciones
     */
    public static function renderNotificationDropdown() {
        $notifications = self::getUserNotifications();
        $count = count($notifications);
        
        $html = "
        <div class='nav-item dropdown me-3'>
            <a class='nav-link position-relative' href='#' id='notificationsDropdown' role='button' data-bs-toggle='dropdown'>
                <i class='fas fa-bell'></i>";
        
        if ($count > 0) {
            $html .= "<span class='position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger'>{$count}</span>";
        }
        
        $html .= "</a><ul class='dropdown-menu dropdown-menu-end'>";
        
        if (empty($notifications)) {
            $html .= "<li><span class='dropdown-item-text text-muted'>No hay notificaciones</span></li>";
        } else {
            foreach ($notifications as $notification) {
                $time = date('H:i', strtotime($notification['created_at']));
                $html .= "
                <li>
                    <div class='dropdown-item small'>
                        <div>{$notification['message']}</div>
                        <small class='text-muted'>{$time}</small>
                    </div>
                </li>";
            }
            $html .= "<li><hr class='dropdown-divider'></li>";
            $html .= "<li><a class='dropdown-item text-center small' href='#'>Ver todas</a></li>";
        }
        
        $html .= "</ul></div>";
        
        return $html;
    }
}
?>
