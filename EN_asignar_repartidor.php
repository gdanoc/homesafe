<?php include('0_ENvalidacion_Admin.php'); ?>
<?php

$host = "localhost";
$port = "5432";
$dbname = "homesafe";
$username = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";

$conn_string = "host=$host port=$port dbname=$dbname user=$username password=$password";
$conn = pg_connect($conn_string);


if (!$conn) {
    die("Conexión fallida: " . pg_last_error());
}


$mensaje = "";
$tipo = "";
$redireccion = "EN_ventas.php";


if (isset($_POST['pedido_id']) && isset($_POST['repartidor_id']) && !empty($_POST['repartidor_id'])) {
    $pedido_id = intval($_POST['pedido_id']);
    $repartidor_id = intval($_POST['repartidor_id']);
    
    
    pg_query($conn, "BEGIN");
    
    try {
        
        $check_table = "SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_name = 'asignaciones_entrega'
        )";
        $result_check_table = pg_query($conn, $check_table);
        $table_exists = pg_fetch_result($result_check_table, 0, 0);
        
        if ($table_exists !== 't') {
            
            $create_table = "CREATE TABLE public.asignaciones_entrega (
                id SERIAL PRIMARY KEY,
                pedido_id INTEGER NOT NULL REFERENCES public.pedidos(id),
                repartidor_id INTEGER NOT NULL REFERENCES public.repartidores(id),
                fecha_asignacion TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
                estado_entrega VARCHAR(20) DEFAULT 'asignado',
                notas TEXT,
                CONSTRAINT asignaciones_estado_check CHECK (estado_entrega::text = ANY (ARRAY['asignado'::character varying, 'en_camino'::character varying, 'entregado'::character varying, 'fallido'::character varying]::text[]))
            )";
            $result_create = pg_query($conn, $create_table);
            
            if (!$result_create) {
                throw new Exception("Error al crear la tabla asignaciones_entrega: " . pg_last_error($conn));
            }
        }
        
        
        $query_check = "SELECT id FROM asignaciones_entrega WHERE pedido_id = $1";
        $params_check = array($pedido_id);
        $result_check = pg_query_params($conn, $query_check, $params_check);
        
        if (pg_num_rows($result_check) > 0) {
            
            $query = "UPDATE asignaciones_entrega SET repartidor_id = $1, fecha_asignacion = CURRENT_TIMESTAMP WHERE pedido_id = $2";
            $params = array($repartidor_id, $pedido_id);
        } else {
            
            $query = "INSERT INTO asignaciones_entrega (pedido_id, repartidor_id) VALUES ($1, $2)";
            $params = array($pedido_id, $repartidor_id);
        }
        
        $result = pg_query_params($conn, $query, $params);
        
        if (!$result) {
            throw new Exception(pg_last_error($conn));
        }
        
        $query_update_repartidor = "UPDATE repartidores SET estado = 'En servicio' WHERE id = $1";
        $params_update_repartidor = array($repartidor_id);
        $result_update_repartidor = pg_query_params($conn, $query_update_repartidor, $params_update_repartidor);
        
        if (!$result_update_repartidor) {
            throw new Exception(pg_last_error($conn));
        }
        
        
        $query_update_pedido = "UPDATE pedidos SET estado = 'listo' WHERE id = $1";
        $params_update_pedido = array($pedido_id);
        $result_update_pedido = pg_query_params($conn, $query_update_pedido, $params_update_pedido);
        
        if (!$result_update_pedido) {
            throw new Exception(pg_last_error($conn));
        }
        
        $query_info = "SELECT p.usuario_id, r.nombre AS nombre_repartidor 
                      FROM pedidos p, repartidores r 
                      WHERE p.id = $1 AND r.id = $2";
        $params_info = array($pedido_id, $repartidor_id);
        $result_info = pg_query_params($conn, $query_info, $params_info);
        $info = pg_fetch_assoc($result_info);
        
        
        $mensaje_usuario = "Your order #$pedido_id has been assigned to " . $info['nombre_repartidor'] . " and its on the way.";
        $query_mensaje = "INSERT INTO mensajes_usuario (usuario_id, pedido_id, mensaje, tipo) VALUES ($1, $2, $3, 'sistema')";
        $params_mensaje = array($info['usuario_id'], $pedido_id, $mensaje_usuario);
        $result_mensaje = pg_query_params($conn, $query_mensaje, $params_mensaje);
        
        if (!$result_mensaje) {
            throw new Exception(pg_last_error($conn));
        }
        
        pg_query($conn, "COMMIT");
        
        $mensaje = "Assigned Delivery Person for the order #".$pedido_id;
        $tipo = "success";
        
    } catch (Exception $e) {
        pg_query($conn, "ROLLBACK");
        
        $mensaje = "Error assigning: ".$e->getMessage();
        $tipo = "warning";
    }
} else {
    $mensaje = "Insufficient parameters. Select the order and the delivery person.";
    $tipo = "warning";
}


pg_close($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Delivery Person</title>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
</head>
<body>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            swal({
                title: "<?php echo ($tipo == 'success') ? 'Success' : 'Error'; ?>",
                text: "<?php echo $mensaje; ?>",
                icon: "<?php echo $tipo; ?>",
                button: "Close",
            }).then(function() {
                window.location = "<?php echo $redireccion; ?>";
            });
        });
    </script>
</body>
</html>