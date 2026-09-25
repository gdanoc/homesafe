<?php
include('includes/CookiesSessionVEn.php');
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

$query = "SELECT * FROM sales_seller WHERE comprador = $1";
$result_sales = pg_query_params($conn, $query, array($_SESSION['email']));
$row_sales = pg_fetch_assoc($result_sales);


$query = "
    SELECT p.*, s.id_propiedad
    FROM sales_seller s
    JOIN propiedades p ON p.id = s.id_propiedad
    WHERE s.comprador = $1
";
$result = pg_query_params($conn, $query, array($_SESSION['email']));

$propertys = [];
while ($row = pg_fetch_assoc($result)) {
    $propertys[] = $row;
    $id_casa = $row["id"] ?? null;
}

$imagenCasa = $row_sales['id_propiedad'];

$query2 = "SELECT * FROM propiedades WHERE id = $1";
$result2 = pg_query_params($conn, $query2, array($id_casa));
$row2 = pg_fetch_assoc($result2);
$nombre_casa = $row2['nombre'] ?? null;

foreach ($propertys as &$prop) {
    $img_query = "SELECT imagen FROM carrusel_propiedad WHERE propiedad_id = $1 ORDER BY id LIMIT 1";
    $img_result = pg_query_params($conn, $img_query, array($prop['id']));
    $rowI = pg_fetch_assoc($img_result);
    $prop['imagen_base64'] = $rowI ? base64_encode(pg_unescape_bytea($rowI['imagen'])) : null;
}
?>
<!DOCTYPE html>
<html class="wide wow-animation" lang="en">

