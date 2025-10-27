<?php
// Simple guía de envío en PDF usando FPDF
require_once __DIR__ . '/../fpdf/fpdf.php';
require_once __DIR__ . '/../config/database.php';

$pdo = getConnection();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die('ID inválido');
}

// Traer datos del envío
$sql = "SELECT e.*, p.id AS pedido_numero, c.nombre AS cliente_nombre, c.email AS cliente_email, p.total AS pedido_total,
               dc.direccion AS direccion_entrega, dc.ciudad AS ciudad_entrega
        FROM envios e
        INNER JOIN pedidos p ON e.pedido_id = p.id
        INNER JOIN clientes c ON p.cliente_id = c.id
        LEFT JOIN direcciones_clientes dc ON e.direccion_entrega_id = dc.id
        WHERE e.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$envio = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$envio) {
    die('Envío no encontrado');
}

class PDF extends FPDF {
    function Header() {
        // Encabezado
        $this->SetFont('Arial','B',14);
        $this->Cell(0,10,utf8_decode('Guía de Envío'),0,1,'C');
        $this->Ln(2);
    }
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,utf8_decode('Página ').$this->PageNo().'/{nb}',0,0,'C');
    }
}

$pdf = new PDF('P','mm','Letter');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',10);

// Datos principales
$pdf->SetFillColor(240,240,240);
$pdf->SetFont('Arial','B',11);
$pdf->Cell(0,8,utf8_decode('Información del Envío'),0,1,'L',true);
$pdf->Ln(1);

$pdf->SetFont('Arial','',10);
$pdf->Cell(45,7,utf8_decode('Número de Guía:'),0,0,'L');
$pdf->SetFont('Arial','B',11);
$pdf->Cell(60,7,utf8_decode($envio['numero_guia'] ?: '-'),0,1,'L');

$pdf->SetFont('Arial','',10);
$pdf->Cell(45,7,utf8_decode('Transportista:'),0,0,'L');
$pdf->Cell(60,7,utf8_decode($envio['transportista'] ?: '-'),0,1,'L');

$pdf->Cell(45,7,utf8_decode('Estado:'),0,0,'L');
$estado = $envio['estado'];
$estadoLegible = ucwords(str_replace('_',' ', $estado));
$pdf->Cell(60,7,utf8_decode($estadoLegible),0,1,'L');

$pdf->Cell(45,7,utf8_decode('Fecha programada:'),0,0,'L');
$pdf->Cell(60,7,utf8_decode($envio['fecha_programada'] ?: '-'),0,1,'L');

if (!empty($envio['fecha_entrega_real'])) {
    $pdf->Cell(45,7,utf8_decode('Fecha entrega real:'),0,0,'L');
    $pdf->Cell(60,7,utf8_decode($envio['fecha_entrega_real']),0,1,'L');
}

$pdf->Ln(4);
$pdf->SetFont('Arial','B',11);
$pdf->Cell(0,8,utf8_decode('Cliente y Pedido'),0,1,'L',true);
$pdf->Ln(1);
$pdf->SetFont('Arial','',10);
$pdf->Cell(45,7,utf8_decode('Cliente:'),0,0,'L');
$pdf->Cell(120,7,utf8_decode($envio['cliente_nombre']),0,1,'L');

$pdf->Cell(45,7,utf8_decode('Correo:'),0,0,'L');
$pdf->Cell(120,7,utf8_decode($envio['cliente_email']),0,1,'L');

$pdf->Cell(45,7,utf8_decode('Pedido #:'),0,0,'L');
$pdf->Cell(120,7,'#'.$envio['pedido_numero'],0,1,'L');

$pdf->Cell(45,7,utf8_decode('Total Pedido:'),0,0,'L');
$pdf->Cell(120,7,'$'.number_format(floatval($envio['pedido_total']),2,'.',','),0,1,'L');

$pdf->Ln(4);
$pdf->SetFont('Arial','B',11);
$pdf->Cell(0,8,utf8_decode('Dirección de Entrega'),0,1,'L',true);
$pdf->Ln(1);
$pdf->SetFont('Arial','',10);
$direccion = trim(($envio['direccion_entrega'] ?: '').' '.($envio['ciudad_entrega'] ? ' - '.$envio['ciudad_entrega'] : ''));
$pdf->MultiCell(0,7,utf8_decode($direccion !== '' ? $direccion : '-'));

if (!empty($envio['observaciones'])) {
    $pdf->Ln(4);
    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(0,8,utf8_decode('Observaciones'),0,1,'L',true);
    $pdf->Ln(1);
    $pdf->SetFont('Arial','',10);
    $pdf->MultiCell(0,7,utf8_decode($envio['observaciones']));
}

$pdf->Ln(10);
$pdf->SetFont('Arial','I',9);
$pdf->Cell(0,6,utf8_decode('Generado por Sol Técnica - '.date('Y-m-d H:i')),0,1,'C');

$pdf->Output('I', 'guia_envio_'.$envio['numero_guia'].'.pdf');
