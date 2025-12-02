<?php
/**
 * Configuración de rutas y URLs del proyecto
 * Este archivo detecta automáticamente si está en desarrollo local o producción
 */

// Detectar si estamos en producción o desarrollo
$is_production = (
    isset($_SERVER['HTTP_HOST']) && 
    !in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', '::1']) &&
    strpos($_SERVER['HTTP_HOST'], '.local') === false
);

// Función auxiliar para detectar la ruta base del proyecto
function detectar_base_path() {
    $script_path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME']));
    $doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    
    // Calcular la ruta relativa desde el document root
    $base_path = str_replace($doc_root, '', $script_path);
    
    // Limpiar la ruta base
    $base_path_parts = explode('/', trim($base_path, '/'));
    
    // Lista de directorios del proyecto que NO son la raíz
    $subdirs = ['config', 'admin', 'cliente', 'repartidor', 'auth', 'includes', 'uploads', 'ajax', 'reportes', 'classes', 'js'];
    
    // Encontrar el directorio raíz del proyecto
    $project_parts = [];
    foreach ($base_path_parts as $part) {
        if ($part && !in_array($part, $subdirs)) {
            $project_parts[] = $part;
        } else {
            // Detener cuando encontramos un subdirectorio conocido
            break;
        }
    }
    
    // Construir la ruta
    if (!empty($project_parts)) {
        return '/' . implode('/', $project_parts);
    }
    
    return ''; // Proyecto en la raíz del dominio
}

// Definir constantes de rutas según el entorno
if ($is_production) {
    // PRODUCCIÓN (Hostinger u otro servidor)
    $project_root = detectar_base_path();
    
    define('BASE_URL', $project_root);
    define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . $project_root);
} else {
    // DESARROLLO LOCAL (XAMPP, WAMP, etc.)
    define('BASE_URL', '/projectTiendaVirtual');
    define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/projectTiendaVirtual');
}

// URLs específicas
define('UPLOADS_URL', BASE_URL . '/uploads');
define('COMPROBANTES_URL', BASE_URL . '/uploads/comprobantes');
define('ADMIN_URL', BASE_URL . '/admin');
define('CLIENTE_URL', BASE_URL . '/cliente');
define('REPARTIDOR_URL', BASE_URL . '/repartidor');

// Paths físicos
define('UPLOADS_PATH', BASE_PATH . '/uploads');
define('COMPROBANTES_PATH', BASE_PATH . '/uploads/comprobantes');

/**
 * Convierte una ruta guardada en BD a URL absoluta correcta
 * 
 * @param string $path Ruta guardada en BD (puede ser relativa o absoluta antigua)
 * @return string URL correcta para el entorno actual
 */
function normalizar_url($path) {
    if (empty($path)) {
        return '';
    }
    
    // Si ya es una URL completa (http:// o https://), devolverla tal cual
    if (preg_match('/^https?:\/\//', $path)) {
        return $path;
    }
    
    // Eliminar /projectTiendaVirtual si existe (rutas antiguas)
    $path = preg_replace('/^\/projectTiendaVirtual/', '', $path);
    
    // Asegurar que empiece con /
    if ($path[0] !== '/') {
        $path = '/' . $path;
    }
    
    // Construir URL con la base correcta
    return BASE_URL . $path;
}

/**
 * Obtiene la URL base del sitio con el protocolo
 * 
 * @return string URL completa del sitio (ej: https://example.com)
 */
function get_site_url() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    return $protocol . '://' . $host;
}

/**
 * Obtiene la URL completa incluyendo la base del proyecto
 * 
 * @return string URL completa del proyecto (ej: https://example.com/proyecto)
 */
function get_full_base_url() {
    return get_site_url() . BASE_URL;
}
