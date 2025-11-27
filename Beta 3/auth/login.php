<?php
session_start();

// Si ya hay sesión activa redirigir según rol estrictamente
if (isset($_SESSION['usuario'])) {
    // Normalizar cargo si aplica para evitar problemas de mayúsculas
    if (isset($_SESSION['cargo'])) {
        $_SESSION['cargo'] = strtolower($_SESSION['cargo']);
    }
    if ($_SESSION['tipo'] === 'empleado') {
        if (isset($_SESSION['cargo'])) {
            if ($_SESSION['cargo'] === 'admin') {
                header('Location: ../admin/dashboard.php');
                exit;
            } elseif ($_SESSION['cargo'] === 'repartidor') {
                header('Location: ../repartidor/dashboard.php');
                exit;
            }
        }
        // Cargo desconocido o sin panel
        header('Location: ../index.php?info=no_dashboard');
        exit;
    } elseif ($_SESSION['tipo'] === 'cliente') {
        header('Location: ../cliente/dashboard.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

require_once '../config/database.php';

$usuario = trim($_POST['usuario'] ?? '');
$contrasena = trim($_POST['contrasena'] ?? '');

if ($usuario === '' || $contrasena === '') {
    header('Location: ../index.php?error=empty');
    exit;
}

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("
        SELECT c.*, 
               CASE WHEN c.tipo='empleado' THEN e.nombre ELSE cl.nombre END AS nombre,
               CASE WHEN c.tipo='empleado' THEN e.cargo ELSE NULL END AS cargo
        FROM credenciales c
        LEFT JOIN empleados e ON c.empleado_id=e.id AND c.tipo='empleado'
        LEFT JOIN clientes cl ON c.cliente_id=cl.id AND c.tipo='cliente'
        WHERE c.usuario=? AND c.activo=1 LIMIT 1
    ");
    $stmt->execute([$usuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($contrasena, $user['contrasena'])) {
        session_regenerate_id(true);
        $_SESSION = [];

        $update = $pdo->prepare("UPDATE credenciales SET ultimo_acceso=NOW() WHERE id=?");
        $update->execute([$user['id']]);

        $_SESSION['usuario'] = $user['usuario'];
        $_SESSION['nombre'] = $user['nombre'];
        $_SESSION['tipo'] = $user['tipo'];
        $_SESSION['user_id'] = $user['id'];

        if ($user['tipo'] === 'empleado') {
            // Normalizar cargo a minúsculas para comparaciones consistentes
            $_SESSION['cargo'] = strtolower($user['cargo']);
            $_SESSION['empleado_id'] = $user['empleado_id'];
            if ($_SESSION['cargo'] === 'admin') {
                $destino = '../admin/dashboard.php';
            } elseif ($_SESSION['cargo'] === 'repartidor') {
                $destino = '../repartidor/dashboard.php';
            } else {
                // Cargo desconocido: regresar a inicio
                $destino = '../index.php?info=no_dashboard';
            }
        } else {
            $_SESSION['cliente_id'] = $user['cliente_id'];
            $destino = '../cliente/dashboard.php';
        }

        header("Location: $destino");
        exit;
    }

    header('Location: ../index.php?error=invalid');
    exit;

} catch (PDOException $e) {
    error_log("Error de login: " . $e->getMessage());
    header('Location: ../index.php?error=system');
    exit;
}
?>