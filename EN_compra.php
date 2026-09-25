<?php
include('translator.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
if (isset($_SESSION['email'])) {
    if (!isset($_SESSION['user_agent']) || $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        session_unset();
        session_destroy();
        header('Location: EN_index.php?error=session_invalid');
        exit();
    }
}

include('user.p.php');

if (!isset($_SESSION['email'])) {
    echo '<script>
        swal({
            title: "Error",
            text: "You must log in to make a purchase.",
            icon: "warning",
            button: "Close",
        }).then(function() {
            window.location = "EN_index.php";
        });
    </script>';
    exit();
}
$nombre = $username;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';


$host = "localhost";
$port = "5432";
$dbname = "homesafe";
$user = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";
$conn_string = "host=$host port=$port dbname=$dbname user=$user password=$password";
$conn = pg_connect($conn_string);


if (!$conn) {
    die("Failed connection: " . pg_last_error());
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
                text: "Your account could not be found.",
                icon: "warning",
                button: "Close",
            }).then(function() {
                window.location = "EN_index.php";
            });
        </script>';
        exit();
    }
}
$usuario_id = $_SESSION['user_id'];


$ubicaciones = [];
$saved_locations_query = "SELECT direccion_envio, latitud, longitud FROM ubicaciones_usuario WHERE usuario_id = $1 ORDER BY fecha_registro DESC";
$saved_locations_result = pg_query_params($conn, $saved_locations_query, array($usuario_id));
if ($saved_locations_result) {
    while ($row = pg_fetch_assoc($saved_locations_result)) {
        $ubicaciones[] = $row;
    }
}


$location_query = "SELECT direccion_envio, latitud, longitud FROM accounts WHERE id = $1";
$location_result = pg_query_params($conn, $location_query, array($usuario_id));
$direccion_guardada = '';
$lat_guardada = null;
$lng_guardada = null;
if (pg_num_rows($location_result) > 0) {
    $loc = pg_fetch_assoc($location_result);
    $direccion_guardada = $loc['direccion_envio'];
    $lat_guardada = $loc['latitud'];
    $lng_guardada = $loc['longitud'];
}


$query = "SELECT c.id, c.producto_id, c.cantidad, c.precio_unitario, m.nombre, m.imagen
          FROM carrito c
          JOIN muebles m ON c.producto_id = m.id
          WHERE c.usuario_id = $1";
$result = pg_query_params($conn, $query, array($usuario_id));


