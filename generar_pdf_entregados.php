<?php
require_once('tcpdf/tcpdf.php');

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

try {
    $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8');

    $pdf->SetCreator('HomeSafe');
    $pdf->SetAuthor('HomeSafe');
    $pdf->SetTitle('Reporte de Pedidos Entregados');

    $pdf->SetMargins(10, 15, 10);
    $pdf->SetAutoPageBreak(TRUE, 15);

    $pdf->AddPage();

    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Reporte de Pedidos Entregados - HomeSafe', 0, 1, 'C');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 10);
    $header = ['Email', 'Fecha Pedido', 'Total', 'Dirección Envío', 'Teléfono', 'Método Pago', 'PayPal Transacción ID', 'Repartidor'];

    $ancho_total = 297 - 20;
    $w = [
        round($ancho_total * 0.18), // Email
        round($ancho_total * 0.10), // Fecha Pedido
        round($ancho_total * 0.08), // Total
        round($ancho_total * 0.16), // Dirección Envío
        round($ancho_total * 0.10), // Teléfono
        round($ancho_total * 0.12), // Método Pago
        round($ancho_total * 0.14), // PayPal Transacción ID
        round($ancho_total * 0.12)  // Repartidor
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
        throw new Exception("Error de conexión PostgreSQL: " . pg_last_error());
    }

    $query = "SELECT id, email, fecha_pedido, total, direccion_envio, telefono, metodo_pago, paypal_transaction_id FROM pedidos WHERE estado = 'entregado' ORDER BY fecha_pedido DESC";
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
        $fecha = $row['fecha_pedido'] ? date('d/m/Y', strtotime($row['fecha_pedido'])) : '';
        $total = $row['total'] ? '$' . number_format($row['total'], 2) : '$0.00';
        $direccion = $row['direccion_envio'] ? substr($row['direccion_envio'], 0, 25) : '';
        $telefono = $row['telefono'] ?? '';
        $metodo = $row['metodo_pago'] ?? '';
        $transaction_id = $row['paypal_transaction_id'] ? substr($row['paypal_transaction_id'], 0, 18) : '';

        // Obtener repartidor asignado
        $pedido_id = $row['id'];
        $query_repartidor = "SELECT r.nombre FROM asignaciones_entrega a JOIN repartidores r ON a.repartidor_id = r.id WHERE a.pedido_id = $1";
        $result_repartidor = pg_query_params($conn, $query_repartidor, array($pedido_id));
        $repartidor = 'No asignado';
        if ($result_repartidor && pg_num_rows($result_repartidor) > 0) {
            $row_repartidor = pg_fetch_assoc($result_repartidor);
            $repartidor = $row_repartidor['nombre'];
        }

        $pdf->Cell($w[0], 10, $email, 'LR', 0, 'L', $fill);
        $pdf->Cell($w[1], 10, $fecha, 'LR', 0, 'C', $fill);
        $pdf->Cell($w[2], 10, $total, 'LR', 0, 'R', $fill);
        $pdf->Cell($w[3], 10, $direccion, 'LR', 0, 'L', $fill);
        $pdf->Cell($w[4], 10, $telefono, 'LR', 0, 'C', $fill);
        $pdf->Cell($w[5], 10, $metodo, 'LR', 0, 'C', $fill);
        $pdf->Cell($w[6], 10, $transaction_id, 'LR', 0, 'L', $fill);
        $pdf->Cell($w[7], 10, $repartidor, 'LR', 0, 'L', $fill);
        $pdf->Ln();
        $fill = !$fill;
    }

    $pdf->Cell(array_sum($w), 0, '', 'T');

    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->Cell(0, 5, 'Reporte generado el: ' . date('d/m/Y H:i:s'), 0, 1, 'R');

    pg_close($conn);

    $pdf->Output('reporte_pedidos_entregados.pdf', 'D');

} catch (Exception $e) {
    error_log("Error en la generación del PDF: " . $e->getMessage());
    http_response_code(500);
    exit;
}
?>