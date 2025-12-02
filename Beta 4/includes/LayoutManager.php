<?php
class LayoutManager {

    /**
     * Renderiza la barra lateral dinámica según el rol del usuario.
     */
    private static function renderSidebar(array $menuConfig, string $currentPage, string $userRole = 'admin'): string {
        if (!isset($menuConfig[$userRole])) {
            error_log("Rol de usuario no encontrado en la configuración: $userRole");
            $userRole = 'admin'; // fallback
        }

        $config = $menuConfig[$userRole];
        
        // Obtener badges dinámicos
        require_once __DIR__ . '/PermissionManager.php';
        $dynamicBadges = PermissionManager::getDynamicBadges();

        // Agrupar ítems por categoría
        $grouped = [];
        $categoryOrder = [];
        foreach ($config['menu_items'] as $item) {
            if (!empty($item['type']) && $item['type'] === 'separator') continue;
            $cat = $item['category'] ?? 'General';
            if (!in_array($cat, $categoryOrder, true)) $categoryOrder[] = $cat;
            $grouped[$cat][] = $item;
        }

        ob_start(); ?>
        <div class="sidebar" role="navigation" aria-label="Sidebar principal">
            <div class="sidebar-inner p-3 d-flex flex-column">
                <a class="d-flex text-decoration-none align-items-center text-white mb-3" href="../index.php">
                    <i class="fas fa-layer-group fa-2x me-3 sidebar-brand-icon"></i>
                    <div>
                        <h5 class="mb-0 sidebar-title"><?= htmlspecialchars($config['title']) ?></h5>
                        <small class="text-muted sidebar-subtitle"><?= htmlspecialchars($config['subtitle']) ?></small>
                    </div>
                </a>

                <!-- Información del usuario -->
                <?php if (isset($_SESSION['nombre'])): ?>
                    <div class="user-info mb-3 d-flex align-items-center text-white">
                        <i class="fas fa-user-circle fa-lg me-2"></i>
                        <div>
                            <strong><?= htmlspecialchars($_SESSION['nombre']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($_SESSION['tipo'] ?? '') ?></small>
                        </div>
                    </div>
                    <hr class="sidebar-sep">
                <?php endif; ?>

                <?php foreach ($categoryOrder as $cat): ?>
                    <div class="sidebar-category mb-2 mt-1"><?= htmlspecialchars($cat) ?></div>
                    <nav class="nav flex-column mb-3">
                        <?php foreach ($grouped[$cat] as $item): ?>
                            <a class="nav-link <?= ($item['url'] === $currentPage) ? 'active' : '' ?>"
                               href="<?= htmlspecialchars($item['url']) ?>"
                               <?= isset($item['spa']) && $item['spa'] === false ? 'data-no-spa="true"' : '' ?>>
                                <i class="<?= htmlspecialchars($item['icon']) ?> me-2 sidebar-icon"></i>
                                <span class="sidebar-text"><?= htmlspecialchars($item['text']) ?></span>
                                <?php if (isset($item['badge'])): ?>
                                    <?php 
                                    // Procesar badge dinámico
                                    if (strpos($item['badge'], 'dynamic:') === 0) {
                                        $badgeKey = str_replace('dynamic:', '', $item['badge']);
                                        $count = $dynamicBadges[$badgeKey] ?? 0;
                                        if ($count > 0) {
                                            $color = $count > 10 ? 'danger' : ($count > 5 ? 'warning' : 'primary');
                                            echo "<span class='badge bg-{$color} rounded-pill float-end sidebar-badge'>{$count}</span>";
                                        }
                                    } else {
                                        // Badge estático
                                        echo "<span class='badge bg-danger rounded-pill float-end sidebar-badge'>" . htmlspecialchars($item['badge']) . "</span>";
                                    }
                                    ?>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                <?php endforeach; ?>

                <hr class="sidebar-sep mt-auto">
                <a class="nav-link text-white logout-link" href="../auth/logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i> Cerrar sesión
                </a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Renderiza la página con sidebar y contenido principal.
     */
    public static function renderAdminPage(string $title, string $content, string $additionalCSS = '', string $additionalJS = ''): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $menuConfigPath = __DIR__ . '/../config/menu_config.php';
        if (!file_exists($menuConfigPath)) {
            die('Error: archivo de configuración de menú no encontrado.');
        }
        $menuConfig = require $menuConfigPath;

        $currentPage = basename($_SERVER['PHP_SELF']);
        $userRole = $_SESSION['user_role'] ?? 'admin';

        $additionalCSS = is_array($additionalCSS) ? implode("\n", $additionalCSS) : (string)$additionalCSS;
        $additionalJS = is_array($additionalJS) ? implode("\n", $additionalJS) : (string)$additionalJS;

        // 🎨 Estilo serio y elegante
        $baseCSS = <<<CSS
        :root {
            --sidebar-width: 230px;
            --color-bg: #f5f6f8;
            --color-sidebar: #1e1e2f;
            --color-sidebar-hover: #2d2d40;
            --color-accent: #3b82f6;
            --color-text: #dcdcdc;
            --color-text-light: #a9a9b3;
            --color-border: #3a3a4d;
        }
        html, body {
            height: 100%;
            background: var(--color-bg);
            font-family: 'Inter', 'Segoe UI', sans-serif;
            color: #333;
            margin: 0;
        }

        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--color-sidebar);
            color: var(--color-text);
            box-shadow: 2px 0 6px rgba(0,0,0,0.2);
            overflow-y: auto;
        }
        .sidebar-title { font-weight: 600; font-size: 1.1rem; }
        .sidebar-subtitle { color: var(--color-text-light); font-size: .8rem; }