$total = 0;
$productos = [];
while ($row = pg_fetch_assoc($result)) {
    $subtotal = $row['precio_unitario'] * $row['cantidad'];
    $total += $subtotal;
    $row['subtotal'] = $subtotal;
    $productos[] = $row;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pago_paypal'])) {
    
    if (count($productos) === 0) {
        echo '<script>
            swal({
                title: "Error",
                text: "There are no products in your cart.",
                icon: "warning",
                button: "Close",
            }).then(function() {
                window.location = "EN_mostrarcarrito.php";
            });
        </script>';
        exit();
    }

    
    $direccion_raw = trim($_POST['direccion']);
    $direccion = htmlspecialchars(trim($direccion_raw), ENT_QUOTES, 'UTF-8');
    if (empty($direccion)) {
        echo '<script>
            swal({
                title: "Error",
                text: "The address cannot be empty or contain invalid characters.",
                icon: "warning",
                button: "Close",
            });
        </script>';
        exit();
    }

    
    $telefono_raw = trim($_POST['telefono']);
    
    $telefono_sin_espacios = str_replace(' ', '', $telefono_raw);
    if (!preg_match('/^\d{4}-\d{4}$/', $telefono_sin_espacios)) {
        echo '<script>
            swal({
                title: "Error",
                text: "The phone number must be in the format XXXX-XXXX, consisting only of numbers and a hyphen.",
                icon: "warning",
                button: "Close",
            });
        </script>';
        exit();
    }
    $telefono = $telefono_sin_espacios;

    
    $latitud = isset($_POST['latitud']) ? floatval($_POST['latitud']) : null;
    $longitud = isset($_POST['longitud']) ? floatval($_POST['longitud']) : null;
    if ($latitud === null || $longitud === null || $latitud < 13.148 || $latitud > 14.445 || $longitud < -90.128 || $longitud > -87.692) {
        echo '<script>
            swal({
                title: "Error",
                text: "The selected location is not within El Salvador.",
                icon: "warning",
                button: "Close",
            });
        </script>';
        exit();
    }
    if (empty($direccion) || empty($telefono) || $latitud === null || $longitud === null) {
        echo '<script>
            swal({
                title: "Error",
                text: "Please fill in all fields and select a location on the map.",
                icon: "warning",
                button: "Close",
            });
        </script>';
        exit();
    } else {
        
        pg_query($conn, "BEGIN");
        try {
            
            $metodo_pago = isset($_POST['metodo_pago']) ? $_POST['metodo_pago'] : null;
            $paypal_transaction_id = isset($_POST['paypal_transaction_id']) ? $_POST['paypal_transaction_id'] : null;
            $query = "INSERT INTO pedidos (usuario_id, email, total, direccion_envio, telefono, metodo_pago, paypal_transaction_id, latitud, longitud)
                      VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9) RETURNING id";
            $result = pg_query_params($conn, $query, array($usuario_id, $email, $total, $direccion, $telefono, $metodo_pago, $paypal_transaction_id, $latitud, $longitud));

            
            $row = pg_fetch_assoc($result);
            $pedido_id = $row['id'];

            
            foreach ($productos as $producto) {
                $subtotal = $producto['precio_unitario'] * $producto['cantidad'];
                $query = "INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio_unitario, subtotal, imagen)
                          VALUES ($1, $2, $3, $4, $5, $6)";
                pg_query_params($conn, $query, array(
                    $pedido_id,
                    $producto['producto_id'],
                    $producto['cantidad'],
                    $producto['precio_unitario'],
                    $subtotal,
                    $producto['imagen']
                ));

                
                $query_update = "UPDATE muebles SET cantidad = cantidad - $1 WHERE id = $2";
                pg_query_params($conn, $query_update, array($producto['cantidad'], $producto['producto_id']));
            }

            
            $check_location_query = "SELECT id FROM ubicaciones_usuario WHERE usuario_id = $1 AND direccion_envio = $2 AND latitud = $3 AND longitud = $4";
            $existing_location_result = pg_query_params($conn, $check_location_query, array($usuario_id, $direccion, $latitud, $longitud));
            if (pg_num_rows($existing_location_result) == 0) {
                
                $insert_location_query = "INSERT INTO ubicaciones_usuario (usuario_id, direccion_envio, latitud, longitud) VALUES ($1, $2, $3, $4)";
                pg_query_params($conn, $insert_location_query, array($usuario_id, $direccion, $latitud, $longitud));
            }

            
            $query = "DELETE FROM carrito WHERE usuario_id = $1";
            pg_query_params($conn, $query, array($usuario_id));

            
            pg_query($conn, "COMMIT");

            try {
                $mail = new PHPMailer(true);
                
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'homesafe2025contact@gmail.com';
                $mail->Password = 'ecqfzpwrvcuoxmnf';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                $mail->CharSet = 'UTF-8';

                
                $mail->setFrom('homesafe2025contact@gmail.com', 'HomeSafe');
                $mail->addAddress($email); 
                $mail->isHTML(true);
                $mail->Subject = 'Factura de tu compra en HomeSafe';

                
                $body = '
                <!DOCTYPE html>
                <html lang="es">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Invoice - HomeSafe</title>
                    <style>
                        * {
                            margin: 0;
                            padding: 0;
                            box-sizing: border-box;
                        }
                        body {
                            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                            line-height: 1.6;
                            color: #333;
                            background-color: #f4f4f4;
                        }
                        .container {
                            max-width: 600px;
                            margin: 0 auto;
                            background-color: #ffffff;
                            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
                            border-radius: 8px;
                            overflow: hidden;
                        }
                        .header {
                            background-color: #343a40; /* Dark grey */
                            color: white;
                            padding: 30px 20px;
                            text-align: center;
                        }
                        .header h1 {
                            font-size: 28px;
                            margin-bottom: 10px;
                            font-weight: 300;
                        }
                        .header p {
                            font-size: 16px;
                            opacity: 0.9;
                        }
                        .content {
                            padding: 30px 30px;
                        }
                        .thank-you {
                            background-color: #e6ffe6; /* Light green */
                            border-left: 4px solid #28a745; /* Green */
                            padding: 20px;
                            margin-bottom: 30px;
                            border-radius: 0 8px 8px 0;
                        }
                        .thank-you h2 {
                            color: #28a745; /* Green */
                            margin-bottom: 10px;
                            font-size: 24px;
                        }
                        .thank-you p {
                            color: #666;
                            font-size: 16px;
                        }
                        .order-details {
                            margin-bottom: 30px;
                        }
                        .order-details h3 {
                            color: #333;
                            margin-bottom: 20px;
                            font-size: 20px;
                            border-bottom: 1px solid #e9ecef;
                            padding-bottom: 10px;
                        }
                        .product-table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-bottom: 20px;
                            background-color: #fff;
                            border-radius: 8px;
                            overflow: hidden;
                            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
                        }
                        .product-table th {
                            background-color: #495057; /* Darker grey */
                            color: white;
                            padding: 15px;
                            text-align: left;
                            font-weight: 600;
                        }
                        .product-table td {
                            padding: 15px;
                            border-bottom: 1px solid #e9ecef;
                        }
                        .product-table tr:last-child td {
                            border-bottom: none;
                        }
                        .product-table tr:nth-child(even) {
                            background-color: #f8f9fa; /* Light grey */
                        }
                        .product-name {
                            font-weight: 600;
                            color: #333;
                        }
                        .product-quantity {
                            color: #666;
                            text-align: center;
                        }
                        .product-price {
                            text-align: right;
                            font-weight: 600;
                            color: #28a745; /* Green */
                        }
                        .total-section {
                            background-color: #f8f9fa; /* Light grey */
                            padding: 20px;
                            border-radius: 8px;
                            margin-bottom: 30px;
                        }
                        .total-row {
                            display: flex;
                            justify-content: space-between;
                            margin-bottom: 10px;
                            padding: 5px 0;
                        }
                        .total-final {
                            border-top: 1px solid #e9ecef;
                            padding-top: 15px;
                            margin-top: 15px;
                            font-size: 18px;
                            font-weight: bold;
                        }
                        .total-final .price {
                            color: #28a745; /* Green */
                            font-size: 22px;
                        }
                        .shipping-info {
                            background-color: #e3f2fd; /* Light blue */
                            border-radius: 8px;
                            padding: 20px;
                            margin-bottom: 30px;
                        }
                        .shipping-info h3 {
                            color: #1976d2; /* Blue */
                            margin-bottom: 15px;
                            font-size: 18px;
                        }
                        .info-row {
                            display: flex;
                            justify-content: space-between;
                            margin-bottom: 8px;
                            padding: 5px 0;
                        }
                        .info-label {
                            font-weight: 600;
                            color: #555;
                        }
                        .info-value {
                            color: #333;
                            text-align: right;
                        }
                        .payment-info {
                            background-color: #fff3cd; /* Light yellow */
                            border-radius: 8px;
                            padding: 20px;
                            margin-bottom: 30px;
                            border: 1px solid #ffeaa7;
                        }
                        .payment-info h3 {
                            color: #856404; /* Dark yellow/brown */
                            margin-bottom: 15px;
                            font-size: 18px;
                        }
                        .transaction-id {
                            background-color: #fff;
                            padding: 10px;
                            border-radius: 4px;
                            font-family: monospace;
                            font-size: 14px;
                            color: #333;
                            word-break: break-all;
                            border: 1px solid #e9ecef;
                        }
                        .footer {
                            background-color: #343a40; /* Dark grey */
                            color: white;
                            padding: 30px 20px;
                            text-align: center;
                        }
                        .footer p {
                            margin-bottom: 10px;
                            opacity: 0.8;
                        }
                        .footer .brand {
                            font-size: 20px;
                            font-weight: bold;
                            color: #f8f9fa; /* Light grey for brand */
                            margin-bottom: 10px;
                        }
                        .divider {
                            height: 1px;
                            background-color: #e9ecef;
                            margin: 30px 0;
                            border-radius: 2px;
                        }
                        @media (max-width: 600px) {
                            .container {
                                margin: 0;
                                border-radius: 0;
                            }
                            .content {
                                padding: 20px 15px;
                            }
                            .header {
                                padding: 30px 15px;
                            }
                            .product-table th,
                            .product-table td {
                                padding: 10px 8px;
                                font-size: 14px;
                            }
                            .total-row {
                                flex-direction: column;
                                text-align: center;
                            }
                            .info-row {
                                flex-direction: column;
                                text-align: left;
                            }
                            .info-value {
                                text-align: left;
                                margin-top: 5px;
                            }
                        }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <!-- Header -->
                        <div class="header">
                            <h1>🏠 HomeSafe</h1>
                            <p>Your home, our priority</p>
                        </div>
                        <!-- Content -->
                        <div class="content">
                            <!-- Thank you message -->
                            <div class="thank-you">
                                <h2>Thank you for your purchase!</h2>
                                <p>Your order has been successfully processed. Below you will find the details of your purchase.</p>
                            </div>
                            <!-- Order details -->
                            <div class="order-details">
                                <h3>📋 Order details</h3>
                                <table class="product-table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th style="text-align: center;">Quantity</th>
                                            <th style="text-align: right;">Precio Unit.</th>
                                            <th style="text-align: right;">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>';
                foreach ($productos as $producto) {
                    $body .= '
                                        <tr>
                                            <td class="product-name">' . htmlspecialchars(tr($producto['nombre'])) . '</td>
                                            <td class="product-quantity">' . intval($producto['cantidad']) . '</td>
                                            <td class="product-price">$' . number_format($producto['precio_unitario'], 2) . '</td>
                                            <td class="product-price">$' . number_format($producto['subtotal'], 2) . '</td>
                                        </tr>';
                }
                $body .= '
                                    </tbody>
                                </table>
                            </div>
                            <!-- Total section -->
                            <div class="total-section">
                                <div class="total-row total-final">
                                    <span>💰 Total purchase amount:</span>
                                    <span class="price">$' . number_format($total, 2) . '</span>
                                </div>
                            </div>
                            <!-- Shipping info -->
                            <div class="shipping-info">
                                <h3>🚚 Shipping information</h3>
                                <div class="info-row">
                                    <span class="info-label">Shipping address:</span>
                                    <span class="info-value">' . htmlspecialchars($direccion) . '</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Contact telephone number:</span>
                                    <span class="info-value">' . htmlspecialchars($telefono) . '</span>
                                </div>
                            </div>
                            <!-- Payment info -->
                            <div class="payment-info">
                                <h3>💳 Payment information</h3>
                                <div class="info-row">
                                    <span class="info-label">Payment method:</span>
                                    <span class="info-value">PayPal</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Transaction ID:</span>
                                </div>
                                <div class="transaction-id">' . htmlspecialchars($paypal_transaction_id) . '</div>
                            </div>
                            <div class="divider"></div>
                            <!-- Additional info -->
                            <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center;">
                                <p style="margin-bottom: 15px; color: #666;">
                                    <strong>🕐 Estimated delivery time:</strong> 3-5 business days
                                </p>
                                <p style="margin-bottom: 15px; color: #666;">
                                    <strong>📞 Customer service:</strong> homesafe2025contact@gmail.com
                                </p>
                                <p style="color: #666;">
                                If you have any questions about your order, please do not hesitate to contact us.
                                </p>
                            </div>
                        </div>
                        <!-- Footer -->
                        <div class="footer">
                            <div class="brand">HomeSafe</div>
                            <p>5 Calle Ote. 1-1, Santa Tecla, El Salvador</p>
                            <p>This is an automated email. Please do not reply.</p>
                            <p style="font-size: 12px; margin-top: 15px;">
                                © 2025 HomeSafe. All rights reserved.
                            </p>
                        </div>
                    </div>
                </body>
                </html>';
                $mail->Body = $body;
                $mail->AltBody = "Thank you for your purchase at HomeSafe. Total: $total. Location: $direccion. Telephone: $telefono. Payment method: PayPal. ID transacción: $paypal_transaction_id.";
                $mail->send();
            } catch (Exception $e) {
                
                error_log("Error sending invoice email: " . $mail->ErrorInfo);
            }

            
            echo '<script>swal({
                title: "Success",
                text: "Purchase made",
                icon: "success",
                button: "Close",
            }).then(function() {
                window.location = "EN_mostrarcarrito.php";
            });</script>';
            exit();
        } catch (Exception $e) {
            
            pg_query($conn, "ROLLBACK");
            echo '<script>
                swal({
                    title: "Purchase error",
                    text: "Your order could not be processed.",
                    icon: "warning",
                    button: "Close",
                }).then(function() {
                    window.location = "EN_mostrarcarrito.php";
                });
            </script>';
        }
    }
}
?>
<!DOCTYPE html>
<html class="wide wow-animation" lang="en">

