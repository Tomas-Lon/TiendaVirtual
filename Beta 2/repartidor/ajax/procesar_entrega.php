<?php
// Configuración estricta para evitar cualquier salida no deseada
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Limpiar TODOS los buffers de salida existentes
while (ob_get_level()) {
    ob_end_clean();
}

// Iniciar nuevo buffer para capturar cualquier salida no deseada
ob_start();

session_start();

// Verificar usuario
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'empleado' || $_SESSION['cargo'] !== 'repartidor') {
    ob_end_clean();
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    die(json_encode(['success' => false, 'message' => 'No autorizado'], JSON_UNESCAPED_UNICODE));
}

require_once '../../config/database.php';
require_once '../../admin/classes/ComprobantePDF.php';
require_once '../../admin/classes/EmailService.php';

try {
    $pdo = getConnection();
    
    $entrega_id = $_POST['entrega_id'] ?? null;
    $receptor_nombre = $_POST['receptor_nombre'] ?? '';
    $receptor_documento = $_POST['receptor_documento'] ?? '';
    $receptor_email = $_POST['receptor_email'] ?? '';
    $firma_data = $_POST['firma_data'] ?? '';
    $foto_data = $_POST['foto_data'] ?? '';
    $observaciones = $_POST['observaciones'] ?? '';
    $ubicacion_lat = $_POST['ubicacion_lat'] ?? null;
    $ubicacion_lng = $_POST['ubicacion_lng'] ?? null;
    
    if (!$entrega_id || !$receptor_nombre) {
        throw new Exception('Datos incompletos');
    }
    
    // Obtener datos del envío
    $stmt = $pdo->prepare("
        SELECT e.*, 
               c.nombre as cliente_nombre, c.email as cliente_email, c.telefono,
               p.numero_documento,
               d.direccion, d.ciudad,
               em.nombre as repartidor_nombre,
               p.id as pedido_id
        FROM envios e
        INNER JOIN pedidos p ON e.pedido_id = p.id
        INNER JOIN clientes c ON p.cliente_id = c.id
        LEFT JOIN direcciones_clientes d ON e.direccion_entrega_id = d.id
        LEFT JOIN empleados em ON e.repartidor_id = em.id
        WHERE e.id = ? AND e.repartidor_id = ?
    ");
    $stmt->execute([$entrega_id, $_SESSION['empleado_id']]);
    $envio = $stmt->fetch();
    
    if (!$envio) {
        throw new Exception('Envío no encontrado o no autorizado');
    }
    
    // Generar código QR único
    $codigo_qr = 'ENT-' . str_pad($entrega_id, 6, '0', STR_PAD_LEFT) . '-' . strtoupper(substr(md5(time()), 0, 6));
    
    // Crear archivos temporales para foto y firma (solo para el PDF)
    $foto_temp_path = null;
    $firma_temp_path = null;
    $archivos_temporales = [];
    
    if ($foto_data && strpos($foto_data, 'data:image') === 0) {
        $temp_dir = sys_get_temp_dir();
        
        // Detectar el tipo de imagen
        $extension = 'jpg';
        if (strpos($foto_data, 'data:image/png') !== false) {
            $extension = 'png';
        }
        
        $foto_temp_filename = 'foto_temp_' . uniqid() . '.' . $extension;
        $foto_temp_path = $temp_dir . DIRECTORY_SEPARATOR . $foto_temp_filename;
        $foto_base64 = preg_replace('/^data:image\/\w+;base64,/', '', $foto_data);
        
        $decoded = base64_decode($foto_base64);
        if ($decoded && file_put_contents($foto_temp_path, $decoded)) {
            $archivos_temporales[] = $foto_temp_path;
        } else {
            $foto_temp_path = null;
        }
    }
    
    if ($firma_data && strpos($firma_data, 'data:image') === 0) {
        $temp_dir = sys_get_temp_dir();
        $firma_temp_filename = 'firma_temp_' . uniqid() . '.png';
        $firma_temp_path = $temp_dir . DIRECTORY_SEPARATOR . $firma_temp_filename;
        $firma_base64 = str_replace('data:image/png;base64,', '', $firma_data);
        
        if (file_put_contents($firma_temp_path, base64_decode($firma_base64))) {
            $archivos_temporales[] = $firma_temp_path;
        } else {
            $firma_temp_path = null;
        }
    }
    
    // Crear registro en tabla entregas
    $stmt = $pdo->prepare("
        INSERT INTO entregas 
        (envio_id, pedido_id, repartidor_id, receptor_nombre, receptor_documento, 
         receptor_email, fecha_creacion, fecha_entrega_real, observaciones, estado, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW(), ?, 'entregado', NOW())
    ");
    $stmt->execute([
        $entrega_id,
        $envio['pedido_id'],
        $_SESSION['empleado_id'],
        $receptor_nombre,
        $receptor_documento ?: null,
        $receptor_email ?: null,
        $observaciones
    ]);
    
    $entrega_nueva_id = $pdo->lastInsertId();
    
    // Insertar en tabla comprobantes_entrega (sin guardar rutas de archivos)
    $stmt = $pdo->prepare("
        INSERT INTO comprobantes_entrega 
        (entrega_id, receptor_nombre, receptor_documento, receptor_email, 
         latitud, longitud, codigo_qr, observaciones)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $entrega_nueva_id,
        $receptor_nombre,
        $receptor_documento ?: null,
        $receptor_email ?: null,
        $ubicacion_lat,
        $ubicacion_lng,
        $codigo_qr,
        $observaciones
    ]);
    
    $comprobante_id = $pdo->lastInsertId();
    
    // Generar PDF del comprobante con archivos temporales
    $pdf_generator = new ComprobantePDFGenerator();
    $pdf_data = [
        'codigo_qr' => $codigo_qr,
        'fecha_entrega' => date('Y-m-d H:i:s'),
        'repartidor_nombre' => $envio['repartidor_nombre'],
        'cliente_nombre' => $envio['cliente_nombre'],
        'telefono_cliente' => $envio['telefono'],
        'direccion_entrega' => $envio['direccion'],
        'ciudad' => $envio['ciudad'],
        'numero_pedido' => $envio['numero_documento'] ?? '',
        'numero_factura' => '',
        'receptor_nombre' => $receptor_nombre,
        'receptor_documento' => $receptor_documento,
        'receptor_email' => $receptor_email,
        'observaciones' => $observaciones,
        'latitud' => $ubicacion_lat,
        'longitud' => $ubicacion_lng,
        'firma_temp_path' => $firma_temp_path,
        'foto_temp_path' => $foto_temp_path
    ];
    
    $pdf_result = $pdf_generator->generar($pdf_data);
    
    // Eliminar archivos temporales después de generar el PDF
    foreach ($archivos_temporales as $archivo_temp) {
        if (file_exists($archivo_temp)) {
            @unlink($archivo_temp);
        }
    }
    
    if ($pdf_result['success']) {
        // Actualizar ruta del PDF en comprobantes
        $stmt = $pdo->prepare("UPDATE comprobantes_entrega SET pdf_path = ? WHERE id = ?");
        $stmt->execute([$pdf_result['url'], $comprobante_id]);
    }
    
    // Actualizar estado del envío a "entregado"
    $stmt = $pdo->prepare("
        UPDATE envios 
        SET estado = 'entregado', 
            fecha_entrega_real = NOW(),
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$entrega_id]);
    
    // Registrar en historial si la tabla existe
    try {
        $stmt = $pdo->prepare("
            INSERT INTO historial_entregas 
            (entrega_id, accion, estado_anterior, estado_nuevo, repartidor_id, observaciones)
            VALUES (?, 'ENTREGA_COMPLETADA', ?, 'entregado', ?, ?)
        ");
        $stmt->execute([$entrega_nueva_id, $envio['estado'], $_SESSION['empleado_id'], $observaciones]);
    } catch (PDOException $e) {
        // Historial opcional - no fallar si no existe
        error_log('Historial no disponible: ' . $e->getMessage());
    }
    
    // Enviar email al cliente con comprobante
    $email_service = new EmailService();
    
    $cliente_email = $receptor_email ?: $envio['cliente_email'];
    if ($cliente_email && $pdf_result['success']) {
        $email_service->enviarComprobanteEntrega(
            $cliente_email,
            $receptor_nombre,
            $pdf_result['filepath'],
            $codigo_qr
        );
        
        // Registrar envío de email
        $stmt = $pdo->prepare("
            UPDATE comprobantes_entrega 
            SET enviado_por_email = 1, fecha_envio_email = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$comprobante_id]);
    }
    
    // Limpiar buffer y enviar respuesta exitosa
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    die(json_encode([
        'success' => true,
        'message' => 'Entrega completada exitosamente',
        'codigo_qr' => $codigo_qr,
        'comprobante_url' => $pdf_result['url'],
        'comprobante_id' => $comprobante_id
    ], JSON_UNESCAPED_UNICODE));
    
} catch (Exception $e) {
    error_log('Error en procesar_entrega.php: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    ob_end_clean();
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    die(json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE));
    
} catch (Error $e) {
    error_log('Error fatal en procesar_entrega.php: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    ob_end_clean();
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    die(json_encode([
        'success' => false,
        'message' => 'Error del sistema: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE));
}
