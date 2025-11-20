<?php
require_once __DIR__ . '/../../fpdf/fpdf.php';

class ComprobantePDF extends FPDF
{
    public $title = '';
    public $empresa = 'SolTecnInd';
    
    public function Header()
    {
        $this->SetFont('Helvetica', 'B', 16);
        $this->SetTextColor(39, 174, 96);
        $this->Cell(0, 10, $this->empresa, 0, 1, 'C');
        
        $this->SetFont('Helvetica', '', 10);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 5, 'Comprobante de Entrega', 0, 1, 'C');
        $this->Ln(5);
        
        // Línea separadora
        $this->SetDrawColor(39, 174, 96);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(2);
    }
    
    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Helvetica', 'I', 8);
        $this->SetTextColor(150, 150, 150);
        $this->Cell(0, 10, 'Página ' . $this->PageNo(), 0, 0, 'C');
    }
    
    public function AddSection($title)
    {
        $this->SetFont('Helvetica', 'B', 11);
        $this->SetTextColor(39, 174, 96);
        $this->Cell(0, 8, $title, 0, 1);
        $this->SetTextColor(0, 0, 0);
    }
    
    public function AddField($label, $value)
    {
        $this->SetFont('Helvetica', 'B', 10);
        $this->Cell(50, 6, $label . ':', 0);
        
        $this->SetFont('Helvetica', '', 10);
        $this->Cell(0, 6, $value, 0, 1);
    }
    
    public function AddWideField($label, $value)
    {
        $this->SetFont('Helvetica', 'B', 10);
        $this->Cell(0, 6, $label . ':', 0, 1);
        
        $this->SetFont('Helvetica', '', 10);
        $this->MultiCell(0, 5, $value);
    }
}

class ComprobantePDFGenerator
{
    private $comprobante_data;
    private $pdf;
    private $output_dir;
    
    public function __construct($output_dir = null)
    {
        $this->output_dir = $output_dir ?: __DIR__ . '/../../uploads/comprobantes/';
        
        // Crear directorio si no existe
        if (!is_dir($this->output_dir)) {
            mkdir($this->output_dir, 0755, true);
        }
    }
    
