<?php
// Script de diagnóstico para detectar salida inesperada

// Iniciar captura de salida
ob_start();

session_start();
require_once '../../config/database.php';
require_once '../../admin/classes/ComprobantePDF.php';
require_once '../../admin/classes/EmailService.php';

// Capturar cualquier salida
$output = ob_get_clean();

// Preparar diagnóstico
$diagnostico = [
    'tiene_salida' => !empty($output),
    'longitud_salida' => strlen($output),
    'salida_raw' => $output,
    'salida_hex' => bin2hex($output),
    'salida_visible' => htmlspecialchars($output),
    'primer_caracter' => !empty($output) ? ord($output[0]) : null,
];

// Enviar como JSON limpio
header('Content-Type: application/json; charset=utf-8');
echo json_encode($diagnostico, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
exit();
