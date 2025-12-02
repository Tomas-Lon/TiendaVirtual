<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SolTecnInd - Tienda Virtual</title>
    <link rel="apple-touch-icon" sizes="180x180" href="/projectTiendaVirtual/favicon_io/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/projectTiendaVirtual/favicon_io/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/projectTiendaVirtual/favicon_io/favicon-16x16.png">
    <link rel="manifest" href="/projectTiendaVirtual/favicon_io/site.webmanifest">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --color-primary: #3b82f6;
            --color-primary-dark: #2563eb;
            --color-bg: #f5f6f8;
            --color-dark: #1e1e2f;
        }

        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background: var(--color-bg);
        }

        .hero-section {
            background: linear-gradient(135deg, var(--color-dark) 0%, #2d2d40 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 0%, transparent 70%);
            animation: pulse 15s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .login-card {
            background: white;
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            color: #333;
            max-width: 420px;
            position: relative;
            z-index: 1;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 2rem;
            font-weight: 700;
            color: var(--color-primary);
            margin-bottom: 1.5rem;
        }

        .brand-logo i {
            font-size: 2.5rem;
        }

        .btn-primary {
            background: var(--color-primary);
            border-color: var(--color-primary);
            padding: 0.75rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background: var(--color-primary-dark);
            border-color: var(--color-primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }

        .btn-lg {
            padding: 1rem 2.5rem;
            font-size: 1.1rem;
        }

        .form-control {
            padding: 0.75rem;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .input-group-text {
            background: var(--color-bg);
            border: 2px solid #e5e7eb;
            border-right: none;
            color: #6b7280;
        }

        .input-group .form-control {
            border-left: none;
        }

        .input-group:focus-within .input-group-text {
            border-color: var(--color-primary);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: white !important;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .navbar-brand i {
            color: var(--color-primary);
        }

        .navbar {
            background: var(--color-dark) !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .hero-content h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        .hero-content p {
            font-size: 1.25rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }

        footer {
            background: var(--color-dark) !important;
            box-shadow: 0 -2px 8px rgba(0,0,0,0.1);
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-layer-group"></i> SolTecnInd
            </a>
            <div class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['usuario'])): ?>
                    <a class="nav-link text-white" href="auth/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                    </a>
                <?php else: ?>
                    <a class="nav-link text-white" href="#login">
                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="hero-section">
        <div class="container">
            <?php if (isset($_SESSION['usuario'])): ?>
                <div class="hero-content" style="position: relative; z-index: 1;">
                    <div class="mb-4">
                        <i class="fas fa-layer-group" style="font-size: 4rem; color: var(--color-primary); margin-bottom: 1rem;"></i>
                    </div>
                    <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></h1>
                    <p>¡Accede a tu panel de control personalizado!</p>
                    <?php
                    $dashboardUrl = '';
                    $panelLabel = '';
                    if ($_SESSION['tipo'] === 'empleado') {
                        $cargo = strtolower($_SESSION['cargo'] ?? '');
                        if ($cargo === 'admin') {
                            $dashboardUrl = 'admin/dashboard.php';
                            $panelLabel = 'Panel Admin';
                        } elseif ($cargo === 'repartidor') {
                            $dashboardUrl = 'repartidor/dashboard.php';
                            $panelLabel = 'Panel Repartidor';
                        } else {
                            $panelLabel = 'Panel no disponible para su cargo';
                        }
                    } elseif ($_SESSION['tipo'] === 'cliente') {
                        $dashboardUrl = 'cliente/dashboard.php';
                        $panelLabel = 'Panel Cliente';
                    }
                    ?>
                    <?php if ($dashboardUrl): ?>
                        <a href="<?php echo $dashboardUrl; ?>" class="btn btn-primary btn-lg mt-3">
                            <i class="fas fa-tachometer-alt"></i> Ir al <?php echo htmlspecialchars($panelLabel); ?>
                        </a>
                    <?php else: ?>
                        <div class="alert alert-warning mt-3" role="alert">
                            Aún no hay un panel asignado para su rol. Contacte al administrador.
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="login-card mx-auto">
                    <div class="brand-logo">
                        <i class="fas fa-layer-group"></i> SolTecnInd
                    </div>
                    <h3 class="text-center mb-4" style="color: #1e1e2f; font-weight: 600;">Iniciar Sesión</h3>
                    
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger">
                            <?php 
                            switch($_GET['error']) {
                                case 'invalid': echo 'Usuario o contraseña incorrectos'; break;
                                case 'empty': echo 'Complete todos los campos'; break;
                                case 'system': echo 'Error interno. Intente más tarde'; break;
                                default: echo 'Error al iniciar sesión';
                            }
                            ?>
                        </div>
                    <?php endif; ?>

                    <form action="auth/login.php" method="POST">
                        <div class="mb-3">
                            <label for="usuario" class="form-label">Usuario</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="usuario" name="usuario" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="contrasena" class="form-label">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword" style="border: 2px solid #e5e7eb;">
                                    <i class="fas fa-eye" id="eyeIcon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt"></i> Entrar
                            </button>
                        </div>
                    </form>

                    <script>
                        const togglePassword = document.getElementById('togglePassword');
                        const passwordInput = document.getElementById('contrasena');
                        const eyeIcon = document.getElementById('eyeIcon');
                        
                        togglePassword.addEventListener('click', function() {
                            const type = passwordInput.type === 'password' ? 'text' : 'password';
                            passwordInput.type = type;
                            
                            eyeIcon.classList.toggle('fa-eye');
                            eyeIcon.classList.toggle('fa-eye-slash');
                        });
                    </script>
                </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <footer class="bg-dark text-light text-center py-4">
        <div class="container">
            <p class="mb-0">&copy; 2025 SolTecnInd - Soluciones Técnicas Industriales</p>
        </div>
    </footer>
</body>
</html>
