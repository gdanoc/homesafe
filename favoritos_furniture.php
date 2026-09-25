<?php
include('includes/Cookies_sessions.php');
?>
<!DOCTYPE html>
<html class="wide wow-animation" lang="en">
<head>
    <title>Muebles</title>
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
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js "></script>
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
    
    .form-group {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    .quantity-btn {
        background-color: #000;
        color: #fff;
        border: none;
        border-radius: 50%;
        width: 35px;
        height: 35px;
        font-size: 18px;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .quantity-btn:hover {
        background-color: #333;
        transform: scale(1.1);
        box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2);
    }
    
    .quantity-input {
        width: 60px;
        height: 35px;
        text-align: center;
        font-size: 16px;
        border: 1px solid #ccc;
        border-radius: 5px;
        margin: 0 5px;
        background-color: #f9f9f9;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .quantity-input:focus {
        outline: none;
        border-color: #000;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
    }
    
    .profile-container {
        background-color: white;
        border-radius: 8px;
        padding: 20px;
        width: 100%;
        max-width: 300px;
        text-align: center;
        margin: 0 auto;
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
    
    /* Barra de búsqueda responsive */
    .search-icon {
        position: relative;
    }
    
    .search-bar {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #f8f9fa;
        border: 2px solid #007bff;
        padding: 15px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
        z-index: 1000;
        border-radius: 8px;
        margin-top: 5px;
        min-width: 250px;
    }
    
    .search-results {
        list-style-type: none;
        padding: 0;
        margin: 0;
        max-height: 200px;
        overflow-y: auto;
        background-color: #ffffff;
        border-radius: 5px;
        margin-top: 10px;
    }
    
    .search-results li {
        padding: 12px;
        cursor: pointer;
        border-bottom: 1px solid #e0e0e0;
        font-size: 14px;
        color: #333;
        background-color: #ffffff;
        transition: all 0.3s ease;
        font-weight: 500;
    }
    
    .search-results li:last-child {
        border-bottom: none;
    }
    
    .search-results li:hover {
        background-color: #007bff;
        color: #ffffff;
        transform: translateX(5px);
    }
    
    #searchInput {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }
    
    /* Responsive para la barra de búsqueda */
    @media (max-width: 992px) {
        .search-bar {
            left: -50px;
            right: -50px;
            min-width: 280px;
        }
    }
    
    @media (max-width: 768px) {
        .search-bar {
            left: -100px;
            right: -100px;
            min-width: 300px;
        }
        
        .search-results li {
            padding: 14px;
            font-size: 15px;
        }
        
        #searchInput {
            padding: 10px;
            font-size: 16px;
        }
    }
    
    /* ARREGLO PARA MÓVILES - La barra centrada y más pequeña */
    @media (max-width: 576px) {
        .search-bar {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            min-width: 220px;
            width: 70vw;
            max-width: 280px;
            margin-top: 10px;
            padding: 10px;
        }
        
        .search-results {
            max-height: 120px;
        }
        
        .search-results li {
            padding: 10px;
            font-size: 14px;
            border-bottom: 1px solid #eee;
        }
        
        #searchInput {
            padding: 8px;
            font-size: 14px;
            border-radius: 6px;
        }
        
        /* Asegurar que el contenedor de búsqueda tenga posición relativa */
        .search-icon {
            position: relative;
        }
    }
    
    @media (max-width: 400px) {
        .search-bar {
            width: 65vw;
            max-width: 250px;
            min-width: 200px;
        }
    }
    
    /* Para pantallas muy pequeñas */
    @media (max-width: 350px) {
        .search-bar {
            width: 60vw;
            max-width: 220px;
            min-width: 180px;
        }
    }
    
    /* Responsive para tarjetas de muebles */
    .furniture-card {
        padding: 12px;
        border: 2px solid #444;
        border-radius: 10px;
        text-align: center;
        width: 100%;
        max-width: 280px;
        margin: 0 auto 20px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        position: relative;
        top: 0;
    }
    
    .furniture-card:hover {
        top: -5px;
        box-shadow: 0 8px 16px rgba(0,0,0,0.2);
    }
    
    .furniture-image {
        width: 100%;
        max-width: 180px;
        height: 180px;
        border-radius: 8px;
        object-fit: cover;
        cursor: pointer;
        margin: 0 auto;
        display: block;
    }
    
    /* Media Queries para Responsive */
    
    /* Tablets y pantallas medianas */
    @media (max-width: 992px) {
        .furniture-card {
            max-width: 250px;
        }
        
        .furniture-image {
            max-width: 160px;
            height: 160px;
        }
        
        .profile-container {
            width: 90%;
            max-width: 280px;
        }
    }
    
    /* Tablets pequeñas */
    @media (max-width: 768px) {
        .furniture-card {
            max-width: 220px;
            padding: 10px;
        }
        
        .furniture-image {
            max-width: 140px;
            height: 140px;
        }
        
        .quantity-btn {
            width: 30px;
            height: 30px;
            font-size: 16px;
        }
        
        .quantity-input {
            width: 50px;
            height: 30px;
            font-size: 14px;
        }
        
        .profile-container {
            padding: 15px;
        }
        
        .profile-name {
            font-size: 1.3em;
        }
        
        /* Navbar responsive */
        .rd-navbar-nav {
            flex-wrap: wrap;
        }
    }
    
    /* Móviles */
    @media (max-width: 576px) {
        .container {
            padding-left: 10px;
            padding-right: 10px;
        }
        
        .furniture-card {
            max-width: 200px;
            padding: 8px;
            margin-bottom: 15px;
        }
        
        .furniture-image {
            max-width: 120px;
            height: 120px;
        }
        
        .post-modern-title {
            font-size: 15px !important;
        }
        
        .quantity-btn {
            width: 28px;
            height: 28px;
            font-size: 14px;
        }
        
        .quantity-input {
            width: 45px;
            height: 28px;
            font-size: 12px;
        }
        
        .btn-sm {
            font-size: 12px;
            padding: 5px 10px;
        }
        
        .profile-container {
            width: 95%;
            padding: 10px;
        }
        
        .profile-image {
            width: 80px;
            height: 80px;
        }
        
        .profile-name {
            font-size: 1.2em;
        }
        
        /* Modal responsive */
        .modal-dialog {
            margin: 10px;
        }
        
        .modal-lg {
            max-width: 95%;
        }
        
        /* Grid responsive para muebles */
        .row-35 > [class*="col-"] {
            margin-bottom: 15px;
        }
    }
    
    /* Móviles muy pequeños */
    @media (max-width: 400px) {
        .furniture-card {
            max-width: 180px;
            padding: 6px;
        }
        
        .furniture-image {
            max-width: 100px;
            height: 100px;
        }
        
        .post-modern-title {
            font-size: 14px !important;
        }
        
        .quantity-btn {
            width: 25px;
            height: 25px;
            font-size: 12px;
        }
        
        .quantity-input {
            width: 40px;
            height: 25px;
            font-size: 11px;
        }
        
        .profile-container {
            padding: 8px;
        }
        
        .profile-image {
            width: 70px;
            height: 70px;
        }
    }
    
    /* Mejoras adicionales para la experiencia móvil */
    @media (max-width: 768px) {
        /* Hacer los botones más fáciles de tocar en móviles */
        .btn {
            min-height: 44px;
            padding: 10px 15px;
        }
        
        /* Mejorar el espaciado en móviles */
        .section {
            padding-top: 30px;
            padding-bottom: 30px;
        }
        
        /* Footer responsive */
        .footer-advanced-layout {
            flex-direction: column;
            text-align: center;
        }
        
        .list-nav {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
        }
    }
    
    /* Asegurar que las imágenes no se desborden */
    img {
        max-width: 100%;
        height: auto;
    }
    
    /* Mejorar la legibilidad en pantallas pequeñas */
    @media (max-width: 576px) {
        body {
            font-size: 14px;
        }
        
        h3 {
            font-size: 1.5rem;
        }
        
        h4 {
            font-size: 1.2rem;
        }
        
        p {
            font-size: 13px;
        }
    }
          /* --- Nuevos estilos para el descuento --- */
          .discount-badge-top {
          position: absolute;
          top: 8px;
          left: 8px;
          background-color: #FFC107; /* Yellow color from image */
          color: #333;
          padding: 4px 8px;
          border-radius: 4px;
          font-size: 12px;
          font-weight: bold;
          display: flex;
          align-items: center;
          gap: 4px;
          z-index: 2; /* Ensure it's above the image */
          box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      }

      .discount-badge-top .fa-arrow-down {
          color: #DC3545; /* Red arrow from image */
      }

      .discount-percentage-badge {
          background-color: #FFC107; /* Yellow color from image */
          color: #333;
          padding: 4px 8px;
          border-radius: 4px;
          font-size: 14px;
          font-weight: bold;
          display: inline-block;
          margin-top: 5px;
          margin-bottom: 10px;
          box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      }
      /* --- Fin de nuevos estilos --- */

    
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
                                    <ul class="list-0">
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="rd-navbar-main-outer custom">
                        <div class="rd-navbar-main">
                            <div class="rd-navbar-nav-wrap" id="rd-navbar-nav-wrap-1">
                                <!-- RD Navbar Nav-->
                                <ul class="rd-navbar-nav">
                                    <li class="rd-nav-item "><a class="rd-nav-link" href="index.php">Inicio</a>
                                    </li>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="properties.php">Propiedades</a>
                                    </li>
                                    <li class="rd-nav-item active"><a class="rd-nav-link"
                                            href="furniture.php">Muebles</a>
                                    </li>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="about-us.php">Sobre
                                            nosotros</a>
                                    </li>
                                    <?php if(!isset($_SESSION['email'])): ?>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-toggle="modal"
                                            data-target="#loginModal">Iniciar Sesión</a></li>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-toggle="modal"
                                            data-target="#RegisterModal">Registrarse</a></li>
                                    <?php else: ?>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="logout.p.php">Cerrar Sesión</a>
                                    </li>
                                    <?php endif; ?>
                                    <?php if(isset($_SESSION['email'])): ?>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-toggle="modal" data-target="#PerfilModal"> Sesión actual: <?php echo htmlspecialchars($username); ?></a>
                                        <?php endif; ?>
                                        <li class="rd-nav-item">
                                            <a class="rd-nav-link" href="mostrarcarrito.php">
                                                <i class="fa fa-shopping-cart" style="font-size: 1.5em;"></i>
                                            </a>
                                        </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>
        </header>
