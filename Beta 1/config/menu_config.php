<?php
/**
 * Configuración de Menús del Sistema
 * Define la estructura de navegación para cada tipo de usuario
 */

return [
    'admin' => [
        'title' => 'SolTecnInd',
        'subtitle' => 'Panel Admin',
        'sidebar_class' => 'admin',
        'menu_items' => [
            [
                'url' => 'dashboard.php',
                'icon' => 'fas fa-tachometer-alt',
                'text' => 'Dashboard',
                'permissions' => ['admin'],
                'category' => 'General'
            ],
            [
                'url' => 'productos.php',
                'icon' => 'fas fa-box',
                'text' => 'Productos',
                'permissions' => ['admin'],
                'category' => 'Inventario'
            ],
            [
                'url' => 'grupos_productos.php',
                'icon' => 'fas fa-tags',
                'text' => 'Grupos de Productos',
                'permissions' => ['admin'],
                'category' => 'Inventario'
            ],
            [
                'url' => 'clientes.php',
                'icon' => 'fas fa-users',
                'text' => 'Clientes',
                'permissions' => ['admin'],
                'category' => 'Clientes'
            ],
            [
                'url' => 'direcciones.php',
                'icon' => 'fas fa-map-marker-alt',
                'text' => 'Direcciones',
                'permissions' => ['admin'],
                'category' => 'Clientes'
            ],
            [
                'type' => 'separator'
            ],
            [
                'url' => 'pedidos.php',
                'icon' => 'fas fa-shopping-cart',
                'text' => 'Pedidos',
                'permissions' => ['admin'],
                'badge' => 'dynamic:pending_orders',
                'category' => 'Ventas'
            ],
            [
                'url' => 'nueva_compra.php',
                'icon' => 'fas fa-plus-circle',
                'text' => 'Nueva Compra',
                'permissions' => ['admin'],
                'category' => 'Ventas'
            ],
            [
                'url' => 'envios.php',
                'icon' => 'fas fa-truck',
                'text' => 'Envíos',
                'permissions' => ['admin'],
                'category' => 'Ventas'
            ],
            [
                'type' => 'separator'
            ],
            [
                'url' => 'empleados.php',
                'icon' => 'fas fa-user-tie',
                'text' => 'Empleados',
                'permissions' => ['admin'],
                'category' => 'Administración'
            ],
            [
                'url' => 'usuarios.php',
                'icon' => 'fas fa-user-cog',
                'text' => 'Usuarios',
                'permissions' => ['admin'],
                'category' => 'Administración'
            ],
            [
                'url' => 'reportes.php',
                'icon' => 'fas fa-chart-bar',
                'text' => 'Reportes',
                'permissions' => ['admin'],
                'category' => 'Administración'
            ],
            [
                'url' => 'descuentos_clientes.php',
                'icon' => 'fas fa-percentage',
                'text' => 'Descuentos',
                'permission' => 'admin.descuentos',
                'category' => 'Administración'
            ]
        ]
    ],
    
    'cliente' => [
        'title' => 'SolTecnInd',
        'subtitle' => 'Portal Cliente',
        'sidebar_class' => 'cliente',
        'menu_items' => [
            [
                'url' => 'dashboard.php',
                'icon' => 'fas fa-tachometer-alt',
                'text' => 'Inicio',
                'category' => 'Compras'
            ],
            [
                'url' => 'productos.php',
                'icon' => 'fas fa-boxes',
                'text' => 'Catálogo',
                'category' => 'Compras'
            ],
            [
                'url' => 'pedidos.php',
                'icon' => 'fas fa-clipboard-list',
                'text' => 'Mis Pedidos',
                'category' => 'Compras'
            ],
            [
                'url' => 'cotizaciones.php',
                'icon' => 'fas fa-file-invoice-dollar',
                'text' => 'Cotizaciones',
                'category' => 'Compras'
            ],
            [
                'url' => 'nueva_compra.php',
                'icon' => 'fas fa-plus-circle',
                'text' => 'Nueva Compra',
                'category' => 'Compras'
            ],
            [
                'type' => 'separator'
            ],
            [
                'url' => 'perfil.php',
                'icon' => 'fas fa-user-circle',
                'text' => 'Mi Perfil',
                'category' => 'Cuenta'
            ]
        ]
    ],
    
    'vendedor' => [
        'title' => 'SolTecnInd',
        'subtitle' => 'Portal Vendedor',
        'sidebar_class' => 'vendedor',
        'menu_items' => [
            [
                'url' => 'dashboard.php',
                'icon' => 'fas fa-tachometer-alt',
                'text' => 'Dashboard',
                'category' => 'General'
            ],
            [
                'url' => 'clientes.php',
                'icon' => 'fas fa-users',
                'text' => 'Mis Clientes',
                'category' => 'Ventas'
            ],
            [
                'url' => 'pedidos.php',
                'icon' => 'fas fa-shopping-cart',
                'text' => 'Pedidos',
                'category' => 'Ventas'
            ],
            [
                'url' => 'nueva_venta.php',
                'icon' => 'fas fa-plus-circle',
                'text' => 'Nueva Venta',
                'category' => 'Ventas'
            ],
            [
                'url' => 'productos.php',
                'icon' => 'fas fa-box',
                'text' => 'Catálogo',
                'category' => 'Ventas'
            ],
            [
                'type' => 'separator'
            ],
            [
                'url' => 'perfil.php',
                'icon' => 'fas fa-user-circle',
                'text' => 'Mi Perfil',
                'category' => 'Cuenta'
            ]
        ]
    ],
    
    'repartidor' => [
        'title' => 'SolTecnInd',
        'subtitle' => 'Portal Repartidor',
        'sidebar_class' => 'repartidor',
        'menu_items' => [
            [
                'url' => 'dashboard.php',
                'icon' => 'fas fa-tachometer-alt',
                'text' => 'Dashboard',
                'category' => 'General'
            ],
            [
                'url' => 'entregas.php',
                'icon' => 'fas fa-truck',
                'text' => 'Mis Entregas',
                'badge' => 'dynamic:pending_deliveries',
                'category' => 'Entregas'
            ],
            [
                'url' => 'rutas.php',
                'icon' => 'fas fa-route',
                'text' => 'Rutas del Día',
                'category' => 'Entregas'
            ],
            [
                'url' => 'historial.php',
                'icon' => 'fas fa-history',
                'text' => 'Historial',
                'category' => 'Entregas'
            ],
            [
                'type' => 'separator'
            ],
            [
                'url' => 'perfil.php',
                'icon' => 'fas fa-user-circle',
                'text' => 'Mi Perfil',
                'category' => 'Cuenta'
            ]
        ]
    ]
];
?>
