<?php
include('includes/Cookies_sessions.php');
include("validacion_cliente.php");
?>
<!DOCTYPE html>
<html class="wide wow-animation" lang="en">

<head>
    <title>Propiedades</title>
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
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <!-- FontAwesome para los íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

    <!-- TU CSS AQUÍ - Lo mantengo igual -->
    <style>
        /* Todo tu CSS permanece igual */
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

        /* Hero Section Mejorada */
        .hero-section {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            padding: 80px 0 60px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero-subtitle {
            font-size: 1.3rem;
            opacity: 0.9;
            margin-bottom: 2rem;
            font-weight: 300;
        }

        /* Filtros mejorados */
        .filters-section {
            background: white;
            padding: 30px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }

        .filter-group {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            align-items: center;
        }

        .filter-item {
            display: flex;
            flex-direction: column;
            min-width: 150px;
        }

        .filter-item label {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }

        .filter-item select,
        .filter-item input {
            padding: 10px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
        }

        .filter-item select:focus,
        .filter-item input:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .filter-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .filter-btn:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }

        /* Mapa mejorado */
        #map-propiedades {
            width: 100%;
            height: 500px;
            border-radius: 15px;
            border: 3px solid #007bff;
            margin-bottom: 50px;
            box-shadow: 0 8px 25px rgba(0, 123, 255, 0.15);
            overflow: hidden;
        }

        .leaflet-popup-content-wrapper {
            border-radius: 12px;
            background: #fff;
            color: #212529;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.2);
        }

        .leaflet-popup-tip {
            background: #007bff;
        }

        .custom-marker {
            background: #007bff;
            border: 3px solid #fff;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            box-shadow: 0 3px 10px rgba(0, 123, 255, 0.3);
        }

        /* Contenedor principal mejorado */
        .propiedades-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 15px;
            position: relative;
        }

        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, #007bff, #0056b3);
            border-radius: 2px;
        }

        .section-title p {
            font-size: 1.1rem;
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Grid de propiedades mejorado */
        .propiedades-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            justify-items: center;
            margin-bottom: 60px;
        }

        /* Tarjetas de propiedades mejoradas */
        .propiedad-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #f0f0f0;
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            width: 100%;
            max-width: 380px;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .propiedad-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #007bff, #0056b3);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .propiedad-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(0, 123, 255, 0.15);
        }

        .propiedad-card:hover::before {
            transform: scaleX(1);
        }

        .propiedad-img {
            position: relative;
            overflow: hidden;
            height: 220px;
        }

        .propiedad-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .propiedad-card:hover .propiedad-img img {
            transform: scale(1.05);
        }

        .price-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(0, 123, 255, 0.95);
            color: white;
            padding: 8px 15px;
            border-radius: 25px;
            font-weight: 700;
            font-size: 0.9rem;
            backdrop-filter: blur(10px);
        }

        .status-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-available {
            background: rgba(40, 167, 69, 0.9);
            color: white;
        }

        .status-sold {
            background: rgba(220, 53, 69, 0.9);
            color: white;
        }

        /* Contenido de la tarjeta mejorado */
        .propiedad-content {
            padding: 25px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .propiedad-content h4 {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: #333;
            line-height: 1.3;
        }

        .propiedad-content p {
            font-size: 0.95rem;
            color: #666;
            margin-bottom: 20px;
            line-height: 1.5;
            flex-grow: 1;
        }

        .propiedad-features {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            font-size: 0.9rem;
            color: #555;
        }

        .feature-icon {
            width: 20px;
            height: 20px;
            margin-right: 8px;
            opacity: 0.7;
        }

        .size-info {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }

        .size-info span {
            font-weight: 600;
            color: #333;
        }

        /* Acciones mejoradas */
        .propiedad-actions {
            padding: 20px 25px;
            border-top: 1px solid #f0f0f0;
            background: #fafafa;
        }

        .btn-contactar {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            font-size: 1rem;
            font-weight: 600;
            padding: 14px 30px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            display: block;
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        .btn-contactar::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-contactar:hover::before {
            left: 100%;
        }

        .btn-contactar:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 123, 255, 0.3);
        }

        .btn-vendido {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
            font-size: 1rem;
            font-weight: 600;
            padding: 14px 30px;
            border: none;
            border-radius: 12px;
            cursor: not-allowed;
            text-decoration: none;
            text-align: center;
            display: block;
            width: 100%;
            opacity: 0.8;
        }

        /* Barra de búsqueda mejorada */
        /* Nueva barra de búsqueda con paleta de colores del sitio */
        .search-section {
            background: linear-gradient(135deg, #212529 0%, #444 50%, #666 100%);
            padding: 20px 0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 50;
            margin-top: 0;
            border-bottom: 3px solid #FFC107;
        }

        .search-container {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
        }

        .search-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            background: white;
            border-radius: 50px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .search-input-wrapper:hover {
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.3);
            transform: translateY(-2px);
            border-color: #FFC107;
        }

        .search-input-wrapper:focus-within {
            box-shadow: 0 12px 35px rgba(255, 193, 7, 0.3);
            transform: translateY(-2px);
            border-color: #FFC107;
        }

        .search-icon-left {
            padding: 0 20px;
            color: #666;
            font-size: 20px;
            display: flex;
            align-items: center;
            transition: color 0.3s ease;
        }

        .search-input-wrapper:focus-within .search-icon-left {
            color: #FFC107;
        }

        #searchInput {
            flex: 1;
            border: none;
            outline: none;
            padding: 18px 10px;
            font-size: 16px;
            background: transparent;
            color: #333;
        }

        #searchInput::placeholder {
            color: #999;
            font-weight: 400;
        }

        .search-btn {
            background: linear-gradient(135deg, #212529 0%, #444 100%);
            border: none;
            padding: 18px 25px;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        .search-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #FFC107 0%, #ffdb4d 100%);
            transition: left 0.3s ease;
            z-index: -1;
        }

        .search-btn:hover::before {
            left: 0;
        }

        .search-btn:hover {
            color: #212529;
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.4);
        }

        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            margin-top: 10px;
            max-height: 300px;
            overflow-y: auto;
            z-index: 100;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            border: 2px solid #FFC107;
        }

        .search-results.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .search-results::-webkit-scrollbar {
            width: 8px;
        }

        .search-results::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .search-results::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #212529 0%, #FFC107 100%);
            border-radius: 10px;
        }

        .search-results::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #FFC107 0%, #212529 100%);
        }

        .search-result-item {
            padding: 15px 20px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 12px;
            position: relative;
            overflow: hidden;
        }

        .search-result-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #212529 0%, #444 100%);
            transition: left 0.3s ease;
            z-index: -1;
        }

        .search-result-item:hover::before {
            left: 0;
        }

        .search-result-item:last-child {
            border-bottom: none;
        }

        .search-result-item:hover {
            color: white;
            transform: translateX(5px);
        }

        .search-result-item:hover .search-result-icon {
            color: #FFC107;
        }

        .search-result-item:first-child {
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }

        .search-result-item:last-child {
            border-bottom-left-radius: 15px;
            border-bottom-right-radius: 15px;
        }

        .search-result-icon {
            font-size: 16px;
            opacity: 0.7;
            color: #666;
            transition: color 0.3s ease;
        }

        .no-results {
            padding: 20px;
            text-align: center;
            color: #666;
            font-style: italic;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .no-results i {
            color: #FFC107;
            font-size: 1.2em;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .search-section {
                padding: 15px 0;
            }

            .search-container {
                margin: 0 15px;
            }

            .search-input-wrapper {
                border-radius: 25px;
            }

            #searchInput {
                padding: 15px 10px;
                font-size: 15px;
            }

            .search-icon-left {
                padding: 0 15px;
                font-size: 18px;
            }

            .search-btn {
                padding: 15px 20px;
                font-size: 14px;
            }

            .search-result-item {
                padding: 12px 15px;
                font-size: 14px;
            }
        }

        @media (max-width: 480px) {
            .search-section {
                padding: 12px 0;
            }

            .search-container {
                margin: 0 10px;
            }

            #searchInput {
                padding: 12px 8px;
                font-size: 14px;
            }

            .search-icon-left {
                padding: 0 12px;
                font-size: 16px;
            }

            .search-btn {
                padding: 12px 15px;
                font-size: 13px;
            }

            .search-btn span {
                display: none;
            }
        }

        /* Animación de carga para los resultados */
        .search-loading {
            padding: 20px;
            text-align: center;
            color: #212529;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .search-loading::after {
            content: '';
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #212529;
            border-radius: 50%;
            border-top-color: #FFC107;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Responsive mejorado */
        @media (max-width: 1200px) {
            .propiedades-grid {
                grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
                gap: 25px;
            }
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .hero-subtitle {
                font-size: 1.1rem;
            }

            .propiedades-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .propiedad-card {
                max-width: 100%;
            }

            .filter-group {
                flex-direction: column;
                gap: 15px;
            }

            .filter-item {
                width: 100%;
                min-width: auto;
            }

            #map-propiedades {
                height: 400px;
                border-radius: 10px;
            }

            .search-bar {
                left: -50px;
                right: -50px;
                min-width: 280px;
            }
        }

        @media (max-width: 576px) {
            .hero-section {
                padding: 60px 0 40px;
            }

            .hero-title {
                font-size: 2rem;
            }

            .section-title h2 {
                font-size: 2rem;
            }

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
                padding: 15px;
            }

            .propiedad-content {
                padding: 20px;
            }

            .propiedad-actions {
                padding: 15px 20px;
            }
        }

        /* Animaciones */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .propiedad-card {
            animation: fadeInUp 0.6s ease forwards;
        }

        .propiedad-card:nth-child(even) {
            animation-delay: 0.1s;
        }

        .propiedad-card:nth-child(3n) {
            animation-delay: 0.2s;
        }

        /* Estados de carga */
        .loading-skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        /* Footer mejorado */
        .bg_background {
            background: linear-gradient(135deg, #212529 0%, #343a40 100%);
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
                                    <li class="rd-nav-item active"><a class="rd-nav-link"
                                            href="properties.php">Propiedades</a>
                                    </li>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="furniture.php">Muebles</a>
                                    </li>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="about-us.php">Sobre
                                            nosotros</a>
                                    </li>
                                    <?php if (!isset($_SESSION['email'])): ?>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-toggle="modal"
                                                data-target="#loginModal">Iniciar Sesión</a></li>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-toggle="modal"
                                                data-target="#RegisterModal">Registrarse</a></li>
                                    <?php else: ?>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="logout.p.php">Cerrar Sesión</a>
                                        </li>
                                    <?php endif; ?>
                                    <?php if (isset($_SESSION['email'])): ?>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-toggle="modal" data-target="#PerfilModal"> Sesión actual: <?php echo htmlspecialchars($username); ?></a>
                                        </li>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="mostrarcarrito.php"><i class="fa fa-shopping-cart" style="font-size: 1.5em;"></i></a>
                                        </li>
                                    <?php endif; ?>

                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>
        </header>
        <br>
        <br>

        <!-- BARRA DE BÚSQUEDA MEJORADA -->
        <section class="search-section">
            <div class="search-container">
                <div class="search-input-wrapper">
                    <div class="search-icon-left">
                        <i class="fa fa-search"></i>
                    </div>
                    <input type="text" id="searchInput" placeholder="Buscar propiedades por nombre..." onkeyup="searchPropertiesLive()" autocomplete="off">
                    <button class="search-btn" onclick="performSearchProperties()">
                        <i class="fa fa-search"></i>
                        <span>Buscar</span>
                    </button>
                </div>
                <div class="search-results" id="searchResults"></div>
            </div>
        </section>

        <?php
        
        $host = "localhost";
        $port = "5432";
        $username = "postgres";
        $password = "TU_PASSWORD_DE_BASE_DE_DATOS";
        $dbname = "homesafe";
        $conn = pg_connect("host=$host port=$port dbname=$dbname user=$username password=$password");

        if (!$conn) {
            die("Conexión fallida: " . pg_last_error());
        }
        $query_map = "SELECT id, nombre, latitud, longitud, precio, estado FROM propiedades WHERE latitud IS NOT NULL AND longitud IS NOT NULL";
        $result_map = pg_query($conn, $query_map);

        $propiedades_map = [];
        while ($row = pg_fetch_assoc($result_map)) {
            $propiedades_map[] = [
                'id' => $row['id'],
                'nombre' => $row['nombre'],
                'latitud' => floatval($row['latitud']),
                'longitud' => floatval($row['longitud']),
                'precio' => $row['precio'],
                'estado' => $row['estado']
            ];
        }
        
        $query = "
        SELECT 
            p.id, p.nombre, p.descripcion, p.size, p.precio, p.estado,
            COALESCE(bedroom_counts.count, 0) AS bedrooms,
            COALESCE(bathroom_counts.count, 0) AS bathrooms
        FROM propiedades p
        LEFT JOIN (
            SELECT propiedad_id, COUNT(*) AS count
            FROM cuartos
            WHERE LOWER(TRIM(tipo_cuarto)) = 'dormitorio'
            GROUP BY propiedad_id
        ) AS bedroom_counts ON p.id = bedroom_counts.propiedad_id
        LEFT JOIN (
            SELECT propiedad_id, COUNT(*) AS count
            FROM cuartos
            WHERE LOWER(TRIM(tipo_cuarto)) IN ('baño', 'bano')
            GROUP BY propiedad_id
        ) AS bathroom_counts ON p.id = bathroom_counts.propiedad_id
        ORDER BY p.id
        ";
        $result = pg_query($conn, $query);

        
        $propiedades = [];
        while ($row = pg_fetch_assoc($result)) {
            $prop_id = $row['id'];
            $img_query = "SELECT imagen FROM carrusel_propiedad WHERE propiedad_id = $prop_id ORDER BY orden";
            $img_result = pg_query($conn, $img_query);
            $imagenes = [];
            while ($img_row = pg_fetch_assoc($img_result)) {
                $imagenes[] = base64_encode(pg_unescape_bytea($img_row['imagen']));
            }
            $row['imagenes'] = $imagenes;
            $propiedades[] = $row;
        }

        $query_mail_user = "SELECT mail_user FROM propiedades";
        $result_mail_user = pg_query($conn, $query_mail_user);
        $mail_user = [];
        while ($row2 = pg_fetch_assoc($result_mail_user)) {
            $seller_email[] = $row2;
        }
        ?>

        <section class="section novi-background section-md text-center">
            <div class="container">
                <h3 class="text-uppercase font-weight-bold wow-outer">
                    <br>
                    <span class="wow slideInDown">Propiedades disponibles</span>
                </h3>
                <br></br>
                <div id="map-propiedades"></div>

                <div class="propiedades-grid">
                    <?php if (count($propiedades) > 0): ?>
                        <?php foreach ($propiedades as $row): ?>

                            <div class="propiedad-card">
                                <?php
                                $is_favorited = false;
                                if (isset($_SESSION['email'])) {
                                    $conn_fav = pg_connect("host=localhost dbname=homesafe user=postgres password=Info2025/*-");
                                    $fav_stmt = pg_query_params($conn_fav, "SELECT id FROM favoritos_propiedades WHERE mail_user = $1 AND propiedad_id = $2", [$_SESSION['email'], $row['id']]);
                                    $is_favorited = pg_num_rows($fav_stmt) > 0;
                                    pg_close($conn_fav);
                                }
                                ?>
                                <?php if (isset($_SESSION['email'])) : ?>
                                    <button type="button" onclick="event.stopPropagation(); toggleFavoriteProperty(<?php echo $row['id']; ?>, this);" class="favorite-btn"
                                        style="position: absolute; top: 15px; right: 15px; background: rgba(255,255,255,0.95); border: 2px solid rgba(231,76,60,0.3); border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.15); cursor: pointer; transition: all 0.3s ease; z-index: 3; padding: 0; outline: none;">
                                        <i class="<?php echo $is_favorited ? 'fa fa-heart' : 'fa fa-heart-o'; ?>" style="color: <?php echo $is_favorited ? 'rgb(231,76,60)' : 'rgb(221,90,76)'; ?>; font-size: 18px;"></i>
                                    </button>
                                <?php endif; ?>

                                <div class="propiedad-img">
                                    <!-- Carrusel Bootstrap -->
                                    <div id="carousel-<?php echo $row['id']; ?>" class="carousel slide" data-ride="carousel">
                                        <div class="carousel-inner">
                                            <?php foreach ($row['imagenes'] as $idx => $img): ?>
                                                <div class="carousel-item <?php echo $idx === 0 ? 'active' : ''; ?>">
                                                    <a href="single_property.php?id=<?php echo $row['id']; ?>">
                                                        <img src="data:image/jpeg;base64,<?php echo $img; ?>" class="d-block w-100" alt="Imagen propiedad">
                                                    </a>
                                                </div>
                                            <?php endforeach; ?>
                                            <?php if (empty($row['imagenes'])): ?>
                                                <div class="carousel-item active">
                                                    <a href="single_property.php?id=<?php echo $row['id']; ?>">
                                                        <img src="images/placeholder.png" class="d-block w-100" alt="Sin imagen">
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <?php if (count($row['imagenes']) > 1): ?>
                                            <a class="carousel-control-prev" href="#carousel-<?php echo $row['id']; ?>" role="button" data-slide="prev">
                                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                <span class="sr-only">Anterior</span>
                                            </a>
                                            <a class="carousel-control-next" href="#carousel-<?php echo $row['id']; ?>" role="button" data-slide="next">
                                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                <span class="sr-only">Siguiente</span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="propiedad-content">
                                    <h4><?php echo htmlspecialchars($row['nombre']); ?></h4>
                                    <p><?php echo htmlspecialchars($row['descripcion']); ?></p>
                                    <div class="propiedad-info">
                                        <span>🛏 Habitaciones: <?php echo htmlspecialchars($row['bedrooms']); ?></span>
                                        <span>🛁 Baños: <?php echo htmlspecialchars($row['bathrooms']); ?></span>
                                    </div>
                                    <div class="propiedad-info">
                                        <span>📏 Medida: <?php echo htmlspecialchars($row['size']); ?> m²</span>
                                    </div>
                                    <p class="propiedad-price">$<?php echo number_format($row['precio'], 2); ?></p>
                                </div>
                                <form method="post" action="single_property.php">
                                    <div class="propiedad-actions">
                                        <?php if ($row['estado'] == "Vendido"): ?>
                                            <a class="btn-vendido button button-md button-primary button-winona wow slideInDown">Vendido</a>
                                        <?php else: ?>
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" class="btn-contactar button button-md button-primary button-winona wow slideInDown">Contactar</button>
                                        <?php endif ?>
                                    </div>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-propiedades">
                            <h3>No hay propiedades disponibles</h3>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </section>

        <?php pg_close($conn); ?>

        <!-- Services-->
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

    <!-- FUNCIONES DE BÚSQUEDA GLOBALES (FUERA DE DOMContentLoaded) -->
    <script>
        
        let searchTimeoutProp;
        let isSearchingProp = false;

        
        function searchPropertiesLive() {
            const query = document.getElementById('searchInput').value.trim();
            const resultsContainer = document.getElementById('searchResults');
            clearTimeout(searchTimeoutProp);

            if (query.length === 0) {
                resultsContainer.classList.remove('show');
                resultsContainer.innerHTML = '';
                return;
            }
            if (query.length < 2) {
                return;
            }
            resultsContainer.innerHTML = '<div class="search-loading">Buscando...</div>';
            resultsContainer.classList.add('show');
            isSearchingProp = true;
            searchTimeoutProp = setTimeout(() => {
                performSearchPropertiesRequest(query);
            }, 300);
        }

        function toggleFavoriteProperty(propiedadId, buttonElement) {
            const heartIcon = buttonElement.querySelector('i');
            const isFavorited = heartIcon.classList.contains('fa-heart');
            fetch('toggle_favorite_property.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        propiedad_id: propiedadId,
                        action: isFavorited ? 'remove' : 'add'
                    })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        if (isFavorited) {
                            heartIcon.className = 'fa fa-heart-o';
                            heartIcon.style.color = 'rgb(221,90,76)';
                        } else {
                            heartIcon.className = 'fa fa-heart';
                            heartIcon.style.color = 'rgb(231,76,60)';
                        }
                    } else {
                        alert(result.message || 'Error al procesar favorito');
                    }
                })
                .catch(error => {
                    alert('Error de conexión');
                });
        }


        
        function performSearchPropertiesRequest(query) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'search_properties.php?q=' + encodeURIComponent(query), true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    isSearchingProp = false;
                    const resultsContainer = document.getElementById('searchResults');
                    if (xhr.status === 200) {
                        try {
                            const results = JSON.parse(xhr.responseText);
                            displaySearchPropertiesResults(results);
                        } catch (e) {
                            resultsContainer.innerHTML = '<div class="no-results">Error en la búsqueda</div>';
                        }
                    } else {
                        resultsContainer.innerHTML = '<div class="no-results">Error de conexión</div>';
                    }
                }
            };
            xhr.send();
        }

        
        function displaySearchPropertiesResults(results) {
            const resultsContainer = document.getElementById('searchResults');
            resultsContainer.innerHTML = '';
            if (results.length > 0) {
                results.forEach(function(result) {
                    const resultItem = document.createElement('div');
                    resultItem.className = 'search-result-item';
                    resultItem.innerHTML = `
                        <i class="fa fa-home search-result-icon"></i>
                        <span>${result.nombre}</span>
                    `;
                    resultItem.onclick = function() {
                        window.location.href = 'single_property.php?id=' + result.id;
                    };
                    resultsContainer.appendChild(resultItem);
                });
            } else {
                resultsContainer.innerHTML = '<div class="no-results"><i class="fa fa-search"></i> No se encontraron propiedades</div>';
            }
            resultsContainer.classList.add('show');
        }

        
        function performSearchProperties() {
            const query = document.getElementById('searchInput').value.trim();
            if (query.length > 0) {
                if (!isSearchingProp) {
                    performSearchPropertiesRequest(query);
                }
            }
        }
    </script>

    <!-- SCRIPT DEL MAPA Y EVENTOS (DENTRO DE DOMContentLoaded) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            var map = L.map('map-propiedades').setView([13.7, -89.2], 8);

            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 18,
            }).addTo(map);

            
            var propiedades = <?php echo json_encode($propiedades_map); ?>;

            
            
            var houseIconDisponible = L.divIcon({
                className: '',
                html: `<svg width="38" height="38" viewBox="0 0 24 24" fill="#007bff" xmlns="http://www.w3.org/2000/svg">
        <path d="M12 3l9 9-1.5 1.5L18 12.5V20a1 1 0 0 1-1 1h-4v-5h-2v5H7a1 1 0 0 1-1-1v-7.5l-1.5 1.5L3 12l9-9z"/>
    </svg>`,
                iconSize: [38, 38],
                iconAnchor: [19, 38],
                popupAnchor: [0, -38]
            });

            
            var houseIconVendida = L.divIcon({
                className: '',
                html: `<svg width="38" height="38" viewBox="0 0 24 24" fill="#6c757d" xmlns="http://www.w3.org/2000/svg">
        <path d="M12 3l9 9-1.5 1.5L18 12.5V20a1 1 0 0 1-1 1h-4v-5h-2v5H7a1 1 0 0 1-1-1v-7.5l-1.5 1.5L3 12l9-9z"/>
    </svg>`,
                iconSize: [38, 38],
                iconAnchor: [19, 38],
                popupAnchor: [0, -38]
            });

            propiedades.forEach(function(prop) {
                if (prop.latitud && prop.longitud) {
                    
                    var icono = (prop.estado && prop.estado.toLowerCase() === "vendido") ? houseIconVendida : houseIconDisponible;

                    
                    var vendidoMsg = (prop.estado && prop.estado.toLowerCase() === "vendido") ?
                        `<span style="color:#dc3545;font-weight:bold;">¡VENDIDA!</span><br>` :
                        "";

                    var marker = L.marker([prop.latitud, prop.longitud], {
                        icon: icono
                    }).addTo(map);
                    marker.bindPopup(
                        `${vendidoMsg}<b>${prop.nombre}</b><br>
            Precio: $${parseFloat(prop.precio).toLocaleString()}<br>
            <a href="single_property.php?id=${prop.id}" style="color:#007bff;font-weight:bold;text-decoration:underline;">Ver detalles</a>`
                    );
                    marker.on('click', function() {
                        window.location.href = `single_property.php?id=${prop.id}`;
                    });
                }
            });

            
            document.addEventListener('click', function(event) {
                const searchContainer = document.querySelector('.search-container');
                const resultsContainer = document.getElementById('searchResults');
                if (searchContainer && resultsContainer && !searchContainer.contains(event.target)) {
                    resultsContainer.classList.remove('show');
                }
            });

            
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('keypress', function(event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        performSearchProperties();
                    }
                });
            }

        });
    </script>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/validacionesES.js"></script>
</body>

</html>