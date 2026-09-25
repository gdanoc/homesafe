<?php
session_start();

require_once('tcpdf/tcpdf.php');

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

if (!isset($_SESSION['email'])) {
    error_log("Session not started or email not defined.");
    http_response_code(401);
    exit;
}

$email_vendedor = $_SESSION['email'];

try {
    $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8');

    $pdf->SetCreator('HomeSafe');
    $pdf->SetAuthor('HomeSafe');
    $pdf->SetTitle('Properties Sold Report');

    $pdf->SetMargins(10, 15, 10);
    $pdf->SetAutoPageBreak(TRUE, 15);

    $pdf->AddPage();

    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Properties Sold Report - HomeSafe', 0, 1, 'C');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 10);
    $header = array('Name', 'Description', 'Bathrooms', 'Rooms', 'Size', 'Price', 'Customer\'s Email', 'Purchase Date');
    
    $ancho_total = 297 - 20; 
    $w = array(
        round($ancho_total * 0.15), 
        round($ancho_total * 0.20), 
        round($ancho_total * 0.08), 
        round($ancho_total * 0.08), 
        round($ancho_total * 0.10), 
        round($ancho_total * 0.10), 
        round($ancho_total * 0.19), 
        round($ancho_total * 0.10)  
    );

    $pdf->SetFillColor(31, 38, 45); 
    $pdf->SetTextColor(255); 
    $pdf->SetDrawColor(31, 38, 45);

    foreach ($header as $i => $h) {
        $pdf->Cell($w[$i], 12, $h, 1, 0, 'C', true);
    }
    $pdf->Ln();

    $pdf->SetTextColor(0);
    $pdf->SetDrawColor(200, 200, 200);
    $pdf->SetFont('helvetica', '', 9);

    $conn = pg_connect("host=localhost dbname=homesafe user=postgres password=Info2025/*-");
    if (!$conn) {
        throw new Exception("PostgreSQL connection error: " . pg_last_error());
    }

    $query = "
        SELECT 
            p.nombre, 
            p.descripcion, 
            p.bathrooms, 
            p.bedrooms, 
            p.size, 
            p.precio, 
            s.comprador, 
            s.fecha_compra
        FROM propiedades p
        JOIN sales_seller s ON p.id = s.id_propiedad
        WHERE p.mail_user = $1 AND p.estado = 'Vendido'
        ORDER BY s.fecha_compra DESC
    ";

    $result = pg_query_params($conn, $query, array($email_vendedor));
    if (!$result) {
        throw new Exception("PostgreSQL query error: " . pg_last_error($conn));
    }

    $pdf->SetFillColor(248, 249, 250);
    $fill = false;

    while ($row = pg_fetch_assoc($result)) {
        if ($pdf->GetY() > 180) {
            $pdf->AddPage();
            
            $pdf->SetFillColor(31, 38, 45);
            $pdf->SetTextColor(255);
            $pdf->SetDrawColor(31, 38, 45);
            $pdf->SetFont('helvetica', 'B', 10);
            foreach ($header as $i => $h) {
                $pdf->Cell($w[$i], 12, $h, 1, 0, 'C', true);
            }
            $pdf->Ln();
            
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(200, 200, 200);
            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetFillColor(248, 249, 250);
        }

        $nombre = $row['nombre'] ? substr($row['nombre'], 0, 18) : '';
        $descripcion = $row['descripcion'] ? substr($row['descripcion'], 0, 25) : '';
        $bathrooms = $row['bathrooms'] ?? '0';
        $bedrooms = $row['bedrooms'] ?? '0';
        $size = $row['size'] ? substr($row['size'], 0, 12) : '';
        $precio = $row['precio'] ? '$' . number_format($row['precio'], 2) : '$0.00';
        $comprador = $row['comprador'] ? substr($row['comprador'], 0, 25) : '';
        $fecha_compra = $row['fecha_compra'] ? date('m/d/Y', strtotime($row['fecha_compra'])) : '';

        $pdf->Cell($w[0], 10, $nombre, 'LR', 0, 'L', $fill);
        $pdf->Cell($w[1], 10, $descripcion, 'LR', 0, 'L', $fill);
        $pdf->Cell($w[2], 10, $bathrooms, 'LR', 0, 'C', $fill);
        $pdf->Cell($w[3], 10, $bedrooms, 'LR', 0, 'C', $fill);
        $pdf->Cell($w[4], 10, $size, 'LR', 0, 'C', $fill);
        $pdf->Cell($w[5], 10, $precio, 'LR', 0, 'R', $fill);
        $pdf->Cell($w[6], 10, $comprador, 'LR', 0, 'L', $fill);
        $pdf->Cell($w[7], 10, $fecha_compra, 'LR', 0, 'C', $fill);
        $pdf->Ln();
        $fill = !$fill;
    }

    $pdf->Cell(array_sum($w), 0, '', 'T');

    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->Cell(0, 5, 'Report generated on: ' . date('m/d/Y H:i:s'), 0, 1, 'R');

    pg_close($conn);

    $pdf->Output('report_properties_sold.pdf', 'D');

} catch (Exception $e) {
    error_log("Error generating PDF: " . $e->getMessage());
    http_response_code(500);
    exit;
}
?>