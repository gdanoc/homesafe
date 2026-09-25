<?php
require_once('tcpdf/tcpdf.php'); 

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // Crear instancia PDF 
    $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8');

    // Información del documento
    $pdf->SetCreator('HomeSafe');
    $pdf->SetAuthor('HomeSafe');
    $pdf->SetTitle('Reporte de Usuarios');

    // Configurar márgenes y auto page break
    $pdf->SetMargins(10, 15, 10);
    $pdf->SetAutoPageBreak(TRUE, 15);

    // Agregar página
    $pdf->AddPage();

    // Título principal
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Reporte de Usuarios - HomeSafe', 0, 1, 'C');
    $pdf->Ln(5);

    // Encabezados de la tabla
    $pdf->SetFont('helvetica', 'B', 10);
    $header = array('Usuario', 'Email', 'Rol');
    
    // Calcular anchos para orientación horizontal (solo 3 columnas)
    $ancho_total = 297 - 20; 
    $w = array(
        round($ancho_total * 0.30), // Usuario
        round($ancho_total * 0.50), // Email  
        round($ancho_total * 0.20)  // Rol
    );

    // Color de encabezado (gris oscuro como el primer código)
    $pdf->SetFillColor(31, 38, 45); 
    $pdf->SetTextColor(255); 
    $pdf->SetDrawColor(31, 38, 45);

    // Crear encabezados
    foreach ($header as $i => $h) {
        $pdf->Cell($w[$i], 12, $h, 1, 0, 'C', true);
    }
    $pdf->Ln();

    // Configurar estilo para los datos
    $pdf->SetTextColor(0);
    $pdf->SetDrawColor(200, 200, 200);
    $pdf->SetFont('helvetica', '', 9);

    // Conexión a la base de datos
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

    // Consulta principal
    $query = "SELECT username, email, password, id_rol FROM accounts";
    $result = pg_query($conn, $query);

    if (!$result) {
        throw new Exception("Error en la consulta PostgreSQL: " . pg_last_error($conn));
    }

    // Roles en español
    $roles = [
        0 => 'Cliente',
        1 => 'Admin',
        2 => 'Vendedor de propiedad',
        3 => 'Repartidor'
    ];

    // Color de fondo alternante
    $pdf->SetFillColor(248, 249, 250);
    $fill = false;

    while ($row = pg_fetch_assoc($result)) {
        // Verificar si necesita nueva página
        if ($pdf->GetY() > 180) {
            $pdf->AddPage();
            
            // IMPORTANTE: Recrear encabezados correctamente
            $pdf->SetFillColor(31, 38, 45);
            $pdf->SetTextColor(255);
            $pdf->SetDrawColor(31, 38, 45);
            $pdf->SetFont('helvetica', 'B', 10);
            foreach ($header as $i => $h) {
                $pdf->Cell($w[$i], 12, $h, 1, 0, 'C', true);
            }
            $pdf->Ln();
            
            // CRÍTICO: Resetear configuración para datos
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(200, 200, 200);
            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetFillColor(248, 249, 250);
        }

        $rolTexto = isset($roles[$row['id_rol']]) ? $roles[$row['id_rol']] : 'Desconocido';

        // Truncar textos si son muy largos
        $username = strlen($row['username']) > 35 ? substr($row['username'], 0, 32) . '...' : $row['username'];
        $email = strlen($row['email']) > 55 ? substr($row['email'], 0, 52) . '...' : $row['email'];

        $pdf->Cell($w[0], 10, $username, 'LR', 0, 'L', $fill);
        $pdf->Cell($w[1], 10, $email, 'LR', 0, 'L', $fill);
        $pdf->Cell($w[2], 10, $rolTexto, 'LR', 0, 'C', $fill);
        $pdf->Ln();
        $fill = !$fill;
    }

    // Línea de cierre de tabla
    $pdf->Cell(array_sum($w), 0, '', 'T');

    // Pie de página con fecha de generación
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->Cell(0, 5, 'Reporte generado el: ' . date('d/m/Y H:i:s'), 0, 1, 'R');

    // Cerrar conexión
    pg_close($conn);

    // Generar y descargar PDF
    $pdf->Output('reporte_usuarios.pdf', 'D');

} catch (Exception $e) {
    error_log("Error en la generación del PDF: " . $e->getMessage());
    echo "Ha ocurrido un error al generar el reporte: " . $e->getMessage();
    exit;
}
?>