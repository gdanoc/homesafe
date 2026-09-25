<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('Access-Control-Allow-Origin: *');

session_start();


$host = "localhost";
$port = "5432";
$dbname = "homesafe";
$user = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";

$conn_string = "host=$host port=$port dbname=$dbname user=$user password=$password";
$conn = pg_connect($conn_string);

if (!$conn) {
    echo "data: " . json_encode(['error' => 'Database connection failed']) . "\n\n";
    exit();
}


$pedido_id = $_GET['pedido_id'] ?? null;
$user_id = $_GET['user_id'] ?? null;

if (!$pedido_id) {
    echo "data: " . json_encode(['error' => 'Missing pedido_id']) . "\n\n";
    exit();
}


function sendEvent($data) {
    echo "data: " . json_encode($data) . "\n\n";
    ob_flush();
    flush();
}


while (true) {
    
    $query = "
    SELECT ae.estado_entrega, ae.latitud_repartidor, ae.longitud_repartidor,
           ae.simulacion_activa, ae.simulacion_progreso, ae.simulacion_step,
           p.latitud as destino_lat, p.longitud as destino_lng,
           r.nombre as repartidor_nombre
    FROM asignaciones_entrega ae
    JOIN pedidos p ON ae.pedido_id = p.id
    JOIN repartidores r ON ae.repartidor_id = r.id
    WHERE ae.pedido_id = $1";
    
    $result = pg_query_params($conn, $query, array($pedido_id));
    
    if ($result && pg_num_rows($result) > 0) {
        $data = pg_fetch_assoc($result);
        
        
        sendEvent([
            'type' => 'delivery_update',
            'pedido_id' => $pedido_id,
            'estado_entrega' => $data['estado_entrega'],
            'simulacion_activa' => $data['simulacion_activa'] === 't',
            'simulacion_progreso' => floatval($data['simulacion_progreso'] ?? 0),
            'simulacion_step' => intval($data['simulacion_step'] ?? 0),
            'repartidor_lat' => floatval($data['latitud_repartidor']),
            'repartidor_lng' => floatval($data['longitud_repartidor']),
            'destino_lat' => floatval($data['destino_lat']),
            'destino_lng' => floatval($data['destino_lng']),
            'repartidor_nombre' => $data['repartidor_nombre'],
            'timestamp' => time()
        ]);
    }
    
    
    sleep(2);
    
    
    if (connection_aborted()) {
        break;
    }
}

pg_close($conn);
?>