<?php
session_start();
try {
    include('user.p.php');
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit();
}
if (!isset($_SESSION['email'])):
    echo '<p><script>swal({
        title: "Error",
        text: "You must be login",
        icon: "warning",
        button: "Close",
    
        }).then(function() {
        window.location = "EN_index.php";
        });</script></p>';
else:
    include('user.p.php');
    if ($rol == 1):
        header('Location: EN_dashboard.php');
    elseif ($rol == 2):
        header('Location: EN_dashboard_vendedor.php');
    endif;
endif;



$host = "localhost";
$port = "5432";
$dbname = "homesafe";
$user = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";

$conn_string = "host=$host port=$port dbname=$dbname user=$user password=$password";
$conn = pg_connect($conn_string);

if (!$conn) {
    die("Conexión fallida: " . pg_last_error());
}


if (!isset($_SESSION['user_id'])) {
    $email = $_SESSION['email'];
    $query = "SELECT id FROM repartidores WHERE email = $1";
    $result = pg_query_params($conn, $query, array($email));
    if (pg_num_rows($result) > 0) {
        $row = pg_fetch_assoc($result);
        $_SESSION['user_id'] = $row['id'];
    } else {
        echo '<script>          swal({          title: "Error",          text: "Your delivery account could not be found.",          icon: "warning",          button: "Close",          }).then(function() {          window.location = "index.php";          });
        </script>';
        exit();
    }
}

$repartidor_id = $_SESSION['user_id'];


$query = "
SELECT p.id as pedido_id, p.email, p.total, p.direccion_envio, p.telefono, p.metodo_pago, p.estado, p.latitud, p.longitud, p.fecha_pedido,
       dp.producto_id, dp.cantidad, dp.precio_unitario, dp.subtotal, dp.imagen as imagen_mueble, m.nombre as nombre_mueble,
       ae.estado_entrega, ae.fecha_asignacion, ae.notas, ae.id, ae.latitud_repartidor, ae.longitud_repartidor
FROM asignaciones_entrega ae
JOIN pedidos p ON ae.pedido_id = p.id
JOIN detalle_pedido dp ON dp.pedido_id = p.id
JOIN muebles m ON dp.producto_id = m.id
WHERE ae.repartidor_id = $1
ORDER BY p.fecha_pedido DESC, p.id, dp.producto_id";

$result = pg_query_params($conn, $query, array($repartidor_id));

$pedidos = [];
if ($result && pg_num_rows($result) > 0) {
    while ($row = pg_fetch_assoc($result)) {
        $pid = $row['pedido_id'];
        if (!isset($pedidos[$pid])) {
            $pedidos[$pid] = ['pedido_id' => $pid,          'email' => $row['email'],          'total' => $row['total'],          'direccion_envio' => $row['direccion_envio'],          'telefono' => $row['telefono'],          'metodo_pago' => $row['metodo_pago'],          'estado' => $row['estado'],          'latitud' => $row['latitud'],          'longitud' => $row['longitud'],          'fecha_pedido' => $row['fecha_pedido'],          'estado_entrega' => $row['estado_entrega'],          'fecha_asignacion' => $row['fecha_asignacion'],          'notas' => $row['notas'],          'id_asignacion' => $row['id'],          'latitud_repartidor' => $row['latitud_repartidor'],          'longitud_repartidor' => $row['longitud_repartidor'],          'productos' => []];
        }
        $pedidos[$pid]['productos'][] = [
            'producto_id' => $row['producto_id'],
            'nombre' => $row['nombre_mueble'],
            'cantidad' => $row['cantidad'],
            'precio_unitario' => $row['precio_unitario'],
            'subtotal' => $row['subtotal'],          
            'imagen_base64' => base64_encode(pg_unescape_bytea($row['imagen_mueble']))
        ];
    }
} else {
    $pedidos = [];
}
if (!$result) {
    echo "Error en la consulta: " . pg_last_error($conn);
    exit;
}


