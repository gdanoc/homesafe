<?php
include('0_ESvalidacion_Seller.php'); //ESPAÑOL 
include('logic_dashboard_vendedor.php');
require_once('0_ESvalidar_suscripcion.php');

$email = $_SESSION['email'] ?? null;
$plan_id = null;
$prop_count = 0;

if ($email) {
    $conn = pg_connect("host=localhost dbname=homesafe user=postgres password=Info2025/*-");
    if ($conn) {
        $query = "SELECT plan_id FROM suscripcion WHERE mail_user = $1 ORDER BY fecha_vencimiento DESC LIMIT 1";
        $result = pg_query_params($conn, $query, array($email));
        if ($result && pg_num_rows($result) > 0) {
            $row = pg_fetch_assoc($result);
            $plan_id = intval($row['plan_id']);
        }
        pg_close($conn);
    }
}

if ($email) {
    $conn = pg_connect("host=localhost dbname=homesafe user=postgres password=Info2025/*-");
    if ($conn) {
        $query = "SELECT COUNT(*) AS total FROM propiedades WHERE mail_user = $1";
        $result = pg_query_params($conn, $query, array($email));
        if ($result && pg_num_rows($result) > 0) {
            $row = pg_fetch_assoc($result);
            $prop_count = intval($row['total']);
        }
        pg_close($conn);
    }
}
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CRUD Vendedor - HomeSafe</title>
    <!-- CSS Libraries -->
    <link href="assets/libs/flot/css/float-chart.css" rel="stylesheet">
    <link href="dist/css/style.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin="" />

    <style>
        /* ===================== VARIABLES CSS ===================== */
        :root {
            --primary-color: #007bff;
            --secondary-color: #7c3aed;
            --success-color: #28a745;
            --warning-color: #f59e0b;
            --danger-color: #dc3545;
            --info-color: #3b82f6;
            --dark-color: #1f2937;
            --light-color: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --white: #ffffff;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --border-radius: 8px;
            --border-radius-lg: 12px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);

            /* Dark Mode Colors */
            --dark-mode-bg: #121212;
            --dark-mode-text: #f8f8f2;
            --dark-mode-element: #1e1e1e;
            --dark-mode-shadow: rgba(0, 0, 0, 0.5);
            --dark-mode-border: #333;
        }

        /* ===================== BASE STYLES ===================== */
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-attachment: fixed;
            margin: 0;
            padding: 0;
            color: var(--gray-700);
            line-height: 1.6;
            font-weight: 400;
            transition: background 0.3s, color 0.3s;
        }

        /* Dark Mode Styles */
        body.dark-mode {
            background: rgb(46, 46, 46);
            color: var(--dark-mode-text);
        }

        /* ===================== CONTAINER STYLES ===================== */
        .container-fluid {
            padding: 30px 25px;
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(10px);
            min-height: 100vh;
        }

        /* ===================== CHART CONTAINER ===================== */
        .chart-container {
            position: relative;
            margin: 30px auto;
            height: 45vh;
            width: 90%;
            max-width: 1200px;
            background: var(--white);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-xl);
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            transition: var(--transition);
            animation: fadeInUp 0.6s ease-out;
        }

        /* Dark Mode Styles */
        body.dark-mode .chart-container {
            background: var(--dark-mode-element);
        }

        .chart-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .chart-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, rgb(46, 56, 67), #6e869e);
            border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;
        }

        /* ===================== FORM STYLES ===================== */
        .form {
            background-color: #fff;
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 1200px;
            margin: 20px auto;
            overflow-x: auto;
            position: relative;
            border: 1px solid var(--gray-200);
            transition: var(--transition);
        }

        .login-input {
            width: 100%;
            /* Asegura que los inputs ocupen todo el ancho */
            padding: 10px;
            margin: 10px 0;
            /* Espacio entre los inputs */
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .login-button {
            width: 100%;
            /* Botón ocupa todo el ancho */
            padding: 10px;
            background-color: rgb(63, 116, 151);
            ;
            border: none;
            border-radius: 4px;
            color: white;
            font-size: 16px;
            margin-top: 20px;
            /* Espacio superior para el botón */
        }

        .login-button:hover {
            background-color: rgb(56, 81, 97);
        }

        /* Dark Mode Styles */
        body.dark-mode .form {
            background: var(--dark-mode-element);
        }

        .form::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(135deg, rgb(46, 56, 67), #6e869e);
        }

        .form:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-xl);
        }

        .login-title {
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: 700;
            color: #333;
            text-align: center;
            position: relative;
            padding-bottom: 15px;
        }

        /* Dark Mode Styles */
        body.dark-mode .login-title {
            color: var(--dark-mode-text);
        }

        .login-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(135deg, rgb(46, 56, 67), #6e869e);
            border-radius: 2px;
        }

        /* ===================== TABLE STYLES ===================== */
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            background: var(--white);
        }

        /* Dark Mode Styles */
        body.dark-mode table {
            background: var(--dark-mode-element);
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th {
            background-color: rgb(63, 116, 151);
            color: var(--white);
            padding: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: left;
            position: relative;
        }

        th::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: rgba(255, 255, 255, 0.2);
        }

        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
            font-size: 0.9rem;
            transition: var(--transition);
        }

        /* Dark Mode Styles */
        body.dark-mode td {
            color: var(--dark-mode-text);
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        /* Dark Mode Styles */
        body.dark-mode tr:nth-child(even) {
            background: rgba(255, 255, 255, 0.05);
        }

        tr:hover {
            background-color: #ddd;
            transform: scale(1.01);
            box-shadow: var(--shadow-md);
        }

        tr:hover td {
            color: var(--gray-800);
        }

        /* Dark Mode Styles */
        body.dark-mode tr:hover td {
            color: var(--dark-mode-text);
        }

        /* ===================== DASHBOARD BOXES ===================== */
        .box {
            border-radius: var(--border-radius-lg);
            padding: 30px 25px;
            color: var(--white);
            text-align: center;
            flex: 1 1 280px;
            min-width: 250px;
            max-width: 350px;
            box-shadow: var(--shadow-xl);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            animation: fadeInUp 0.6s ease-out;
        }

        /* Dark Mode Styles */
        body.dark-mode .box {
            background: var(--dark-mode-element);
        }

        .box::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .box:hover::before {
            left: 100%;
        }

        .box:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .box h3 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            line-height: 1.2;
        }

        .box h6 {
            font-size: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            opacity: 0.9;
            margin: 0;
            letter-spacing: 1px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        /* ===================== BOX COLOR VARIANTS ===================== */
        .bg-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .bg-info:hover {
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.4);
        }

        .bg-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            box-shadow: 0 10px 30px rgba(17, 153, 142, 0.3);
        }

        .bg-success:hover {
            box-shadow: 0 20px 40px rgba(17, 153, 142, 0.4);
        }

        .bg-warning {
            background: linear-gradient(135deg, #ff9a56 0%, #ffad56 100%);
            box-shadow: 0 10px 30px rgba(255, 154, 86, 0.3);
        }

        .bg-warning:hover {
            box-shadow: 0 20px 40px rgba(255, 154, 86, 0.4);
        }

        /* ===================== IMAGE STYLES ===================== */
        img {
            max-width: 115px;
            height: auto;
            border-radius: 5px;
            display: block;
            margin: auto;
            transition: var(--transition);
        }

        img:hover {
            transform: scale(1.05);
        }

        /* ===================== NAVIGATION STYLES ===================== */
        .nav-link {
            display: flex;
            align-items: center;
            transition: var(--transition);
            position: relative;
        }

        .nav-link img {
            margin-right: 10px;
            border-radius: 50%;
        }

        /* ===================== SIDEBAR STYLES ===================== */
        .left-sidebar {
            transition: var(--transition);
            background: var(--white);
            box-shadow: var(--shadow-xl);
        }

        /* Dark Mode Styles */
        body.dark-mode .left-sidebar {
            background: var(--dark-mode-element);
        }

        .left-sidebar.collapsed {
            width: 85px;
        }

        .left-sidebar.collapsed .hide-menu {
            opacity: 0;
            visibility: hidden;
        }

        .left-sidebar.collapsed .sidebar-link {
            text-align: center;
            padding: 18px 12px;
            justify-content: center;
        }

        .left-sidebar.collapsed .sidebar-link i {
            font-size: 24px;
            margin-right: 0;
        }

        #sidebarnav {
            padding: 25px 0;
        }

        .sidebar-item {
            margin: 0 15px 8px;
            border-radius: var(--border-radius);
            transition: var(--transition);
            position: relative;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 16px 20px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: var(--transition);
            border-radius: var(--border-radius);
            color: var(--gray-600);
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }

        /* Dark Mode Styles */
        body.dark-mode .sidebar-link {
            color: var(--dark-mode-text);
        }

        .sidebar-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(79, 70, 229, 0.1), transparent);
            transition: left 0.3s;
        }

        .sidebar-link:hover::before {
            left: 100%;
        }

        .sidebar-link i {
            font-size: 20px;
            margin-right: 5px;
            color: var(--gray-500);
            transition: var(--transition);
            width: 20px;
            text-align: center;
        }

        .sidebar-item:hover {
            background: linear-gradient(135deg, rgb(47, 65, 77) 0%, #e0f2fe 100%);
            transform: translateX(5px);
            box-shadow: 0 0 5px rgba(255, 255, 255, 0.3);
            border-radius: var(--border-radius);
        }

        .sidebar-item:hover .sidebar-link i {
            color: var(--primary-color);
            transform: scale(1.1);
        }

        /* ===================== BUTTON STYLES ===================== */
        .btn {
            display: inline-block;
            font-weight: 400;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
            padding: 0.375rem 0.75rem;
            font-size: 0.9rem;
            line-height: 1.5;
            border-radius: 0.25rem;
            transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            text-decoration: none;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 0.2rem;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            border-radius: 8px;
            padding: 12px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #0069d9;
            border-color: #0062cc;
            transform: translateY(-2px);
        }

        .btn-group {
            display: flex;
            gap: 5px;
        }

        .btn-success {
            color: #fff;
            background-color: #28a745;
            border-color: #28a745;
        }

        .btn-success:hover {
            color: #fff;
            background-color: #218838;
            border-color: #1e7e34;
        }

        .btn-danger {
            color: #fff;
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .btn-danger:hover {
            color: #fff;
            background-color: #c82333;
            border-color: #bd2130;
        }

        /* ===================== PDF DOWNLOAD STYLES ===================== */
        .pdf-download {
            padding: 10px;
            text-align: center;
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

        /* Dark Mode Styles */
        body.dark-mode .language-selector {
            background: rgba(0, 0, 0, 0.3);
        }

        .language-label {
            margin-right: 12px;
            font-weight: 600;
            color: var(--white);
            font-size: 0.9rem;
        }

        /* Dark Mode Styles */
        body.dark-mode .language-label {
            color: var(--dark-mode-text);
        }

        .language-select {
            padding: 8px 15px;
            border-radius: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--gray-700);
            cursor: pointer;
            transition: var(--transition);
            outline: none;
        }

        /* Dark Mode Styles */
        body.dark-mode .language-select {
            background: var(--dark-mode-element);
            color: var(--dark-mode-text);
        }

        .language-select:hover,
        .language-select:focus {
            border-color: var(--white);
            background: var(--white);
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
            transform: scale(1.02);
        }

        .language-select option {
            background: var(--white);
            color: var(--gray-700);
            padding: 10px;
        }

        /* Dark Mode Styles */
        body.dark-mode .language-select option {
            background: var(--dark-mode-element);
            color: var(--dark-mode-text);
        }

        /* ===================== NAVBAR STYLES ===================== */
        #navbarSupportedContent {
            background: #1F262D !important;
        }

        /* Dark Mode Styles */
        body.dark-mode #navbarSupportedContent {
            background: #333 !important;
        }

        /* ===================== PAGE WRAPPER ===================== */
        .page-wrapper {
            background: #ffffff;
            transition: background 0.3s ease;
        }

        body.dark-mode .page-wrapper {
            background: rgb(46, 46, 46);
        }

        /* ===================== RESPONSIVE DESIGN ===================== */
        @media (max-width: 1024px) {
            .container-fluid {
                padding: 20px 15px;
            }

            .chart-container {
                width: 95%;
                padding: 25px;
            }
        }

        @media (max-width: 768px) {
            .form {
                padding: 10px;
                width: 95%;
            }

            .login-title {
                font-size: 1.75rem;
            }

            table {
                font-size: 14px;
                min-width: 600px;
            }

            th,
            td {
                padding: 8px;
            }

            .box {
                flex: 1 1 45%;
                min-width: 200px;
                padding: 25px 20px;
            }

            .box h3 {
                font-size: 2rem;
            }

            .box h6 {
                font-size: 0.9rem;
            }

            .chart-container {
                height: 35vh;
                padding: 20px;
            }

            .sidebar-link {
                font-size: 0.9rem;
                padding: 14px 18px;
            }
        }

        @media (max-width: 480px) {
            .form {
                width: 95%;
                padding: 5px;
                margin: 15px auto;
            }

            .login-title {
                font-size: 1.5rem;
                margin-bottom: 20px;
            }

            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
                font-size: 12px;
            }

            th,
            td {
                font-size: 12px;
                padding: 6px;
            }

            .box {
                flex: 1 1 100%;
                margin: 10px 0;
                padding: 20px 15px;
            }

            .box h3 {
                font-size: 1.8rem;
            }

            .box h6 {
                font-size: 0.85rem;
            }

            .chart-container {
                width: 98%;
                height: 30vh;
                padding: 15px;
                margin: 20px auto;
            }

            .row {
                gap: 10px;
                margin: 20px 0;
            }

            .container-fluid {
                padding: 15px 10px;
            }
        }

        /* ===================== SCROLL STYLES ===================== */
        ::-webkit-scrollbar {
            width: 14px;
        }

        ::-webkit-scrollbar-track {
            background: var(--gray-100);
        }

        /* Dark Mode Styles */
        body.dark-mode ::-webkit-scrollbar-track {
            background: #333;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, rgb(46, 56, 67), #6e869e);
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #6e869e, rgb(46, 56, 67));
        }

        /* ===================== LOADING ANIMATIONS ===================== */
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

        .box,
        .form,
        .chart-container {
            animation: fadeInUp 0.6s ease-out;
        }

        .box:nth-child(1) {
            animation-delay: 0.1s;
        }

        .box:nth-child(2) {
            animation-delay: 0.2s;
        }

        .box:nth-child(3) {
            animation-delay: 0.3s;
        }

        /* ===================== UTILITIES ===================== */
        .text-gradient {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* ====================== OTHERS STYLES FOR CREATE =============== */

        /* Nuevos estilos para el formulario dinámico */
        .form-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
            width: 90%;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }

        .section-title {
            color: #2c3e50;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid rgb(63, 116, 151);
        }

        .form-control {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: rgb(17, 160, 255);
            box-shadow: 0 0 0 0.2rem rgba(41, 26, 245, 0.25);
        }

        .room-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid rgb(63, 116, 151);
        }

        .image-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .image-preview-item {
            position: relative;
            width: 100px;
            height: 100px;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid #dee2e6;
        }

        .image-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .remove-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(220, 53, 69, 0.8);
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            font-size: 12px;
            cursor: pointer;
        }

        .add-room-btn {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            color: white;
            padding: 15px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .add-room-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }

        .remove-room-btn {
            background: #dc3545;
            border: none;
            color: white;
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 14px;
        }

        .file-input-wrapper {
            position: relative;
            display: inline-block;
            cursor: pointer;
            width: 100%;
        }

        .file-input-wrapper input[type=file] {
            position: absolute;
            left: -9999px;
        }

        .file-input-display {
            display: block;
            padding: 12px 15px;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            text-align: center;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .file-input-display:hover {
            border-color: rgb(39, 112, 146);
            background: rgb(242, 246, 255);
        }

        .main-images-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .main-image-item {
            position: relative;
            aspect-ratio: 1;
            border-radius: 10px;
            overflow: hidden;
            border: 2px solid #dee2e6;
        }

        .main-image-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Estilos para Google Maps */
        .map-container {
            position: relative;
            margin-bottom: 20px;
        }

        #map {
            height: 400px;
            width: 100%;
            border-radius: 10px;
            border: 2px solid #dee2e6;
        }

        .location-info {
            background: #e8f5e8;
            border: 1px solid #28a745;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }

        .location-info.empty {
            background: #fff3cd;
            border-color: #ffc107;
        }

        .search-box {
            position: absolute;
            top: 10px;
            left: 10px;
            right: 10px;
            z-index: 1000;
        }

        .search-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            font-size: 14px;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .search-input:focus {
            outline: none;
            border-color: rgb(26, 146, 245);
        }

        .map-instructions {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            text-align: center;
        }

        /* =========================== Dropdown list Rooms ========================== */
        .form-select {
            appearance: none;
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 12px 40px 12px 16px;
            font-size: 14px;
            font-weight: 500;
            color: #495057;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);

            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 12px center;
            background-repeat: no-repeat;
            background-size: 16px;
        }

        .form-select:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1), 0 4px 12px rgba(0, 0, 0, 0.08);
            transform: translateY(-1px);
        }

        .form-select:hover {
            border-color: #cbd5e1;
            background: linear-gradient(145deg, #f8f9fa, #ffffff);
        }

        /* Estilos para Leaflet */
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

    <script>
        const userPlanId = <?php echo json_encode($plan_id); ?>;
        const userPropCount = <?php echo json_encode($prop_count); ?>;
    </script>
</head>

<body>
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <div class="preloader">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div>
    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <div id="main-wrapper">
        <!-- ============================================================== -->
        <!-- Topbar header - style you can find in pages.scss -->
        <!-- ============================================================== -->
        <header class="topbar" data-navbarbg="skin5">
            <nav class="navbar top-navbar navbar-expand-md navbar-dark">
                <div class="navbar-header" data-logobg="skin5">
                    <!-- This is for the sidebar toggle which is visible on mobile only -->
                    <a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)"><i
                            class="ti-menu ti-close"></i></a>
                    <!-- ============================================================== -->
                    <!-- Logo -->
                    <!-- ============================================================== -->
                    <a class="navbar-brand" href="dashboard_vendedor.php">
                        <!-- Logo icon -->
                        <b class="logo-icon p-l-10">
                            <img src="assets/images/logo-icon.png" alt="homepage" class="light-logo" />
                        </b>
                        <!-- Logo text -->
                        <span class="logo-text">
                            <img src="assets/images/logo-text.png" alt="homepage" class="light-logo" />
                        </span>
                    </a>
                    <a class="topbartoggler d-block d-md-none waves-effect waves-light" href="javascript:void(0)"
                        data-toggle="collapse" data-target="#navbarSupportedContent"
                        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><i
                            class="ti-more"></i></a>
                </div>

                <div class="navbar-collapse collapse" id="navbarSupportedContent" data-navbarbg="skin5">
                    <ul class="navbar-nav float-left mr-auto">
                        <li class="nav-item d-none d-md-block">
                            <a class="nav-link sidebartoggler waves-effect waves-light" href="javascript:void(0)"
                                data-sidebartype="mini-sidebar">
                                <i class="mdi mdi-menu font-24"></i>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="d-none d-md-block">Crear Propiedades<i class="page-tittle"></i></span>
                                <span class="d-block d-md-none"><i class="fa fa-plus"></i></span>
                            </a>
                        </li>
                    </ul>

                    <ul class="navbar-nav float-right">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark pro-pic" href="#"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <img src="assets/images/users/1.jpg" alt="user" class="rounded-circle" width="31">
                                Hola, <?php echo htmlspecialchars($username); ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right user-dd animated">
                                <a class="dropdown-item" href="logout.p.php">
                                    <i class="fa fa-power-off m-r-5 m-l-5"></i>Cerrar Sesión
                                </a>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>

        <!-- Sidebar -->
        <?php
        require("left_sidebar/seller.php");
        ?>

        <!-- Page wrapper -->
        <div class="page-wrapper">
            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-12 d-flex no-block align-items-center">
                    </div>
                </div>
            </div>

            <!-- Formulario Principal -->
            <div class="container-fluid">
                <div class="text-center mb-4">
                    <h1 class="display-4 text-primary fw-bold">
                        <i class="fas fa-home me-3"></i> Crear Nueva Propiedad
                    </h1>
                    <p class="lead text-muted">Completa la informacion acerca de esta propiedad</p>
                </div>

                <form id="propertyForm" action="backend_create_vendedor.php" method="post" enctype="multipart/form-data">
                    <!-- Información Básica -->
                    <div class="form-card">
                        <h3 class="section-title">
                            <i class="fas fa-info-circle me-2"></i> Información básica
                        </h3>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Nombre de la Propiedad</label>
                                <input type="text" name="nombre" class="form-control" maxlength="50">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Precio ($)</label>
                                <input type="number" name="precio" class="form-control">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">Tamaño (m²)</label>
                                <input type="number" name="size" class="form-control">
                            </div>
                        </div>

                        <input type="hidden" name="fecha" value="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <!-- Ubicación con Leaflet -->
                    <div class="form-card">
                        <h3 class="section-title">
                            <i class="fas fa-map-marker-alt me-2"></i> Ubicación de la Propiedad
                        </h3>

                        <div class="map-instructions">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Instrucciones:</strong> Haz clic en el mapa para seleccionar la ubicación exacta de la propiedad.
                        </div>

                        <div class="map-container">
                            <div id="map"></div>
                        </div>

                        <div class="address-display">
                            <h6>Dirección seleccionada:</h6>
                            <p id="selected-address">Haz clic en el mapa para seleccionar una dirección</p>
                        </div>

                        <div class="coordinate-info">
                            <strong>Coordenadas:</strong> <span id="coordinates">No seleccionadas</span>
                        </div>

                        <!-- Campos ocultos para almacenar la ubicación -->
                        <input type="hidden" name="latitud" id="latitud" required>
                        <input type="hidden" name="longitud" id="longitud" required>
                        <input type="hidden" name="direccion_completa" id="direccion_completa" required>
                    </div>

                    <!-- Imágenes Principales -->
                    <div class="form-card">
                        <h3 class="section-title">
                            <i class="fas fa-images me-2"></i> Imagenes de la Propiedad
                        </h3>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Seleccionar Imágenes</label>
                            <div class="file-input-wrapper">
                                <input type="file" id="mainImages" name="main_images[]" multiple accept="image/*">
                                <label for="mainImages" class="file-input-display">
                                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                    <div>Clic para seleccionar una imagen</div>
                                </label>
                            </div>
                        </div>
                        <div id="mainImagesPreview" class="main-images-grid"></div>
                    </div>

                    <!-- Cuartos/Habitaciones -->
                    <div class="form-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="section-title mb-0">
                                <i class="fas fa-door-open me-2"></i> Cuartos y Espacios
                            </h3>
                            <button type="button" class="add-room-btn" onclick="addRoom()">
                                <i class="fas fa-plus me-2"></i>Agregar Cuarto
                            </button>
                        </div>

                        <div id="roomsContainer">
                            <!-- Los cuartos se agregarán dinámicamente aquí -->
                        </div>
                    </div>

                    <!-- Botón de Envío -->
                    <div class="text-center mb-5">
                        <button type="submit" name="submit" class="btn btn-primary btn-lg px-5">
                            <i class="fas fa-save me-2"></i> Crear Propiedad
                        </button>
                    </div>
                </form>

                <script>
                    
                    let roomCounter = 0;
                    let map;
                    let marker;
                    let selectedAddress = '';
                    let selectedLat = null;
                    let selectedLng = null;

                    
                    const EL_SALVADOR_BOUNDS = [
                        [12.0, -90.5], 
                        [14.5, -87.0] 
                    ];

                    
                    function initMap() {
                        
                        map = L.map('map').setView([13.7942, -88.8965], 8);

                        
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '© OpenStreetMap contributors',
                            maxZoom: 18
                        }).addTo(map);

                        
                        map.setMaxBounds(EL_SALVADOR_BOUNDS);
                        map.on('drag', function() {
                            map.panInsideBounds(EL_SALVADOR_BOUNDS, {
                                animate: false
                            });
                        });

                        
                        map.on('click', function(e) {
                            const lat = e.latlng.lat;
                            const lng = e.latlng.lng;

                            
                            if (lat >= EL_SALVADOR_BOUNDS[0][0] && lat <= EL_SALVADOR_BOUNDS[1][0] &&
                                lng >= EL_SALVADOR_BOUNDS[0][1] && lng <= EL_SALVADOR_BOUNDS[1][1]) {

                                setMarker(lat, lng);
                                getAddressFromCoordinates(lat, lng);
                            } else {
                                swal({
                                    title: "Ubicación no válida",
                                    text: "Por favor selecciona una ubicación dentro de El Salvador",
                                    icon: "warning",
                                    button: "Entendido",
                                });
                            }
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
                                    document.getElementById('direccion_completa').value = selectedAddress;
                                } else {
                                    selectedAddress = `Ubicación: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                                    document.getElementById('selected-address').textContent = selectedAddress;
                                    document.getElementById('direccion_completa').value = selectedAddress;
                                }
                            })
                            .catch(error => {
                                console.error('Error al obtener la dirección:', error);
                                selectedAddress = `Ubicación: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                                document.getElementById('selected-address').textContent = selectedAddress;
                                document.getElementById('direccion_completa').value = selectedAddress;
                            });
                    }

                    
                    document.getElementById('mainImages').addEventListener('change', function(e) {
                        const preview = document.getElementById('mainImagesPreview');
                        preview.innerHTML = '';

                        Array.from(e.target.files).forEach((file, index) => {
                            if (file.type.startsWith('image/')) {
                                const reader = new FileReader();
                                reader.onload = function(e) {
                                    const div = document.createElement('div');
                                    div.className = 'main-image-item';
                                    div.innerHTML = `
                        <img src="${e.target.result}" alt="Preview">
                        <button type="button" class="remove-image" onclick="removeMainImage(${index})">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                                    preview.appendChild(div);
                                };
                                reader.readAsDataURL(file);
                            }
                        });
                    });

                    function removeMainImage(index) {
                        const input = document.getElementById('mainImages');
                        const dt = new DataTransfer();
                        const files = Array.from(input.files);

                        files.forEach((file, i) => {
                            if (i !== index) {
                                dt.items.add(file);
                            }
                        });

                        input.files = dt.files;
                        input.dispatchEvent(new Event('change'));
                    }
                    //Toda esta funcion permitía desabilitar los campos, así como redireccionar directamente al vendedor de nuevo al dashboard, pero no es del todo seguro puesto que necesita otra validación para evitar problemas, solo queda de ejemplo.

                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    //
                    
                    
                    
                    
                    
                    
                    //
                    
                    
                    



                    function addRoom() {
                        if (userPlanId === 1 || userPlanId === 4) {
                            return;
                        }
                        
                        if (userPlanId === 2) {
                            const rooms = document.querySelectorAll('.room-section');
                            if (rooms.length >= 3) {
                                swal({
                                    title: "Límite de cuartos",
                                    text: "Tu plan HomeSafe Plus solo permite agregar hasta 3 cuartos por propiedad.",
                                    icon: "warning",
                                    button: "Cerrar"
                                });
                                return;
                            }
                        }
                        roomCounter++;
                        const container = document.getElementById('roomsContainer');

                        const roomDiv = document.createElement('div');
                        roomDiv.className = 'room-section';
                        roomDiv.id = `room-${roomCounter}`;

                        roomDiv.innerHTML = `
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0 text-primary">
                                    <i class="fas fa-bed me-2"></i>Cuarto #${roomCounter}
                                </h5>
                                <button type="button" class="remove-room-btn" onclick="removeRoom(${roomCounter})">
                                    <i class="fas fa-trash me-1"></i>Eliminar
                                </button>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Tipo de Cuarto</label>
                                    <select name="room_types[]" class="form-select">
                                        <option value="Dormitorio">Dormitorio</option>
                                        <option value="Sala">Sala</option>
                                        <option value="Comedor">Comedor</option>
                                        <option value="Cocina">Cocina</option>
                                        <option value="Baño">Baño</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Nombre del Cuarto</label>
                                    <input type="text" name="room_names[]" class="form-control">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Imagenes del Cuarto</label>
                                    <input type="file" name="room_images_${roomCounter}[]" 
                                        class="form-control" multiple accept="image/*" 
                                        onchange="previewRoomImages(this, ${roomCounter})">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Descripcion del Cuarto</label>
                                <textarea name="room_descriptions[]" class="form-control" rows="2"></textarea>
                            </div>
                            
                            <div id="roomImagesPreview-${roomCounter}" class="image-preview"></div>
                        `;

                        container.appendChild(roomDiv);
                    }

                    document.addEventListener('DOMContentLoaded', function() {
                        if (userPlanId === 1 || userPlanId === 4) {
                            
                            const btnAddRoom = document.querySelector('.add-room-btn');
                            if (btnAddRoom) {
                                btnAddRoom.style.display = 'none';
                            }

                            
                            const container = document.getElementById('roomsContainer');
                            container.innerHTML = '<div class="alert alert-warning" role="alert" style="margin-bottom: 15px;">Tu plan HomeSafe Lite no permite modificar o agregar cuartos y espacios.</div>';
                        } else {
                            addRoom();
                        }
                        initMap();
                    });

                    function removeRoom(roomId) {
                        const roomElement = document.getElementById(`room-${roomId}`);
                        if (roomElement) {
                            roomElement.remove();
                        }
                    }

                    function previewRoomImages(input, roomId) {
                        const preview = document.getElementById(`roomImagesPreview-${roomId}`);
                        preview.innerHTML = '';

                        Array.from(input.files).forEach((file, index) => {
                            if (file.type.startsWith('image/')) {
                                const reader = new FileReader();
                                reader.onload = function(e) {
                                    const div = document.createElement('div');
                                    div.className = 'image-preview-item';
                                    div.innerHTML = `
                        <img src="${e.target.result}" alt="Preview">
                        <button type="button" class="remove-image" onclick="removeRoomImage(${roomId}, ${index})">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                                    preview.appendChild(div);
                                };
                                reader.readAsDataURL(file);
                            }
                        });
                    }

                    function removeRoomImage(roomId, imageIndex) {
                        console.log(`Removing image ${imageIndex} from room ${roomId}`);
                    }

                    
                    document.addEventListener('DOMContentLoaded', function() {
                        addRoom();
                        initMap();
                    });

                    
                    document.getElementById('propertyForm').addEventListener('submit', function(e) {
                        function showAlert(text) {
                            swal({
                                title: "Error",
                                text: text,
                                icon: "warning",
                                button: "OK"
                            });
                        }

                        
                        const nombre = this.nombre.value.trim();
                        if (!nombre) {
                            e.preventDefault();
                            showAlert("El nombre de la propiedad es obligatorio.");
                            return;
                        }
                        if (nombre.length > 50) {
                            e.preventDefault();
                            showAlert("El nombre de la propiedad no puede exceder 50 caracteres.");
                            return;
                        }

                        
                        const precio = Number(this.precio.value);
                        if (!this.precio.value.trim()) {
                            e.preventDefault();
                            showAlert("El precio es obligatorio.");
                            return;
                        }
                        if (isNaN(precio) || precio < 1 || precio > 1000000) {
                            e.preventDefault();
                            showAlert("El precio debe ser un número entre 1 y 1,000,000.");
                            return;
                        }

                        
                        const descripcion = this.descripcion.value.trim();
                        if (!descripcion) {
                            e.preventDefault();
                            showAlert("La descripción es obligatoria.");
                            return;
                        }

                        
                        const size = Number(this.size.value);
                        if (!this.size.value.trim()) {
                            e.preventDefault();
                            showAlert("El tamaño es obligatorio.");
                            return;
                        }
                        if (isNaN(size) || size < 1 || size > 10000) {
                            e.preventDefault();
                            showAlert("El tamaño debe ser un número entre 1 y 10,000.");
                            return;
                        }

                        
                        const latitud = document.getElementById('latitud').value;
                        const longitud = document.getElementById('longitud').value;
                        if (!latitud || !longitud) {
                            e.preventDefault();
                            showAlert("Debes seleccionar una ubicación en el mapa.");
                            return;
                        }

                        
                        const mainImages = document.getElementById('mainImages').files;
                        if (mainImages.length === 0) {
                            e.preventDefault();
                            showAlert("Necesitas seleccionar al menos una imagen principal.");
                            return;
                        }

                        
                        const rooms = document.querySelectorAll('.room-section');
                        if (userPlanId !== 1 && rooms.length === 0) {
                            e.preventDefault();
                            showAlert("Necesitas agregar al menos un cuarto.");
                            return;
                        }

                        
                        for (let i = 0; i < rooms.length; i++) {
                            const room = rooms[i];
                            const roomNameInput = room.querySelector('input[name="room_names[]"]');
                            if (!roomNameInput.value.trim()) {
                                e.preventDefault();
                                showAlert(`El nombre del cuarto #${i + 1} es obligatorio.`);
                                return;
                            }
                            if (roomNameInput.value.length > 50) {
                                e.preventDefault();
                                showAlert(`El nombre del cuarto #${i + 1} no puede exceder 50 caracteres.`);
                                return;
                            }
                        }
                    });
                </script>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="assets/libs/popper.js/dist/umd/popper.min.js"></script>
    <script src="assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js"></script>
    <script src="assets/extra-libs/sparkline/sparkline.js"></script>
    <script src="dist/js/waves.js"></script>
    <script src="dist/js/sidebarmenu.js"></script>
    <script src="dist/js/custom.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>
</body>

</html>