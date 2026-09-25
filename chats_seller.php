<?php
include('logic_dashboard_vendedor.php');
include('0_ESvalidacion_Seller.php');
include('chat_logica.php');
require_once('0_ESvalidar_suscripcion.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon.png">
    <title>CRUD Vendedor - HomeSafe</title>
    <!-- Custom CSS -->
    <link href="assets/libs/flot/css/float-chart.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="dist/css/style.min.css" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
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
        .row {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin: 30px 0;
            padding: 0 10px;
        }

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
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            color: #fff;
            background-color: #0069d9;
            border-color: #0062cc;
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

        .edit-button {
            display: inline-block;
            padding: 8px 16px;
            background-color: rgb(63, 116, 151);
            /* Verde moderno */
            color: white;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s ease-in-out, transform 0.2s ease;
            border: none;
            cursor: pointer;
        }

        .edit-button:hover {
            background-color: rgb(53, 96, 126);
            /* Color más oscuro al pasar el cursor */
            transform: translateY(-2px);
            /* Efecto sutil de elevación */
            color: rgb(200, 233, 255);
        }

        /* ===================== ESTILOS PARA PAGINACIÓN Y ORDENAMIENTO ===================== */

        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0;
            padding: 15px;
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
        }

        body.dark-mode .pagination-container {
            background: var(--dark-mode-element);
        }

        .pagination {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            gap: 5px;
        }

        .pagination li {
            display: inline-block;
        }

        .pagination a,
        .pagination span {
            display: block;
            padding: 8px 12px;
            text-decoration: none;
            border: 1px solid #ddd;
            border-radius: 4px;
            color: #333;
            transition: all 0.3s ease;
        }

        body.dark-mode .pagination a,
        body.dark-mode .pagination span {
            color: var(--dark-mode-text);
            border-color: #555;
        }

        .pagination a:hover {
            background-color: #f5f5f5;
            border-color: #999;
        }

        body.dark-mode .pagination a:hover {
            background-color: #555;
        }

        .pagination .current {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }

        .pagination .disabled {
            color: #ccc;
            cursor: not-allowed;
        }

        .sortable-header {
            cursor: pointer;
            user-select: none;
            position: relative;
            transition: background-color 0.3s ease;
        }

        .sortable-header:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .sortable-header::after {
            content: ' ↕';
            font-size: 12px;
            opacity: 0.5;
        }

        .sortable-header.asc::after {
            content: ' ↑';
            opacity: 1;
        }

        .sortable-header.desc::after {
            content: ' ↓';
            opacity: 1;
        }

        .records-info {
            font-size: 14px;
            color: #666;
        }

        body.dark-mode .records-info {
            color: var(--dark-mode-text);
        }

        .records-per-page {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .records-per-page select {
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
        }

        body.dark-mode .records-per-page select {
            background: var(--dark-mode-element);
            color: var(--dark-mode-text);
            border-color: #555;
        }
    </style>

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
                            <!--You can put here icon as well 
                            <!-- Dark Logo icon -->
                            <img src="assets/images/logo-icon.png" alt="homepage" class="light-logo" />

                        </b>
                        <!--End Logo icon -->
                        <!-- Logo text -->
                        <span class="logo-text">
                            <!-- dark Logo text -->
                            <img src="assets/images/logo-text.png" alt="homepage" class="light-logo" />

                        </span>
                        <!-- Logo icon -->
                        <!-- <b class="logo-icon"> -->
                        <!--You can put here icon as well 
                        <!-- Dark Logo icon -->
                        <!-- <img src="assets/images/logo-text.png" alt="homepage" class="light-logo" /> -->

                        <!-- </b> -->
                        <!--End Logo icon -->
                    </a>
                    <!-- ============================================================== -->
                    <!-- End Logo -->
                    <!-- ============================================================== -->
                    <!-- ============================================================== -->
                    <!-- Toggle which is visible on mobile only -->
                    <!-- ============================================================== -->
                    <a class="topbartoggler d-block d-md-none waves-effect waves-light" href="javascript:void(0)"
                        data-toggle="collapse" data-target="#navbarSupportedContent"
                        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><i
                            class="ti-more"></i></a>
                </div>
                <!-- ============================================================== -->
                <!-- End Logo -->
                <!-- ============================================================== -->
                <div class="navbar-collapse collapse" id="navbarSupportedContent" data-navbarbg="skin5">
                    <!-- ============================================================== -->
                    <!-- toggle and nav items -->
                    <!-- ============================================================== -->
                    <ul class="navbar-nav float-left mr-auto">
                        <li class="nav-item d-none d-md-block"><a
                                class="nav-link sidebartoggler waves-effect waves-light" href="javascript:void(0)"
                                data-sidebartype="mini-sidebar"><i class="mdi mdi-menu font-24"></i></a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="d-none d-md-block">Solicitudes para chatear<i class="page-tittle"></i></span>
                            </a>
                        </li>
                    </ul>
                    <!-- ============================================================== -->
                    <!-- Right side toggle and nav items -->
                    <!-- ============================================================== -->
                    <ul class="navbar-nav float-right">
                        <ul class="navbar-nav float-right">
                            <div class="language-selector">
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
                                        window.location.href = 'chats_seller.php';
                                    } else if (lang === 'en') {
                                        window.location.href = 'EN_chats_seller.php';
                                    }
                                }
                            </script>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark pro-pic" href="#"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <img src="assets/images/users/1.jpg" alt="user" class="rounded-circle" width="31">
                                    Hola, <?php echo htmlspecialchars($username); ?>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right user-dd animated">
                                    <!-- <a class="dropdown-item" href="javascript:void(0)"><i class="ti-user m-r-5 m-l-5"></i>-->
                                    <!--     My Profile</a> -->
                                    <!-- <div class="dropdown-divider"></div> -->
                                    <a class="dropdown-item" href="logout.p.php"><i class="fa fa-power-off m-r-5 m-l-5"></i>
                                        Cerrar Sesión</a>
                                </div>
                            </li>
                        </ul>
                </div>
            </nav>
        </header>
        <!-- ============================================================== -->
        <!-- End Topbar header -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <?php
        require("left_sidebar/seller.php");
        ?>
        <!-- ============================================================== -->
        <!-- Page wrapper  -->
        <!-- ============================================================== -->
        <div class="page-wrapper">
            <!-- ============================================================== -->
            <!-- Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-12 d-flex no-block align-items-center">
                    </div>
                </div>
            </div>
            <div class="form">
                <h2 class="login-title">Solicitudes de Chat</h2>

                <?php
                
                $records_per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
                $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

                $total_records = count($solicitudes);
                $total_pages = ceil($total_records / $records_per_page);
                $offset = ($current_page - 1) * $records_per_page;

                
                $solicitudes_page = array_slice($solicitudes, $offset, $records_per_page);
                ?>

                <?php if (empty($solicitudes_page)) : ?>
                    <div style="padding: 20px; background-color: #f8d7da; color: #721c24; border-radius: 8px; border: 1px solid #f5c6cb; text-align: center; margin-top: 20px;">
                        <strong>No tienes solicitudes de chat.</strong>
                    </div>
                <?php else : ?>
                    <div class="pagination-container">
                        <div class="records-info">
                            Mostrando <?php echo $offset + 1; ?> - <?php echo min($offset + $records_per_page, $total_records); ?>
                            de <?php echo $total_records; ?> solicitudes
                        </div>
                        <div class="records-per-page">
                            <label>Registros por página:</label>
                            <select onchange="changePerPage(this.value)">
                                <option value="10" <?= $records_per_page == 10 ? 'selected' : ''; ?>>10</option>
                                <option value="25" <?= $records_per_page == 25 ? 'selected' : ''; ?>>25</option>
                                <option value="50" <?= $records_per_page == 50 ? 'selected' : ''; ?>>50</option>
                                <option value="100" <?= $records_per_page == 100 ? 'selected' : ''; ?>>100</option>
                            </select>
                        </div>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Propiedad</th>
                                <th>Acción</th>
                                <th>Cancelar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($solicitudes_page as $sol) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($sol['id_chat']) ?></td>
                                    <td><?= htmlspecialchars($sol['user_gmail_customer']) ?></td>
                                    <td><?= htmlspecialchars($sol['propiedad'] ?? 'Sin nombre') ?></td>
                                    <td>
                                        <?php if (in_array($sol['id_chat'], $chats_aceptados)): ?>
                                            <form method="POST" action="chat/index.php">
                                                <input type="hidden" name="id_chat" value="<?= $sol['id_chat'] ?>">
                                                <button type="submit" class="btn btn-success">Ir al chat</button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" action="chat_aceptar.php">
                                                <input type="hidden" name="chat_id" value="<?= $sol['id_chat'] ?>">
                                                <button type="submit" name="aceptar" class="btn btn-primary">Aceptar</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="POST" action="chat_borrar.php">
                                            <input type="hidden" name="chat_id" value="<?= $sol['id_chat'] ?>">
                                            <button type="submit" name="rechazar" class="btn btn-primary">Rechazar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>


                    <!-- Controles de paginación -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination-container">
                            <ul class="pagination">
                                <?php if ($current_page > 1): ?>
                                    <li><a href="?page=1&per_page=<?= $records_per_page ?>">Primera</a></li>
                                    <li><a href="?page=<?= $current_page - 1 ?>&per_page=<?= $records_per_page ?>">Anterior</a></li>
                                <?php endif; ?>

                                <?php
                                $start_page = max(1, $current_page - 2);
                                $end_page = min($total_pages, $current_page + 2);
                                for ($i = $start_page; $i <= $end_page; $i++):
                                ?>
                                    <li>
                                        <?php if ($i == $current_page): ?>
                                            <span class="current"><?= $i ?></span>
                                        <?php else: ?>
                                            <a href="?page=<?= $i ?>&per_page=<?= $records_per_page ?>"><?= $i ?></a>
                                        <?php endif; ?>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($current_page < $total_pages): ?>
                                    <li><a href="?page=<?= $current_page + 1 ?>&per_page=<?= $records_per_page ?>">Siguiente</a></li>
                                    <li><a href="?page=<?= $total_pages ?>&per_page=<?= $records_per_page ?>">Última</a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>



            <!-- ============================================================== -->
            <!-- End Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- End Wrapper -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- All Jquery -->
            <!-- ============================================================== -->
            <script src="assets/libs/jquery/dist/jquery.min.js"></script>
            <!-- Bootstrap tether Core JavaScript -->
            <script src="assets/libs/popper.js/dist/umd/popper.min.js"></script>
            <script src="assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
            <script src="assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js"></script>
            <script src="assets/extra-libs/sparkline/sparkline.js"></script>
            <!--Wave Effects -->
            <script src="dist/js/waves.js"></script>
            <!--Menu sidebar -->
            <script src="dist/js/sidebarmenu.js"></script>
            <!--Custom JavaScript -->
            <script src="dist/js/custom.min.js"></script>
            <!--This page JavaScript -->
            <!-- <script src="dist/js/pages/dashboards/dashboard1.js"></script> -->
            <!-- Charts js Files -->
            <script src="assets/libs/flot/excanvas.js"></script>
            <script src="assets/libs/flot/jquery.flot.js"></script>
            <script src="assets/libs/flot/jquery.flot.pie.js"></script>
            <script src="assets/libs/flot/jquery.flot.time.js"></script>
            <script src="assets/libs/flot/jquery.flot.stack.js"></script>
            <script src="assets/libs/flot/jquery.flot.crosshair.js"></script>
            <script src="assets/libs/flot.tooltip/js/jquery.flot.tooltip.min.js"></script>
            <script src="dist/js/pages/chart/chart-page-init.js"></script>

            <script>
                function changePerPage(perPage) {
                    const urlParams = new URLSearchParams(window.location.search);
                    urlParams.set('per_page', perPage);
                    urlParams.set('page', '1'); 
                    window.location.search = urlParams.toString();
                }
            </script>
        </div>
    </div>
</body>

</html>