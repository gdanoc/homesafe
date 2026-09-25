<?php
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
$id_asignacion = $input['id_asignacion'];
$pedido_id = $input['pedido_id'];

if (!$id_asignacion || !$pedido_id) {
    echo json_encode(['success' => false, 'error' => 'Datos requeridos faltantes']);
    exit();
}

try {
    
    pg_query($conn, "BEGIN");

    
    $query = "UPDATE asignaciones_entrega SET estado_entrega = 'entregado' WHERE id = $1";
    $result = pg_query_params($conn, $query, array($id_asignacion));

    if (!$result) {
        throw new Exception(pg_last_error($conn));
    }

    
    $query = "UPDATE pedidos SET estado = 'entregado' WHERE id = $1";
    $result = pg_query_params($conn, $query, array($pedido_id));

    if (!$result) {
        throw new Exception(pg_last_error($conn));
    }

    
    $query = "SELECT usuario_id FROM pedidos WHERE id = $1";
    $result = pg_query_params($conn, $query, array($pedido_id));
    $pedido_info = pg_fetch_assoc($result);

    if ($pedido_info) {
        
        $mensaje_usuario = "Su pedido #$pedido_id ha sido entregado exitosamente.";
        $query_mensaje = "INSERT INTO mensajes_usuario (usuario_id, pedido_id, mensaje, tipo) VALUES ($1, $2, $3, 'entrega')";
        $params_mensaje = array($pedido_info['usuario_id'], $pedido_id, $mensaje_usuario);
        $result_mensaje = pg_query_params($conn, $query_mensaje, $params_mensaje);

        if (!$result_mensaje) {
            throw new Exception(pg_last_error($conn));
        }
    }

    
    pg_query($conn, "COMMIT");

    echo json_encode(['success' => true, 'message' => 'Entrega completada exitosamente']);

} catch (Exception $e) {
    pg_query($conn, "ROLLBACK");
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

pg_close($conn);
?>