<head>
    <title>Checkout - HomeSafe</title>
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
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin="" />
    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>
    <style>
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

        .bg_background {
            background-color: #212529;
        }

        .checkout-container {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .order-summary {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }

        .product-item {
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }

        .product-item:last-child {
            border-bottom: none;
        }

        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }

        #paypal-button-container {
            margin-top: 20px;
        }

        .profile-container {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            width: 300px;
            text-align: center;
            /*background-image: url('path/to/your/background-image.jpg');*/
            background-size: cover;
            background-position: center;
        }

        .profile-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 0 auto 20px;
            background-color: #ddd;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        .profile-image svg {
            width: 60%;
            height: 60%;
        }

        .profile-name {
            font-size: 1.5em;
            margin-bottom: 5px;
        }

        .profile-role {
            color: #777;
            margin-bottom: 15px;
        }

        .profile-info-item {
            margin-bottom: 10px;
        }

        .profile-info-item strong {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }

        /* Estilos para el mapa */
        .map-container {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }

        #map {
            height: 400px;
            width: 100%;
            border-radius: 8px;
            border: 2px solid #e9ecef;
        }

        .address-display {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 12px;
            margin-top: 15px;
            min-height: 50px;
        }

        .address-display h6 {
            margin-bottom: 8px;
            color: #495057;
            font-weight: 600;
        }

        .address-display p {
            margin: 0;
            color: #6c757d;
            font-size: 14px;
        }

        .map-instructions {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 12px 16px;
            margin-bottom: 15px;
            border-radius: 0 6px 6px 0;
        }

        .map-instructions i {
            color: #2196f3;
            margin-right: 8px;
        }

        .coordinate-info {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 10px;
            margin-top: 10px;
            font-size: 12px;
            color: #856404;
        }
    </style>