pg_close($conn);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Assigned Orders - Repartidor</title>
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon.png">
    <link href="assets/libs/flot/css/float-chart.css" rel="stylesheet" />
    <link href="dist/css/style.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
    <style>
        /* Variables CSS para consistencia */
        :root {
            --primary-color: #1F262D;
            --primary-light: #2C353E;
            --primary-dark: #161B20;
            --accent-color: #4A90E2;
            --success-color: #27AE60;
            --warning-color: #F39C12;
            --danger-color: #E74C3C;
            --info-color: #3498DB;
            --light-gray: #F8F9FA;
            --medium-gray: #6C757D;
            --dark-gray: #495057;
            --border-color: #DEE2E6;
            --shadow-light: 0 2px 10px rgba(31, 38, 45, 0.08);
            --shadow-medium: 0 4px 20px rgba(31, 38, 45, 0.12);
            --shadow-strong: 0 8px 30px rgba(31, 38, 45, 0.15);
            --border-radius: 12px;
            --border-radius-small: 8px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Reset y base */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: var(--primary-color);
            line-height: 1.6;
            min-height: 100vh;
        }

        /* Header mejorado */
        header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            padding: 20px 30px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            box-shadow: var(--shadow-medium);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 20" fill="rgba(255,255,255,0.03)"><circle cx="10" cy="10" r="2"/><circle cx="30" cy="10" r="2"/><circle cx="50" cy="10" r="2"/><circle cx="70" cy="10" r="2"/><circle cx="90" cy="10" r="2"/></svg>') repeat;
            pointer-events: none;
        }

        header>* {
            position: relative;
            z-index: 1;
        }

        header .user-info {
            font-size: 18px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        header .user-info::before {
            content: '👋';
            font-size: 20px;
        }

        header button {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            padding: 12px 24px;
            border-radius: var(--border-radius-small);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            backdrop-filter: blur(10px);
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        header button:hover {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        /* Contenedor principal */
        .form {
            background: white;
            margin: 30px auto;
            padding: 40px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-strong);
            width: 95%;
            max-width: 1400px;
            overflow: hidden;
            position: relative;
        }

        .form::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color), var(--success-color));
        }

        /* Título principal */
        .login-title {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            padding-bottom: 15px;
        }

        .login-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, var(--accent-color), var(--success-color));
            border-radius: 2px;
        }

        .login-title::before {
            content: '📦';
            margin-right: 12px;
            font-size: 28px;
        }

        /* Tabla mejorada */
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-light);
            margin-top: 20px;
        }

        thead {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            color: white;
        }

        th {
            padding: 20px 16px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 13px;
            border: none;
            position: relative;
        }

        th::after {
            content: '';
            position: absolute;
            right: 0;
            top: 25%;
            height: 50%;
            width: 1px;
            background: rgba(255, 255, 255, 0.2);
        }

        th:last-child::after {
            display: none;
        }

        td {
            padding: 20px 16px;
            border-bottom: 1px solid var(--border-color);
            transition: var(--transition);
            vertical-align: middle;
            font-size: 14px;
        }

        tr:hover td {
            background-color: rgba(74, 144, 226, 0.04);
            transform: scale(1.001);
        }

        tr:last-child td {
            border-bottom: none;
        }

        /* Imágenes de productos */
        img.product-image {
            width: 60px;
            height: 60px;
            border-radius: var(--border-radius-small);
            object-fit: cover;
            border: 2px solid var(--border-color);
            transition: var(--transition);
            cursor: pointer;
        }

        img.product-image:hover {
            transform: scale(1.1);
            border-color: var(--accent-color);
            box-shadow: var(--shadow-medium);
        }

        /* Botones mejorados */
        button.details-btn {
            background: linear-gradient(135deg, var(--accent-color) 0%, #357ABD 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: var(--border-radius-small);
            cursor: pointer;
            font-weight: 500;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        button.details-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: var(--transition);
        }

        button.details-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(74, 144, 226, 0.4);
        }

        button.details-btn:hover::before {
            left: 100%;
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color) 0%, #219A52 100%);
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: var(--border-radius-small);
            cursor: pointer;
            font-weight: 500;
            font-size: 12px;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(39, 174, 96, 0.4);
        }

        /* Estados con badges */
        td:nth-child(3),
        td:nth-child(8) {
            position: relative;
        }

        td:nth-child(3)::before,
        td:nth-child(8)::before {
            content: attr(data-status);
            position: absolute;
            top: 50%;
            left: 16px;
            transform: translateY(-50%);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            padding-top: 60px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fff;
            margin: auto;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 800px;
            position: relative;
        }

        .close-modal {
            color: #aaa;
            position: absolute;
            top: 10px;
            right: 20px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close-modal:hover {
            color: black;
        }

        #map,
        #mapRoute {
            height: 400px;
            width: 100%;
            border-radius: 8px;
            border: 2px solid #e9ecef;
            margin-top: 15px;
        }

        /* Contenedor flex para imagen + info */
        .product-list {
            margin-top: 15px;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        /* Cada producto en fila con imagen y texto */
        .product-item {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }

        img.product-image {
            width: 100%;
            max-width: 250px;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            border: 1px solid #ccc;
            object-fit: contain;
            flex-shrink: 0;
        }

        /* Contenedor de texto al lado de la imagen */
        .product-item>div {
            flex: 1;
            min-width: 200px;
        }

        /* Estilos para el mensaje de instrucciones */
        .instruction-message {
            background-color: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
            color: #0066cc;
            font-weight: bold;
            text-align: center;
        }

        /* Botón de guardar ubicación */
        .btn-save-location {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 15px;
            font-weight: bold;
        }

        .btn-save-location:hover {
            background-color: #0056b3;
        }

        .btn-save-location:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }

        /* NUEVOS ESTILOS PARA SIMULACIÓN DE ENTREGA */
        .delivery-controls {
            background-color: #f8f9fa;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
        }

        .delivery-status {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
        }

        .status-asignado {
            background-color: #ffc107;
        }

        .status-en-camino {
            background-color: #17a2b8;
        }

        .status-entregado {
            background-color: #28a745;
        }

        .delivery-button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            margin-right: 10px;
            margin-bottom: 10px;
        }

        .delivery-button:hover {
            background-color: #218838;
        }

        .delivery-button:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }

        .delivery-button.finish {
            background-color: #dc3545;
        }

        .delivery-button.finish:hover {
            background-color: #c82333;
        }

        .delivery-info {
            background-color: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
        }

        .eta-display {
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
            margin: 10px 0;
        }

        #deliverySimulationMap {
            height: 350px;
            width: 100%;
            border-radius: 8px;
            border: 2px solid #e9ecef;
            margin-top: 15px;
        }

        /* NUEVOS ESTILOS PARA UI ELEMENTS DEL SIMULADOR */
        .delivery-status-card {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .delivery-status-card h6 {
            margin-bottom: 15px;
            color: #495057;
            font-weight: 600;
        }

        .delivery-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
            margin-bottom: 15px;
        }

        .info-box {
            background-color: white;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 10px;
            text-align: center;
        }

        .info-box small {
            display: block;
            color: #6c757d;
            font-size: 0.75rem;
            margin-bottom: 5px;
        }

        .info-box .value {
            font-weight: bold;
            font-size: 0.9rem;
        }

        .progress-container {
            margin-top: 15px;
        }

        .progress {
            height: 12px;
            background-color: #e9ecef;
            border-radius: 6px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #007bff, #0056b3);
            border-radius: 6px;
            transition: width 0.3s ease;
        }

        .progress-bar.progress-bar-striped {
            background-image: linear-gradient(45deg, rgba(255, 255, 255, .15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, .15) 50%, rgba(255, 255, 255, .15) 75%, transparent 75%, transparent);
            background-size: 1rem 1rem;
        }

        .progress-bar.progress-bar-animated {
            animation: progress-bar-stripes 1s linear infinite;
        }

        @keyframes progress-bar-stripes {
            0% {
                background-position: 1rem 0;
            }

            100% {
                background-position: 0 0;
            }
        }

        .alert {
            padding: 12px 16px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 6px;
            display: flex;
            align-items: center;
        }

        .alert-info {
            color: #0c5460;
            background-color: #d1ecf1;
            border-color: #bee5eb;
        }

        .alert i {
            margin-right: 8px;
        }

        /* ===================== LANGUAGE SELECTOR ===================== */
        .language-selector {
            display: inline-flex;
            align-items: center;
            font-family: 'Inter', sans-serif;
            margin: 12px 0;
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 15px;
            border-radius: 25px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-left: 10px;
        }

        .language-label {
            margin-right: 12px;
            font-weight: 600;
            color: var(--white);
            font-size: 0.9rem;
        }

        .language-select {
            padding: 8px 15px;
            border-radius: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.2);
            /* Cambiado de 0.9 a 0.2 para más transparencia */
            backdrop-filter: blur(10px);
            font-size: 0.9rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.9);
            /* Cambiado a color claro en lugar de gris oscuro */
            cursor: pointer;
            transition: var(--transition);
            outline: none;
        }

        .language-select:hover,
        .language-select:focus {
            border-color: var(--white);
            background: rgba(255, 255, 255, 0.3);
            /* Cambiado para mantener la transparencia */
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
            transform: scale(1.02);
        }

        .language-select option {
            background: rgba(30, 30, 30, 0.95);
            /* Fondo oscuro semitransparente para las opciones */
            color: rgba(255, 255, 255, 0.9);
            /* Texto claro para las opciones */
            padding: 10px;
        }

        .table-container {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            /* para mejor scroll en móviles */
        }
    </style>
