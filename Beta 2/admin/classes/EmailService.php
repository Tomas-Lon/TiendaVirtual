<?php

class EmailService
{
    private $smtp_host = 'smtp.gmail.com';
    private $smtp_port = 587;
    private $smtp_user = '';
    private $smtp_password = '';
    private $from_email = '';
    private $from_name = 'SolTecnInd';
    
    public function __construct($config = [])
    {
        // Si no se proporciona config, intentar cargar desde archivo
        if (empty($config)) {
            $config_file = __DIR__ . '/../../config/email_config.php';
            if (file_exists($config_file)) {
                $email_config = require $config_file;
                $config = $email_config['email'] ?? [];
            }
        }
        
        if (!empty($config)) {
            $this->smtp_host = $config['host'] ?? $this->smtp_host;
            $this->smtp_port = $config['port'] ?? $this->smtp_port;
            $this->smtp_user = $config['username'] ?? ($config['user'] ?? $this->smtp_user);
            $this->smtp_password = $config['password'] ?? $this->smtp_password;
            $this->from_email = $config['from_email'] ?? $this->from_email;
            $this->from_name = $config['from_name'] ?? $this->from_name;
        }
    }
    
    /**
     * Enviar comprobante de entrega por email
     */
    public function enviarComprobanteEntrega($destinatario_email, $cliente_nombre, $comprobante_path, $codigo_qr)
    {
        if (empty($this->smtp_user) || empty($this->smtp_password)) {
            return [
                'success' => false,
                'message' => 'Configuración de email no disponible'
            ];
        }
        
        try {
            // Headers del email
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: " . $this->from_name . " <" . $this->from_email . ">\r\n";
            $headers .= "Reply-To: " . $this->from_email . "\r\n";
            
            // Asunto
            $asunto = "Comprobante de Entrega - " . $codigo_qr;
            
            // Cuerpo del email
            $body = $this->_generarBodyComprobante($cliente_nombre, $codigo_qr);
            
            // Enviar email base
            $email_enviado = mail(
                $destinatario_email,
                $asunto,
                $body,
                $headers
            );
            
            if ($email_enviado) {
                // Intentar enviar con adjunto usando método alternativo
                $this->_enviarConAdjunto($destinatario_email, $asunto, $body, $comprobante_path);
                
                return [
                    'success' => true,
                    'message' => 'Comprobante enviado correctamente a ' . $destinatario_email
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al enviar el email'
                ];
            }
            
        } catch (Exception $e) {
            error_log('Error en EmailService: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Generar HTML del body del email
     */
    private function _generarBodyComprobante($cliente_nombre, $codigo_qr)
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; background-color: #f5f5f5; }
                .container { max-width: 600px; margin: 0 auto; background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                .header { background-color: #27ae60; color: white; padding: 20px; border-radius: 8px 8px 0 0; text-align: center; }
                .header h1 { margin: 0; font-size: 24px; }
                .content { padding: 20px 0; }
                .section { margin-bottom: 20px; }
                .section h3 { color: #27ae60; border-bottom: 2px solid #27ae60; padding-bottom: 10px; }
                .info-row { display: flex; padding: 8px 0; border-bottom: 1px solid #eee; }
                .label { font-weight: bold; width: 150px; color: #333; }
                .value { flex: 1; color: #666; }
                .code-box { background-color: #f9f9f9; padding: 15px; border-left: 4px solid #27ae60; margin: 15px 0; border-radius: 4px; }
                .code-box .label { display: block; margin-bottom: 5px; }
                .code-box .code { font-size: 18px; font-weight: bold; color: #27ae60; font-family: monospace; }
                .button { display: inline-block; background-color: #27ae60; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none; margin-top: 10px; }
                .footer { text-align: center; color: #999; font-size: 12px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>✓ Entrega Completada</h1>
                </div>
                
                <div class='content'>
                    <div class='section'>
                        <h3>Información de Entrega</h3>
                        <div class='info-row'>
                            <div class='label'>Estimado(a):</div>
                            <div class='value'>" . htmlspecialchars($cliente_nombre) . "</div>
                        </div>
                        <div class='info-row'>
                            <div class='label'>Fecha:</div>
                            <div class='value'>" . date('d/m/Y H:i:s') . "</div>
                        </div>
                    </div>
                    
                    <div class='section'>
                        <p>Tu entrega ha sido completada exitosamente. Adjunto encontrarás el comprobante de entrega con todos los detalles.</p>
                    </div>
                    
                    <div class='code-box'>
                        <div class='label'>Código de Referencia:</div>
                        <div class='code'>" . $codigo_qr . "</div>
                        <p style='margin: 10px 0 0 0; font-size: 12px; color: #999;'>Guarda este código para futuras referencias</p>
                    </div>
                    
                    <div class='section'>
                        <h3>¿Qué hacer ahora?</h3>
                        <ul style='color: #666; line-height: 1.8;'>
                            <li>Revisa el comprobante PDF adjunto</li>
                            <li>Verifica que el contenido coincida con tu pedido</li>
                            <li>Conserva este email para tus registros</li>
                            <li>Si tienes dudas, contacta con nosotros</li>
                        </ul>
                    </div>
                </div>
                
                <div class='footer'>
                    <p>Este es un email automático de SolTecnInd.</p>
                    <p>Por favor no respondas a este email. Si tienes alguna pregunta, contacta directamente con nosotros.</p>
                    <p>&copy; 2025 SolTecnInd. Todos los derechos reservados.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Enviar email con adjunto usando comando del sistema (método alternativo)
     */
    private function _enviarConAdjunto($destinatario, $asunto, $body, $pdf_path)
    {
        if (!file_exists($pdf_path)) {
            return false;
        }
        
        try {
            // Este método es alternativo usando PHP mail
            // Para producción, considera usar PHPMailer o Swift Mailer
            $boundary = md5(time());
            
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: multipart/mixed; boundary=\"" . $boundary . "\"\r\n";
            $headers .= "From: " . $this->from_name . " <" . $this->from_email . ">\r\n";
            
            $message = "--" . $boundary . "\r\n";
            $message .= "Content-Type: text/html; charset=\"UTF-8\"\r\n";
            $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
            $message .= $body . "\r\n\r\n";
            
            // Adjuntar PDF
            $message .= "--" . $boundary . "\r\n";
            $message .= "Content-Type: application/pdf; name=\"" . basename($pdf_path) . "\"\r\n";
            $message .= "Content-Transfer-Encoding: base64\r\n";
            $message .= "Content-Disposition: attachment; filename=\"" . basename($pdf_path) . "\"\r\n\r\n";
            $message .= chunk_split(base64_encode(file_get_contents($pdf_path))) . "\r\n\r\n";
            
            $message .= "--" . $boundary . "--";
            
            return mail($destinatario, $asunto, $message, $headers);
            
        } catch (Exception $e) {
            error_log('Error al enviar adjunto: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar notificación de entrega al cliente
     */
    public function enviarNotificacionEntrega($cliente_email, $cliente_nombre, $repartidor_nombre)
    {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . $this->from_name . " <" . $this->from_email . ">\r\n";
        
        $asunto = "Tu pedido ha sido entregado";
        
        $body = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; background-color: #f5f5f5; padding: 20px; }
                .content { background-color: white; padding: 20px; border-radius: 8px; }
                .success { color: #27ae60; font-size: 18px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='content'>
                    <h2 class='success'>✓ ¡Tu pedido ha sido entregado!</h2>
                    <p>Hola <strong>" . htmlspecialchars($cliente_nombre) . "</strong>,</p>
                    <p>Tu entrega fue completada por <strong>" . htmlspecialchars($repartidor_nombre) . "</strong>.</p>
                    <p>Revisa tu email por el comprobante de entrega detallado.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return mail($cliente_email, $asunto, $body, $headers);
    }
}
