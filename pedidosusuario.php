<?php
include('includes/CookiesSessionV.php');
?>
<?php


$servername = "localhost";
$user = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";
$dbname = "homesafe";

$conn = pg_connect("host=$servername dbname=$dbname user=$user password=$password");


if (!$conn) {
    die("Error en la conexión: " . pg_last_error());
}


if (!isset($_SESSION['user_id'])) {
    
    $email = $_SESSION['email'];
    $query = "SELECT id FROM accounts WHERE email = $1";
    $result = pg_query_params($conn, $query, array($email));

    if (pg_num_rows($result) > 0) {
        $row = pg_fetch_assoc($result);
        $_SESSION['user_id'] = $row['id'];
    } else {
        echo '<script>
            swal({
                title: "Error",
                text: "No se pudo encontrar tu cuenta",
                icon: "warning",
                button: "Cerrar",
            }).then(function() {
                window.location = "index.php";
            });
        </script>';
        exit();
    }
}

$usuario_id = $_SESSION['user_id'];


$query = "SELECT * FROM pedidos WHERE usuario_id = $1 ORDER BY fecha_pedido DESC";
$result_pedidos = pg_query_params($conn, $query, array($usuario_id));
$pedidos = [];

while ($row = pg_fetch_assoc($result_pedidos)) {
    $pedido_id = $row['id'];

    
    $query_detalles = "SELECT dp.*, m.nombre, m.imagen as imagen_mueble 
                      FROM detalle_pedido dp 
                      JOIN muebles m ON dp.producto_id = m.id 
                      WHERE dp.pedido_id = $1";
    $result_detalles = pg_query_params($conn, $query_detalles, array($pedido_id));

    $detalles = [];
    while ($detalle = pg_fetch_assoc($result_detalles)) {
        $detalles[] = $detalle;
    }

    
    $query_repartidor = "SELECT r.id, r.nombre, r.telefono, r.email, ae.estado_entrega, ae.fecha_asignacion, ae.latitud_repartidor, ae.longitud_repartidor 
                         FROM asignaciones_entrega ae 
                         JOIN repartidores r ON ae.repartidor_id = r.id 
                         WHERE ae.pedido_id = $1";
    $result_repartidor = pg_query_params($conn, $query_repartidor, array($pedido_id));

    if (pg_num_rows($result_repartidor) > 0) {
        $row['repartidor'] = pg_fetch_assoc($result_repartidor);
    } else {
        $row['repartidor'] = null;
    }

    $row['detalles'] = $detalles;
    $pedidos[] = $row;
}


$query_mensajes = "SELECT * FROM mensajes_usuario WHERE usuario_id = $1 AND leido = false ORDER BY fecha DESC";
$result_mensajes = pg_query_params($conn, $query_mensajes, array($usuario_id));
$mensajes = [];

while ($row = pg_fetch_assoc($result_mensajes)) {
    $mensajes[] = $row;
}


if (count($mensajes) > 0) {
    $query_marcar = "UPDATE mensajes_usuario SET leido = true WHERE usuario_id = $1 AND leido = false";
    $result_marcar = pg_query_params($conn, $query_marcar, array($usuario_id));
}
?>
<!DOCTYPE html>
<html class="wide wow-animation" lang="en">