<?php
    
    $servername = "localhost";
    $username = "postgres";
    $password = "TU_PASSWORD_DE_BASE_DE_DATOS";
    $dbname = "homesafe";
    $conn = pg_connect("host=$servername dbname=$dbname user=$username password=$password");
    if (!$conn) {
        die("Conexión fallida: " . pg_last_error());
    }
    $email = $_SESSION['email'];

    
    $query = "
    SELECT m.id, m.nombre, m.descripcion, m.precio, m.descuento, m.cantidad, m.imagen
    FROM muebles m
    INNER JOIN favoritos f ON m.id = f.mueble_id
    WHERE f.mail_user = $1
    ";
    $result = pg_query_params($conn, $query, array($email));
    ?>
    <section class="section novi-background section-md text-center">
        <div class="container">
            <h3 class="text-uppercase font-weight-bold wow-outer">
                <br>
                <span class="wow slideInDown">Mis Favoritos</span>
            </h3>
            <div class="row row-lg-50 row-35 offset-top-2">
                <?php
                if (pg_num_rows($result) > 0) {
                    while ($row = pg_fetch_assoc($result)) {
                        $precio_original = (float)$row['precio'];
                        $descuento = isset($row['descuento']) ? (float)$row['descuento'] : 0;
                        if ($descuento > 0) {
                            $precio_con_descuento = $precio_original * (1 - $descuento / 100);
                        } else {
                            $precio_con_descuento = $precio_original;
                        }
                        
                        ?>
<div class="col-md-4 col-lg-3 wow-outer" style="padding-top: 8px; padding-bottom: 20px; margin-bottom: 10px;">
    <article class="post-modern wow fadeIn" style="padding: 12px; border: 2px solid #444; border-radius: 10px; text-align: center; max-width: 280px; margin: 0 auto; transition: all 0.3s ease; box-shadow: 0 4px 8px rgba(0,0,0,0.1); position: relative; top: 0;"
        onmouseover="this.style.top='-5px'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.2)';"
        onmouseout="this.style.top='0'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.1)';">

        <div style="position: relative; display: inline-block;">
            <?php if ($descuento > 0) : ?>
                <div class="discount-badge-top">
                    <i class="fa fa-arrow-down"></i> Producto con descuento
                </div>
            <?php endif; ?>

            <a href="javascript:void(0);" onclick="loadFurnitureDetails(<?php echo $row['id']; ?>); $('#detailsModal').modal('show');" class="post-modern-media">
                <img src="data:image/jpeg;base64,<?php echo base64_encode(pg_unescape_bytea($row['imagen'])); ?>"
                    alt="<?php echo $row['nombre']; ?>"
                    style="width: 180px; height: 180px; border-radius: 8px; object-fit: cover; cursor: pointer;" />
            </a>

            <?php
            $is_favorited = false;
            if (isset($_SESSION['email'])) {
                $conn_fav = pg_connect("host=localhost port=5432 dbname=homesafe user=postgres password=Info2025/*-");
                $fav_stmt = pg_query_params($conn_fav, "SELECT id FROM favoritos WHERE mail_user = $1 AND mueble_id = $2", [$_SESSION['email'], $row['id']]);
                $is_favorited = pg_num_rows($fav_stmt) > 0;
                pg_close($conn_fav);
            }
            ?>
            <?php if (isset($_SESSION['email'])) : ?>
                <button type="button" onclick="event.stopPropagation(); toggleFavorite(<?php echo $row['id']; ?>, this);" class="favorite-btn"
                    style="position: absolute; bottom: 8px; right: 8px; background: rgba(255, 255, 255, 0.95); border: 2px solid rgba(231, 76, 60, 0.3); border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); cursor: pointer; transition: all 0.3s ease; z-index: 3; padding: 0; outline: none;"
                    onmouseover="this.style.background='rgba(255, 255, 255, 1)'; this.style.transform='scale(1.1)'; this.style.boxShadow='0 6px 16px rgba(0, 0, 0, 0.2)'; this.style.borderColor='rgba(231, 76, 60, 0.6)';"
                    onmouseout="this.style.background='rgba(255, 255, 255, 0.95)'; this.style.transform='scale(1)'; this.style.boxShadow='0 4px 12px rgba(0, 0, 0, 0.15)'; this.style.borderColor='rgba(231, 76, 60, 0.3)';">
                    <i class="<?php echo $is_favorited ? 'fa fa-heart' : 'fa fa-heart-o'; ?>"
                        style="color: <?php echo $is_favorited ? 'rgb(231, 76, 60)' : 'rgb(221, 90, 76)'; ?>; font-size: 18px; line-height: 1; display: flex; align-items: center; justify-content: center; margin: 0; padding: 0; margin-right: 4px;"></i>
                </button>
            <?php endif; ?>
        </div>

        <h4 class="post-modern-title" style="margin-top: 10px; font-size: 17px; font-weight: bold;">
            <?php echo htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8'); ?>
        </h4>

        <p style="font-size: 16px; color: #444; margin-bottom: 5px; font-weight: bold;">
            $<?php echo htmlspecialchars(number_format($precio_con_descuento, 2), ENT_QUOTES, 'UTF-8'); ?>
            <?php if ($descuento > 0) : ?>
                <span style="text-decoration: line-through; color: #888; margin-left: 8px; font-weight: normal;">
                    $<?php echo htmlspecialchars(number_format($precio_original, 2), ENT_QUOTES, 'UTF-8'); ?>
                </span>
            <?php endif; ?>
        </p>

        <?php if ($descuento > 0) : ?>
            <div class="discount-percentage-badge">
                - <?php echo htmlspecialchars($descuento, ENT_QUOTES, 'UTF-8'); ?>%
            </div>
        <?php endif; ?>

        <p style="font-size: 14px;">Cantidad: <?php echo htmlspecialchars($row['cantidad'], ENT_QUOTES, 'UTF-8'); ?></p>
        <p style="font-size: 12px; color: #666; margin-top: 5px; max-height: 60px; overflow: hidden;">
            <?php echo htmlspecialchars($row['descripcion'], ENT_QUOTES, 'UTF-8'); ?>
        </p>
    </article>
</div>

<?php }
                } else {
                    echo '<div class="col-12 text-center py-5"><h4>No tienes muebles en favoritos</h4></div>';
                }
                pg_close($conn);
                ?>
            </div>
        </div>
    </section>
        <!-- Services-->
        <!-- Best offer-->
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
                                        href="https://www.instagram.com/homesafe25 "></a></li>
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
                        <!-- Rights-->
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
    <script>
    