<head>
    <title>My Properties - HomeSafe</title>
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
    <style>
        .product-image-container {
            margin-right: 15px;
        }

        .product-image {
            width: 165px;
            height: 130px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #dee2e6;
        }

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

        /* ===== CONTENEDOR PRINCIPAL DE CHATS ===== */
        .chats-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px 0;
        }

        /* ===== ITEMS DE CHAT ===== */
        .chat-accordion-item {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
            animation: fadeInUp 0.5s ease forwards;
        }

        .chat-accordion-item:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .chat-accordion-item:nth-child(2) {
            animation-delay: 0.1s;
        }

        .chat-accordion-item:nth-child(3) {
            animation-delay: 0.2s;
        }

        .chat-accordion-item:nth-child(4) {
            animation-delay: 0.3s;
        }

        /* ===== HEADER DEL CHAT ===== */
        .chat-header {
            padding: 20px;
            cursor: pointer;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 1px solid #dee2e6;
            transition: background 0.3s ease;
        }

        .chat-header:hover {
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
        }

        .chat-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-title h5 {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .property-name {
            color: #6c757d;
            font-size: 14px;
            font-weight: 500;
        }

        .chat-status-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /* ===== BADGES DE ESTADO ===== */
        .status-badge {
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
        }

        .status-accepted {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border: 2px solid #28a745;
        }

        .status-pending {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            color: #856404;
            border: 2px solid #ffc107;
        }

        .status-canceled {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            border: 2px solid #dc3545;
            animation: pulse-red 2s infinite;
        }

        /* ===== FLECHA DE EXPANSIÓN ===== */
        .expand-arrow {
            font-size: 16px;
            color: #6c757d;
            transition: transform 0.3s ease;
        }

        .chat-header[aria-expanded="true"] .expand-arrow {
            transform: rotate(180deg);
        }

        /* ===== CONTENIDO EXPANDIBLE ===== */
        .chat-details {
            border-top: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }

        .collapsing {
            transition: height 0.3s ease;
        }

        .chat-body {
            padding: 24px;
            background: #fafbfc;
        }

        /* ===== GRID DE DETALLES ===== */
        .details-grid {
            display: grid;
            gap: 20px;
            margin-bottom: 24px;
        }

        .detail-item {
            background: white;
            padding: 16px;
            border-radius: 8px;
            border-left: 4px solid #007bff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .detail-item-canceled {
            border-left: 4px solid #dc3545 !important;
            background: #fff5f5 !important;
        }

        .detail-item-canceled .detail-label {
            color: #721c24;
        }

        .detail-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .detail-value {
            color: #2c3e50;
            font-size: 15px;
            word-break: break-word;
        }

        /* ===== TEXTO DE ESTADOS ===== */
        .text-success {
            color: #28a745;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .text-warning {
            color: #ffc107;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .text-canceled {
            color: #dc3545;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 15px;
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            padding: 8px 12px;
            border-radius: 20px;
            border: 1px solid #dc3545;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            animation: shake 0.5s ease-in-out;
        }

        /* ===== ACCIONES DEL CHAT ===== */
        .chat-actions {
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 8px;
            border: 2px dashed #dee2e6;
        }

        .btn-action {
            padding: 12px 24px;
            font-weight: 600;
            border-radius: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            min-width: 200px;
        }

        /* ===== ACCIONES PARA CHAT CANCELADO ===== */
        .canceled-actions {
            text-align: center;
            padding: 30px 20px;
            background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
            border-radius: 12px;
            border: 2px solid #dc3545;
            margin: 20px 0;
        }

        .canceled-icon {
            opacity: 0.7;
        }

        .canceled-actions h6 {
            font-weight: 700;
            font-size: 18px;
        }

        .canceled-actions p {
            font-size: 14px;
            line-height: 1.6;
            max-width: 400px;
            margin: 0 auto 20px;
        }

        .alternative-actions {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #f5c6cb;
            margin-top: 15px;
        }

        .alternative-actions .btn {
            font-size: 12px;
            padding: 6px 12px;
        }

        /* ===== ESTADO VACÍO ===== */
        .empty-chats {
            padding: 80px 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 16px;
            margin: 40px 0;
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

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-2px);
            }

            75% {
                transform: translateX(2px);
            }
        }

        @keyframes pulse-red {
            0% {
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
            }
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .chat-summary {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .chat-status-container {
                align-self: flex-end;
            }

            .details-grid {
                gap: 15px;
            }

            .detail-item {
                padding: 12px;
            }

            .btn-action {
                min-width: auto;
                width: 100%;
            }

            .alternative-actions .d-flex {
                flex-direction: column;
            }

            .alternative-actions .btn {
                width: 100%;
                margin-bottom: 8px;
            }
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
                                <!-- RD Navbar Brand-->
                                <a class="rd-navbar-brand" href="EN_index.php"><img
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
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="EN_index.php">Home</a>
                                    </li>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="EN_properties.php">Properties</a>
                                    </li>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="EN_furniture.php">Furniture</a>
                                    </li>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="EN_about-us.php">About Us
                                        </a>
                                    </li>
                                    <?php if (!isset($_SESSION['email'])): ?>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-toggle="modal" data-target="#loginModal">Log In</a></li>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-toggle="modal" data-target="#RegisterModal">Sign Up</a></li>
                                    <?php else: ?>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="EN_logout.p.php">Log Out</a></li>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-toggle="modal" data-target="#PerfilModal">Current Session: <?php echo htmlspecialchars($username); ?></a></li>
                                        <!-- Ícono del carrito de compras -->
                                        <li class="rd-nav-item">
                                            <a class="rd-nav-link" href="EN_mostrarcarrito.php">
                                                <i class="fa fa-shopping-cart" style="font-size: 1.5em;"></i>
                                            </a>
                                        </li>
                                        </li>

                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php include 'EN_modals.php'; ?>

                </nav>
            </div>
        </header>

        <!-- Contenido de Mis Porpiedades -->
        <section class="section novi-background section-md text-center">
            <div class="container">
                <h3 class="text-uppercase font-weight-bold wow-outer">
                    <br>
                    <span class="wow slideInDown">My Properties</span>
                </h3>
                <?php if (count($propertys) > 0): ?>
                    <div class="chats-container">
                        <?php foreach ($propertys as $index => $property): ?>
                            <div class="chat-accordion-item" data-chat-id="<?php echo $property['id']; ?>">
                                <div class="chat-header" data-bs-toggle="collapse" data-bs-target="#chatCollapse<?php echo $index; ?>" aria-expanded="false" aria-controls="chatCollapse<?php echo $index; ?>">
                                    <div class="chat-summary">
                                        <div class="chat-title">
                                            <h5 class="mb-1">
                                                <i class="fas fa-user-circle me-2"></i>
                                                Property of: <?php echo htmlspecialchars($property['mail_user']); ?>
                                            </h5>
                                            <p class="property-name mb-1">
                                                <i class="fas fa-home me-1"></i>
                                                <?php echo htmlspecialchars($property['nombre']); ?>
                                            </p>
                                        </div>
                                        <div class="chat-status-container">
                                            <h5 class="mb-1">
                                                <?php if ($property['imagen_base64']): ?>
                                                    <img src="data:image/jpeg;base64,<?php echo $property['imagen_base64']; ?>" class="product-image">
                                                <?php endif; ?>
                                                <br>
                                                <text><?php echo '$' . $property['precio']; ?></text>
                                            </h5>
                                            <i class="fas fa-chevron-down expand-arrow"></i>
                                        </div>
                                    </div>
                                </div>
                                <div id="chatCollapse<?php echo $index; ?>" class="collapse chat-details">
                                    <div class="chat-body">
                                        <div class="details-grid">
                                            <div class="detail-item">
                                                <div class="detail-label">
                                                    <i class="fas fa-envelope me-2"></i>
                                                    Your Email:
                                                </div>
                                                <div class="detail-value">
                                                    <?php echo htmlspecialchars($_SESSION['email']); ?>
                                                </div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">
                                                    <i class="fas fa-envelope me-2"></i>
                                                    Seller:
                                                </div>
                                                <div class="detail-value">
                                                    <?php echo htmlspecialchars($property['mail_user']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-chats">
                        <i class="fas fa-comments fa-4x text-muted mb-4"></i>
                        <h4>Your dont have properties</h4>
                        <p class="text-muted mb-4">When you confirm your purchase with a seller, it will appear here.</p>
                        <a href="properties.php" class="btn btn-primary btn-lg">
                            Explore properties
                        </a>
                    </div>
                <?php endif; ?>

            </div>
        </section>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                
                const chatHeaders = document.querySelectorAll('.chat-header');

                chatHeaders.forEach(header => {
                    header.addEventListener('click', function() {
                        const arrow = this.querySelector('.expand-arrow');
                        const isExpanded = this.getAttribute('aria-expanded') === 'true';

                        
                        if (isExpanded) {
                            arrow.style.transform = 'rotate(0deg)';
                        } else {
                            arrow.style.transform = 'rotate(180deg)';
                        }
                    });
                });

                
                const chatItems = document.querySelectorAll('.chat-accordion-item');

                chatItems.forEach(item => {
                    item.addEventListener('mouseenter', function() {
                        this.style.transform = 'translateY(-3px)';
                    });

                    item.addEventListener('mouseleave', function() {
                        this.style.transform = 'translateY(0)';
                    });
                });
            });
        </script>

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
    <!-- Bootstrap 5 JS para el funcionamiento de los componentes -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p"
        crossorigin="anonymous"></script>
</body>

</html>
<?php

pg_close($conn);
?>