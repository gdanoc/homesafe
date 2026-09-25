<?php
include('includes/Cookies_sessionsEn.php');
?>
<!DOCTYPE html>
<html class="wide wow-animation" lang="en">

<head>
    <title>About us</title>
    <meta name="format-detection" content="telephone=no">
    <meta name="viewport" content="width=device-width height=device-height initial-scale=1.0 maximum-scale=1.0 user-scalable=0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta charset="utf-8">
    <link rel="icon" href="images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Work+Sans:300,400,500,700,800%7CPoppins:300,400,700">
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/fonts.css">
    <link rel="stylesheet" href="css/style.css" id="main-styles-link">
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <style>
        .bg_background {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        /* Modales más amplios y minimalistas */
        .modal-dialog-enhanced {
            max-width: 450px;
            margin: 2rem auto;
        }

        .modal-content {
            border: none;
            border-radius: 8px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            margin: auto;
        }

        .modal-header {
            border: none;
            padding: 2rem 2rem 1rem;
            background: rgb(63, 116, 151);
            position: relative;
        }

        .modal-title {
            font-weight: 600;
            font-size: 1.5rem;
            color: white !important;
            margin: 0;
        }

        .close {
            position: absolute;
            right: 1.5rem;
            top: 1.5rem;
            color: white;
            opacity: 0.8;
            font-size: 1.5rem;
            transition: opacity 0.3s ease;
        }

        .close:hover {
            opacity: 1;
            color: white;
        }

        .modal-body {
            background: #fafafa;
        }

        /* Formularios mejorados */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .input-group {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .input-group:focus-within {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .input-group-text {
            background: white;
            border: none;
            color: rgb(63, 116, 151);
            font-size: 1.1rem;
        }

        .form-control {
            border: none;
            padding: 0.875rem 1rem;
            font-size: 1rem;
            background: white;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            box-shadow: none;
            border: none;
            background: white;
        }

        /* Botones mejorados */
        .btn-primary {
            background-color: rgb(63, 116, 151);
            border: none;
            padding: 0.875rem 2rem;
            font-weight: 500;
            font-size: 1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
            background: rgb(56, 81, 97);
        }

        /* Enlaces mejorados */
        .modal-body a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .modal-body a:hover {
            color: #764ba2;
            text-decoration: none;
        }

        /* Perfil rediseñado */
        .profile-container {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            width: 100%;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid #f0f0f0;
        }

        .profile-image {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: auto;
            margin-bottom: 10px;
            background: linear-gradient(135deg, rgb(46, 56, 67), #6e869e);
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            color: white;
        }

        .profile-image svg {
            width: 50%;
            height: 50%;
        }

        .profile-name {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 600;
        }

        .profile-info {
            margin-top: 2rem;
        }

        .profile-info-item {
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 12px;
            border-left: 4px solid rgb(63, 116, 151);
        }

        .profile-info-item strong {
            display: block;
            margin-bottom: 0.5rem;
            color: rgb(63, 116, 151);
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .profile-info-item p {
            margin: 0;
            color: #555;
        }

        .profile-info-item a {
            color: rgb(63, 116, 151);
            text-decoration: none;
            font-weight: 500;
        }

        .profile-info-item a:hover {
            color: rgb(75, 98, 162);
        }

        /* Selector de idioma mejorado */
        .language-selector {
            display: inline-block;
            font-family: 'Poppins', sans-serif;
            margin: 1rem 0;
        }

        .language-label {
            margin-right: 10px;
            font-weight: 600;
            color: #333;
        }

        .language-select {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            background-color: #fff;
            font-size: 0.9rem;
            font-weight: 500;
            color: #333;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .language-select:hover,
        .language-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        /* Animaciones sutiles */
        .modal.fade .modal-dialog {
            transform: translateY(-50px);
            transition: transform 0.3s ease-out;
        }

        .modal.show .modal-dialog {
            transform: translateY(0);
        }
    </style>
</head>

<body>

    <?php include 'EN_modals.php'; ?>

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
                                    <li class="rd-nav-item "><a class="rd-nav-link" href="EN_index.php">Home</a>
                                    </li>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="EN_properties.php">Properties</a>
                                    </li>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="EN_furniture.php">Furniture</a>
                                    </li>
                                    <li class="rd-nav-item active"><a class="rd-nav-link" href="EN_about-us.php">About Us</a>
                                    </li>
                                    <?php if (!isset($_SESSION['email'])): ?>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-toggle="modal" data-target="#loginModal">Log In</a></li>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-toggle="modal" data-target="#RegisterModal">Sign Up</a></li>
                                    <?php else: ?>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="EN_logout.p.php">Log Out</a></li>
                                    <?php endif; ?>
                                    <?php if (isset($_SESSION['email'])): ?>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-toggle="modal" data-target="#PerfilModal"> Current Session: <?php echo htmlspecialchars($username); ?></a>
                                        <?php endif; ?>
                                        <li class="rd-nav-item">
                                            <a class="rd-nav-link" href="EN_mostrarcarrito.php">
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
        <!-- Working at CaseCraft-->
        <br> <br> <br>
        <section class="section novi-background section-lg">
            <div class="container">
                <div class="row row-50 justify-content-center justify-content-lg-between flex-lg-row-reverse">
                    <div class="col-md-10 col-lg-6 col-xl-5">
                        <h3 class="text-uppercase">Who are we?</h3>
                        <p class="about-subtitle">HomeSafe is a virtual store where you can buy properties by contacting real estate agents and you can also find the furniture section, such as chairs, sofas, tables, etc.</p>
 <p>The team provides a user-friendly web environment that allows you to access the different sections without problems. The project will have the objectives, justification, a description of the project, a flow chart and additional information.</p>
                    </div>
                    <div class="col-md-10 col-lg-6 col-xl-6"><img class="img-responsive" src="images/Imagotipo-II.png" alt="" width="570" height="388" />
                    </div>
                </div>
            </div>
        </section>
        <!-- Advantages and Achievements-->
        <section class="section novi-background section-lg text-center bg-gray-100">
            <div class="container">
                <h3 class="text-uppercase wow-outer"><span class="wow slideInUp">Our Team</span></h3>
                <div class="row row-50 row-xxl-70 justify-content-center justify-content-lg-start">
                    <div class="col-md-10 col-lg-4 wow-outer">
                        <!-- Profile Creative-->
                        <article class="profile-creative wow slideInLeft">
                            <figure class="profile-creative-figure"><img class="profile-creative-image" src="images/team-1-270x273.jpg" alt="" width="270" height="273" />
                            </figure>
                            <div class="profile-creative-main">
                                <br>
                                <br>
                                <h5 class="profile-creative-title"><a>Diego Alemán</a></h5>
                                <p class="profile-creative-position">Frontend and Backend Developer</p>
                            </div>
                        </article>
                    </div>
                    <div class="col-md-10 col-lg-4 wow-outer">
                        <!-- Profile Creative-->
                        <article class="profile-creative wow slideInLeft" data-wow-delay=".2s">
                            <figure class="profile-creative-figure"><img class="profile-creative-image" src="images/team-2-270x273.jpg" alt="" width="270" height="273" />
                            </figure>
                            <div class="profile-creative-main">
                                <br>
                                <br>
                                <h5 class="profile-creative-title"><a>Rafael Escobar</a></h5>
                                <p class="profile-creative-position">Backend Developer</p>
                            </div>
                        </article>
                    </div>
                    <div class="col-md-10 col-lg-4 wow-outer">
                        <!-- Profile Creative-->
                        <article class="profile-creative wow slideInLeft" data-wow-delay=".4s">
                            <figure class="profile-creative-figure"><img class="profile-creative-image" src="images/team-3-270x273.jpg" alt="" width="270" height="273" />
                            </figure>
                            <div class="profile-creative-main">
                                <br>
                                <br>
                                <h5 class="profile-creative-title"><a>Gerardo Orellana</a></h5>
                                <p class="profile-creative-position">Frontend and Backend Developer</p>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </section>
        <!-- Page Footer-->
        <footer class="section novi-background footer-advanced bg-gray-700">
            <div class="footer-advanced-main">
                <div class="container">
                    <div class="row row-50">
                        <div class="col-lg-4">
                            <h5 class="font-weight-bold text-uppercase text-white">About Us</h5>
                            <p class="footer-advanced-text">HomeSafe is an online store where you can buy real estate and furniture. This website has a user-friendly and intuitive interface.</p>
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
                                <li><a class="icon novi-icon icon-sm link-default mdi mdi-instagram" href="https://www.instagram.com/homesafe25"></a></li>
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
                    <div class="footer-advanced-layout"><a class="brand" href="EN_index.php"><img src="images/logo-light-115x34.png" alt="" width="115" height="34" srcset="images/logo-light-115x34.png 2x" /></a>
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
    <script src="js/validacionesES.js"></script>
</body>

</html>