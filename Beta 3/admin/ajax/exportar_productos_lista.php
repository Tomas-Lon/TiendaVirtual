<?php
/**
 * Endpoint AJAX para exportar lista completa de productos
 * Soporta formatos: PDF y XLSX (CSV con BOM UTF-8)
 */

session_start();

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once '../../config/database.php';
require_once '../classes/PDFExporter.php';
require_once '../classes/CSVExporter.php';

// Obtener formato solicitado
$formato = $_GET['formato'] ?? 'pdf';

if (!in_array($formato, ['pdf', 'xlsx'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Formato no válido']);
    exit;
}

try {
    $pdo = getConnection();
    
    // Obtener todos los productos ordenados por código
    $sql = "SELECT 
                p.codigo,
                p.descripcion,
                g.nombre as grupo,
                p.precio,
                p.unidad_medida,
                p.unidad_empaque,
                p.created_at as fecha_creacion
            FROM productos p
            LEFT JOIN grupos_productos g ON p.grupo_id = g.id
            ORDER BY p.codigo ASC";
    
    $stmt = $pdo->query($sql);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($productos)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'No hay productos para exportar']);
        exit;
    }
    
    // Exportar según formato
    if ($formato === 'pdf') {
        exportarPDF($productos);
    } else {
        exportarXLSX($productos);
    }
    
} catch (Exception $e) {
    error_log("Error al exportar productos: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al exportar productos']);
    exit;
}

/**
 * Exporta la lista de productos en formato PDF
 */
function exportarPDF($productos) {
    require_once '../../fpdf/fpdf.php';
    
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();
    
    // Logo o encabezado
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Lista Completa de Productos', 0, 1, 'C');
    
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, 'Generado el: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
    $pdf->Ln(5);
    
    // Línea separadora
    $pdf->SetDrawColor(200, 200, 200);
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
    $pdf->Ln(5);
    
    // Headers de tabla
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetFillColor(230, 230, 230);
    
    $pdf->Cell(25, 8, utf8_decode('Código'), 1, 0, 'C', true);
    $pdf->Cell(65, 8, utf8_decode('Descripción'), 1, 0, 'C', true);
    $pdf->Cell(35, 8, 'Grupo', 1, 0, 'C', true);
    $pdf->Cell(23, 8, 'Precio', 1, 0, 'C', true);
    $pdf->Cell(20, 8, 'U. Medida', 1, 0, 'C', true);
    $pdf->Cell(22, 8, 'U. Empaque', 1, 1, 'C', true);
    
    // Datos
    $pdf->SetFont('Arial', '', 8);
    $contador = 0;
    
    foreach ($productos as $producto) {
        // Verificar si necesitamos nueva página
        if ($pdf->GetY() > 270) {
            $pdf->AddPage();
            
            // Repetir headers
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetFillColor(230, 230, 230);
            $pdf->Cell(25, 8, utf8_decode('Código'), 1, 0, 'C', true);
            $pdf->Cell(65, 8, utf8_decode('Descripción'), 1, 0, 'C', true);
            $pdf->Cell(35, 8, 'Grupo', 1, 0, 'C', true);
            $pdf->Cell(23, 8, 'Precio', 1, 0, 'C', true);
            $pdf->Cell(20, 8, 'U. Medida', 1, 0, 'C', true);
            $pdf->Cell(22, 8, 'U. Empaque', 1, 1, 'C', true);
            $pdf->SetFont('Arial', '', 7);
        }
        
        $codigo = substr($producto['codigo'], 0, 18);
        $descripcion = strlen($producto['descripcion']) > 40 
            ? substr($producto['descripcion'], 0, 37) . '...' 
            : $producto['descripcion'];
        $grupo = strlen($producto['grupo'] ?? 'Sin grupo') > 22
            ? substr($producto['grupo'] ?? 'Sin grupo', 0, 19) . '...'
            : ($producto['grupo'] ?? 'Sin grupo');
        $precio = '$' . number_format($producto['precio'], 2);
        $unidad_medida = $producto['unidad_medida'] ?? 'und';
        $unidad_empaque = $producto['unidad_empaque'] ?? 1;
        
        $pdf->Cell(25, 6, utf8_decode($codigo), 1, 0, 'L');
        $pdf->Cell(65, 6, utf8_decode($descripcion), 1, 0, 'L');
        $pdf->Cell(35, 6, utf8_decode($grupo), 1, 0, 'L');
        $pdf->Cell(23, 6, $precio, 1, 0, 'R');
        $pdf->Cell(20, 6, utf8_decode($unidad_medida), 1, 0, 'C');
        $pdf->Cell(22, 6, $unidad_empaque, 1, 1, 'C');
        
        $contador++;
    }
    
    // Resumen
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 8, 'Total de productos: ' . $contador, 0, 1, 'L');
    
    // Footer en la última página
    $pdf->SetY(-15);
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->SetTextColor(128, 128, 128);
    $pdf->Cell(0, 10, 'Página ' . $pdf->PageNo() . ' - Sistema de Gestión', 0, 0, 'C');
    
    // Generar descarga
    $nombre_archivo = 'lista_productos_' . date('Y-m-d_H-i-s') . '.pdf';
    
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Expires: 0');
    
    $pdf->Output('D', $nombre_archivo);
    exit;
}

/**
 * Exporta la lista de productos en formato XLSX (CSV con BOM UTF-8)
 */
function exportarXLSX($productos) {
    $nombre_archivo = 'lista_productos_' . date('Y-m-d_H-i-s') . '.csv';
    
    // Configurar headers para descarga
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Expires: 0');
    
    // Crear archivo CSV en memoria
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8 (para que Excel reconozca acentos)
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Headers
    $headers = ['Código', 'Descripción', 'Grupo', 'Precio', 'Unidad Medida', 'Unidad Empaque'];
    fputcsv($output, $headers, ';');
    
    // Datos
    foreach ($productos as $producto) {
        $fila = [
            $producto['codigo'],
            $producto['descripcion'],
            $producto['grupo'] ?? 'Sin grupo',
            '$' . number_format($producto['precio'], 2, '.', ','),
            $producto['unidad_medida'] ?? 'und',
            $producto['unidad_empaque'] ?? 1
        ];
        fputcsv($output, $fila, ';');
    }
    
    fclose($output);
    exit;
}
