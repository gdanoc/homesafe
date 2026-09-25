<?php
require_once('tcpdf/tcpdf.php'); 


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    
    $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8');

    
    $pdf->SetCreator('HomeSafe');
    $pdf->SetAuthor('HomeSafe');
    $pdf->SetTitle('Reporte de Suscripciones');

    
    $pdf->SetMargins(10, 15, 10);
    $pdf->SetAutoPageBreak(TRUE, 15);

    
    $pdf->AddPage();

    
    $pdf->SetFont('helvetica', 'B', 16);

    
    $pdf->Cell(0, 10, 'Reporte de Suscripciones - HomeSafe', 0, 1, 'C');
    $pdf->Ln(5);

    
    $pdf->SetFont('helvetica', 'B', 10);

    
    $header = ['Email', 'Teléfono', 'Fecha Suscrito', 'Fecha Vencimiento', 'ID Transacción PayPal', 'Tipo de suscripción'];
    
    
    $ancho_total = 297 - 20; 
    
    
    $w = [
        round($ancho_total * 0.25), 
        round($ancho_total * 0.12), 
        round($ancho_total * 0.15), 
        round($ancho_total * 0.15), 
        round($ancho_total * 0.20), 
        round($ancho_total * 0.13)  
    ];
    
    
    $w[5] = $ancho_total - array_sum(array_slice($w, 0, 5));

    
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

    
    $host = "localhost";
    $port = "5432";
    $dbname = "homesafe";
    $username = "postgres";
    $password = "TU_PASSWORD_DE_BASE_DE_DATOS";

    $conn_string = "host=$host port=$port dbname=$dbname user=$username password=$password";
    $conn = pg_connect($conn_string);

    if (!$conn) {
        throw new Exception("Error de conexión PostgreSQL: " . pg_last_error());
    }

    
    $query = "SELECT mail_user, telefono, fecha_suscrito, fecha_vencimiento, paypal_transaction_id, plan_id FROM suscripcion ORDER BY fecha_vencimiento ASC";
    $result = pg_query($conn, $query);

    if (!$result) {
        throw new Exception("Error en la consulta PostgreSQL: " . pg_last_error($conn));
    }

    
    
    $planes = [];
    $plan_query = "SELECT id, nombre FROM planes";
    $plan_result = pg_query($conn, $plan_query);
    if ($plan_result) {
        while ($plan = pg_fetch_assoc($plan_result)) {
            $planes[$plan['id']] = $plan['nombre'];
        }
    }

    
    $pdf->SetFillColor(248, 249, 250);
    $fill = false;

    while ($row = pg_fetch_assoc($result)) {
        $tipo_suscripcion = isset($planes[$row['plan_id']]) ? $planes[$row['plan_id']] : 'N/A';

        
        $email = strlen($row['mail_user']) > 35 ? substr($row['mail_user'], 0, 32) . '...' : $row['mail_user'];
        $telefono = strlen($row['telefono']) > 15 ? substr($row['telefono'], 0, 12) . '...' : $row['telefono'];
        $fecha_suscrito = date('d/m/Y', strtotime($row['fecha_suscrito']));
        $fecha_vencimiento = date('d/m/Y', strtotime($row['fecha_vencimiento']));
        $paypal_id = strlen($row['paypal_transaction_id'] ?? '') > 25 ? substr($row['paypal_transaction_id'] ?? '', 0, 22) . '...' : ($row['paypal_transaction_id'] ?? '');
        $tipo = strlen($tipo_suscripcion) > 15 ? substr($tipo_suscripcion, 0, 12) . '...' : $tipo_suscripcion;

        
        if ($pdf->GetY() > 180) {
            $pdf->AddPage();
            
            $pdf->SetFillColor(31, 38, 45);
            $pdf->SetTextColor(255);
            $pdf->SetFont('helvetica', 'B', 10);
            foreach ($header as $i => $h) {
                $pdf->Cell($w[$i], 12, $h, 1, 0, 'C', true);
            }
            $pdf->Ln();
            $pdf->SetTextColor(0);
            $pdf->SetFont('helvetica', '', 9);
        }

        
        $pdf->Cell($w[0], 10, $email, 'LR', 0, 'L', $fill);
        $pdf->Cell($w[1], 10, $telefono, 'LR', 0, 'C', $fill);
        $pdf->Cell($w[2], 10, $fecha_suscrito, 'LR', 0, 'C', $fill);
        $pdf->Cell($w[3], 10, $fecha_vencimiento, 'LR', 0, 'C', $fill);
        $pdf->Cell($w[4], 10, $paypal_id, 'LR', 0, 'L', $fill);
        $pdf->Cell($w[5], 10, $tipo, 'LR', 0, 'C', $fill);
        $pdf->Ln();
        $fill = !$fill;
    }

    
    $pdf->Cell(array_sum($w), 0, '', 'T');

    
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->Cell(0, 5, 'Reporte generado el: ' . date('d/m/Y H:i:s'), 0, 1, 'R');

    
    pg_close($conn);

    
    $pdf->Output('reporte_suscripciones.pdf', 'D');

} catch (Exception $e) {
    error_log("Error en la generación del PDF: " . $e->getMessage());
    echo "Ha ocurrido un error al generar el reporte: " . $e->getMessage();
    exit;
}
?>