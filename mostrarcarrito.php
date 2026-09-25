<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">

<?php
include('includes/CookiesSessionV.php');
?>
<?php
$stock_query = "SELECT id, cantidad FROM muebles WHERE id = $1";


$host = "localhost";
$user = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";
$dbname = "homesafe";

$conn = pg_connect("host=$host dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Conexión fallida: " . pg_last_error());
}


if (!isset($_SESSION['user_id'])) {
    $email = $_SESSION['email'];
    $query = "SELECT id FROM accounts WHERE email = $1";
    $result = pg_query_params($conn, $query, array($email));
    if (pg_num_rows($result) > 0) {
        $row = pg_fetch_assoc($result);
        $_SESSION['user_id'] = $row['id'];
    } else {
        echo '<script>alert("Error: No se pudo encontrar tu cuenta."); window.location = "index.php";</script>';
        exit();
    }
}

$usuario_id = $_SESSION['user_id'];


$cleanup_query = "DELETE FROM carrito WHERE usuario_id = $1 AND fecha_agregado < NOW() - INTERVAL '24 hours'";
pg_query_params($conn, $cleanup_query, array($usuario_id));


$query = "SELECT c.id, c.producto_id, c.cantidad, c.precio_unitario, c.fecha_agregado, m.nombre, m.imagen
          FROM carrito c
          JOIN muebles m ON c.producto_id = m.id
          WHERE c.usuario_id = $1
          ORDER BY c.fecha_agregado DESC";
$result = pg_query_params($conn, $query, array($usuario_id));


$total = 0;
$productos = [];
while ($row = pg_fetch_assoc($result)) {
    $subtotal = $row['precio_unitario'] * $row['cantidad'];
    $total += $subtotal;
    $row['subtotal'] = $subtotal;
    $productos[] = $row;
}
?>

<!DOCTYPE html>
<html class="wide wow-animation" lang="en">

