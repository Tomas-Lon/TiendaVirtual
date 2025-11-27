<?php
session_start();
require_once '../config/database.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario'])) {
    // Si no está logueado, redirigir al login
    header('Location: ../auth/login.php');
    exit();
}

// Verificar que sea empleado con acceso al admin
if ($_SESSION['tipo'] !== 'empleado') {
    // Si es cliente, redirigir a su dashboard
    header('Location: ../cliente/dashboard.php');
    exit();
}

// Si es empleado válido, redirigir al dashboard administrativo
header('Location: dashboard.php');
exit();
?>