function increaseQuantity(inputId) {
    const input = document.getElementById(inputId);
    if (!input || input.disabled) return;
    const max = parseInt(input.max);
    let currentValue = parseInt(input.value);
    if (currentValue < max) {
        input.value = currentValue + 1;
    }
}  
  
function decreaseQuantity(inputId) {
    const input = document.getElementById(inputId);
    if (!input || input.disabled) return;
    const min = parseInt(input.min);
    let currentValue = parseInt(input.value);
    if (currentValue > min) {
        input.value = currentValue - 1;
    }
}

function addToCart(id, quantity, availableQuantity) {
    quantity = parseInt(quantity);
    availableQuantity = parseInt(availableQuantity);
    if (quantity <= 0) {
        noStockAlert();
        return;
    }
    if (quantity > availableQuantity) {
        swal("No puedes agregar más unidades de las disponibles.", "", "warning");
        return;
    }
    window.location.href = 'addcarrito.php?id=' + id + '&quantity=' + quantity;
}


function noStockAlert() {
    swal("No puedes agregar este producto al carrito porque ya no quedan unidades disponibles.", "", "warning");
}
    
    function toggleSearchBar() {
        var searchBar = document.getElementById('searchBar');
        if (searchBar.style.display === 'block') {
            searchBar.style.display = 'none';
        } else {
            searchBar.style.display = 'block';
        }
    }

    
    function searchFurniture() {
    var query = document.getElementById('searchInput').value;
    if (query.length > 0) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'search_furniture.php?q=' + encodeURIComponent(query), true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var results = JSON.parse(xhr.responseText);
                var resultsContainer = document.getElementById('searchResults');
                resultsContainer.innerHTML = '';
                if (results.length > 0) {
                    results.forEach(function(result) {
                        var li = document.createElement('li');
                        li.textContent = result.nombre;
                        li.onclick = function() {
                            loadFurnitureDetails(result.id);
                            $('#detailsModal').modal('show'); 
                        };
                        resultsContainer.appendChild(li);
                    });
                } else {
                    var li = document.createElement('li');
                    li.textContent = 'No se encontraron resultados';
                    resultsContainer.appendChild(li);
                }
            }
        };
        xhr.send();
    } else {
        document.getElementById('searchResults').innerHTML = '';
    }
}
function loadFurnitureDetails(furnitureId) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'furnituredetalles.php?id=' + furnitureId, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            var details = response.furniture ? response.furniture : response; 
            var reviews = response.reviews || [];

            document.getElementById('furnitureName').textContent = details.nombre;
            document.getElementById('furniturePrice').textContent = details.precio;
            document.getElementById('furnitureQuantity').textContent = details.cantidad;
            document.getElementById('furnitureDescription').textContent = details.descripcion;
            document.getElementById('furnitureImage').src = "data:image/jpeg;base64," + details.imagen;
            document.getElementById('furnitureId').value = details.id;

            
            var quantityInput = document.getElementById('quantity-modal');
            var decrementButton = document.getElementById('btn-decrement-modal');
            var incrementButton = document.getElementById('btn-increment-modal');
            quantityInput.max = details.cantidad;
            quantityInput.value = 1;
            decrementButton.disabled = incrementButton.disabled = (details.cantidad <= 0);

            
            var reviewsContainer = document.getElementById('furnitureReviewsContainer');
            var noReviewsMessage = document.getElementById('noReviewsMessage');
            reviewsContainer.innerHTML = '';
            if (reviews.length > 0) {
                noReviewsMessage.style.display = 'none';
                reviews.forEach(function(review) {
                    var reviewDiv = document.createElement('div');
                    reviewDiv.className = 'review-item mb-3 p-3 border rounded';
                    reviewDiv.innerHTML = `
                        <div class="font-weight-bold">${htmlspecialchars(review.user_email)}</div>
                        <div>${getStarHtml(review.rating)}</div>
                        <p>${htmlspecialchars(review.comment)}</p>
                        <small class="text-muted">${new Date(review.created_at).toLocaleDateString('es-ES')}</small>
                    `;
                    reviewsContainer.appendChild(reviewDiv);
                });
            } else {
                noReviewsMessage.style.display = 'block';
            }
        }
    };
    xhr.send();
}


