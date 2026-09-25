<?php
require_once('tcpdf/tcpdf.php'); 

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

try {
    $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8');

    $pdf->SetCreator('HomeSafe');
    $pdf->SetAuthor('HomeSafe');
    $pdf->SetTitle('Reporte de Propiedades');

    $pdf->SetMargins(10, 15, 10);
    $pdf->SetAutoPageBreak(TRUE, 15);

    $pdf->AddPage();

    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Reporte de Propiedades - HomeSafe', 0, 1, 'C');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 10);
    $header = ['Nombre', 'Descripción', 'Tamaño', 'Precio', 'Fecha', 'Email'];

    $ancho_total = 297 - 20;
    $w = [
        round($ancho_total * 0.17), // Nombre
        round($ancho_total * 0.30), // Descripción
        round($ancho_total * 0.10), // Tamaño
        round($ancho_total * 0.10), // Precio
        round($ancho_total * 0.10), // Fecha
        round($ancho_total * 0.23)  // Email
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

    $query = "SELECT nombre, descripcion, size, precio, fecha, mail_user FROM propiedades ORDER BY fecha DESC";
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

        $nombre = $row['nombre'] ? substr($row['nombre'], 0, 30) : '';
        $descripcion = $row['descripcion'] ? substr($row['descripcion'], 0, 50) : '';
        $size = $row['size'] ?? '';
        $precio = $row['precio'] ? '$' . number_format($row['precio'], 2) : '$0.00';
        $fecha = $row['fecha'] ? date('d/m/Y', strtotime($row['fecha'])) : '';
        $email = $row['mail_user'] ? substr($row['mail_user'], 0, 40) : '';

        $pdf->Cell($w[0], 10, $nombre, 'LR', 0, 'L', $fill);
        $pdf->Cell($w[1], 10, $descripcion, 'LR', 0, 'L', $fill);
        $pdf->Cell($w[2], 10, $size, 'LR', 0, 'C', $fill);
        $pdf->Cell($w[3], 10, $precio, 'LR', 0, 'R', $fill);
        $pdf->Cell($w[4], 10, $fecha, 'LR', 0, 'C', $fill);
        $pdf->Cell($w[5], 10, $email, 'LR', 0, 'L', $fill);
        $pdf->Ln();
        $fill = !$fill;
    }

    $pdf->Cell(array_sum($w), 0, '', 'T');

    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->Cell(0, 5, 'Report generated: ' . date('m/d/Y H:i:s'), 0, 1, 'R');

    pg_close($conn);

    $pdf->Output('properties_report.pdf', 'D');

} catch (Exception $e) {
    error_log("Error en la generación del PDF: " . $e->getMessage());
    http_response_code(500);
    exit;
}
?>