        .sidebar .nav-link {
            color: var(--color-text);
            border-radius: 6px;
            padding: .55rem .75rem;
            margin: 3px 0;
            transition: all .2s;
            display: flex;
            align-items: center;
        }
        .sidebar .nav-link:hover {
            background: var(--color-sidebar-hover);
            transform: translateX(4px);
        }
        .sidebar .nav-link.active {
            background: var(--color-sidebar-hover);
            border-left: 4px solid var(--color-accent);
            color: #fff;
            font-weight: 600;
        }

        .sidebar-icon { width: 26px; text-align: center; }
        .sidebar-category {
            font-size: .75rem;
            text-transform: uppercase;
            color: var(--color-text-light);
            margin-top: .75rem;
            margin-bottom: .25rem;
        }

        .sidebar-sep { border-color: var(--color-border); }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 1.5rem;
            background: var(--color-bg);
            min-height: 100vh;
        }

        .card {
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 1rem;
            background: #fff;
        }

        .user-info {
            background: rgba(255,255,255,0.05);
            border-radius: 8px;
            padding: 0.6rem 0.75rem;
        }
        .user-info i {
            color: var(--color-accent);
        }

        @media (max-width: 991px) {
            .sidebar { position: relative; width: 100%; height: auto; }
            .main-content { margin-left: 0; }
        }
        CSS;

        $baseJS = <<<JS
        <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.querySelector('.sidebar');
            const sidebarLinks = document.querySelectorAll('.sidebar .nav-link:not(.logout-link)');
            
            // Restaurar posición del scroll del sidebar al cargar
            const savedScrollPosition = sessionStorage.getItem('sidebarScrollPosition');
            if (savedScrollPosition && sidebar) {
                sidebar.scrollTop = parseInt(savedScrollPosition, 10);
            }
            
            // Guardar posición del scroll antes de cualquier navegación
            sidebarLinks.forEach(link => {
                link.addEventListener('click', () => {
                    if (sidebar) {
                        sessionStorage.setItem('sidebarScrollPosition', sidebar.scrollTop);
                    }
                });
            });
            
            // Role switcher
            const select = document.getElementById('roleSwitcher');
            if (select) {
                const savedRole = sessionStorage.getItem('selectedRole');
                if (savedRole) select.value = savedRole;
                select.addEventListener('change', async () => {
                    const role = select.value;
                    sessionStorage.setItem('selectedRole', role);
                    await fetch('../auth/set_role.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'user_role=' + encodeURIComponent(role)
                    });
                    window.location.reload();
                });
            }
        });
        </script>
        JS;

        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= htmlspecialchars($title) ?> - Panel</title>
            <link rel="apple-touch-icon" sizes="180x180" href="/projectTiendaVirtual/favicon_io/apple-touch-icon.png">
            <link rel="icon" type="image/png" sizes="32x32" href="/projectTiendaVirtual/favicon_io/favicon-32x32.png">
            <link rel="icon" type="image/png" sizes="16x16" href="/projectTiendaVirtual/favicon_io/favicon-16x16.png">
            <link rel="manifest" href="/projectTiendaVirtual/favicon_io/site.webmanifest">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
            <style><?= $baseCSS . "\n" . $additionalCSS ?></style>
        </head>
        <body>
            <div class="container-fluid">
                <div class="row">
                    <?= self::renderSidebar($menuConfig, $currentPage, $userRole) ?>
                    <div class="col main-content">
                        <?= $content ?>
                    </div>
                </div>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
            <?= $baseJS . "\n" . $additionalJS ?>
        </body>
        </html>
        <?php
    }

    /**
     * Renderiza la paginación con estilo Bootstrap.
     */
    public static function renderPagination(int $currentPage, int $totalPages, string $baseUrl, array $params = []): string {
        if ($totalPages <= 1) return '';

        $queryParams = $params;
        unset($queryParams['page']);
        $queryString = http_build_query($queryParams);
        $urlPrefix = $baseUrl . '?' . ($queryString ? $queryString . '&' : '');

        ob_start(); ?>
        <nav aria-label="Paginación" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $urlPrefix ?>page=1"><i class="fas fa-angle-double-left"></i></a>
                </li>
                <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                    <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                        <a class="page-link" href="<?= $urlPrefix ?>page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $urlPrefix ?>page=<?= $totalPages ?>"><i class="fas fa-angle-double-right"></i></a>
                </li>
            </ul>
        </nav>
        <?php
        return ob_get_clean();
    }
}
?>