function getStarHtml(rating) {
    let stars = '';
    for (let i = 1; i <= 5; i++) {
        stars += i <= rating
            ? '<i class="fa fa-star" style="color: #FFC107;"></i>'
            : '<i class="fa fa-star-o" style="color: #ccc;"></i>';
    }
    return stars;
}


function htmlspecialchars(str) {
    if (!str) return '';
    var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return str.replace(/[&<>"']/g, function(m) { return map[m]; });
}
function addToCartFromModal() {
    var furnitureId = document.getElementById('furnitureId').value; 
    var quantity = document.getElementById('quantity-modal').value;
    var availableQuantity = document.getElementById('furnitureQuantity').textContent; 
    
    
    if (quantity <= 0) {
        noStockAlert();
        return;
    }
    if (parseInt(quantity) > parseInt(availableQuantity)) {
        swal("No puedes agregar más unidades de las disponibles.", "", "warning");
        return;
    }
    
    addToCart(furnitureId, quantity, availableQuantity);
}

function getFurnitureIdFromModal() {
    
    
    return furnitureId; 
}

function toggleFavorite(muebleId, buttonElement) {
    const heartIcon = buttonElement.querySelector('i');
    const isFavorited = heartIcon.classList.contains('fa-heart');
    fetch('0_toggle_favorite.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            mueble_id: muebleId,
            action: isFavorited ? 'remove' : 'add'
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            if (isFavorited) {
                heartIcon.className = 'fa fa-heart-o';
                heartIcon.style.color = 'rgb(221, 90, 76)';
            } else {
                heartIcon.className = 'fa fa-heart';
                heartIcon.style.color = 'rgb(231, 76, 60)';
            }
        } else {
            alert(result.message || 'Error al procesar favorito');
        }
    })
    .catch(error => {
        alert('Error de conexión');
    });
}


