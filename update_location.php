<?php
header('Content-Type: application/json');
session_start();


if (!isset($_SESSION['email']) || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Usuario no autenticado']);
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
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']);
    exit();
}


$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['latitud']) || !isset($input['longitud']) || !isset($input['id_asignacion'])) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit();
}

$latitud = floatval($input['latitud']);
$longitud = floatval($input['longitud']);
$id_asignacion = intval($input['id_asignacion']);
$repartidor_id = $_SESSION['user_id'];

try {
    
    $query_asignacion = "UPDATE asignaciones_entrega 
                        SET latitud_repartidor = $1, longitud_repartidor = $2 
                        WHERE id = $3 AND repartidor_id = $4";
    
    $result_asignacion = pg_query_params($conn, $query_asignacion, 
                                       array($latitud, $longitud, $id_asignacion, $repartidor_id));
    
    if (!$result_asignacion) {
        throw new Exception('Error al actualizar asignación: ' . pg_last_error($conn));
    }
    
    
    $query_repartidor = "UPDATE repartidores 
                        SET latitud = $1, longitud = $2 
                        WHERE id = $3";
    
    $result_repartidor = pg_query_params($conn, $query_repartidor, 
                                       array($latitud, $longitud, $repartidor_id));
    
    if (!$result_repartidor) {
        throw new Exception('Error al actualizar repartidor: ' . pg_last_error($conn));
    }
    
    
    $affected_rows = pg_affected_rows($result_asignacion);
    
    if ($affected_rows > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'Ubicación actualizada correctamente',
            'latitud' => $latitud,
            'longitud' => $longitud
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No se encontró la asignación o no tienes permisos']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    pg_close($conn);
}
?>