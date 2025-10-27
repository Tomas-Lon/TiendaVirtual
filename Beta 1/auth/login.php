<?php
session_start();

if (isset($_SESSION['usuario'])) {
    switch ($_SESSION['tipo']) {
        case 'empleado':
            if ($_SESSION['cargo'] === 'admin') {
                header('Location: ../admin/dashboard.php');
            } else {
                header('Location: ../repartidor/dashboard.php');
            }
            break;
        case 'cliente':
            header('Location: ../cliente/dashboard.php');
            break;
    }
    exit;
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
            $_SESSION['cargo'] = $user['cargo'];
            $_SESSION['empleado_id'] = $user['empleado_id'];
            $destino = ($user['cargo'] === 'admin') ? '../admin/dashboard.php' : '../repartidor/dashboard.php';
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