<head>
    <title>Mis Pedidos - HomeSafe</title>
    <meta name="format-detection" content="telephone=no">
    <meta name="viewport"
        content="width=device-width height=device-height initial-scale=1.0 maximum-scale=1.0 user-scalable=0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta charset="utf-8">
    <link rel="icon" href="images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" type="text/css"
        href="//fonts.googleapis.com/css?family=Work+Sans:300,400,500,700,800%7CPoppins:300,400,700">
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/fonts.css">
    <link rel="stylesheet" href="css/style.css" id="main-styles-link">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />

    <style>
        /* ===== ESTILOS GENERALES ===== */
        .ie-panel {
            display: none;
            background: #212121;
            padding: 10px 0;
            box-shadow: 3px 3px 5px 0 rgba(0, 0, 0, .3);
            clear: both;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        html.ie-10 .ie-panel,
        html.lt-ie-10 .ie-panel {
            display: block;
        }

        .phone-icon {
            display: inline-block;
            width: 16px;
            height: 16px;
            margin-right: 5px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'%3E%3Cpath fill='%230056b3' d='M497.39 361.8l-112-48a24 24 0 0 0-28 6.9l-49.6 60.6A370.66 370.66 0 0 1 130.6 204.11l60.6-49.6a23.94 23.94 0 0 0 6.9-28l-48-112A24.16 24.16 0 0 0 122.6.61l-104 24A24 24 0 0 0 0 48c0 256.5 207.9 464 464 464a24 24 0 0 0 23.4-18.6l24-104a24.29 24.29 0 0 0-14.01-27.6z'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain;
            vertical-align: middle;
        }

        .bg_background {
            background-color: #212529;
        }

        /* ===== ICONOS DE RUTA Y UBICACIÓN ===== */
        .route-icon {
            color: #007bff;
            margin-right: 8px;
            font-size: 16px;
        }

        .location-icon {
            color: #dc3545;
            margin-right: 8px;
            font-size: 16px;
        }

        .route-header {
            display: flex;
            align-items: center;
            font-weight: 600;
            color: #495057;
            margin-bottom: 10px;
        }

        .location-header {
            display: flex;
            align-items: center;
            font-weight: 500;
            color: #6c757d;
            font-size: 14px;
        }

        /* ===== CONTENEDOR PRINCIPAL DE PEDIDOS ===== */
        .orders-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px 0;
        }

        /* ===== ITEMS DE PEDIDO ===== */
        .order-accordion-item {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
            animation: fadeInUp 0.5s ease forwards;
        }

        .order-accordion-item:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .order-accordion-item:nth-child(2) {
            animation-delay: 0.1s;
        }

        .order-accordion-item:nth-child(3) {
            animation-delay: 0.2s;
        }

        .order-accordion-item:nth-child(4) {
            animation-delay: 0.3s;
        }

        /* ===== HEADER DEL PEDIDO ===== */
        .order-header {
            padding: 20px 25px;
            cursor: pointer;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 1px solid #dee2e6;
            transition: background 0.3s ease;
        }

        .order-header:hover {
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
        }

        .order-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-title h5 {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 8px;
            font-size: 18px;
        }

        .order-date {
            color: #6c757d;
            font-size: 14px;
            font-weight: 500;
        }

        .order-status-container {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 10px;
        }

        /* ===== TOTAL DEL PEDIDO ===== */
        .order-total {
            text-align: right;
        }

        .total-label {
            font-size: 14px;
            color: #6c757d;
            font-weight: 500;
        }

        .total-amount {
            font-size: 20px;
            font-weight: 700;
            color: #2c3e50;
            margin-left: 8px;
        }

        /* ===== BADGES DE ESTADO ===== */
        .status-badge {
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            min-width: 120px;
            justify-content: center;
        }

        .status-pendiente {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            color: #856404;
            border: 2px solid #ffc107;
        }

        .status-procesando {
            background: linear-gradient(135deg, #cce5ff 0%, #b3d9ff 100%);
            color: #004085;
            border: 2px solid #007bff;
        }

        .status-listo {
            background: linear-gradient(135deg, #0056b3 0%, #004494 100%);
            color: #ffffff;
            border: 2px solid #003d82;
        }

        .status-entregado {
            background: linear-gradient(135deg, #d1e7dd 0%, #badbcc 100%);
            color: #0f5132;
            border: 2px solid #28a745;
        }

        .status-cancelado {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            border: 2px solid #dc3545;
        }

        /* ===== FLECHA DE EXPANSIÓN ===== */
        .expand-arrow {
            font-size: 16px;
            color: #6c757d;
            transition: transform 0.3s ease;
            margin-top: 10px;
        }

        .order-header[aria-expanded="true"] .expand-arrow {
            transform: rotate(180deg);
        }

        /* ===== CONTENIDO EXPANDIBLE ===== */
        .order-details {
            border-top: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }

        .collapsing {
            transition: height 0.3s ease;
        }

        .order-body {
            padding: 25px;
            background: #fafbfc;
        }

        /* ===== GRID DE DETALLES ===== */
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .detail-item {
            background: white;
            padding: 18px;
            border-radius: 10px;
            border-left: 4px solid #007bff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .detail-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .detail-value {
            color: #2c3e50;
            font-size: 15px;
            line-height: 1.4;
        }

        /* ===== SECCIÓN DE PRODUCTOS ===== */
        .products-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            border: 2px dashed #dee2e6;
        }

        .products-grid {
            display: grid;
            gap: 15px;
        }

        .product-item-accordion {
            display: flex;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .product-item-accordion:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }

        .product-image-container {
            margin-right: 15px;
        }

        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #dee2e6;
        }

        .product-info {
            flex-grow: 1;
        }

        .product-name {
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 600;
            font-size: 16px;
        }

        .product-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .quantity {
            color: #6c757d;
            font-size: 14px;
        }

        .subtotal {
            font-weight: 700;
            color: #2c3e50;
            font-size: 16px;
        }

        /* ===== SEGUIMIENTO DE ENTREGA ===== */
        .delivery-tracking {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #eee;
        }

        .delivery-info {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .delivery-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #0056b3;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 20px;
        }

        .delivery-details h6 {
            margin-bottom: 5px;
        }

        .tracking-steps {
            position: relative;
            padding-left: 30px;
        }

        .tracking-step {
            position: relative;
            padding-bottom: 20px;
        }

        .tracking-step:last-child {
            padding-bottom: 0;
        }

        .tracking-step:before {
            content: '';
            position: absolute;
            left: -20px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #ccc;
            z-index: 1;
        }

        .tracking-step.active:before {
            background-color: #0056b3;
        }

        .tracking-step:after {
            content: '';
            position: absolute;
            left: -14px;
            top: 17px;
            width: 2px;
            height: calc(100% - 12px);
            background-color: #ccc;
        }

        .tracking-step.active:after {
            background-color: #0056b3;
        }

        .tracking-step:last-child:after {
            display: none;
        }

        .tracking-label {
            font-weight: 600;
            margin-bottom: 3px;
        }

        .tracking-time {
            font-size: 12px;
            color: #777;
        }

        /* ===== MAPA DE RUTA ===== */
        .delivery-map-container {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #eee;
        }

        .delivery-map {
            height: 400px;
            width: 100%;
            border-radius: 8px;
            border: 2px solid #e9ecef;
            margin-top: 15px;
        }

        .map-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .map-info h6 {
            margin: 0;
            color: #495057;
        }

        .route-info {
            display: flex;
            gap: 20px;
            font-size: 14px;
            color: #6c757d;
        }

        .route-info span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .btn-toggle-map {
            background-color: #0056b3;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .btn-toggle-map:hover {
            background-color: #004494;
        }

        .btn-toggle-map:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 86, 179, 0.25);
        }

        .btn-simulation {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
            margin-left: 10px;
        }

        .btn-simulation:hover {
            background-color: #218838;
        }

        .btn-simulation:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }

        /* ===== SIMULACIÓN DE ENTREGA ===== */
        .simulation-controls {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            padding: 15px;
            min-width: 250px;
        }

        .simulation-panel h4 {
            margin: 0 0 10px 0;
            color: #333;
            text-align: center;
        }

        .simulation-buttons {
            display: flex;
            gap: 5px;
            margin-bottom: 10px;
        }

        .simulation-buttons button {
            flex: 1;
            padding: 8px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
        }

        .btn-pause {
            background: #ffc107;
        }

        .btn-resume {
            background: #28a745;
            color: white;
        }

        .btn-reset {
            background: #6c757d;
            color: white;
        }

        .simulation-info {
            text-align: center;
            font-size: 14px;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 5px;
        }

        .progress-fill {
            height: 100%;
            background: #007bff;
            transition: width 0.3s ease;
        }

        .delivery-simulation-marker {
            font-size: 20px;
            text-align: center;
            animation: bounce 1s infinite;
        }

        .delivery-complete-message {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 2000;
            background: rgba(0, 0, 0, 0.8);
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .completion-alert {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .completion-alert h3 {
            color: #28a745;
            margin-bottom: 15px;
        }

        .completion-alert button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 15px;
        }

        /* ===== ESTADO VACÍO ===== */
        .empty-orders {
            padding: 80px 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 16px;
            margin: 40px 0;
            text-align: center;
        }

        .empty-orders i {
            font-size: 5rem;
            color: #ccc;
            margin-bottom: 20px;
        }

        /* ===== ANIMACIONES ===== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes bounce {

            0%,
            20%,
            50%,
            80%,
            100% {
                transform: translateY(0);
            }

            40% {
                transform: translateY(-10px);
            }

            60% {
                transform: translateY(-5px);
            }
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .order-summary {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .order-status-container {
                align-self: flex-end;
                align-items: flex-end;
            }

            .details-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .detail-item {
                padding: 15px;
            }

            .total-amount {
                font-size: 18px;
            }

            .product-details {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }

            .map-info {
                flex-direction: column;
                align-items: flex-start;
            }

            .route-info {
                flex-direction: column;
                gap: 10px;
            }

            .simulation-controls {
                top: 10px;
                right: 10px;
                left: 10px;
                min-width: auto;
            }
        }

        @media (max-width: 480px) {
            .order-header {
                padding: 15px 20px;
            }

            .order-body {
                padding: 20px;
            }

            .order-title h5 {
                font-size: 16px;
            }

            .total-amount {
                font-size: 16px;
            }

            .orders-container {
                padding: 15px 0;
            }
        }
    </style>
</head>

<body>
    <?php include 'modals.php'; ?>

    <div class="page">
        <!-- Page Header-->
        <header class="section novi-background page-header">
            <!-- RD Navbar-->
            <div class="rd-navbar-wrap">
                <nav class="rd-navbar rd-navbar-corporate" data-layout="rd-navbar-fixed"
                    data-sm-layout="rd-navbar-fixed" data-md-layout="rd-navbar-fixed"
                    data-md-device-layout="rd-navbar-fixed" data-lg-layout="rd-navbar-static"
                    data-lg-device-layout="rd-navbar-static" data-lg-stick-up="true" data-lg-stick-up-offset="118px"
                    data-xl-layout="rd-navbar-static" data-xl-device-layout="rd-navbar-static" data-xl-stick-up="true"
                    data-xl-stick-up-offset="118px" data-xxl-layout="rd-navbar-static"
                    data-xxl-device-layout="rd-navbar-static" data-xxl-stick-up-offset="118px" data-xxl-stick-up="true">
                    <div class="rd-navbar-aside-outer">
                        <div class="rd-navbar-aside">
                            <!-- RD Navbar Panel-->
                            <div class="rd-navbar-panel">
                                <!-- RD Navbar Toggle-->
                                <button class="rd-navbar-toggle"
                                    data-rd-navbar-toggle="#rd-navbar-nav-wrap-1"><span></span></button>
                                <!-- RD Navbar Brand--><a class="rd-navbar-brand" href="index.php"><img
                                        src="images/logo-default-151x44.png" alt="" width="151" height="44"
                                        srcset="images/logo-default-151x44.png 2x" /></a>
                            </div>
                            <div class="rd-navbar-collapse">
                                <button class="rd-navbar-collapse-toggle rd-navbar-fixed-element-1"
                                    data-rd-navbar-toggle="#rd-navbar-collapse-content-1"><span></span></button>
                                <div class="rd-navbar-collapse-content" id="rd-navbar-collapse-content-1">
                                    <article class="unit align-items-center">
                                        <div class="unit-left"><span
                                                class="icon novi-icon icon-md icon-modern mdi mdi-phone"></span></div>
                                        <div class="unit-body">
                                            <ul class="list-0">
                                                <li><a class="link-default" href="tel:#">1-800-1234-567</a></li>
                                                <li><a class="link-default" href="tel:#">1-800-8763-765</a></li>
                                            </ul>
                                        </div>
                                    </article>
                                    <article class="unit align-items-center">
                                        <div class="unit-left"><span
                                                class="icon novi-icon icon-md icon-modern mdi mdi-map-marker"></span>
                                        </div>
                                        <div class="unit-body"><a class="link-default" href="tel:#">5 Calle Ote. 1-1,
                                                <br>Santa Tecla, El Salvador</a></div>
                                    </article>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="rd-navbar-main-outer custom">
                        <div class="rd-navbar-main">
                            <div class="rd-navbar-nav-wrap" id="rd-navbar-nav-wrap-1">
                                <!-- RD Navbar Nav-->
                                <ul class="rd-navbar-nav">
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="index.php">Inicio</a>
                                    </li>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="properties.php">Propiedades</a>
                                    </li>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="furniture.php">Muebles</a>
                                    </li>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="about-us.php">Sobre
                                            nosotros</a>
                                    </li>
                                    <?php if (!isset($_SESSION['email'])): ?>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-toggle="modal" data-target="#loginModal">Iniciar Sesión</a></li>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-toggle="modal" data-target="#RegisterModal">Registrarse</a></li>
                                    <?php else: ?>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="logout.p.php">Cerrar Sesión</a></li>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-toggle="modal" data-target="#PerfilModal"> Sesión actual: <?php echo htmlspecialchars($username); ?></a></li>
                                        <!-- Ícono del carrito de compras -->
                                        <li class="rd-nav-item">
                                            <a class="rd-nav-link" href="mostrarcarrito.php">
                                                <i class="fa fa-shopping-cart" style="font-size: 1.5em;"></i>
                                            </a>
                                        </li>
                                        </li>

                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>



                </nav>
            </div>
        </header>

        <!-- Contenido de Mis Pedidos -->
        <!-- Contenido de Mis Pedidos con Acordeón -->
        <section class="section novi-background section-md text-center">
            <div class="container">
                <h3 class="text-uppercase font-weight-bold wow-outer">
                    <br>
                    <span class="wow slideInDown">Mis Pedidos</span>
                </h3>

                <?php if (count($mensajes) > 0): ?>
                    <div class="container mt-4">
                        <?php foreach ($mensajes as $mensaje): ?>
                            <div class="alert <?php echo ($mensaje['tipo'] == 'cancelacion') ? 'alert-danger' : 'alert-success'; ?> alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($mensaje['mensaje']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (count($pedidos) > 0): ?>
                    <div class="orders-container">
                        <?php foreach ($pedidos as $index => $pedido): ?>
                            <div class="order-accordion-item" data-order-id="<?php echo $pedido['id']; ?>">
                                <!-- Header del pedido (siempre visible) -->
                                <div class="order-header" data-bs-toggle="collapse" data-bs-target="#orderCollapse<?php echo $index; ?>" aria-expanded="false" aria-controls="orderCollapse<?php echo $index; ?>">
                                    <div class="order-summary">
                                        <div class="order-title">
                                            <h5 class="mb-1">
                                                <i class="fas fa-receipt me-2"></i>
                                                Pedido #<?php echo $pedido['id']; ?>
                                            </h5>
                                            <p class="order-date mb-1">
                                                <i class="fas fa-calendar me-1"></i>
                                                <?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?>
                                            </p>
                                        </div>
                                        <div class="order-status-container">
                                            <div class="order-total mb-2">
                                                <span class="total-label">Total:</span>
                                                <span class="total-amount">$<?php echo number_format($pedido['total'], 2); ?></span>
                                            </div>
                                            <span class="status-badge status-<?php echo $pedido['estado']; ?>">
                                                <?php if ($pedido['estado'] == 'pendiente'): ?>
                                                    <i class="fas fa-clock me-1"></i>
                                                    Pendiente
                                                <?php elseif ($pedido['estado'] == 'procesando'): ?>
                                                    <i class="fas fa-cog me-1"></i>
                                                    Procesando
                                                <?php elseif ($pedido['estado'] == 'listo'): ?>
                                                    <i class="fas fa-check-circle me-1"></i>
                                                    Listo
                                                <?php elseif ($pedido['estado'] == 'entregado'): ?>
                                                    <i class="fas fa-truck me-1"></i>
                                                    Entregado
                                                <?php elseif ($pedido['estado'] == 'cancelado'): ?>
                                                    <i class="fas fa-times-circle me-1"></i>
                                                    Cancelado
                                                <?php endif; ?>
                                            </span>
                                            <i class="fas fa-chevron-down expand-arrow"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contenido expandible -->
                                <div id="orderCollapse<?php echo $index; ?>" class="collapse order-details">
                                    <div class="order-body">
                                        <!-- Información de envío -->
                                        <div class="details-grid">
                                            <div class="detail-item">
                                                <div class="detail-label">
                                                    <i class="fas fa-map-marker-alt me-2"></i>
                                                    Dirección de envío:
                                                </div>
                                                <div class="detail-value">
                                                    <?php echo nl2br(htmlspecialchars($pedido['direccion_envio'])); ?>
                                                </div>
                                            </div>

                                            <div class="detail-item">
                                                <div class="detail-label">
                                                    <i class="fas fa-phone me-2"></i>
                                                    Teléfono de contacto:
                                                </div>
                                                <div class="detail-value">
                                                    <?php echo htmlspecialchars($pedido['telefono']); ?>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Información de seguimiento y repartidor -->
                                        <?php if ($pedido['estado'] == 'listo' || $pedido['estado'] == 'entregado'): ?>
                                            <?php if ($pedido['repartidor']): ?>
                                                <div class="delivery-tracking">
                                                    <h6 class="mb-3"><i class="fas fa-truck me-2"></i> Seguimiento de entrega</h6>

                                                    <div class="delivery-info">
                                                        <div class="delivery-avatar">
                                                            <i class="fas fa-user"></i>
                                                        </div>
                                                        <div class="delivery-details">
                                                            <h6><?php echo htmlspecialchars($pedido['repartidor']['nombre']); ?></h6>
                                                            <p class="mb-0">
                                                                <span class="phone-icon"></span> <?php echo htmlspecialchars($pedido['repartidor']['telefono']); ?>
                                                            </p>
                                                        </div>
                                                    </div>

                                                    <div class="tracking-steps">
                                                        <div class="tracking-step <?php echo ($pedido['estado'] == 'listo' || $pedido['estado'] == 'entregado') ? 'active' : ''; ?>">
                                                            <div class="tracking-label">Pedido asignado a repartidor</div>
                                                            <div class="tracking-time"><?php echo date('d/m/Y H:i', strtotime($pedido['repartidor']['fecha_asignacion'])); ?></div>
                                                        </div>

                                                        <div class="tracking-step <?php echo ($pedido['repartidor']['estado_entrega'] == 'en_camino' || $pedido['estado'] == 'entregado') ? 'active' : ''; ?>">
                                                            <div class="tracking-label">En camino</div>
                                                            <div class="tracking-time">
                                                                <?php if ($pedido['repartidor']['estado_entrega'] == 'en_camino' || $pedido['estado'] == 'entregado'): ?>
                                                                    En proceso
                                                                <?php else: ?>
                                                                    Pendiente
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>

                                                        <div class="tracking-step <?php echo ($pedido['estado'] == 'entregado') ? 'active' : ''; ?>">
                                                            <div class="tracking-label">Entregado</div>
                                                            <div class="tracking-time">
                                                                <?php if ($pedido['estado'] == 'entregado'): ?>
                                                                    Completado
                                                                <?php else: ?>
                                                                    Pendiente
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <?php if ($pedido['estado'] == 'listo' || $pedido['estado'] == 'pendiente'):    ?>
                                                    <form method="post" action="chat/chat_repartidor/cus.php" style="display: inline;">
                                                        <input type="hidden" name="id_pedido" value="<?php echo $pedido['id']; ?>">
                                                        <input type="hidden" name="email_other" value="<?php echo $pedido['repartidor']['email']; ?>">
                                                        <button type="submit" class="btn btn-success btn-action">
                                                            <i class="fas fa-check-circle me-1"></i>
                                                            Entrar al chat
                                                        </button>
                                                    </form>
                                                <?php endif; ?>



                                                <!-- MAPA DE RUTA DEL REPARTIDOR -->
                                                <?php if ($pedido['repartidor']['latitud_repartidor'] && $pedido['repartidor']['longitud_repartidor'] && $pedido['latitud'] && $pedido['longitud']): ?>
                                                    <div class="delivery-map-container">
                                                        <div class="map-info">
                                                            <div class="route-header">
                                                                <i class="fas fa-route route-icon"></i>
                                                                RUTA DE ENTREGA
                                                            </div>
                                                            <div class="route-info">
                                                                <div class="location-header">
                                                                    <i class="fas fa-map-marker-alt location-icon"></i>
                                                                    Tu ubicación
                                                                </div>
                                                                <span><i class="fas fa-truck" style="color: #0056b3;"></i> Repartidor</span>
                                                            </div>
                                                            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                                                <button class="btn-toggle-map" onclick="toggleDeliveryMap(<?php echo $pedido['id']; ?>)">
                                                                    <i class="fas fa-eye"></i> Ver mapa
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div id="delivery-map-<?php echo $pedido['id']; ?>" class="delivery-map" style="display: none;"></div>
                                                    </div>
                                                <?php endif; ?>

                                            <?php else: ?>
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle me-2"></i> Tu pedido está listo y será asignado a un repartidor pronto.
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <!-- Lista de productos -->
                                        <div class="products-section mt-4">
                                            <h6 class="mb-3">
                                                <i class="fas fa-box me-2"></i>
                                                Productos (<?php echo count($pedido['detalles']); ?>)
                                            </h6>
                                            <div class="products-grid">
                                                <?php foreach ($pedido['detalles'] as $detalle): ?>
                                                    <div class="product-item-accordion">
                                                        <div class="product-image-container">
                                                            <?php if (!empty($detalle['imagen_mueble'])): ?>
                                                                <img src="data:image/jpeg;base64,<?php echo base64_encode(pg_unescape_bytea($detalle['imagen_mueble'])); ?>" alt="<?php echo htmlspecialchars($detalle['nombre']); ?>" class="product-image">
                                                            <?php else: ?>
                                                                <div class="product-image d-flex align-items-center justify-content-center bg-light">
                                                                    <i class="fas fa-image text-muted"></i>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="product-info">
                                                            <h6 class="product-name"><?php echo htmlspecialchars($detalle['nombre']); ?></h6>
                                                            <div class="product-details">
                                                                <span class="quantity"><?php echo $detalle['cantidad']; ?> x $<?php echo number_format($detalle['precio_unitario'], 2); ?></span>
                                                                <span class="subtotal">$<?php echo number_format($detalle['subtotal'], 2); ?></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-orders">
                        <i class="fas fa-shopping-bag fa-4x text-muted mb-4"></i>
                        <h4>No tienes pedidos realizados</h4>
                        <p class="text-muted mb-4">Cuando realices una compra, podrás ver el historial de tus pedidos aquí.</p>
                        <a href="furniture.php" class="btn btn-primary btn-lg">
                            Ir a comprar
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Footer -->
        <footer class="section novi-background footer-advanced bg-gray-700">
            <div class="footer-advanced-main">
                <div class="container">
                    <div class="row row-50">
                        <div class="col-lg-4">
                            <h5 class="font-weight-bold text-uppercase text-white">Sobre nosotros</h5>
                            <p class="footer-advanced-text">HomeSafe es una tienda online donde se pueden comprar
                                inmuebles y muebles. Este sitio web tiene una interfaz fácil de usar e intuitiva.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-advanced-aside">
                <div class="container">
                    <div class="footer-advanced-layout">
                        <div>
                            <ul class="list-nav">
                                <li><a href="index.php">Inicio</a></li>
                                <li><a href="about-us.php">Sobre nosotros</a></li>
                                <li><a href="properties.php">Propiedades</a></li>
                                <li><a href="furniture.php">Muebles</a></li>
                            </ul>
                        </div>
                        <div>
                            <ul class="foter-social-links list-inline list-inline-md">
                                <li><a class="icon novi-icon icon-sm link-default mdi mdi-instagram"
                                        href="https://www.instagram.com/homesafe25"></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container">
                <hr>
            </div>
            <div class="footer-advanced-aside">
                <div class="container">
                    <div class="footer-advanced-layout"><a class="brand" href="index.php"><img
                                src="images/logo-light-115x34.png" alt="" width="115" height="34"
                                srcset="images/logo-light-115x34.png 2x" /></a>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Global Mailform Output-->
    <div class="snackbars" id="form-output-global"></div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.min.js"></script>

    <!-- Javascript-->
    <script src="js/core.min.js"></script>
    <script src="js/script.js"></script>
    <!-- Bootstrap 5 JS para el funcionamiento de los componentes -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p"
        crossorigin="anonymous"></script>

    <script>
        
        let deliveryMaps = {};
        let visibleMaps = {};

        
        const pedidosData = <?php echo json_encode($pedidos); ?>;

        function toggleDeliveryMap(pedidoId) {
            const mapContainer = document.getElementById(`delivery-map-${pedidoId}`);
            const button = event.target.closest('.btn-toggle-map');

            if (visibleMaps[pedidoId]) {
                
                mapContainer.style.display = 'none';
                button.innerHTML = '<i class="fas fa-eye"></i> Ver mapa';
                visibleMaps[pedidoId] = false;

                
                if (deliveryMaps[pedidoId]) {
                    deliveryMaps[pedidoId].remove();
                    delete deliveryMaps[pedidoId];
                }
            } else {
                
                mapContainer.style.display = 'block';
                button.innerHTML = '<i class="fas fa-eye-slash"></i> Ocultar mapa';
                visibleMaps[pedidoId] = true;

                
                setTimeout(() => {
                    initializeDeliveryMap(pedidoId);
                }, 100);
            }
        }

        function initializeDeliveryMap(pedidoId) {
            
            const pedido = pedidosData.find(p => p.id == pedidoId);

            if (!pedido || !pedido.repartidor || !pedido.repartidor.latitud_repartidor || !pedido.repartidor.longitud_repartidor) {
                console.error('Datos del pedido o repartidor no encontrados');
                return;
            }

            const destinoLat = parseFloat(pedido.latitud);
            const destinoLng = parseFloat(pedido.longitud);
            const repartidorLat = parseFloat(pedido.repartidor.latitud_repartidor);
            const repartidorLng = parseFloat(pedido.repartidor.longitud_repartidor);

            
            const map = L.map(`delivery-map-${pedidoId}`).setView([destinoLat, destinoLng], 13);

            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            
            const destinoMarker = L.marker([destinoLat, destinoLng], {
                icon: L.icon({
                    iconUrl: 'https://cdn-icons-png.flaticon.com/512/684/684908.png',
                    iconSize: [32, 32],
                    iconAnchor: [16, 32],
                    popupAnchor: [0, -32]
                })
            }).addTo(map);

            destinoMarker.bindPopup(`
            <div style="text-align: center;">
                <strong>🏠 Tu ubicación</strong><br>
                <small>${pedido.direccion_envio}</small>
            </div>
        `);

            
            const repartidorMarker = L.marker([repartidorLat, repartidorLng], {
                icon: L.icon({
                    iconUrl: 'https://cdn-icons-png.flaticon.com/512/2776/2776067.png',
                    iconSize: [32, 32],
                    iconAnchor: [16, 32],
                    popupAnchor: [0, -32]
                })
            }).addTo(map);

            repartidorMarker.bindPopup(`
            <div style="text-align: center;">
                <strong>🚚 Repartidor</strong><br>
                <small>${pedido.repartidor.nombre}</small><br>
                <small>${pedido.repartidor.telefono}</small>
            </div>
        `);

            
            const routingControl = L.Routing.control({
                waypoints: [
                    L.latLng(repartidorLat, repartidorLng),
                    L.latLng(destinoLat, destinoLng)
                ],
                routeWhileDragging: false,
                lineOptions: {
                    styles: [{
                        color: '#0056b3',
                        opacity: 0.8,
                        weight: 5
                    }]
                },
                createMarker: function() {
                    return null; 
                },
                addWaypoints: false,
                show: false 
            }).addTo(map);

            
            const bounds = L.latLngBounds([
                [destinoLat, destinoLng],
                [repartidorLat, repartidorLng]
            ]);

            map.fitBounds(bounds, {
                padding: [20, 20],
                maxZoom: 15
            });

            
            setTimeout(() => {
                map.invalidateSize();
            }, 200);

            
            deliveryMaps[pedidoId] = map;

            
            setTimeout(() => {
                destinoMarker.openPopup();
            }, 500);
        }

        
        window.addEventListener('beforeunload', function() {
            Object.values(deliveryMaps).forEach(map => {
                if (map) {
                    map.remove();
                }
            });
        });
    </script>
</body>

</html>
<?php

pg_close($conn);
?>