<?php include('0_ESvalidacion_Admin.php'); ?>
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


echo '<!DOCTYPE html>
<html>
<head>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
</head>
<body>';


if (isset($_POST['id']) && isset($_POST['estado'])) {
    $id = intval($_POST['id']);
    $estado = $_POST['estado'];
    
    
    
    $estados_validos = ['pendiente', 'procesando', 'listo', 'entregado', 'cancelado'];
    if (!in_array($estado, $estados_validos)) {
        echo '<script>
            swal({
                title: "Error",
                text: "Estado no válido",
                icon: "warning",
                button: "Cerrar",
            }).then(function() {
                window.location = "ventas.php";
            });
        </script>';
        exit;
    }
    
    
    $query_pedido = "SELECT usuario_id, email, total FROM pedidos WHERE id = $1";
    $params_pedido = array($id);
    $result_pedido = pg_query_params($conn, $query_pedido, $params_pedido);
    
    if (pg_num_rows($result_pedido) === 0) {
        echo '<script>
            swal({
                title: "Error",
                text: "Pedido no encontrado",
                icon: "warning",
                button: "Cerrar",
            }).then(function() {
                window.location = "ventas.php";
            });
        </script>';
        exit;
    }
    
    $pedido = pg_fetch_assoc($result_pedido);
    $usuario_id = $pedido['usuario_id'];
    $email = $pedido['email'];
    $total = $pedido['total'];
    
    
    pg_query($conn, "BEGIN");
    
    try {
        
        
        $estado_bd = ($estado === 'enviado') ? 'listo' : $estado;
        
        $query = "UPDATE pedidos SET estado = $1 WHERE id = $2";
        $params = array($estado_bd, $id);
        $result = pg_query_params($conn, $query, $params);
        
        if (!$result) {
            throw new Exception(pg_last_error($conn));
        }
        
        
        if ($estado === 'entregado' || $estado === 'cancelado') {
            
            $query_asignacion = "SELECT repartidor_id FROM asignaciones_entrega WHERE pedido_id = $1";
            $params_asignacion = array($id);
            $result_asignacion = pg_query_params($conn, $query_asignacion, $params_asignacion);
            
            if (pg_num_rows($result_asignacion) > 0) {
                $asignacion = pg_fetch_assoc($result_asignacion);
                $repartidor_id = $asignacion['repartidor_id'];
                
                
                $query_repartidor = "UPDATE repartidores SET estado = 'Disponible' WHERE id = $1";
                $params_repartidor = array($repartidor_id);
                $result_repartidor = pg_query_params($conn, $query_repartidor, $params_repartidor);
                
                if (!$result_repartidor) {
                    throw new Exception(pg_last_error($conn));
                }
            }
        }
        
        
        if ($estado === 'cancelado') {
            $mensaje = "Su pedido #$id ha sido cancelado. Se procesará un reembolso por $total USD a su método de pago original.";
            $query_mensaje = "INSERT INTO mensajes_usuario (usuario_id, pedido_id, mensaje, tipo) VALUES ($1, $2, $3, 'cancelacion')";
            $params_mensaje = array($usuario_id, $id, $mensaje);
            $result_mensaje = pg_query_params($conn, $query_mensaje, $params_mensaje);
            
            if (!$result_mensaje) {
                throw new Exception(pg_last_error($conn));
            }
        } 
        
        else if ($estado === 'entregado') {
            $mensaje = "Su pedido #$id ha sido entregado. ¡Gracias por su compra!";
            $query_mensaje = "INSERT INTO mensajes_usuario (usuario_id, pedido_id, mensaje, tipo) VALUES ($1, $2, $3, 'entrega')";
            $params_mensaje = array($usuario_id, $id, $mensaje);
            $result_mensaje = pg_query_params($conn, $query_mensaje, $params_mensaje);
            
            if (!$result_mensaje) {
                throw new Exception(pg_last_error($conn));
            }
        }
        
        else if ($estado === 'listo' || $estado === 'enviado') {
            $mensaje = ($estado === 'enviado') 
                ? "Su pedido #$id ha sido enviado y está en camino." 
                : "Su pedido #$id está listo para ser enviado.";
            
            $tipo = ($estado === 'enviado') ? 'sistema' : 'listo';
            $query_mensaje = "INSERT INTO mensajes_usuario (usuario_id, pedido_id, mensaje, tipo) VALUES ($1, $2, $3, $4)";
            $params_mensaje = array($usuario_id, $id, $mensaje, $tipo);
            $result_mensaje = pg_query_params($conn, $query_mensaje, $params_mensaje);
            
            if (!$result_mensaje) {
                throw new Exception(pg_last_error($conn));
            }
        }        
        
        pg_query($conn, "COMMIT");
        
        
        echo '<script>
            swal({
                title: "Éxito",
                text: "El estado del pedido ha sido actualizado a ' . ucfirst($estado) . '.",
                icon: "success",
                button: "Cerrar",
            }).then(function() {
                window.location = "ventas.php";
            });
        </script>';
        
    } catch (Exception $e) {
        
        pg_query($conn, "ROLLBACK");
        
        echo '<script>
            swal({
                title: "Error",
                text: "Error al actualizar el estado: ' . $e->getMessage() . '",
                icon: "warning",
                button: "Cerrar",
            }).then(function() {
                window.location = "ventas.php";
            });
        </script>';
    }
} else {
    echo '<script>
        swal({
            title: "Error",
            text: "Parámetros insuficientes",
            icon: "warning",
            button: "Cerrar",
        }).then(function() {
            window.location = "ventas.php";
        });
    </script>';
}


pg_close($conn);
echo '</body></html>';
?>