<head>
    <title>Carrito de Compras - HomeSafe</title>
    <meta name="format-detection" content="telephone=no">
    <meta name="viewport" content="width=device-width height=device-height initial-scale=1.0 maximum-scale=1.0 user-scalable=0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta charset="utf-8">
    <link rel="icon" href="images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Work+Sans:300,400,500,700,800%7CPoppins:300,400,700">
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/fonts.css">
    <link rel="stylesheet" href="css/style.css" id="main-styles-link">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

        .cart-item {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            border-left: 4px solid #007bff;
        }

        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
        }

        .cart-summary {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .btn-checkout {
            width: 100%;
            margin-top: 10px;
        }

        .empty-cart {
            text-align: center;
            padding: 50px 0;
        }

        .empty-cart i {
            font-size: 5rem;
            color: #ccc;
            margin-bottom: 20px;
        }

        /* Estilos del contador de expiración mejorados */
        .expiration-timer {
            background: linear-gradient(135deg, #495057 0%, #343a40 100%);
            color: white;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 8px rgba(52, 58, 64, 0.3);
            margin-bottom: 8px;
            animation: pulse-glow 2s infinite;
            border: 1px solid #6c757d;
        }

        .expiration-timer.warning {
            background: linear-gradient(135deg, #fd7e14 0%, #e55d00 100%);
            border: 1px solid #fd7e14;
            animation: pulse-warning 1s infinite;
        }

        .expiration-timer.critical {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            border: 1px solid #dc3545;
            animation: pulse-critical 0.5s infinite;
        }

        .expiration-timer i {
            font-size: 1rem;
        }

        @keyframes pulse-glow {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
                box-shadow: 0 2px 8px rgba(52, 58, 64, 0.3);
            }

            50% {
                transform: scale(1.02);
                opacity: 0.9;
                box-shadow: 0 4px 12px rgba(52, 58, 64, 0.4);
            }
        }

        @keyframes pulse-warning {

            0%,
            100% {
                transform: scale(1);
                box-shadow: 0 2px 8px rgba(253, 126, 20, 0.4);
            }

            50% {
                transform: scale(1.03);
                box-shadow: 0 4px 12px rgba(253, 126, 20, 0.6);
            }
        }

        @keyframes pulse-critical {

            0%,
            100% {
                transform: scale(1);
                box-shadow: 0 2px 8px rgba(220, 53, 69, 0.5);
            }

            50% {
                transform: scale(1.05);
                box-shadow: 0 6px 16px rgba(220, 53, 69, 0.8);
            }
        }

        .timer-icon {
            animation: rotate 2s linear infinite;
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        /* Variante alternativa más sutil */
        .expiration-timer.subtle {
            background: #f8f9fa;
            color: #495057;
            border: 2px solid #dee2e6;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .expiration-timer.subtle.warning {
            background: #fff3cd;
            color: #856404;
            border: 2px solid #ffeaa7;
        }

        .expiration-timer.subtle.critical {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #f5c6cb;
        }

        /* Información adicional del carrito con colores mejorados */
        .cart-info-box {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin-top: 15px;
        }

        .cart-info-box .info-icon {
            color: #007bff;
            margin-right: 8px;
        }

        .cart-info-box small {
            color: #495057;
            line-height: 1.4;
        }

        /* Responsive mejorado */
        @media (max-width: 768px) {
            .expiration-timer {
                font-size: 0.75rem;
                padding: 6px 10px;
                border-radius: 15px;
            }

            .expiration-timer i {
                font-size: 0.9rem;
            }
        }

        /* Estados adicionales para mejor UX */
        .expiration-timer.expired {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            opacity: 0.7;
            animation: none;
        }

        .expiration-timer.paused {
            animation-play-state: paused;
            opacity: 0.8;
        }

        /* Estilos de controles de cantidad */
        .form-group {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .quantity-btn {
            background-color: #000;
            color: #fff;
            border: none;
            border-radius: 6px;
            width: 32px;
            height: 32px;
            font-size: 16px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            flex-shrink: 0;
        }

        .quantity-btn:hover {
            background-color: #333;
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .quantity-btn:active {
            transform: scale(0.95);
        }

        .quantity-input {
            width: 45px;
            height: 32px;
            font-size: 13px;
            font-weight: 500;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #f9f9f9;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
            flex-shrink: 0;
            text-align: center;
            line-height: 30px;
            vertical-align: middle;
            box-sizing: border-box;
            padding: 0 !important;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .quantity-input::-webkit-outer-spin-button,
        .quantity-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .quantity-input[type=number] {
            -moz-appearance: textfield;
        }

        .quantity-input:focus {
            outline: none;
            border-color: #000;
            box-shadow: 0 0 3px rgba(0, 0, 0, 0.2);
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 5px;
            justify-content: center;
            min-width: 125px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .cart-item {
                padding: 10px;
            }

            .quantity-controls {
                min-width: 110px;
            }

            .quantity-btn {
                width: 28px;
                height: 28px;
                font-size: 14px;
            }

            .quantity-input {
                width: 40px;
                height: 28px;
                font-size: 12px;
                line-height: 26px;
            }

            .expiration-timer {
                font-size: 0.75rem;
                padding: 6px 10px;
            }
        }

        .price-update {
            animation: priceChange 0.3s ease-in-out;
        }

        @keyframes priceChange {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
                color: #28a745;
            }

            100% {
                transform: scale(1);
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
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="index.php">Inicio</a></li>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="properties.php">Propiedades</a></li>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="furniture.php">Muebles</a></li>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="about-us.php">Sobre nosotros</a></li>
                                    <?php if (!isset($_SESSION['email'])): ?>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-toggle="modal" data-target="#loginModal">Iniciar Sesión</a></li>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-toggle="modal" data-target="#RegisterModal">Registrarse</a></li>
                                    <?php else: ?>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="logout.p.php">Cerrar Sesión</a></li>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-toggle="modal" data-target="#PerfilModal"> Sesión actual: <?php echo htmlspecialchars($username); ?></a></li>
                                        <li class="rd-nav-item">
                                            <a class="rd-nav-link" href="mostrarcarrito.php">
                                                <i class="fa fa-shopping-cart" style="font-size: 1.5em;"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>
        </header>

        <!-- Contenido del Carrito -->
        <section class="section novi-background section-md text-center">
            <div class="container">
                <h3 class="text-uppercase font-weight-bold wow-outer">
                    <br>
                    <span class="wow slideInDown">Mi carrito de compra</span>
                </h3>
                <div class="row row-lg-50 row-35 offset-top-2">
                    <?php if (count($productos) > 0): ?>
                        <form method="post" action="updatecarrito.php">
                            <div class="row">
                                <div class="col-lg-8">
                                    <?php foreach ($productos as $producto): ?>
                                        <?php
                                        
                                        $stock_result = pg_query_params($conn, $stock_query, array($producto['producto_id']));
                                        $stock_row = pg_fetch_assoc($stock_result);
                                        $stock_disponible = $stock_row ? (int)$stock_row['cantidad'] : 0;

                                        
                                        $fecha_agregado = $producto['fecha_agregado'];
                                        $timestamp_agregado = strtotime($fecha_agregado);
                                        $timestamp_expiracion = $timestamp_agregado + (24 * 60 * 60); 
                                        $tiempo_restante = $timestamp_expiracion - time();
                                        ?>
                                        <div class="cart-item row align-items-center">
                                            <div class="col-md-2">
                                                <img src="data:image/jpeg;base64,<?php echo base64_encode(pg_unescape_bytea($producto['imagen'])); ?>"
                                                    alt="<?php echo htmlspecialchars($producto['nombre']); ?>" class="img-fluid">
                                            </div>
                                            <div class="col-md-4">
                                                <h5><?php echo htmlspecialchars($producto['nombre']); ?></h5>
                                                <p class="text-muted">Precio unitario: $<?php echo number_format($producto['precio_unitario'], 2); ?></p>

                                                <!-- Contador de expiración -->
                                                <div class="expiration-timer" id="timer-<?php echo $producto['producto_id']; ?>"
                                                    data-expiration="<?php echo $timestamp_expiracion; ?>">
                                                    <i class="fas fa-clock timer-icon"></i>
                                                    <span class="timer-text">Calculando...</span>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="quantity-controls">
                                                    <button type="button" class="quantity-btn" onclick="decreaseQuantity('qty-<?php echo $producto['producto_id']; ?>')">-</button>
                                                    <input type="number"
                                                        id="qty-<?php echo $producto['producto_id']; ?>"
                                                        name="cantidad[<?php echo $producto['producto_id']; ?>]"
                                                        value="<?php echo $producto['cantidad']; ?>"
                                                        min="1"
                                                        max="<?php echo $stock_disponible; ?>"
                                                        class="quantity-input"
                                                        readonly
                                                        data-precio="<?php echo $producto['precio_unitario']; ?>">
                                                    <button type="button" class="quantity-btn" onclick="increaseQuantity('qty-<?php echo $producto['producto_id']; ?>', <?php echo $stock_disponible; ?>)">+</button>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <p class="fw-bold subtotal" id="subtotal-<?php echo $producto['producto_id']; ?>">
                                                    $<?php echo number_format($producto['subtotal'], 2); ?>
                                                </p>
                                            </div>
                                            <div class="col-md-2 text-end">
                                                <a href="javascript:void(0);"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="confirmarEliminacion(<?php echo $producto['producto_id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                    <div class="d-flex flex-column flex-sm-row justify-content-between mt-4 gap-3">
                                        <a href="furniture.php" class="btn btn-outline-secondary w-100 w-sm-auto">
                                            <i class="fas fa-arrow-left me-2"></i> Seguir comprando
                                        </a>
                                        <button type="submit" class="btn btn-primary btn-sm w-100 w-sm-auto">
                                            Actualizar carrito
                                        </button>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="cart-summary">
                                        <h4 class="mb-3">Resumen del pedido</h4>
                                        <hr>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Subtotal:</span>
                                            <span id="cart-subtotal">$<?php echo number_format($total, 2); ?></span>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between mb-4">
                                            <strong>Total:</strong>
                                            <strong id="cart-total">$<?php echo number_format($total, 2); ?></strong>
                                        </div>
                                        <a href="compra.php" class="btn btn-success btn-checkout">
                                            <i class="fas fa-lock me-2"></i> Proceder al pago
                                        </a>

                                        <!-- Información sobre expiración -->
                                        <div class="mt-3 p-3" style="background-color: #e3f2fd; border-radius: 8px; border-left: 4px solid #2196f3;">
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle"></i>
                                                <strong>Información:</strong> Los productos se eliminan automáticamente después de 24 horas.
                                                Al agregar nuevos productos, el tiempo se reinicia para todos.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="empty-cart">
                            <i class="fas fa-shopping-cart"></i>
                            <h4>Tu carrito está vacío</h4>
                            <p>Parece que aún no has agregado productos a tu carrito.</p>
                            <a href="furniture.php" class="btn btn-primary mt-3">Ir a comprar</a>
                        </div>
                    <?php endif; ?>
                </div>
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

    <!-- Javascript-->
    <script src="js/core.min.js"></script>
    <script src="js/script.js"></script>
    <script src="js/validacionesES.js"></script>

    <script>
        
        function updateTimers() {
            const timers = document.querySelectorAll('.expiration-timer');

            timers.forEach(timer => {
                const expirationTime = parseInt(timer.dataset.expiration);
                const currentTime = Math.floor(Date.now() / 1000);
                const timeLeft = expirationTime - currentTime;

                if (timeLeft <= 0) {
                    
                    location.reload();
                    return;
                }

                
                const hours = Math.floor(timeLeft / 3600);
                const minutes = Math.floor((timeLeft % 3600) / 60);
                const seconds = timeLeft % 60;

                
                let timeText = '';
                if (hours > 0) {
                    timeText = `${hours}h ${minutes}m ${seconds}s`;
                } else if (minutes > 0) {
                    timeText = `${minutes}m ${seconds}s`;
                } else {
                    timeText = `${seconds}s`;
                }

                
                const textElement = timer.querySelector('.timer-text');
                textElement.textContent = `Expira en: ${timeText}`;

                
                timer.classList.remove('warning', 'critical');

                if (timeLeft <= 300) { 
                    timer.classList.add('critical');
                } else if (timeLeft <= 3600) { 
                    timer.classList.add('warning');
                }
            });
        }

        
        function updateCartPrices() {
            let total = 0;
            let hasValidProducts = false;

            
            document.querySelectorAll('.quantity-input').forEach(function(input) {
                const precioUnitario = parseFloat(input.dataset.precio);
                const cantidad = parseInt(input.value);

                
                if (isNaN(precioUnitario) || isNaN(cantidad) || cantidad <= 0) {
                    console.error('Valores inválidos:', {
                        precio: precioUnitario,
                        cantidad: cantidad,
                        inputId: input.id
                    });
                    return; 
                }

                const productoId = input.id.replace('qty-', '');
                const subtotal = precioUnitario * cantidad;

                
                const subtotalElem = document.getElementById('subtotal-' + productoId);
                if (subtotalElem) {
                    subtotalElem.textContent = '$' + subtotal.toFixed(2);
                    subtotalElem.classList.add('price-update');
                    setTimeout(() => subtotalElem.classList.remove('price-update'), 300);
                }

                total += subtotal;
                hasValidProducts = true;
            });

            
            if (hasValidProducts) {
                
                const totalElem = document.getElementById('cart-total');
                const subtotalElem = document.getElementById('cart-subtotal');

                if (totalElem) {
                    totalElem.textContent = '$' + total.toFixed(2);
                    totalElem.classList.add('price-update');
                    setTimeout(() => totalElem.classList.remove('price-update'), 300);
                }

                if (subtotalElem) {
                    subtotalElem.textContent = '$' + total.toFixed(2);
                    subtotalElem.classList.add('price-update');
                    setTimeout(() => subtotalElem.classList.remove('price-update'), 300);
                }
            } else {
                console.warn('No se encontraron productos válidos para calcular el total');
            }
        }

        
        function increaseQuantity(inputId, max) {
            const input = document.getElementById(inputId);
            if (!input) {
                console.error('No se encontró el input:', inputId);
                return;
            }

            let currentValue = parseInt(input.value);
            if (isNaN(currentValue)) currentValue = 1;

            if (currentValue < max) {
                input.value = currentValue + 1;
                updateCartPrices(); 
            } else {
                Swal.fire({
                    title: 'Stock insuficiente',
                    text: `Solo hay ${max} unidades disponibles`,
                    icon: 'warning',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        }

        function decreaseQuantity(inputId) {
            const input = document.getElementById(inputId);
            if (!input) {
                console.error('No se encontró el input:', inputId);
                return;
            }

            let currentValue = parseInt(input.value);
            if (isNaN(currentValue)) currentValue = 1;

            if (currentValue > 1) {
                input.value = currentValue - 1;
                updateCartPrices(); 
            }
        }

        function confirmarEliminacion(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Una vez eliminado, no podrás recuperar este producto.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'eliminarcarrito.php?id=' + id;
                }
            });
        }

        
        function debugCartData() {
            console.log('=== DEBUG CART DATA ===');
            document.querySelectorAll('.quantity-input').forEach(function(input) {
                console.log({
                    id: input.id,
                    value: input.value,
                    precio: input.dataset.precio,
                    precioFloat: parseFloat(input.dataset.precio),
                    cantidadInt: parseInt(input.value)
                });
            });
            console.log('=====================');
        }

        
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Inicializando carrito...');

            
            let missingPriceData = false;
            document.querySelectorAll('.quantity-input').forEach(function(input) {
                if (!input.dataset.precio) {
                    console.error('Input sin data-precio:', input.id);
                    missingPriceData = true;
                }
            });

            if (missingPriceData) {
                console.error('Algunos inputs no tienen el atributo data-precio');
                debugCartData();
            }

            updateTimers();
            updateCartPrices(); 

            
            setInterval(updateTimers, 1000);

            
            debugCartData();
        });
    </script>



    <?php if (isset($_GET['update'])): ?>
        <script>
            <?php if ($_GET['update'] == 'success'): ?>
                Swal.fire({
                    title: "Éxito",
                    text: "Se actualizó/zaron <?php echo intval($_GET['count']); ?> producto/s en el carrito",
                    icon: "success",
                    button: "Cerrar",
                }).then(() => {
                    if (window.history.replaceState) {
                        window.history.replaceState(null, null, window.location.pathname);
                    }
                });
            <?php elseif ($_GET['update'] == 'error'): ?>
                Swal.fire({
                    title: "Error",
                    text: "<?php echo htmlspecialchars($_GET['msg']); ?>",
                    icon: "warning",
                    button: "Cerrar",
                }).then(() => {
                    if (window.history.replaceState) {
                        window.history.replaceState(null, null, window.location.pathname);
                    }
                });
            <?php endif; ?>
        </script>
    <?php endif; ?>
</body>

</html>

<?php

pg_close($conn);
?>