    /**
     * Generar PDF del comprobante de entrega
     */
    public function generar($data)
    {
        // Guardar nivel de error actual
        $oldErrorReporting = error_reporting();
        
        // Suprimir warnings durante generación de PDF
        error_reporting(0);
        ini_set('display_errors', 0);
        
        $this->comprobante_data = $data;
        $this->pdf = new ComprobantePDF('P', 'mm', 'A4');
        $this->pdf->AddPage();
        
        // Información principal
        $this->_addInfoPrincipal();
        
        $this->pdf->Ln(3);
        
        // Información de entrega
        $this->_addInfoEntrega();
        
        $this->pdf->Ln(5);
        
        // Información del receptor
        $this->_addInfoReceptor();
        
        $this->pdf->Ln(5);
        
        // Información del pedido
        if (!empty($data['numero_pedido']) || !empty($data['numero_factura'])) {
            $this->_addInfoPedido();
            $this->pdf->Ln(5);
        }
        
        // Códigos y referencias
        $this->_addCodigosReferencia();
        
        $this->pdf->Ln(10);
        
        // Evidencias fotográficas (firma y foto)
        $this->_addEvidenciasFotograficas();
        
        $this->pdf->Ln(5);
        
        // Firma y timestamp
        $this->_addFirmasYTimestamp();
        
        // Generar nombre del archivo
        $filename = 'comprobante_' . $data['codigo_qr'] . '.pdf';
        $filepath = $this->output_dir . $filename;
        
        // Guardar PDF
        $this->pdf->Output('F', $filepath);
        
        // Restaurar nivel de error original
        error_reporting($oldErrorReporting);
        
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'url' => '/projectTiendaVirtual/uploads/comprobantes/' . $filename
        ];
    }
    
    private function _addInfoPrincipal()
    {
        $this->pdf->AddSection('INFORMACIÓN GENERAL');
        
        $this->pdf->AddField('Código Comprobante', $this->comprobante_data['codigo_qr']);
        $this->pdf->AddField('Fecha de Entrega', date('d/m/Y H:i', strtotime($this->comprobante_data['fecha_entrega'])));
        $this->pdf->AddField('Repartidor', $this->comprobante_data['repartidor_nombre']);
    }
    
    private function _addInfoEntrega()
    {
        $this->pdf->AddSection('DIRECCIÓN Y DATOS DE ENTREGA');
        
        $this->pdf->AddField('Cliente', $this->comprobante_data['cliente_nombre']);
        $this->pdf->AddField('Teléfono', $this->comprobante_data['telefono_cliente'] ?: 'N/A');
        $this->pdf->AddWideField('Dirección', $this->comprobante_data['direccion_entrega']);
        $this->pdf->AddField('Ciudad', $this->comprobante_data['ciudad'] ?: 'N/A');
    }
    
    private function _addInfoReceptor()
    {
        $this->pdf->AddSection('RECIBIDO POR');
        
        $this->pdf->AddField('Nombre', $this->comprobante_data['receptor_nombre']);
        
        if (!empty($this->comprobante_data['receptor_documento'])) {
            $this->pdf->AddField('Documento', $this->comprobante_data['receptor_documento']);
        }
        
        if (!empty($this->comprobante_data['receptor_email'])) {
            $this->pdf->AddField('Email', $this->comprobante_data['receptor_email']);
        }
    }
    
    private function _addInfoPedido()
    {
        $this->pdf->AddSection('REFERENCIA DE PEDIDO');
        
        if (!empty($this->comprobante_data['numero_pedido'])) {
            $this->pdf->AddField('Número de Pedido', $this->comprobante_data['numero_pedido']);
        }
        
        if (!empty($this->comprobante_data['numero_factura'])) {
            $this->pdf->AddField('Número de Factura', $this->comprobante_data['numero_factura']);
        }
        
        if (!empty($this->comprobante_data['observaciones'])) {
            $this->pdf->AddWideField('Observaciones', $this->comprobante_data['observaciones']);
        }
    }
    
    private function _addCodigosReferencia()
    {
        $this->pdf->AddSection('CÓDIGO DE REFERENCIA');
        
        $this->pdf->SetFont('Helvetica', '', 10);
        $this->pdf->SetTextColor(100, 100, 100);
        $this->pdf->MultiCell(0, 5, 
            "Escanea este código QR para verificar la entrega:\n\n" .
            "Código: " . $this->comprobante_data['codigo_qr']
        );
        
        // Si hay ubicación GPS
        if (!empty($this->comprobante_data['latitud']) && !empty($this->comprobante_data['longitud'])) {
            $this->pdf->Ln(3);
            $this->pdf->SetFont('Helvetica', '', 9);
            $this->pdf->SetTextColor(100, 100, 100);
            $this->pdf->Cell(0, 5, 
                'Ubicación: ' . $this->comprobante_data['latitud'] . ', ' . $this->comprobante_data['longitud'],
                0, 1
            );
        }
    }
    
    private function _addEvidenciasFotograficas()
    {
        $tiene_firma = !empty($this->comprobante_data['firma_temp_path']);
        $tiene_foto = !empty($this->comprobante_data['foto_temp_path']);
        
        if (!$tiene_firma && !$tiene_foto) {
            return;
        }
        
        $this->pdf->AddSection('EVIDENCIAS FOTOGRÁFICAS');
        $this->pdf->Ln(2);
        
        $y_inicial = $this->pdf->GetY();
        $x_firma = 15;
        $x_foto = 110;
        $ancho_imagen = 85;
        $alto_imagen = 50;
        
        // Verificar si hay espacio suficiente en la página
        if (($y_inicial + $alto_imagen + 20) > 270) {
            $this->pdf->AddPage();
            $y_inicial = $this->pdf->GetY();
        }
        
        // Mostrar firma del receptor
        if ($tiene_firma) {
            $firma_file = $this->comprobante_data['firma_temp_path'];
            
            if (file_exists($firma_file) && is_readable($firma_file)) {
                // Marco para la firma
                $this->pdf->SetDrawColor(200, 200, 200);
                $this->pdf->Rect($x_firma, $y_inicial, $ancho_imagen, $alto_imagen);
                
                // Insertar imagen de firma
                try {
                    // Suprimir warnings de FPDF
                    @$this->pdf->Image($firma_file, $x_firma + 2, $y_inicial + 2, $ancho_imagen - 4, $alto_imagen - 4);
                } catch (Exception $e) {
                    // Si falla, mostrar texto alternativo
                    error_log('Error al insertar firma en PDF: ' . $e->getMessage());
                    $this->pdf->SetXY($x_firma, $y_inicial + ($alto_imagen / 2));
                    $this->pdf->SetFont('Helvetica', 'I', 8);
                    $this->pdf->SetTextColor(150, 150, 150);
                    $this->pdf->Cell($ancho_imagen, 5, 'Firma no disponible', 0, 0, 'C');
                }
                
                // Etiqueta debajo de la firma
                $this->pdf->SetXY($x_firma, $y_inicial + $alto_imagen + 2);
                $this->pdf->SetFont('Helvetica', 'B', 9);
                $this->pdf->SetTextColor(0, 0, 0);
                $this->pdf->Cell($ancho_imagen, 5, 'Firma del Receptor', 0, 0, 'C');
            }
        }
        
        // Mostrar foto de entrega
        if ($tiene_foto) {
            $foto_file = $this->comprobante_data['foto_temp_path'];
            
            if (file_exists($foto_file) && is_readable($foto_file)) {
                // Marco para la foto
                $this->pdf->SetDrawColor(200, 200, 200);
                $this->pdf->Rect($x_foto, $y_inicial, $ancho_imagen, $alto_imagen);
                
                // Insertar imagen de foto
                try {
                    // Suprimir warnings de FPDF
                    @$this->pdf->Image($foto_file, $x_foto + 2, $y_inicial + 2, $ancho_imagen - 4, $alto_imagen - 4);
                } catch (Exception $e) {
                    // Si falla, mostrar texto alternativo
                    error_log('Error al insertar foto en PDF: ' . $e->getMessage());
                    $this->pdf->SetXY($x_foto, $y_inicial + ($alto_imagen / 2));
                    $this->pdf->SetFont('Helvetica', 'I', 8);
                    $this->pdf->SetTextColor(150, 150, 150);
                    $this->pdf->Cell($ancho_imagen, 5, 'Foto no disponible', 0, 0, 'C');
                }
                
                // Etiqueta debajo de la foto
                $this->pdf->SetXY($x_foto, $y_inicial + $alto_imagen + 2);
                $this->pdf->SetFont('Helvetica', 'B', 9);
                $this->pdf->SetTextColor(0, 0, 0);
                $this->pdf->Cell($ancho_imagen, 5, 'Foto de Entrega', 0, 0, 'C');
            }
        }
        
        // Mover cursor después de las imágenes
        $this->pdf->SetY($y_inicial + $alto_imagen + 10);
        $this->pdf->SetTextColor(0, 0, 0);
    }
    
    private function _addFirmasYTimestamp()
    {
        $this->pdf->SetFont('Helvetica', 'B', 10);
        $this->pdf->SetTextColor(39, 174, 96);
        
        $this->pdf->Cell(0, 8, '✓ COMPROBANTE GENERADO ELECTRÓNICAMENTE', 0, 1);
        
        $this->pdf->SetFont('Helvetica', '', 9);
        $this->pdf->SetTextColor(100, 100, 100);
        
        $fecha_generacion = date('d/m/Y H:i:s');
        $this->pdf->Cell(0, 5, 'Fecha de generación: ' . $fecha_generacion, 0, 1);
        $this->pdf->Cell(0, 5, 'Sistema: ' . $_SERVER['HTTP_HOST'], 0, 1);
        
        $this->pdf->Ln(5);
        
        // Línea separadora final
        $this->pdf->SetDrawColor(200, 200, 200);
        $this->pdf->Line(10, $this->pdf->GetY(), 200, $this->pdf->GetY());
        
        $this->pdf->Ln(5);
        
        $this->pdf->SetFont('Helvetica', 'I', 8);
        $this->pdf->SetTextColor(150, 150, 150);
        $this->pdf->MultiCell(0, 3,
            "Este documento es un comprobante electrónico de entrega de mercancía.\n" .
            "Ambas partes (cliente y repartidor) reciben copia de este comprobante.\n" .
            "Para cualquier reclamo, contacta a través de la plataforma de SolTecnInd."
        );
    }
}
