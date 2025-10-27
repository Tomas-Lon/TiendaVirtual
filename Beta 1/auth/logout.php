<?php
session_start();

// Limpiar variables de sesión
$_SESSION = [];

// Borrar cookie de sesión si existe
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir la sesión completamente
session_destroy();

// Redirigir a la página principal
header('Location: ../index.php');
exit;
?>