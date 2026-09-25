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

if (!$id_asignacion) {
    echo json_encode(['success' => false, 'error' => 'ID de asignación requerido']);
    exit();
}

try {
    
    $query = "
    SELECT p.id as pedido_id, p.email, p.total, p.direccion_envio, p.telefono, p.metodo_pago, p.estado, p.latitud, p.longitud, p.fecha_pedido,
           ae.estado_entrega, ae.fecha_asignacion, ae.notas, ae.id, ae.latitud_repartidor, ae.longitud_repartidor
    FROM asignaciones_entrega ae
    JOIN pedidos p ON ae.pedido_id = p.id
    WHERE ae.id = $1
    LIMIT 1";

    $result = pg_query_params($conn, $query, array($id_asignacion));

    if (!$result || pg_num_rows($result) == 0) {
        throw new Exception('Pedido no encontrado');
    }

    $pedido = pg_fetch_assoc($result);

    
    $query_productos = "
    SELECT dp.producto_id, dp.cantidad, dp.precio_unitario, dp.subtotal, 
           dp.imagen as imagen_mueble, m.nombre as nombre_mueble
    FROM detalle_pedido dp
    JOIN muebles m ON dp.producto_id = m.id
    WHERE dp.pedido_id = $1";
  
    $result_productos = pg_query_params($conn, $query_productos, array($pedido['pedido_id']));
  
    $productos = [];
    if ($result_productos) {
        while ($row = pg_fetch_assoc($result_productos)) {
            $productos[] = [
                'producto_id' => $row['producto_id'],
                'nombre' => $row['nombre_mueble'],
                'cantidad' => $row['cantidad'],
                'precio_unitario' => $row['precio_unitario'],
                'subtotal' => $row['subtotal'],
                'imagen_base64' => base64_encode(pg_unescape_bytea($row['imagen_mueble']))
            ];
        }
    }

    
    $pedido['productos'] = $productos;

    echo json_encode(['success' => true, 'pedido' => $pedido]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

pg_close($conn);
exit();
?>