</head>

<body>
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
                                <!-- RD Navbar Brand--><a class="rd-navbar-brand" href="EN_index.php"><img
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

                    <div class="modal fade" id="PerfilModal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header bg_background text-white">
                                    <h4 class="modal-title" style="color: white;">Profile</h4>
                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="profile-container">
                                        <div class="profile-image">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="12" cy="7" r="4"></circle>
                                            </svg>
                                        </div>
                                        <strong>Username</strong>
                                        <h2 class="profile-name"><?php echo htmlspecialchars($username); ?></h2>

                                        <div class="profile-info">
                                            <div class="profile-info-item">
                                                <strong>Email</strong>
                                                <p><a><?php echo $_SESSION['email']; ?></a></p>
                                            </div>
                                            <div class="profile-info-item">
                                                <strong>Orders</strong>
                                                <p><a href="EN_pedidosusuario.php">View Orders</a></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="rd-navbar-main-outer custom">
                        <div class="rd-navbar-main">
                            <div class="rd-navbar-nav-wrap" id="rd-navbar-nav-wrap-1">
                                <!-- RD Navbar Nav-->
                                <ul class="rd-navbar-nav">
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="EN_index.php">Home</a></li>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="EN_properties.php">Properties</a></li>
                                    <li class="rd-nav-item active"><a class="rd-nav-link" href="EN_furniture.php">Furniture</a></li>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="EN_about-us.php">About Us</a></li>
                                    <?php if (!isset($_SESSION['email'])): ?>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-toggle="modal" data-target="#loginModal">Log In</a></li>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-toggle="modal" data-target="#RegisterModal">Sign Up</a></li>
                                    <?php else: ?>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="EN_logout.p.php">Log Out</a></li>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-toggle="modal" data-target="#PerfilModal">Current Session: <?php echo htmlspecialchars($username); ?></a></li>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="EN_mostrarcarrito.php"><i class="fa fa-shopping-cart" style="font-size: 1.5em;"></i></a></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>
        </header>
        <!-- Contenido del Checkout -->
        <section class="section novi-background section-md text-center">
            <div class="container">
                <h3 class="text-uppercase font-weight-bold wow-outer">
                    <br>
                    <span class="wow slideInDown">Checkout</span>
                </h3>
                <div class="row">
                    <div class="col-lg-8">
                        <div class="checkout-container">
                            <h4 class="mb-4">Shipping Information</h4>
                            <form method="post" action="">
                                <div class="mb-2">
                                    <h>Username</h>
                                    <label class="form-label"></label>
                                    <p><?php echo htmlspecialchars($username); ?></p>
                                </div>
                                <div class="mb-2">
                                    <h>E-mail</h>
                                    <label class="form-label"></label>
                                    <p><?php echo htmlspecialchars($_SESSION['email']); ?></p>
                                </div>
                                <div class="mb-3">
                                    <label for="ubicacion_guardada">Select a saved location:</label>
                                    <select id="ubicacion_guardada" name="ubicacion_guardada" class="form-control">
                                        <option value="">-- New location --</option>
                                        <?php foreach ($ubicaciones as $ubicacion): ?>
                                            <option value='<?php echo json_encode($ubicacion); ?>'>
                                                <?php echo htmlspecialchars($ubicacion['direccion_envio']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <!-- Sección del Mapa -->
                                <div class="map-container">
                                    <h5 class="mb-3">Select your shipping address</h5>
                                    <div class="map-instructions">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>Instructions:</strong> Click on the map to select your shipping address. The marker will be placed at the selected location.
                                    </div>
                                    <div id="map"></div>
                                    <div class="address-display">
                                        <h6>Selected address:</h6>
                                        <p id="selected-address">Click on the map to select an address</p>
                                    </div>
                                    <div class="coordinate-info">
                                        <strong>Coordinates:</strong> <span id="coordinates">Not selected</span>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <h>Phone number *required field*</h>
                                    <label class="form-label"></label>
                                    <input type="text" class="form-control" id="telefono" name="telefono" maxlength="9" placeholder="XXXX-XXXX" required>
                                </div>
                                <!-- Campos ocultos para coordenadas y dirección -->
                                <input type="hidden" id="direccion" name="direccion" required>
                                <input type="hidden" id="latitud" name="latitud">
                                <input type="hidden" id="longitud" name="longitud">
                                <input type="hidden" name="metodo_pago" value="paypal">
                                <!-- Agregar SDK de PayPal -->
                                <script src="https://www.paypal.com/sdk/js?client-id=AdYdJDj0e0M467JdootgS5YO3GOZrS3_H-BFEJcau4KTp1uRJb3JSRyVMD13ThlMy5ojBz-__S5hVTb9&currency=USD"></script>
                                <!-- Contenedor para botones de PayPal -->
                                <div id="paypal-button-container" class="mb-4"></div>
                                <script>
                                    
                                    let map;
                                    let marker;
                                    let selectedAddress = '';
                                    let selectedLat = null;
                                    let selectedLng = null;

                                    
                                    const EL_SALVADOR_BOUNDS = {
                                        north: 14.445,
                                        south: 13.148,
                                        east: -87.692,
                                        west: -90.128
                                    };

                                    
                                    const direccionGuardada = <?php echo json_encode($direccion_guardada); ?>;
                                    const latGuardada = <?php echo json_encode($lat_guardada); ?>;
                                    const lngGuardada = <?php echo json_encode($lng_guardada); ?>;
                                    const savedLocations = <?php echo json_encode($ubicaciones); ?>;

                                    
                                    function isInElSalvador(lat, lng) {
                                        return lat >= EL_SALVADOR_BOUNDS.south &&
                                            lat <= EL_SALVADOR_BOUNDS.north &&
                                            lng >= EL_SALVADOR_BOUNDS.west &&
                                            lng <= EL_SALVADOR_BOUNDS.east;
                                    }

                                    
                                    function validateLocationInElSalvador(lat, lng) {
                                        const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=10&addressdetails=1`;

                                        return fetch(url)
                                            .then(response => response.json())
                                            .then(data => {
                                                if (data && data.address) {
                                                    const country = data.address.country;
                                                    const countryCode = data.address.country_code;

                                                    
                                                    if (countryCode === 'sv' || country === 'El Salvador') {
                                                        return {
                                                            valid: true,
                                                            address: data.display_name
                                                        };
                                                    } else {
                                                        return {
                                                            valid: false,
                                                            message: `This location is in ${country}. Only addresses in El Salvador are permitted.`
                                                        };
                                                    }
                                                } else {
                                                    return {
                                                        valid: false,
                                                        message: "The location could not be verified. Please select a more specific point."
                                                    };
                                                }
                                            })
                                            .catch(error => {
                                                console.error('Error validating location:', error);
                                                return {
                                                    valid: false,
                                                    message: "Error validating location. Please try again."
                                                };
                                            });
                                    }

                                    
                                    function initMap() {
                                        
                                        let initialLat = 13.7942;
                                        let initialLng = -88.8965;
                                        let initialZoom = 8;

                                        
                                        if (latGuardada && lngGuardada) {
                                            initialLat = parseFloat(latGuardada);
                                            initialLng = parseFloat(lngGuardada);
                                            initialZoom = 15;
                                        }

                                        map = L.map('map', {
                                            minZoom: 8,
                                            maxZoom: 18,
                                            maxBounds: [
                                                [EL_SALVADOR_BOUNDS.south - 0.1, EL_SALVADOR_BOUNDS.west - 0.1],
                                                [EL_SALVADOR_BOUNDS.north + 0.1, EL_SALVADOR_BOUNDS.east + 0.1]
                                            ],
                                            maxBoundsViscosity: 1.0
                                        }).setView([initialLat, initialLng], initialZoom);

                                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                            attribution: '© OpenStreetMap contributors',
                                            maxZoom: 18
                                        }).addTo(map);

                                        
                                        if (latGuardada && lngGuardada) {
                                            setMarker(initialLat, initialLng);
                                            document.getElementById('selected-address').textContent = direccionGuardada || `Ubicación: ${initialLat.toFixed(6)}, ${initialLng.toFixed(6)}`;
                                            document.getElementById('direccion').value = direccionGuardada || '';
                                            document.getElementById('latitud').value = initialLat;
                                            document.getElementById('longitud').value = initialLng;
                                            document.getElementById('coordinates').textContent = `${initialLat.toFixed(6)}, ${initialLng.toFixed(6)}`;

                                            
                                            const dropdown = document.getElementById('ubicacion_guardada');
                                            let foundInDropdown = false;
                                            for (let i = 0; i < dropdown.options.length; i++) {
                                                const optionValue = dropdown.options[i].value;
                                                if (optionValue) {
                                                    const loc = JSON.parse(optionValue);
                                                    if (parseFloat(loc.latitud) === initialLat && parseFloat(loc.longitud) === initialLng) {
                                                        dropdown.value = optionValue;
                                                        foundInDropdown = true;
                                                        break;
                                                    }
                                                }
                                            }
                                            if (!foundInDropdown) {
                                                dropdown.value = '';
                                            }
                                        }

                                        
                                        map.on('click', function(e) {
                                            const lat = e.latlng.lat;
                                            const lng = e.latlng.lng;

                                            
                                            if (!isInElSalvador(lat, lng)) {
                                                swal({
                                                    title: "Location out of range",
                                                    text: "The selected location is outside the borders of El Salvador.",
                                                    icon: "warning",
                                                    button: "Understood.",
                                                });
                                                return;
                                            }

                                            
                                            validateLocationInElSalvador(lat, lng).then(result => {
                                                if (result.valid) {
                                                    setMarker(lat, lng);
                                                    document.getElementById('selected-address').textContent = result.address;
                                                    document.getElementById('direccion').value = result.address;
                                                    document.getElementById('ubicacion_guardada').value = '';
                                                } else {
                                                    swal({
                                                        title: "Invalid location",
                                                        text: result.message,
                                                        icon: "warning",
                                                        button: "Understood.",
                                                    });
                                                }
                                            });
                                        });
                                    }

                                    
                                    function setMarker(lat, lng) {
                                        if (marker) {
                                            map.removeLayer(marker);
                                        }
                                        marker = L.marker([lat, lng]).addTo(map);
                                        selectedLat = lat;
                                        selectedLng = lng;

                                        
                                        document.getElementById('latitud').value = lat;
                                        document.getElementById('longitud').value = lng;
                                        document.getElementById('coordinates').textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                                    }

                                    
                                    function getAddressFromCoordinates(lat, lng) {
                                        const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=16&addressdetails=1`;
                                        fetch(url)
                                            .then(response => response.json())
                                            .then(data => {
                                                if (data && data.display_name) {
                                                    selectedAddress = data.display_name;
                                                    document.getElementById('selected-address').textContent = selectedAddress;
                                                    document.getElementById('direccion').value = selectedAddress;
                                                } else {
                                                    selectedAddress = `Location: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                                                    document.getElementById('selected-address').textContent = selectedAddress;
                                                    document.getElementById('direccion').value = selectedAddress;
                                                }
                                            })
                                            .catch(error => {
                                                console.error('Error obtaining address:', error);
                                                selectedAddress = `Location: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                                                document.getElementById('selected-address').textContent = selectedAddress;
                                                document.getElementById('direccion').value = selectedAddress;
                                            });
                                    }

                                    
                                    document.getElementById('ubicacion_guardada').addEventListener('change', function() {
                                        const val = this.value;
                                        if (!val) {
                                            
                                            if (marker) {
                                                map.removeLayer(marker);
                                                marker = null;
                                            }
                                            document.getElementById('selected-address').textContent = 'Click on the map to select an address';
                                            document.getElementById('direccion').value = '';
                                            document.getElementById('latitud').value = '';
                                            document.getElementById('longitud').value = '';
                                            document.getElementById('coordinates').textContent = 'Not selected';
                                            return;
                                        }
                                        const ubicacion = JSON.parse(val);
                                        const lat = parseFloat(ubicacion.latitud);
                                        const lng = parseFloat(ubicacion.longitud);
                                        const direccion = ubicacion.direccion_envio;

                                        setMarker(lat, lng);
                                        map.setView([lat, lng], 15);
                                        document.getElementById('selected-address').textContent = direccion;
                                        document.getElementById('direccion').value = direccion;
                                        document.getElementById('latitud').value = lat;
                                        document.getElementById('longitud').value = lng;
                                        document.getElementById('coordinates').textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                                    });

                                    
                                    document.addEventListener('DOMContentLoaded', function() {
                                        initMap();

                                        
                                        document.getElementById('telefono').addEventListener('input', function(e) {
                                            let valor = e.target.value.replace(/\D/g, '');
                                            if (valor.length > 4) {
                                                valor = valor.substring(0, 4) + '-' + valor.substring(4, 8);
                                            }
                                            e.target.value = valor;
                                        });

                                        
                                        paypal.Buttons({
                                            
                                            createOrder: function(data, actions) {
                                                const direccion = document.getElementById('direccion').value;
                                                const telefono = document.getElementById('telefono').value;
                                                const latitud = document.getElementById('latitud').value;
                                                const longitud = document.getElementById('longitud').value;

                                                if (!direccion || !telefono || !latitud || !longitud) {
                                                    swal({
                                                        title: "Error",
                                                        text: "Please fill in all fields and select a location on the map.",
                                                        icon: "warning",
                                                        button: "Close",
                                                    });
                                                    return Promise.reject(new Error('Incomplete fields'));
                                                }

                                                const total = <?php echo $total; ?>;
                                                return actions.order.create({
                                                    purchase_units: [{
                                                        description: 'Shop at HomeSafe',
                                                        amount: {
                                                            currency_code: 'USD',
                                                            value: total.toString()
                                                        }
                                                    }]
                                                });
                                            }, 
                                            onApprove: function(data, actions) {
                                                return actions.order.capture().then(function(orderData) {
                                                    const transaction = orderData.purchase_units[0].payments.captures[0];

                                                    
                                                    const formData = new FormData(document.querySelector('form'));
                                                    formData.append('paypal_transaction_id', transaction.id);
                                                    formData.append('pago_paypal', 'completado');

                                                    
                                                    fetch('', {
                                                            method: 'POST',
                                                            body: formData
                                                        })
                                                        .then(response => response.text())
                                                        .then(html => {
                                                            swal({
                                                                title: "Success",
                                                                text: "Purchase completed successfully",
                                                                icon: "success",
                                                                button: "Close",
                                                            }).then(() => {
                                                                window.location = "EN_mostrarcarrito.php";
                                                            });
                                                        })
                                                        .catch(error => {
                                                            console.error('Purchase error:', error);
                                                            swal({
                                                                title: "Error",
                                                                text: "There was a problem processing your purchase.",
                                                                icon: "error",
                                                                button: "Close",
                                                            });
                                                        });
                                                });
                                            },
                                            onError: function(err) {
                                                console.error('PayPal payment error:', err);
                                                swal({
                                                    title: "Payment error or fill in the details",
                                                    text: "There was a problem processing your payment with PayPal. Please try again.",
                                                    icon: "error",
                                                    button: "Close",
                                                });
                                            }
                                        }).render('#paypal-button-container');
                                    });
                                </script>

                            </form>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="order-summary">
                            <h4 class="mb-3">Order Summary</h4>
                            <?php if (count($productos) > 0): ?>
                                <div class="product-list mb-4">
                                    <?php foreach ($productos as $producto): ?>
                                        <div class="product-item d-flex align-items-center py-2">
                                            <img src="data:image/jpeg;base64,<?php echo base64_encode(pg_unescape_bytea($producto['imagen'])); ?>" alt="<?php echo htmlspecialchars(tr($producto['nombre'])); ?>" class="product-img me-3">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0"><?php echo htmlspecialchars(tr($producto['nombre'])); ?></h6>
                                                <small class="text-muted"><?php echo $producto['cantidad']; ?> x $<?php echo number_format($producto['precio_unitario'], 2); ?></small>
                                            </div>
                                            <div class="text-end">
                                                <span class="fw-bold">$<?php echo number_format($producto['subtotal'], 2); ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <span>$<?php echo number_format($total, 2); ?></span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between mb-0">
                                    <strong>Total:</strong>
                                    <strong class="fs-5">$<?php echo number_format($total, 2); ?></strong>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    There are no products in your cart.
                                    <a href="EN_furniture.php" class="alert-link">Go to shopping</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Footer -->
        <footer class="section novi-background footer-advanced bg-gray-700">
            <div class="footer-advanced-main">
                <div class="container">
                    <div class="row row-50">
                        <div class="col-lg-4">
                            <h5 class="font-weight-bold text-uppercase text-white">About Us</h5>
                            <p class="footer-advanced-text">HomeSafe is an online store where you can buy
                                real estate and furniture. This website has a user-friendly and intuitive interface.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-advanced-aside">
                <div class="container">
                    <div class="footer-advanced-layout">
                        <div>
                            <ul class="list-nav">
                                <li><a href="EN_index.php">Home</a></li>
                                <li><a href="EN_about-us.php">About Us</a></li>
                                <li><a href="EN_properties.php">Properties</a></li>
                                <li><a href="EN_furniture.php">Furniture</a></li>
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
                    <div class="footer-advanced-layout"><a class="brand" href="EN_index.php"><img
                                src="images/logo-light-115x34.png" alt="" width="115" height="34"
                                srcset="images/logo-light-115x34.png 2x" /></a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    <!-- Global Mailform Output-->
    <div class="snackbars" id="form-output-global"></div>
    <!-- Javascript-->
    <script src="js/core.min.js"></script>
    <script src="js/script.js"></script>
</body>

</html>
<?php

pg_close($conn);
?>