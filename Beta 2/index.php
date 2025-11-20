<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SolTecnInd - Tienda Virtual</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --rojo-principal: #e74c3c;
            --rojo-oscuro: #c0392b;
            --gris-fondo: #f8f9fa;
        }

        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('assets/images/hero-bg.jpg');
            background-size: cover;
            background-position: center;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
        }

        .login-card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
            color: #333;
            max-width: 400px;
        }

        .brand-logo {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--rojo-principal);
            margin-bottom: 1rem;
        }

        .btn-primary {
            background: var(--rojo-principal);
            border-color: var(--rojo-principal);
        }

        .btn-primary:hover {
            background: var(--rojo-oscuro);
            border-color: var(--rojo-oscuro);
        }

        .form-control:focus {
            border-color: var(--rojo-principal);
            box-shadow: 0 0 0 0.2rem rgba(231, 76, 60, 0.25);
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand text-danger" href="#"><i class="fas fa-industry"></i> SolTecnInd</a>
            <div class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['usuario'])): ?>
                    <a class="nav-link" href="auth/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                <?php else: ?>
                    <a class="nav-link" href="#login"><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="hero-section">
        <div class="container">
            <?php if (isset($_SESSION['usuario'])): ?>
                <div class="hero-content">
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
                        } elseif ($cargo === '') {
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
                    <div class="brand-logo"><i class="fas fa-industry"></i> SolTecnInd</div>
                    <h3 class="text-center mb-4">Iniciar Sesión</h3>
                    
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
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt"></i> Entrar
                            </button>
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
