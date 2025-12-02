<?php
/**
 * Configuración de Email para SolTecnInd
 * 
 * IMPORTANTE: Para enviar emails, necesitas configurar:
 * 1. Reemplazar SMTP_USER con tu email de Gmail
 * 2. Reemplazar SMTP_PASSWORD con tu contraseña o token de Gmail
 * 3. Habilitar "Acceso a aplicaciones menos seguras" en tu cuenta de Gmail
 *    O generar una "Contraseña de aplicación" en https://myaccount.google.com/apppasswords
 */

return [
    // Configuración SMTP para Gmail
    'email' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => 'solucionestecind@gmail.com',
        'password' => 'Soluciones01',
        'from_email' => 'solucionestecind@gmail.com', 
        'from_name' => 'SolTecnInd',
    ],
];
