<?php
if (!defined('CURLOPT_CONNECTTIMEOUT')) {
    define('CURLOPT_CONNECTTIMEOUT', 78);
}
if (!defined('CURLOPT_TIMEOUT')) {
    define('CURLOPT_TIMEOUT', 13);
}
if (!defined('CURLOPT_RETURNTRANSFER')) {
    define('CURLOPT_RETURNTRANSFER', 19913);
}
if (!defined('CURLOPT_SSL_VERIFYPEER')) {
    define('CURLOPT_SSL_VERIFYPEER', 64);
}
if (!defined('CURLOPT_FOLLOWLOCATION')) {
    define('CURLOPT_FOLLOWLOCATION', 52);
}

require_once('tcpdf/tcpdf.php'); 

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

try {
    $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8');

    $pdf->SetCreator('HomeSafe');
    $pdf->SetAuthor('HomeSafe');
    $pdf->SetTitle('Sales Report');

    $pdf->SetMargins(10, 15, 10);
    $pdf->SetAutoPageBreak(TRUE, 15);

    $pdf->AddPage();

    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Sales Report - HomeSafe', 0, 1, 'C');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 10);
    $header = array('Email', 'Date', 'State', 'Total', 'Direction', 'Phone Number', 'Payment Method', 'Transaction ID');
    
    $ancho_total = 297 - 20; 
    $w = array(
        round($ancho_total * 0.18), 
        round($ancho_total * 0.10), 
        round($ancho_total * 0.08), 
        round($ancho_total * 0.08), 
        round($ancho_total * 0.20), 
        round($ancho_total * 0.10), 
        round($ancho_total * 0.12), 
        round($ancho_total * 0.14)  
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

    $servername = "localhost";
    $username = "postgres";
    $password = "TU_PASSWORD_DE_BASE_DE_DATOS";
    $dbname = "homesafe";

    $conn_string = "host=$servername dbname=$dbname user=$username password=$password";
    $conn = pg_connect($conn_string);

    if (!$conn) {
        throw new Exception("Error de conexión PostgreSQL: " . pg_last_error());
    }

    $query = "SELECT email, fecha_pedido, estado, total, direccion_envio, telefono, metodo_pago, paypal_transaction_id FROM pedidos ORDER BY fecha_pedido DESC";
    $result = pg_query($conn, $query);

    if (!$result) {
        throw new Exception("Error en la consulta PostgreSQL: " . pg_last_error($conn));
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

        $email = $row['email'] ? substr($row['email'], 0, 22) : '';
        $fecha = $row['fecha_pedido'] ? date('d/m/Y', strtotime($row['fecha_pedido'])) : '';
        $estado = $row['estado'] ?? '';
        $total = $row['total'] ? '$' . number_format($row['total'], 2) : '$0.00';
        $direccion = $row['direccion_envio'] ? substr($row['direccion_envio'], 0, 25) : '';
        $telefono = $row['telefono'] ?? '';
        $metodo = $row['metodo_pago'] ?? '';
        $transaction_id = $row['paypal_transaction_id'] ? substr($row['paypal_transaction_id'], 0, 18) : '';

        $pdf->Cell($w[0], 10, $email, 'LR', 0, 'L', $fill);
        $pdf->Cell($w[1], 10, $fecha, 'LR', 0, 'C', $fill);
        $pdf->Cell($w[2], 10, $estado, 'LR', 0, 'C', $fill);
        $pdf->Cell($w[3], 10, $total, 'LR', 0, 'R', $fill);
        $pdf->Cell($w[4], 10, $direccion, 'LR', 0, 'L', $fill);
        $pdf->Cell($w[5], 10, $telefono, 'LR', 0, 'C', $fill);
        $pdf->Cell($w[6], 10, $metodo, 'LR', 0, 'C', $fill);
        $pdf->Cell($w[7], 10, $transaction_id, 'LR', 0, 'L', $fill);
        $pdf->Ln();
        $fill = !$fill;
    }

    $pdf->Cell(array_sum($w), 0, '', 'T');

    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->Cell(0, 5, 'Report generated: ' . date('m/d/Y H:i:s'), 0, 1, 'R');

    pg_close($conn);

    $pdf->Output('sales_report.pdf', 'D');
    
} catch (Exception $e) {
    error_log("Error en la generación del PDF: " . $e->getMessage());
    http_response_code(500);
    exit;
}
?>