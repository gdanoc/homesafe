<?php
header('Content-Type: application/json');


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    
    $servername = "localhost";
    $username = "postgres";
    $password = "TU_PASSWORD_DE_BASE_DE_DATOS";
    $dbname = "homesafe";

    
    $conn = pg_connect("host=$servername dbname=$dbname user=$username password=$password");

    
    if (!$conn) {
        throw new Exception("Conexión fallida: " . pg_last_error());
    }

    
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($id <= 0) {
        throw new Exception("Invalid furniture ID");
    }

    
    $query = "SELECT 
        m.id, 
        m.nombre, 
        m.descripcion, 
        m.precio, 
        m.cantidad, 
        m.imagen, 
        m.descuento,
        COALESCE(AVG(r.rating), 0) as avg_rating,
        COUNT(r.id) as total_reviews
    FROM muebles m 
    LEFT JOIN reviews r ON m.id = r.mueble_id 
    WHERE m.id = $1
    GROUP BY m.id, m.nombre, m.descripcion, m.precio, m.cantidad, m.imagen, m.descuento";

    
    $result = pg_query_params($conn, $query, [$id]);

    if (!$result) {
        throw new Exception("Error in furniture query: " . pg_last_error($conn));
    }

    $furniture = [];
    if (pg_num_rows($result) > 0) {
        $furniture = pg_fetch_assoc($result);
        $furniture['imagen'] = base64_encode(pg_unescape_bytea($furniture['imagen']));
        
        
        $precio_original = (float)$furniture['precio'];
        $descuento_valor = isset($furniture['descuento']) ? (float)$furniture['descuento'] : 0.00;
        
        if ($descuento_valor > 0) {
            $precio_con_descuento = $precio_original * (1 - ($descuento_valor / 100));
            $furniture['precio_original'] = number_format($precio_original, 2);
            $furniture['precio'] = number_format($precio_con_descuento, 2);
        } else {
            $furniture['precio'] = number_format($precio_original, 2);
        }
        
        $furniture['descuento'] = $descuento_valor;
    } else {
        throw new Exception("Furniture not found");
    }

    
    $reviews = [];

$queryReviews = "SELECT id, rating, comment, created_at, user_email FROM reviews WHERE mueble_id = $1 ORDER BY created_at DESC";
$resultReviews = pg_query_params($conn, $queryReviews, [$id]);
    if ($resultReviews) {
        while ($row = pg_fetch_assoc($resultReviews)) {
            $reviews[] = $row;
        }
    }

    
    pg_close($conn);

    
    echo json_encode([
        'furniture' => $furniture,
        'reviews' => $reviews
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>