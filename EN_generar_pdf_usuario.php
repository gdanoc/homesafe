<?php
require_once('tcpdf/tcpdf.php'); 

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8');

    $pdf->SetCreator('HomeSafe');
    $pdf->SetAuthor('HomeSafe');
    $pdf->SetTitle('Report of Users');

    $pdf->SetMargins(10, 15, 10);
    $pdf->SetAutoPageBreak(TRUE, 15);

    $pdf->AddPage();

    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Report of Users - HomeSafe', 0, 1, 'C');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 10);
    $header = array('User', 'Email', 'Rol');
    
    $ancho_total = 297 - 20; 
    $w = array(
        round($ancho_total * 0.30), 
        round($ancho_total * 0.45), 
        round($ancho_total * 0.25)  
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

    $host = "localhost";
    $port = "5432";
    $dbname = "homesafe";
    $username = "postgres";
    $password = "TU_PASSWORD_DE_BASE_DE_DATOS";

    $conn_string = "host=$host port=$port dbname=$dbname user=$username password=$password";
    $conn = pg_connect($conn_string);

    if (!$conn) {
        throw new Exception("Conection error PostgreSQL: " . pg_last_error());
    }

    $query = "SELECT username, email, password, id_rol FROM accounts";
    $result = pg_query($conn, $query);

    if (!$result) {
        throw new Exception("Error query PostgreSQL: " . pg_last_error($conn));
    }

    $roles = [
        0 => 'Customer',
        1 => 'Admin',
        2 => 'Real State Agent',
        3 => 'Delivery Person'
    ];

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

        $rolTexto = isset($roles[$row['id_rol']]) ? $roles[$row['id_rol']] : 'Unknown';

        $username = strlen($row['username']) > 35 ? substr($row['username'], 0, 32) . '...' : $row['username'];
        $email = strlen($row['email']) > 50 ? substr($row['email'], 0, 47) . '...' : $row['email'];

        $pdf->Cell($w[0], 10, $username, 'LR', 0, 'L', $fill);
        $pdf->Cell($w[1], 10, $email, 'LR', 0, 'L', $fill);
        $pdf->Cell($w[2], 10, $rolTexto, 'LR', 0, 'C', $fill);
        $pdf->Ln();
        $fill = !$fill;
    }

    $pdf->Cell(array_sum($w), 0, '', 'T');

    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->Cell(0, 5, 'Report generated el: ' . date('m/d/Y H:i:s'), 0, 1, 'R');

    pg_close($conn);

    $pdf->Output('users_reports.pdf', 'D');

} catch (Exception $e) {
    error_log("Error generating PDF: " . $e->getMessage());
    echo "There was an error generating the report: " . $e->getMessage();
    exit;
}
?>