<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<?php
include("includes/Cookies_sessions.php");
include("validacion_cliente.php");


$conn = pg_connect("host=localhost dbname=homesafe user=postgres password=Info2025/*-");
if (!$conn) {
    die("Conexión fallida: " . pg_last_error());
}

$email = $_SESSION['email'];
$query_trial_status = "SELECT tuvo_plan_trial FROM suscripcion WHERE mail_user = $1 ORDER BY fecha_vencimiento DESC LIMIT 1";
$result_trial_status = pg_query_params($conn, $query_trial_status, array($email));
$tuvo_plan_trial = 'no'; 
if ($result_trial_status && pg_num_rows($result_trial_status) > 0) {
    $row_trial_status = pg_fetch_assoc($result_trial_status);
    $tuvo_plan_trial = $row_trial_status['tuvo_plan_trial'];
}
pg_close($conn);
?>
<!DOCTYPE html>
<html class="wide wow-animation" lang="en">

<head>
    <title>Planes de Suscripción - HomeSafe</title>
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
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            position: relative;
            top: 0;
        }

        .furniture-card:hover {
            top: -5px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
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
            .row-35>[class*="col-"] {
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

        .heart-link .fa-heart {
            font-size: 1.5em;
        }

        /* Título principal - Mantener estilo original */
        h3.text-uppercase {
            color: #333;
            font-size: 2.5rem;
            margin-bottom: 2rem;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
        }

        /* Contenedor principal de planes */
        .plans-main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Trial Card - Horizontal y sutil */
        .trial-section {
            width: 100%;
            margin-bottom: 3rem;
        }

        .trial-card {
            background: #1F262D;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            padding: 24px 32px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 2px solid #10a37f;
        }

        .trial-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #10a37f, #12b580);
        }

        .trial-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
        }

        .trial-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 2rem;
            position: relative;
            z-index: 2;
        }

        .trial-info {
            flex: 1;
            text-align: left;
        }

        .trial-header-section {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 12px;
        }

        .trial-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #10a37f;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .trial-badge {
            background: linear-gradient(135deg, #10a37f, #12b580);
            color: white;
            padding: 6px 12px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 8px rgba(16, 163, 127, 0.3);
        }

        .trial-desc {
            color: #c0c0c0;
            font-size: 1rem;
            line-height: 1.5;
            margin-bottom: 16px;
        }

        .trial-benefits {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .trial-benefit {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #e0e0e0;
            font-size: 0.95rem;
            padding-left: 24px;
            position: relative;
        }

        .trial-benefit::before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #10a37f;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .trial-cta {
            text-align: center;
            min-width: 200px;
        }

        .trial-btn {
            background: linear-gradient(135deg, #10a37f, #12b580);
            border: none;
            border-radius: 12px;
            padding: 16px 24px;
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 16px rgba(16, 163, 127, 0.3);
            white-space: nowrap;
        }

        .trial-btn:hover {
            background: linear-gradient(135deg, #0e8c6e, #10a37f);
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(16, 163, 127, 0.4);
        }

        /*======================================*/
        /*===========Suscripción estilos=========*/
        /*======================================*/

        .plans-container {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
            align-items: stretch;
        }

        .plan-form {
            flex: 1;
            max-width: 350px;
            min-width: 300px;
        }

        .plan-card {
            background: #1F262D;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            padding: 32px 24px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.6s ease forwards;
        }

        .plan-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #10a37f, #12b580);
        }

        .plan-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.4);
        }

        .plan-header {
            text-align: center;
            margin-bottom: 24px;
        }

        .plan-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: #10a37f;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .plan-price {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 12px;
            color: #12b580;
        }

        .plan-price small {
            font-size: 0.8rem;
            color: #a0a0a0;
            font-weight: 400;
        }

        .plan-desc {
            font-size: 1rem;
            line-height: 1.5;
            color: #c0c0c0;
            text-align: center;
            margin-bottom: 24px;
            min-height: 48px;
        }

        .plan-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .plan-benefits {
            list-style: none;
            padding-left: 0;
            margin-bottom: 32px;
            flex: 1;
        }

        .plan-benefits li {
            margin-bottom: 12px;
            padding-left: 24px;
            position: relative;
            color: #e0e0e0;
            font-size: 0.95rem;
            line-height: 1.4;
        }

        .plan-benefits li::before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #10a37f;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .subscribe-btn {
            background: linear-gradient(135deg, #10a37f, #12b580);
            border: none;
            border-radius: 12px;
            padding: 16px 24px;
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 16px rgba(16, 163, 127, 0.3);
        }

        .subscribe-btn:hover {
            background: linear-gradient(135deg, #0e8c6e, #10a37f);
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(16, 163, 127, 0.4);
        }

        /* Efectos adicionales */
        .plan-card.featured {
            border: 2px solid #10a37f;
            transform: scale(1.05);
        }

        .plan-card.featured::before {
            height: 6px;
        }

        .plan-card.featured:hover {
            transform: scale(1.05) translateY(-8px);
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

        .plan-form:nth-child(1) .plan-card {
            animation-delay: 0.1s;
        }

        .plan-form:nth-child(2) .plan-card {
            animation-delay: 0.2s;
        }

        .plan-form:nth-child(3) .plan-card {
            animation-delay: 0.3s;
        }

        /* Responsive */
        @media (max-width: 768px) {
            h3.text-uppercase {
                font-size: 2rem;
                margin-bottom: 1.5rem;
            }

            .trial-content {
                flex-direction: column;
                gap: 1.5rem;
                text-align: center;
            }

            .trial-info {
                text-align: center;
            }

            .trial-benefits {
                justify-content: center;
            }

            .trial-section {
                margin-bottom: 2rem;
            }

            .plans-container {
                flex-direction: column;
                align-items: center;
                gap: 20px;
            }

            .plan-form {
                max-width: 100%;
                width: 100%;
            }

            .plan-card {
                margin: 0 auto;
                max-width: 400px;
            }

            .plan-card.featured {
                transform: none;
            }

            .plan-card.featured:hover {
                transform: translateY(-8px);
            }

            .plan-title {
                font-size: 1.5rem;
            }

            .plan-price {
                font-size: 1.75rem;
            }
        }

        @media (max-width: 480px) {
            h3.text-uppercase {
                font-size: 1.75rem;
            }

            .trial-card {
                padding: 20px 24px;
            }

            .trial-header-section {
                flex-direction: column;
                gap: 0.5rem;
                align-items: center;
            }

            .trial-benefits {
                flex-direction: column;
                align-items: center;
                gap: 1rem;
            }

            .plan-card {
                padding: 24px 20px;
            }

            .plan-title,
            .trial-title {
                font-size: 1.3rem;
            }

            .plan-price {
                font-size: 1.5rem;
            }
        }

        /* Estilos adicionales para mantener compatibilidad */
        .wow-outer {
            position: relative;
        }

        .slideInDown {
            animation: slideInDown 1s ease-out;
        }

        @keyframes slideInDown {
            from {
                transform: translateY(-30px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .novi-background {
            position: relative;
        }

        .text-center {
            text-align: center;
        }

        .font-weight-bold {
            font-weight: bold;
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
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="properties.php">Propiedades</a>
                                    </li>
                                    <li class="rd-nav-item"><a class="rd-nav-link"
                                            href="furniture.php">Muebles</a>
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
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-toggle="modal"
                                                data-target="#PerfilModal"> Sesión actual:
                                                <?php echo htmlspecialchars($username); ?></a>
                                        <?php endif; ?>
                                        <li class="rd-nav-item">
                                            <a class="rd-nav-link" href="mostrarcarrito.php" title="Ver carrito">
                                                <i class="fa fa-shopping-cart" style="font-size: 1.5em;"></i>
                                            </a>
                                        </li>

                                        <!--<li class="rd-nav-item">
                                            <a class="rd-nav-link heart-link" href="favoritos_furniture.php" title="Ver favoritos">
                                                <i class="fa fa-heart"></i>
                                            </a>
                                        </li> -->
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
        
        $query = "SELECT id, nombre, descripcion, precio, cantidad, imagen FROM muebles";
        $result = pg_query($conn, $query);
        ?>
        <section class="section novi-background section-md text-center">
            <!-- Título principal - Mantiene su prominencia -->
            <h3 class="text-uppercase font-weight-bold wow-outer">
                <br>
                <span class="wow slideInDown">Planes de Suscripción para vendedores HomeSafe</span>
                <br>
                <small>Ahorra hasta un 35% al subir de plan: más herramientas, más publicaciones, menos costo por propiedad.</small>
            </h3>

            <!-- Contenedor principal -->
            <div class="plans-main-container">
                <!-- Trial Plan Card - Horizontal y sutil -->
                <?php if ($tuvo_plan_trial === 'no'): ?>
                    <div class="trial-section">
                        <form method="GET" action="compra_suscripcion.php">
                            <input type="hidden" name="plan" value="trial" />
                            <div class="trial-card">
                                <div class="trial-content">
                                    <div class="trial-info">
                                        <div class="trial-header-section">
                                            <div class="trial-title">HomeSafe Trial</div>
                                            <div class="trial-badge">21 días gratis</div>
                                        </div>
                                        <div class="trial-desc">
                                            Hecho para descubrir las funcionalidades como vendedor, con esta prueba gratuita tendrás de manera sintetizada en qué consiste la versión para vendedores.
                                        </div>
                                        <div class="trial-benefits">
                                            <div class="trial-benefit">Publicación de 1 propiedad</div>
                                            <div class="trial-benefit">Herramientas básicas para administrar propiedades</div>
                                        </div>
                                    </div>
                                    <div class="trial-cta">
                                        <button type="submit" class="trial-btn">Comenzar Prueba</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <!-- Mostrar mensaje o no mostrar nada -->
                    <div class="alert alert-info text-center">
                        Ya has utilizado el plan de prueba HomeSafe Trial.
                    </div>
                <?php endif; ?>

                <!-- Regular Plans - 3 tarjetas principales -->
                <div class="plans-container">
                    <form method="GET" action="compra_suscripcion.php" class="plan-form">
                        <input type="hidden" name="plan" value="lite" />
                        <div class="plan-card">
                            <div class="plan-content">
                                <div class="plan-header">
                                    <div class="plan-title">HomeSafe Lite</div>
                                    <div class="plan-price">$2 <small>Mensuales</small></div>
                                </div>

                                <div class="plan-desc">
                                    Ideal para nuevos vendedores que quieren probar la plataforma y emprender.
                                </div>

                                <ul class="plan-benefits">
                                    <li>Publicación de 5 propiedades al mes</li>
                                    <li>Herramientas básicas para administrar tus propiedades</li>
                                </ul>
                            </div>
                            <button type="submit" class="subscribe-btn">Suscribirse</button>
                        </div>
                    </form>

                    <form method="GET" action="compra_suscripcion.php" class="plan-form">
                        <input type="hidden" name="plan" value="plus" />
                        <div class="plan-card featured">
                            <div class="plan-content">
                                <div class="plan-header">
                                    <div class="plan-title">HomeSafe Plus</div>
                                    <div class="plan-price">$4.50 <small>Mensuales</small></div>
                                </div>

                                <div class="plan-desc">
                                    Pensado para vendedores activos que buscan más visibilidad y herramientas. Destaca tus anuncios y accede a opciones avanzadas.
                                </div>

                                <ul class="plan-benefits">
                                    <li>Beneficios 'HomeSafe Lite'</li>
                                    <li>Publicación de hasta 10 propiedades al mes</li>
                                    <li>Acceso a implementar información específica en las propiedades</li>
                                    <!--<li>Herramientas de comparación</li>-->
                                    <!--<li>Prioridad en la lista de propiedades</li>-->
                                    <li>Estadísticas avanzadas</li>
                                </ul>
                            </div>
                            <button type="submit" class="subscribe-btn">Suscribirse</button>
                        </div>
                    </form>

                    <form method="GET" action="compra_suscripcion.php" class="plan-form">
                        <input type="hidden" name="plan" value="pro" />
                        <div class="plan-card">
                            <div class="plan-content">
                                <div class="plan-header">
                                    <div class="plan-title">HomeSafe Pro</div>
                                    <div class="plan-price">$7 <small>Mensuales</small></div>
                                    <small>HomeSafe Pro te ofrece el máximo valor: todas las funciones por menos de lo que pagarías combinando Lite y Plus.</small>
                                </div>

                                <div class="plan-desc">
                                    Para profesionales inmobiliarios que quieren el máximo potencial. Publicaciones ilimitadas, herramientas exclusivas de marketing y acceso completo.
                                </div>

                                <ul class="plan-benefits">
                                    <li>Beneficios 'HomeSafe Plus'</li>
                                    <li>Publicaciones ilimitadas</li>
                                    <li>Acceso total a todas las herramientas como vendedor</li>
                                    <li>Soporte prioritario</li>
                                    <!--<li>Historial de ventas de tus propiedades</li> -->
                                </ul>
                            </div>
                            <button type="submit" class="subscribe-btn">Suscribirse</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
        <?php pg_close($conn); ?>
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
                    var details = JSON.parse(xhr.responseText);
                    
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

                    if (details.cantidad > 0) {
                        decrementButton.disabled = false;
                        incrementButton.disabled = false;
                    } else {
                        decrementButton.disabled = true;
                        incrementButton.disabled = true;
                    }
                }
            };
            xhr.send();
        }

        function addToCartFromModal() {
            var furnitureId = document.getElementById('furnitureId').value; 
            var quantity = document.getElementById('quantity-modal').value;
            var availableQuantity = document.getElementById('furnitureQuantity')
                .textContent; 

            
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
                    headers: {
                        'Content-Type': 'application/json'
                    },
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
    </script>
    <script src="js/validacionesES.js"></script>
</body>

</html>