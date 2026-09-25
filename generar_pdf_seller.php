<?php
session_start();

require_once('tcpdf/tcpdf.php');

// NO mostrar errores en pantalla para evitar problemas con PDF
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

if (!isset($_SESSION['email'])) {
    error_log("Sesión no iniciada o email no definido.");
    http_response_code(401);
    exit;
}

$email_vendedor = $_SESSION['email'];

try {
    // Crear instancia PDF 
    $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8');

    // Información del documento
    $pdf->SetCreator('HomeSafe');
    $pdf->SetAuthor('HomeSafe');
    $pdf->SetTitle('Reporte de Propiedades Vendidas');

    // Configurar márgenes y auto page break
    $pdf->SetMargins(10, 15, 10);
    $pdf->SetAutoPageBreak(TRUE, 15);

    // Agregar página
    $pdf->AddPage();

    // Título principal
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Reporte de Propiedades Vendidas - HomeSafe', 0, 1, 'C');
    $pdf->Ln(5);

    // Encabezados de la tabla
    $pdf->SetFont('helvetica', 'B', 10);
    $header = array('Nombre', 'Descripción', 'Baños', 'Habitaciones', 'Tamaño', 'Precio', 'Email del Comprador', 'Fecha de Compra');
    
    // Calcular anchos para orientación horizontal (8 columnas)
    $ancho_total = 297 - 20; 
    $w = array(
        round($ancho_total * 0.15), // Nombre
        round($ancho_total * 0.20), // Descripción
        round($ancho_total * 0.08), // Baños
        round($ancho_total * 0.10), // Habitaciones
        round($ancho_total * 0.10), // Tamaño
        round($ancho_total * 0.10), // Precio
        round($ancho_total * 0.17), // Email del Comprador
        round($ancho_total * 0.10)  // Fecha de Compra
    );

    // Color de encabezado (gris oscuro estándar)
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
    $conn = pg_connect("host=localhost dbname=homesafe user=postgres password=Info2025/*-");
    if (!$conn) {
        throw new Exception("Error de conexión PostgreSQL: " . pg_last_error());
    }

    // Consulta principal
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
        throw new Exception("Error en la consulta PostgreSQL: " . pg_last_error($conn));
    }

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

        // Manejar valores null y formatear datos
        $nombre = $row['nombre'] ? substr($row['nombre'], 0, 18) : '';
        $descripcion = $row['descripcion'] ? substr($row['descripcion'], 0, 25) : '';
        $bathrooms = $row['bathrooms'] ?? '0';
        $bedrooms = $row['bedrooms'] ?? '0';
        $size = $row['size'] ? substr($row['size'], 0, 12) : '';
        $precio = $row['precio'] ? '$' . number_format($row['precio'], 2) : '$0.00';
        $comprador = $row['comprador'] ? substr($row['comprador'], 0, 22) : '';
        $fecha_compra = $row['fecha_compra'] ? date('d/m/Y', strtotime($row['fecha_compra'])) : '';

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

    // Línea de cierre de tabla
    $pdf->Cell(array_sum($w), 0, '', 'T');

    // Pie de página con fecha de generación
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->Cell(0, 5, 'Reporte generado el: ' . date('d/m/Y H:i:s'), 0, 1, 'R');

    // Cerrar conexión
    pg_close($conn);

    // Generar y descargar PDF
    $pdf->Output('reporte_propiedades_vendidas.pdf', 'D');

} catch (Exception $e) {
    // Log del error sin mostrarlo en pantalla
    error_log("Error en la generación del PDF: " . $e->getMessage());
    http_response_code(500);
    exit;
}
?>