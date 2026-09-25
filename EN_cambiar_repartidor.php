<?php include('0_ENvalidacion_Admin.php'); //ESPAÑOL 
?>
<?php

session_start();
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


$pedido_id = isset($_POST['pedido_id']) ? intval($_POST['pedido_id']) : 0;


if ($pedido_id > 0) {
    $query_pedido = "SELECT id, estado, direccion_envio AS direccion_entrega, total FROM pedidos WHERE id = $1";
    $result_pedido = pg_query_params($conn, $query_pedido, array($pedido_id));

    if (pg_num_rows($result_pedido) == 0) {
        
        echo '<script>
            alert("The order does not exist.");
            window.location = "EN_ventas.php";
        </script>';
        exit;
    }

    $pedido = pg_fetch_assoc($result_pedido);

    
    $query_asignacion = "SELECT a.id, a.repartidor_id, r.nombre AS nombre_repartidor 
                         FROM asignaciones_entrega a 
                         JOIN repartidores r ON a.repartidor_id = r.id 
                         WHERE a.pedido_id = $1";
    $result_asignacion = pg_query_params($conn, $query_asignacion, array($pedido_id));
    $repartidor_actual = pg_num_rows($result_asignacion) > 0 ? pg_fetch_assoc($result_asignacion) : null;

    
    $query_repartidores = "SELECT id, nombre, telefono, estado FROM repartidores ORDER BY nombre";
    $result_repartidores = pg_query($conn, $query_repartidores);
}