</head>

<body>
    <header style="background-color: #f5581a; padding: 10px 20px; color: white; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
        <div style="font-size: 18px; font-weight: bold;">
            Hello, <?php echo htmlspecialchars($_SESSION['email']); ?>
        </div>
        <div class="language-selector">
            <label for="language" class="language-label">Language:</label>
            <select id="language" name="language" class="language-select" onchange="changeLanguage()">
                <option value="" disabled hidden selected>Language</option>
                <option value="es">Spanish</option>
                <option value="en">English</option>
            </select>
        </div>
        <script>
            function changeLanguage() {
                const lang = document.getElementById('language').value;
                if (lang === 'es') {
                    window.location.href = 'index.php';
                } else if (lang === 'en') {
                    window.location.href = 'EN_index.php';
                }
            }
        </script>
        <div>
            <form action="EN_logout.p.php" method="post" style="margin: 0;"> <button type="submit" style="          background-color: #fff;          color: #024866ff;          border: none;          padding: 8px 16px;          border-radius: 6px;          font-weight: bold;          cursor: pointer;          transition: background-color 0.3s ease;          " onmouseover="this.style.backgroundColor='#f2f2f2'" onmouseout="this.style.backgroundColor='#fff'"> Log Out </button>
            </form>
        </div>
    </header>

    <div class="form">
        <h1 class="login-title">Assigned Orders</h1>

        <?php if (count($pedidos) > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Client (Email)</th>
                            <th>Order Status</th>
                            <th>Total</th>
                            <th>Delivery Location</th>
                            <th>Telephone</th>
                            <th>Order Date</th>
                            <th>Delivery Status</th>
                            <th>Actions</th>
                            <th>Chat</th>
                        </tr>
                    </thead>
                    <tbody> <?php foreach ($pedidos as $pedido): ?> <tr>
                                <td><?php echo htmlspecialchars($pedido['pedido_id']); ?></td>
                                <td><?php echo htmlspecialchars($pedido['email']); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($pedido['estado'])); ?></td>
                                <td>$<?php echo number_format($pedido['total'], 2); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($pedido['direccion_envio'])); ?></td>
                                <td><?php echo htmlspecialchars($pedido['telefono']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($pedido['estado_entrega'])); ?></td>
                                <td> <button class="details-btn" data-pedido='<?php echo json_encode($pedido); ?>'>View Details</button> </td>
                                <td>
                                    <form method="post" action="chatEn/chat_repartidor/rep.php" style="display: inline;">
                                        <input type="hidden" name="id_pedido" value="<?php echo $pedido['pedido_id']; ?>">
                                        <input type="hidden" name="email_customer" value="<?php echo $pedido['email']; ?>">
                                        <button type="submit" class="btn btn-success btn-action">
                                            <i class="fas fa-check-circle me-1"></i>
                                            Enter chat
                                        </button>
                                    </form>
                                </td>
                            </tr> <?php endforeach; ?> </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>You currently have no orders assigned.</p>
        <?php endif; ?>
    </div>

    <!-- Modal para detalles -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Order Details</h2>
            <div id="modalContent"> <!-- Aquí se cargan los detalles dinámicamente -->
            </div>
            <div id="map" style="height: 300px; width: 100%; border-radius: 8px; border: 2px solid #e9ecef; margin-top: 15px;"></div>
            <button id="btnOpenLocationModal" style="margin-top: 15px; background-color: #28a745; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; font-weight: bold;"> 📍 Select my location and view route
            </button>

            <!-- NUEVA SECCIÓN: Controles de entrega con UI Elements mejorados -->
            <div id="deliveryControls" class="delivery-controls" style="display: none;">
                <h3>🚚 Delivery Control</h3>

                <!-- Estado de la Entrega -->
                <div class="delivery-status-card">
                    <h6>Delivery Status</h6>

                    <!-- Mensaje principal -->
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle"></i>
                        <span id="delivery-message">Select your location to get started</span>
                    </div>

                    <!-- Información de la entrega -->
                    <div class="delivery-info-grid">
                        <div class="info-box">
                            <small>Status</small>
                            <div class="value" id="delivery-status">Preparing</div>
                        </div>
                        <div class="info-box">
                            <small>ETA</small>
                            <div class="value" style="color: #007bff;" id="eta-display">--</div>
                        </div>
                        <div class="info-box">
                            <small>Distance</small>
                            <div class="value" style="color: #28a745;" id="distance-display">--</div>
                        </div>
                        <div class="info-box">
                            <small>Progress</small>
                            <div class="value" style="color: #ffc107;" id="progress-text">0%</div>
                        </div>
                    </div>

                    <!-- Barra de progreso -->
                    <div class="progress-container">
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated"
                                role="progressbar"
                                id="progress-bar"
                                style="width: 0%">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estado visual con indicador -->
                <div id="deliveryStatus" class="delivery-status">
                    <span class="status-indicator" id="statusIndicator"></span>
                    <span id="statusText">Status: Assigned</span>
                </div>

                <!-- Botones de control -->
                <div id="deliveryButtons">
                    <button id="btnStartDelivery" class="delivery-button"> 🚀 Start Delivery </button>
                    <button id="btnFinishDelivery" class="delivery-button finish" style="display: none;"> ✅ Complete Delivery </button>
                </div>

                <!-- ETA Display (legacy - mantenido para compatibilidad) -->
                <div id="etaDisplay" class="eta-display" style="display: none;">
                    ⏱️ Estimated time of arrival: <span id="etaTime">--</span>
                </div>

                <!-- Información adicional (legacy - mantenido para compatibilidad) -->
                <div id="deliveryInfo" class="delivery-info" style="display: none;">
                    <p id="deliveryMessage">Preparing delivery simulation...</p>
                </div>

                <!-- Mapa de simulación -->
                <div id="deliverySimulationMap" style="display: none;"></div>
            </div>

            <div id="rutaPedidoContainer" style="display:none; margin-top:30px;">
                <h3 style="margin-bottom:10px;">Order route</h3>
                <div id="mapRutaPedido" style="height: 300px; width: 100%; border-radius: 8px; border: 2px solid #e9ecef;"></div>
            </div>
        </div>
    </div>

    <!-- Modal para seleccionar ubicación -->
    <div id="locationModal" class="modal">
        <div class="modal-content">
            <span class="close-modal-location">&times;</span>
            <h2>📍 Select Delivery Location</h2>
            <div class="instruction-message"> <i class="fas fa-info-circle"></i> Click on the map to select your current location. The route to your destination will be automatically plotted.
            </div>
            <div id="mapRoute"></div>
            <div id="locationMessage" style="margin-top: 10px; padding: 10px; border-radius: 5px; text-align: center;"></div>
            <button id="btnSaveLocation" class="btn-save-location" disabled> 💾 Save Selected Location
            </button>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.min.js"></script>

    <!-- NUEVO: Script de simulación de entrega -->
    <script src="EN_delivery_simulator.js"></script>

    <script>
        
        const modal = document.getElementById('detailsModal');
        const modalContent = document.getElementById('modalContent');
        const closeModalBtn = document.querySelector('.close-modal');
        const btnOpenLocationModal = document.getElementById('btnOpenLocationModal');
        const locationModal = document.getElementById('locationModal');
        const closeLocationModalBtn = document.querySelector('.close-modal-location');
        const btnSaveLocation = document.getElementById('btnSaveLocation');
        const locationMessage = document.getElementById('locationMessage');
        let map, marker;
        let mapRoute, markerRepartidor, markerDestino, controlRouting;
        let currentPedido = null;
        let latRepartidor = null;
        let lonRepartidor = null;
        
        window.mapRutaPedido = null;

        
        let currentSimulator = null;
        let deliverySimulationMap = null;

        
        function openModal(pedido) {
            currentPedido = pedido;
            modalContent.innerHTML = '';
            const infoHtml = `
        <p><strong>Order ID:</strong> ${pedido.pedido_id}</p>
        <p><strong>Client Email:</strong> ${pedido.email}</p>
        <p><strong>Shipping Address:</strong> ${pedido.direccion_envio.replace('<br>')}</p>
        <p><strong>Telephone:</strong> ${pedido.telefono}</p>
        <p><strong>Order Status:</strong> ${pedido.estado.charAt(0).toUpperCase() + pedido.estado.slice(1)}</p>
        <p><strong>Delivery Status:</strong> ${pedido.estado_entrega.charAt(0).toUpperCase() + pedido.estado_entrega.slice(1)}</p>
        <p><strong>Order Date:</strong> ${new Date(pedido.fecha_pedido).toLocaleString()}</p>
        <h3>Products:</h3>
        <div class="product-list">${pedido.productos.map(p => `
            <div class="product-item">
                <img src="data:image/jpeg;base64,${p.imagen_base64}" alt="${p.nombre}" class="product-image" />
                <div>
                    <p><strong>${p.nombre}</strong></p>
                    <p>Quantity: ${p.cantidad}</p>
                    <p>Unit Price: $${parseFloat(p.precio_unitario).toFixed(2)}</p>
                    <p>Subtotal: $${parseFloat(p.subtotal).toFixed(2)}</p>
                </div>
            </div>
        `).join('')}</div>
    `;
            modalContent.innerHTML = infoHtml;
            modal.style.display = 'block';

            
            const deliveryControls = document.getElementById('deliveryControls');
            if (pedido.latitud_repartidor && pedido.longitud_repartidor) {
                deliveryControls.style.display = 'block';
                updateDeliveryStatus(pedido.estado_entrega);
                setupDeliveryControls(pedido);
            } else {
                deliveryControls.style.display = 'none';
            }

            
            const lat = parseFloat(pedido.latitud);
            const lng = parseFloat(pedido.longitud);
            setTimeout(() => {
                if (!map) {
                    map = L.map('map').setView([lat, lng], 15);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors',
                        maxZoom: 18
                    }).addTo(map);
                    marker = L.marker([lat, lng]).addTo(map).bindPopup('📍 Destination: ' + pedido.direccion_envio).openPopup();
                } else {
                    map.setView([lat, lng], 15);
                    marker.setLatLng([lat, lng]);
                    marker.bindPopup('📍 Destination: ' + pedido.direccion_envio).openPopup();
                }
                map.invalidateSize();
            }, 100);

            
            const rutaPedidoContainer = document.getElementById('rutaPedidoContainer');
            if (window.mapRutaPedido) {
                window.mapRutaPedido.remove();
                window.mapRutaPedido = null;
            }
            if (pedido.latitud_repartidor && pedido.longitud_repartidor) {
                rutaPedidoContainer.style.display = 'block';
                setTimeout(() => {
                    window.mapRutaPedido = L.map('mapRutaPedido').setView([lat, lng], 13);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '© OpenStreetMap'
                    }).addTo(window.mapRutaPedido);

                    
                    L.marker([lat, lng], {
                        icon: L.icon({
                            iconUrl: 'https://cdn-icons-png.flaticon.com/512/684/684908.png',
                            iconSize: [32, 32],
                            iconAnchor: [16, 32]
                        })
                    }).addTo(window.mapRutaPedido).bindPopup('🏠 Destination').openPopup();

                    
                    L.marker([pedido.latitud_repartidor, pedido.longitud_repartidor], {
                        icon: L.icon({
                            iconUrl: 'https://cdn-icons-png.flaticon.com/512/2776/2776067.png',
                            iconSize: [32, 32],
                            iconAnchor: [16, 32]
                        })
                    }).addTo(window.mapRutaPedido).bindPopup('🚚 Your Location').openPopup();

                    
                    L.Routing.control({
                        waypoints: [
                            L.latLng(pedido.latitud_repartidor, pedido.longitud_repartidor),
                            L.latLng(lat, lng)
                        ],
                        routeWhileDragging: false,
                        lineOptions: {
                            styles: [{
                                color: 'blue',
                                opacity: 0.8,
                                weight: 5
                            }]
                        },
                        createMarker: function() {
                            return null;
                        },
                        addWaypoints: false
                    }).addTo(window.mapRutaPedido);

                    
                    let bounds = L.latLngBounds([
                        [lat, lng],
                        [pedido.latitud_repartidor, pedido.longitud_repartidor]
                    ]);
                    window.mapRutaPedido.fitBounds(bounds, {
                        padding: [50, 50]
                    });
                    window.mapRutaPedido.invalidateSize();
                }, 200);
            } else {
                rutaPedidoContainer.style.display = 'none';
            }
        }

        
        function updateDeliveryStatus(estado) {
            const statusIndicator = document.getElementById('statusIndicator');
            const statusText = document.getElementById('statusText');

            statusIndicator.className = 'status-indicator';

            switch (estado.toLowerCase()) {
                case 'asignado':
                    statusIndicator.classList.add('status-asignado');
                    statusText.textContent = 'Status: Assigned';
                    break;
                case 'en camino':
                case 'en_camino':
                    statusIndicator.classList.add('status-en-camino');
                    statusText.textContent = 'Status: on the way';
                    break;
                case 'entregado':
                    statusIndicator.classList.add('status-entregado');
                    statusText.textContent = 'Status: Delivered';
                    break;
                default:
                    statusIndicator.classList.add('status-asignado');
                    statusText.textContent = 'Status: ' + estado;
            }
        }

        function setupDeliveryControls(pedido) {
            const btnStartDelivery = document.getElementById('btnStartDelivery');
            const btnFinishDelivery = document.getElementById('btnFinishDelivery');

            
            if (pedido.estado_entrega.toLowerCase() === 'asignado') {
                btnStartDelivery.style.display = 'inline-block';
                btnFinishDelivery.style.display = 'none';
            } else if (pedido.estado_entrega.toLowerCase() === 'en camino' || pedido.estado_entrega.toLowerCase() === 'en_camino') {
                btnStartDelivery.style.display = 'none';
                btnFinishDelivery.style.display = 'inline-block';
            } else if (pedido.estado_entrega.toLowerCase() === 'entregado') {
                btnStartDelivery.style.display = 'none';
                btnFinishDelivery.style.display = 'none';
            }

            
            btnStartDelivery.onclick = function() {
                startDeliveryProcess(pedido);
            };

            btnFinishDelivery.onclick = function() {
                finishDeliveryProcess(pedido);
            };
        }

        function startDeliveryProcess(pedido) {
            const btnStartDelivery = document.getElementById('btnStartDelivery');
            btnStartDelivery.disabled = true;
            btnStartDelivery.textContent = '⏳ Starting...';

            fetch('iniciar_entrega.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id_asignacion: pedido.id_asignacion,
                        pedido_id: pedido.pedido_id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateDeliveryStatus('en camino');
                        btnStartDelivery.style.display = 'none';
                        document.getElementById('btnFinishDelivery').style.display = 'inline-block';

                        
                        return fetch('obtener_pedido.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                id_asignacion: pedido.id_asignacion
                            })
                        });
                    } else {
                        throw new Error(data.error || 'Unknown error');
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        
                        Object.assign(pedido, data.pedido);

                        
                        initializeDeliverySimulation(pedido);

                        
                        openModal(pedido);

                        swal({
                            title: "Delivery started!",
                            text: "The delivery simulation has begun.",
                            icon: "success",
                            button: "Continue"
                        });
                    } else {
                        swal({
                            title: "Error",
                            text: data.error || "No updated data could be obtained.",
                            icon: "error",
                            button: "Close"
                        });
                    }
                })
                .catch(error => {
                    swal({
                        title: "Error",
                        text: "Communication error: " + error.message,
                        icon: "error",
                        button: "Close"
                    });
                })
                .finally(() => {
                    btnStartDelivery.disabled = false;
                    btnStartDelivery.textContent = '🚀 Start Delivery';
                });
        }

        function finishDeliveryProcess(pedido) {
            const btnFinishDelivery = document.getElementById('btnFinishDelivery');
            btnFinishDelivery.disabled = true;
            btnFinishDelivery.textContent = '⏳ Finishing up...';

            
            if (currentSimulator) {
                currentSimulator.stop();
            }

            
            fetch('finalizar_entrega.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id_asignacion: pedido.id_asignacion,
                        pedido_id: pedido.pedido_id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        
                        updateDeliveryStatus('entregado');
                        btnFinishDelivery.style.display = 'none';
                        
                        document.getElementById('deliverySimulationMap').style.display = 'none';
                        document.getElementById('etaDisplay').style.display = 'none';
                        document.getElementById('deliveryInfo').style.display = 'none';
                        swal({
                            title: "Delivery Complete!",
                            text: "Delivery Complete!",
                            icon: "success",
                            button: "Close"
                        });
                    } else {
                        swal({
                            title: "Error",
                            text: "Error al finalizar la entrega: " + (data.error || 'Error desconocido'),
                            icon: "error",
                            button: "Close"
                        });
                    }
                })
                .catch(error => {
                    swal({
                        title: "Error",
                        text: "Communication error: " + error.message,
                        icon: "error",
                        button: "Close"
                    });
                })
                .finally(() => {
                    btnFinishDelivery.disabled = false;
                    btnFinishDelivery.textContent = '✅ Complete Delivery';
                });
        }

        function initializeDeliverySimulation(pedido) {
            console.log('Initializing simulation with order:', pedido);

            
            if (!pedido.latitud_repartidor || !pedido.longitud_repartidor || !pedido.latitud || !pedido.longitud) {
                console.error('Missing coordinates for simulation');
                return;
            }

            const startCoords = {
                lat: parseFloat(pedido.latitud_repartidor),
                lng: parseFloat(pedido.longitud_repartidor)
            };

            const endCoords = {
                lat: parseFloat(pedido.latitud),
                lng: parseFloat(pedido.longitud)
            };

            
            const uiElements = {
                messageElement: document.getElementById('delivery-message'),
                etaElement: document.getElementById('eta-display'),
                progressElement: document.getElementById('progress-bar'),
                progressText: document.getElementById('progress-text'),
                statusElement: document.getElementById('delivery-status'),
                distanceElement: document.getElementById('distance-display')
            };

            const mapContainer = document.getElementById('deliverySimulationMap');
            const etaDisplay = document.getElementById('etaDisplay');
            const deliveryInfo = document.getElementById('deliveryInfo');
            const deliveryMessage = document.getElementById('deliveryMessage');

            
            mapContainer.style.display = 'block';
            etaDisplay.style.display = 'block';
            deliveryInfo.style.display = 'block';

            
            if (deliverySimulationMap) {
                deliverySimulationMap.remove();
            }

            setTimeout(() => {
                
                deliverySimulationMap = L.map('deliverySimulationMap').setView([
                    startCoords.lat,
                    startCoords.lng
                ], 13);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap'
                }).addTo(deliverySimulationMap);

                
                currentSimulator = new DeliverySimulator(deliverySimulationMap, startCoords, endCoords, uiElements);

                
                currentSimulator.setOnCompleteCallback(() => {
                    console.log('Simulación completada - habilitando botón de finalizar');
                    
                    const finalizarBtn = document.getElementById('btnFinishDelivery');
                    if (finalizarBtn) {
                        finalizarBtn.disabled = false;
                        finalizarBtn.classList.remove('btn-secondary');
                        finalizarBtn.classList.add('btn-success');
                    }
                });

                
                setTimeout(() => {
                    currentSimulator.start();
                }, 1000);

                deliverySimulationMap.invalidateSize();
            }, 200);
        }

        
        function iniciarMapaRuta() {
            if (mapRoute) {
                mapRoute.remove();
                mapRoute = null;
                controlRouting = null;
                markerRepartidor = null;
                markerDestino = null;
            }
            const latEntrega = parseFloat(currentPedido.latitud);
            const lonEntrega = parseFloat(currentPedido.longitud);
            latRepartidor = null;
            lonRepartidor = null;
            btnSaveLocation.disabled = true;
            setTimeout(() => {
                mapRoute = L.map('mapRoute').setView([latEntrega, lonEntrega], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap'
                }).addTo(mapRoute);
                markerDestino = L.marker([latEntrega, lonEntrega], {
                    icon: L.icon({
                        iconUrl: 'https://cdn-icons-png.flaticon.com/512/684/684908.png',
                        iconSize: [32, 32],
                        iconAnchor: [16, 32]
                    })
                }).addTo(mapRoute).bindPopup('🏠 Destino: ' + currentPedido.direccion_envio).openPopup();
                locationMessage.style.backgroundColor = '#fff3cd';
                locationMessage.style.border = '1px solid #ffeaa7';
                locationMessage.style.color = '#856404';
                locationMessage.textContent = 'Click on the map to select your current location.';
                mapRoute.on('click', function(e) {
                    latRepartidor = e.latlng.lat;
                    lonRepartidor = e.latlng.lng;
                    if (markerRepartidor) {
                        markerRepartidor.setLatLng([latRepartidor, lonRepartidor]);
                    } else {
                        markerRepartidor = L.marker([latRepartidor, lonRepartidor], {
                            icon: L.icon({
                                iconUrl: 'https://cdn-icons-png.flaticon.com/512/2776/2776067.png',
                                iconSize: [32, 32],
                                iconAnchor: [16, 32]
                            })
                        }).addTo(mapRoute).bindPopup('🚚 Your location').openPopup();
                    }
                    if (controlRouting) {
                        mapRoute.removeControl(controlRouting);
                    }
                    controlRouting = L.Routing.control({
                        waypoints: [
                            L.latLng(latRepartidor, lonRepartidor),
                            L.latLng(latEntrega, lonEntrega)
                        ],
                        routeWhileDragging: false,
                        lineOptions: {
                            styles: [{
                                color: 'blue',
                                opacity: 0.8,
                                weight: 5
                            }]
                        },
                        createMarker: function() {
                            return null;
                        },
                        addWaypoints: false
                    }).addTo(mapRoute);
                    let bounds = L.latLngBounds([
                        [latEntrega, lonEntrega],
                        [latRepartidor, lonRepartidor]
                    ]);
                    mapRoute.fitBounds(bounds, {
                        padding: [50, 50]
                    });
                    locationMessage.style.backgroundColor = '#d4edda';
                    locationMessage.style.border = '1px solid #c3e6cb';
                    locationMessage.style.color = '#155724';
                    locationMessage.innerHTML = '✅ Location selected correctly. Planned route. <br><strong>You can save the location now.</strong>';
                    btnSaveLocation.disabled = false;
                });
                mapRoute.invalidateSize();
            }, 100);
        }

        closeModalBtn.onclick = function() {
            modal.style.display = 'none';
            if (window.mapRutaPedido) {
                window.mapRutaPedido.remove();
                window.mapRutaPedido = null;
            }
            
            if (currentSimulator) {
                currentSimulator.stop();
                currentSimulator = null;
            }
            if (deliverySimulationMap) {
                deliverySimulationMap.remove();
                deliverySimulationMap = null;
            }
        };

        closeLocationModalBtn.onclick = function() {
            locationModal.style.display = 'none';
            resetLocationModal();
        };

        function resetLocationModal() {
            locationMessage.textContent = '';
            if (mapRoute) {
                mapRoute.remove();
                mapRoute = null;
            }
            latRepartidor = null;
            lonRepartidor = null;
            markerRepartidor = null;
            markerDestino = null;
            controlRouting = null;
            btnSaveLocation.disabled = true;
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
                if (window.mapRutaPedido) {
                    window.mapRutaPedido.remove();
                    window.mapRutaPedido = null;
                }
                
                if (currentSimulator) {
                    currentSimulator.stop();
                    currentSimulator = null;
                }
                if (deliverySimulationMap) {
                    deliverySimulationMap.remove();
                    deliverySimulationMap = null;
                }
            }
            if (event.target == locationModal) {
                locationModal.style.display = 'none';
                resetLocationModal();
            }
        };

        
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.details-btn').forEach(button => {
                button.addEventListener('click', () => {
                    const pedido = JSON.parse(button.getAttribute('data-pedido'));
                    openModal(pedido);
                });
            });
        });

        btnOpenLocationModal.addEventListener('click', () => {
            modal.style.display = 'none';
            locationModal.style.display = 'block';
            iniciarMapaRuta();
        });

        btnSaveLocation.addEventListener('click', () => {
            if (latRepartidor === null || lonRepartidor === null) {
                swal({
                    title: "Error",
                    text: "No valid location has been selected.",
                    icon: "warning",
                    button: "Close"
                });
                return;
            }
            btnSaveLocation.disabled = true;
            btnSaveLocation.textContent = '⏳ Saving...';
            fetch('update_location.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        latitud: latRepartidor,
                        longitud: lonRepartidor,
                        id_asignacion: currentPedido.id_asignacion
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        swal({
                            title: "Success!",
                            text: "Location saved successfully.",
                            icon: "success",
                            button: "Close"
                        }).then(() => {
                            locationModal.style.display = 'none';
                            resetLocationModal();
                            
                            location.reload();
                        });
                    } else {
                        swal({
                            title: "Error",
                            text: "Error saving location: " + (data.error || 'Unknown error'),
                            icon: "error",
                            button: "Close"
                        });
                    }
                })
                .catch(error => {
                    swal({
                        title: "Error",
                        text: "Error communicating with the server: " + error.message,
                        icon: "error",
                        button: "Close"
                    });
                })
                .finally(() => {
                    btnSaveLocation.disabled = false;
                    btnSaveLocation.textContent = '💾 Save Selected Location';
                });
        });
    </script>
</body>

</html>