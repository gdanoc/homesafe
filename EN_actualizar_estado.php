<?php include('0_ENvalidacion_Admin.php'); ?>
<?php
require_once 'translator.php';

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


if (isset($_GET['id']) && isset($_GET['estado'])) {
    $id = intval($_GET['id']);
    $estado = $_GET['estado'];
    
    
    
    $estados_validos = ['pendiente', 'procesando', 'listo', 'entregado', 'cancelado'];
    if (!in_array($estado, $estados_validos)) {
        echo '<script>
            swal({
                title: "Error",
                text: "Invalid Status",
                icon: "warning",
                button: "Close",
            }).then(function() {
                window.location = "EN_ventas.php";
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
                text: "Order not found",
                icon: "warning",
                button: "Close",
            }).then(function() {
                window.location = "EN_ventas.php";
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
            $mensaje = "Your order #$id has been cancelled. A refund for $total USD will be processed to your original payment method..";
            $query_mensaje = "INSERT INTO mensajes_usuario (usuario_id, pedido_id, mensaje, tipo) VALUES ($1, $2, $3, 'cancelacion')";
            $params_mensaje = array($usuario_id, $id, $mensaje);
            $result_mensaje = pg_query_params($conn, $query_mensaje, $params_mensaje);
            
            if (!$result_mensaje) {
                throw new Exception(pg_last_error($conn));
            }
        } 
        
        else if ($estado === 'entregado') {
            $mensaje = "Your oder #$id has been delivered. Thanks for your purchase!";
            $query_mensaje = "INSERT INTO mensajes_usuario (usuario_id, pedido_id, mensaje, tipo) VALUES ($1, $2, $3, 'entrega')";
            $params_mensaje = array($usuario_id, $id, $mensaje);
            $result_mensaje = pg_query_params($conn, $query_mensaje, $params_mensaje);
            
            if (!$result_mensaje) {
                throw new Exception(pg_last_error($conn));
            }
        }
        
        else if ($estado === 'listo' || $estado === 'enviado') {
            $mensaje = ($estado === 'enviado') 
                ? "Your order #$id has been sent and is on its way." 
                : "Your order #$id is ready to be delivered.";
            
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
                title: "Success",
                text: "Order status has been updated to ' . ucfirst(tr($estado)) . '.",
                icon: "success",
                button: "Close",
            }).then(function() {
                window.location = "EN_ventas.php";
            });
        </script>';
        
    } catch (Exception $e) {
        
        pg_query($conn, "ROLLBACK");
        
        echo '<script>
            swal({
                title: "Error",
                text: "Error updating status: ' . $e->getMessage() . '",
                icon: "warning",
                button: "Close",
            }).then(function() {
                window.location = "EN_ventas.php";
            });
        </script>';
    }
} else {
    echo '<script>
        swal({
            title: "Error",
            text: "Insufficient parameters",
            icon: "warning",
            button: "Close",
        }).then(function() {
            window.location = "EN_ventas.php";
        });
    </script>';
}


pg_close($conn);
echo '</body></html>';
?>