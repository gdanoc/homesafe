<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');
session_start();

try {
    include('user.p.php');
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit();
}


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
$pedido_id = $input['pedido_id'] ?? null;

if (!$id_asignacion) {
    echo json_encode(['success' => false, 'error' => 'ID de asignación requerido']);
    exit();
}

try {
    
    $query_check = "
    SELECT p.latitud, p.longitud, ae.latitud_repartidor, ae.longitud_repartidor, 
           p.id as pedido_id, ae.estado_entrega
    FROM asignaciones_entrega ae
    JOIN pedidos p ON ae.pedido_id = p.id
    WHERE ae.id = $1";
  
    $result_check = pg_query_params($conn, $query_check, array($id_asignacion));
  
    if (!$result_check || pg_num_rows($result_check) == 0) {
        throw new Exception('No se encontró la asignación de entrega');
    }
  
    $data = pg_fetch_assoc($result_check);
  
    
    if ($data['estado_entrega'] !== 'asignado') {
        throw new Exception('La entrega ya fue iniciada o completada');
    }
  
    
    if (!$data['latitud_repartidor'] || !$data['longitud_repartidor']) {
        throw new Exception('Ubicación del repartidor no disponible. Debe seleccionar su ubicación primero.');
    }
  
    if (!$data['latitud'] || !$data['longitud']) {
        throw new Exception('Ubicación del destino no disponible.');
    }

    
    $query_update = "UPDATE asignaciones_entrega SET estado_entrega = 'en_camino' WHERE id = $1";
    $result_update = pg_query_params($conn, $query_update, array($id_asignacion));

    if (!$result_update) {
        throw new Exception('Error al actualizar estado: ' . pg_last_error($conn));
    }

    
    echo json_encode([
        'success' => true, 
        'message' => 'Entrega iniciada correctamente',
        'data' => [
            'id_asignacion' => $id_asignacion,
            'pedido_id' => $data['pedido_id'],
            'estado_entrega' => 'en_camino'
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

pg_close($conn);
?>