function addToCartFromModal() {
          var furnitureId = document.getElementById('furnitureId').value; 
          var quantity = document.getElementById('quantity-modal').value;
          var availableQuantity = document.getElementById('furnitureQuantity').textContent; 
          
          if (quantity <= 0) {
              noStockAlert();
              return;
          }
          if (parseInt(quantity) > parseInt(availableQuantity)) {
              swal("No puedes agregar más unidades de las disponibles.", "", "warning");
              return;
          }
          
          addToCart(furnitureId, quantity, availableQuantity);
      }
      
      function getFurnitureIdFromModal() {
          
          
          return furnitureId; 
      }
              
              function openReviewModal(muebleId, furnitureName) {
            <?php if (!isset($_SESSION['email'])) : ?>
                Swal.fire('Debes iniciar sesión para añadir una reseña.', '', 'warning');
                return;
            <?php endif; ?>
            document.getElementById('reviewFurnitureId').value = muebleId;
            document.getElementById('reviewFurnitureName').textContent = furnitureName;
            document.getElementById('reviewForm').reset();
            updateCharCounter(document.getElementById('reviewComment'), 'charCounter');
            $('#reviewModal').modal('show');
        }

        function updateCharCounter(textarea, counterId) {
            const currentLength = textarea.value.length;
            const maxLength = textarea.maxLength;
            document.getElementById(counterId).textContent = `${currentLength}/${maxLength}`;
        }

        function submitReview() {
            const muebleId = document.getElementById('reviewFurnitureId').value;
            const rating = document.querySelector('input[name="rating"]:checked')?.value;
            const comment = document.getElementById('reviewComment').value;
            
            if (!rating) {
                Swal.fire('Por favor, selecciona una calificación de estrellas.', '', 'warning');
                return;
            }
            if (comment.trim() === '') {
                Swal.fire('Por favor, escribe un mensaje para tu reseña.', '', 'warning');
                return;
            }
            
            fetch('submit_review.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    mueble_id: muebleId,
                    rating: rating,
                    comment: comment
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    Swal.fire('¡Reseña enviada con éxito!', '', 'success');
                    $('#reviewModal').modal('hide');
                } else {
                    Swal.fire('Error al enviar la reseña: ' + (result.message || 'Inténtalo de nuevo.'), '', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error de conexión al enviar la reseña.', '', 'error');
            });
        }


    </script>
<script src="js/validacionesES.js"></script>
</body>
</html>