$mensaje = "";
$tipo = "";


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_repartidor'])) {
    $pedido_id = intval($_POST['pedido_id']);
    $repartidor_id = intval($_POST['repartidor_id']);
    $repartidor_anterior_id = isset($_POST['repartidor_anterior_id']) ? intval($_POST['repartidor_anterior_id']) : 0;

    
    pg_query($conn, "BEGIN");

    try {
        
        if ($repartidor_anterior_id > 0) {
            $query_update_anterior = "UPDATE repartidores SET estado = 'Disponible' WHERE id = $1";
            $result_update_anterior = pg_query_params($conn, $query_update_anterior, array($repartidor_anterior_id));

            if (!$result_update_anterior) {
                throw new Exception("Error updating statusr: " . pg_last_error($conn));
            }
        }

        
        $query_check = "SELECT id FROM asignaciones_entrega WHERE pedido_id = $1";
        $result_check = pg_query_params($conn, $query_check, array($pedido_id));

        if (pg_num_rows($result_check) > 0) {
            
            $query = "UPDATE asignaciones_entrega SET repartidor_id = $1, fecha_asignacion = CURRENT_TIMESTAMP WHERE pedido_id = $2";
            $params = array($repartidor_id, $pedido_id);
        } else {
            
            $query = "INSERT INTO asignaciones_entrega (pedido_id, repartidor_id) VALUES ($1, $2)";
            $params = array($pedido_id, $repartidor_id);
        }

        $result = pg_query_params($conn, $query, $params);

        if (!$result) {
            throw new Exception("Error updating allocate: " . pg_last_error($conn));
        }

        
        $query_update_repartidor = "UPDATE repartidores SET estado = 'En servicio' WHERE id = $1";
        $result_update_repartidor = pg_query_params($conn, $query_update_repartidor, array($repartidor_id));

        if (!$result_update_repartidor) {
            throw new Exception("Error updating the new delivery person: " . pg_last_error($conn));
        }

        
        $query_info = "SELECT nombre FROM repartidores WHERE id = $1";
        $result_info = pg_query_params($conn, $query_info, array($repartidor_id));
        $info_repartidor = pg_fetch_assoc($result_info);

        
        $query_usuario = "SELECT usuario_id FROM pedidos WHERE id = $1";
        $result_usuario = pg_query_params($conn, $query_usuario, array($pedido_id));
        $info_pedido = pg_fetch_assoc($result_usuario);

        
        $mensaje_usuario = "Your order #$pedido_id Has been assigned to " . $info_repartidor['nombre'] . ".";
        $query_mensaje = "INSERT INTO mensajes_usuario (usuario_id, pedido_id, mensaje, tipo) VALUES ($1, $2, $3, 'sistema')";
        $params_mensaje = array($info_pedido['usuario_id'], $pedido_id, $mensaje_usuario);
        $result_mensaje = pg_query_params($conn, $query_mensaje, $params_mensaje);

        if (!$result_mensaje) {
            throw new Exception("Error creating a message: " . pg_last_error($conn));
        }

        pg_query($conn, "COMMIT");

        $mensaje = "Delivery person changed for the order #$pedido_id";
        $tipo = "success";
    } catch (Exception $e) {
        pg_query($conn, "ROLLBACK");

        $mensaje = "Error: " . $e->getMessage();
        $tipo = "warning";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Delivery Person - HomeSafe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <style>
        :root {
            --primary-color: #1F262D;
            --secondary-color: #2C3E50;
            --accent-color: #3498DB;
            --success-color: #27AE60;
            --warning-color: #F39C12;
            --danger-color: #E74C3C;
            --light-bg: #F8F9FA;
            --white: #FFFFFF;
            --text-light: #6C757D;
            --border-color: #E9ECEF;
            --shadow: 0 4px 20px rgba(31, 38, 45, 0.1);
            --shadow-hover: 0 8px 30px rgba(31, 38, 45, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--light-bg) 0%, #E8F4FD 100%);
            min-height: 100vh;
            line-height: 1.6;
            color: var(--primary-color);
        }

        .main-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .form-wrapper {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }

        .main-card {
            background: var(--white);
            border-radius: 20px;
            box-shadow: var(--shadow);
            border: none;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .main-card:hover {
            box-shadow: var(--shadow-hover);
            transform: translateY(-2px);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: var(--white);
            padding: 30px;
            text-align: center;
            border: none;
            position: relative;
        }

        .card-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='m36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
            opacity: 0.1;
            z-index: 0;
        }

        .card-header h4 {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0;
            position: relative;
            z-index: 1;
        }

        .card-header i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .card-body {
            padding: 40px;
        }

        .order-info {
            background: var(--light-bg);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            border-left: 5px solid var(--accent-color);
        }

        .info-item {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-item:last-child {
            margin-bottom: 0;
        }

        .info-label {
            font-weight: 700;
            color: var(--primary-color);
            min-width: 140px;
        }

        .info-value {
            color: var(--text-light);
            flex: 1;
        }

        .info-icon {
            color: var(--accent-color);
            width: 20px;
            text-align: center;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 15px;
            border-radius: 25px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: var(--warning-color);
            color: var(--white);
        }

        .status-assigned {
            background: var(--accent-color);
            color: var(--white);
        }

        .status-delivered {
            background: var(--success-color);
            color: var(--white);
        }

        .form-section {
            background: var(--white);
            border: 2px solid var(--border-color);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .form-section:hover {
            border-color: var(--accent-color);
            box-shadow: 0 2px 15px rgba(52, 152, 219, 0.1);
        }

        .form-label {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 12px;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-select {
            border: 2px solid var(--border-color);
            border-radius: 12px;
            padding: 15px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--white);
            color: var(--primary-color);
        }

        .form-select:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
            outline: none;
        }

        .btn-group-custom {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 30px;
        }

        .btn-custom {
            padding: 15px 30px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            min-width: 150px;
            justify-content: center;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--accent-color) 0%, #2980B9 100%);
            color: var(--white);
        }

        .btn-primary-custom:hover {
            background: linear-gradient(135deg, #2980B9 0%, #1F4E79 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }

        .btn-secondary-custom {
            background: var(--text-light);
            color: var(--white);
        }

        .btn-secondary-custom:hover {
            background: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(31, 38, 45, 0.3);
        }

        .alert-custom {
            border-radius: 12px;
            padding: 20px;
            border: none;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .alert-danger {
            background: rgba(231, 76, 60, 0.1);
            border-left: 5px solid var(--danger-color);
            color: var(--danger-color);
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 10px;
            }

            .card-header {
                padding: 20px;
            }

            .card-header h4 {
                font-size: 1.5rem;
            }

            .card-body {
                padding: 20px;
            }

            .order-info {
                padding: 20px;
            }

            .form-section {
                padding: 20px;
            }

            .btn-group-custom {
                flex-direction: column;
                align-items: stretch;
            }

            .btn-custom {
                min-width: auto;
            }

            .info-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .info-label {
                min-width: auto;
            }
        }

        .loader {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: var(--white);
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .no-assignment {
            color: var(--warning-color);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .current-delivery {
            color: var(--success-color);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4>Change Delivery Person - Order #<?php echo $pedido_id; ?></h4>
                    </div>
                    <div class="card-body">
                        <?php if (!isset($pedido)): ?>
                            <div class="alert alert-danger">
                                The order does not exist.
                            </div>
                            <div class="text-center">
                                <a href="EN_ventas.php" class="btn btn-secondary">Back to Sales</a>
                            </div>
                        <?php else: ?>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p><strong>Order Status:</strong> <?php echo $pedido['estado']; ?></p>
                                    <p><strong>Delivery Location:</strong> <?php echo $pedido['direccion_entrega']; ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Total:</strong> $<?php echo number_format($pedido['total'], 2); ?></p>
                                    <?php if ($repartidor_actual): ?>
                                        <p><strong>Current Delivery Person:</strong> <?php echo $repartidor_actual['nombre_repartidor']; ?></p>
                                    <?php else: ?>
                                        <p><strong>Current Delivery Person:</strong> No assigned</p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <form method="post" action="EN_cambiar_repartidor.php">
                                <input type="hidden" name="pedido_id" value="<?php echo $pedido_id; ?>">
                                <?php if ($repartidor_actual): ?>
                                    <input type="hidden" name="repartidor_anterior_id" value="<?php echo $repartidor_actual['repartidor_id']; ?>">
                                <?php endif; ?>

                                <div class="mb-3">
                                    <label for="repartidor_id" class="form-label">Select a new delivery person:</label>
                                    <select name="repartidor_id" id="repartidor_id" class="form-select" required>
                                        <option value="">-- Select a delivery person --</option>
                                        <?php while ($repartidor = pg_fetch_assoc($result_repartidores)): ?>
                                            <option value="<?php echo $repartidor['id']; ?>" <?php echo ($repartidor_actual && $repartidor['id'] == $repartidor_actual['repartidor_id']) ? 'selected' : ''; ?>>
                                                <?php echo $repartidor['nombre']; ?> -
                                                <?php echo $repartidor['telefono']; ?>
                                                <?php echo $repartidor['estado']; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="ventas.php" class="btn btn-secondary me-md-2">Cancel</a>
                                    <button type="submit" name="cambiar_repartidor" class="btn btn-primary">Change delivery person</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($mensaje): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                swal({
                    title: "<?php echo ($tipo == 'success') ? 'Success' : 'Error'; ?>",
                    text: "<?php echo $mensaje; ?>",
                    icon: "<?php echo $tipo; ?>",
                    button: "Close",
                }).then(function() {
                    <?php if ($tipo == 'success'): ?>
                        window.location = "EN_ventas.php";
                    <?php endif; ?>
                });
            });
        </script>
    <?php endif; ?>
</body>

</html>