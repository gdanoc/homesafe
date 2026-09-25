<?php
require_once('tcpdf/tcpdf.php'); 

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

try {
    $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8');

    $pdf->SetCreator('HomeSafe');
    $pdf->SetAuthor('HomeSafe');
    $pdf->SetTitle('Delivered Orders Report');

    $pdf->SetMargins(10, 15, 10);
    $pdf->SetAutoPageBreak(TRUE, 15);

    $pdf->AddPage();

    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Delivered Orders Report - HomeSafe', 0, 1, 'C');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 10);
    $header = ['Email', 'Order Date', 'Total', 'Shipping Address', 'Phone Number', 'Payment Method', 'PayPal Transaction ID', 'Delivery Person'];

    $ancho_total = 297 - 20;
    $w = [
        round($ancho_total * 0.18), // Email
        round($ancho_total * 0.10), // Order Date
        round($ancho_total * 0.08), // Total
        round($ancho_total * 0.16), // Shipping Address
        round($ancho_total * 0.10), // Phone Number
        round($ancho_total * 0.12), // Payment Method
        round($ancho_total * 0.14), // PayPal Transaction ID
        round($ancho_total * 0.12)  // Delivery Person
    ];

    $pdf->SetFillColor(31, 38, 45);
    $pdf->SetTextColor(255);
    $pdf->SetDrawColor(31, 38, 45);

    foreach ($header as $i => $col) {
        $pdf->Cell($w[$i], 12, $col, 1, 0, 'C', true);
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
        throw new Exception("PostgreSQL connection error: " . pg_last_error());
    }

    $query = "SELECT id, email, fecha_pedido, total, direccion_envio, telefono, metodo_pago, paypal_transaction_id FROM pedidos WHERE estado = 'entregado' ORDER BY fecha_pedido DESC";
    $result = pg_query($conn, $query);

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
            foreach ($header as $i => $col) {
                $pdf->Cell($w[$i], 12, $col, 1, 0, 'C', true);
            }
            $pdf->Ln();

            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(200, 200, 200);
            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetFillColor(248, 249, 250);
        }

        $email = $row['email'] ? substr($row['email'], 0, 22) : '';
        $fecha = $row['fecha_pedido'] ? date('m/d/Y', strtotime($row['fecha_pedido'])) : '';
        $total = $row['total'] ? '$' . number_format($row['total'], 2) : '$0.00';
        $direccion = $row['direccion_envio'] ? substr($row['direccion_envio'], 0, 25) : '';
        $telefono = $row['telefono'] ?? '';
        $metodo = $row['metodo_pago'] ?? '';
        $transaction_id = $row['paypal_transaction_id'] ? substr($row['paypal_transaction_id'], 0, 18) : '';

        // Get delivery person assigned
        $pedido_id = $row['id'];
        $query_delivery = "SELECT r.nombre FROM asignaciones_entrega a JOIN repartidores r ON a.repartidor_id = r.id WHERE a.pedido_id = $1";
        $result_delivery = pg_query_params($conn, $query_delivery, array($pedido_id));
        $delivery_person = 'Not assigned';
        if ($result_delivery && pg_num_rows($result_delivery) > 0) {
            $row_delivery = pg_fetch_assoc($result_delivery);
            $delivery_person = $row_delivery['nombre'];
        }

        $pdf->Cell($w[0], 10, $email, 'LR', 0, 'L', $fill);
        $pdf->Cell($w[1], 10, $fecha, 'LR', 0, 'C', $fill);
        $pdf->Cell($w[2], 10, $total, 'LR', 0, 'R', $fill);
        $pdf->Cell($w[3], 10, $direccion, 'LR', 0, 'L', $fill);
        $pdf->Cell($w[4], 10, $telefono, 'LR', 0, 'C', $fill);
        $pdf->Cell($w[5], 10, $metodo, 'LR', 0, 'C', $fill);
        $pdf->Cell($w[6], 10, $transaction_id, 'LR', 0, 'L', $fill);
        $pdf->Cell($w[7], 10, $delivery_person, 'LR', 0, 'L', $fill);
        $pdf->Ln();
        $fill = !$fill;
    }

    $pdf->Cell(array_sum($w), 0, '', 'T');

    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->Cell(0, 5, 'Report Generated: ' . date('m/d/Y H:i:s'), 0, 1, 'R');

    pg_close($conn);

    $pdf->Output('report_orders_delivered.pdf', 'D');

} catch (Exception $e) {
    error_log("Error generating PDF: " . $e->getMessage());
    http_response_code(500);
    exit;
}
?>