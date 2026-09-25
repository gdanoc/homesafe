<?php
session_start();
header('Content-Type: application/json');


if (!isset($_SESSION['email']) || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}


$host = "localhost";
$port = "5432";
$dbname = "homesafe";
$user = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";

$conn_string = "host=$host port=$port dbname=$dbname user=$user password=$password";
$conn = pg_connect($conn_string);

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit();
}


$input = json_decode(file_get_contents('php://input'), true);
$pedido_id = $input['pedido_id'] ?? null;
$latitud = $input['latitud'] ?? null;
$longitud = $input['longitud'] ?? null;
$repartidor_id = $_SESSION['user_id'];

if (!$pedido_id || !$latitud || !$longitud) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit();
}

try {
    
    $query = "UPDATE asignaciones_entrega 
              SET latitud_repartidor = $1, longitud_repartidor = $2 
              WHERE pedido_id = $3 AND repartidor_id = $4";
    
    $result = pg_query_params($conn, $query, array($latitud, $longitud, $pedido_id, $repartidor_id));
    
    if ($result) {
        
        $query2 = "UPDATE repartidores 
                   SET latitud_actual = $1, longitud_actual = $2 
                   WHERE id = $3";
        
        pg_query_params($conn, $query2, array($latitud, $longitud, $repartidor_id));
        
        echo json_encode(['success' => true, 'message' => 'Ubicación guardada correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar la ubicación']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

pg_close($conn);
?>