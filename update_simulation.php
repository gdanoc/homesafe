<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
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
$id_asignacion = $input['id_asignacion'] ?? null;
$progreso = $input['progreso'] ?? 0;
$step = $input['step'] ?? 0;
$lat = $input['lat'] ?? null;
$lng = $input['lng'] ?? null;

if (!$id_asignacion) {
    echo json_encode(['success' => false, 'error' => 'ID de asignación requerido']);
    exit();
}

try {
    $query = "
    UPDATE asignaciones_entrega 
    SET simulacion_progreso = $1,
        simulacion_step = $2,
        simulacion_posicion_lat = $3,
        simulacion_posicion_lng = $4
    WHERE id = $5";
    
    $result = pg_query_params($conn, $query, array($progreso, $step, $lat, $lng, $id_asignacion));
    
    if (!$result) {
        throw new Exception('Error al actualizar simulación: ' . pg_last_error($conn));
    }
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

pg_close($conn);
?>