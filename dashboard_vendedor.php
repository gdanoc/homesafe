<?php
session_start();
include('0_ESvalidacion_Seller.php'); //ESPAÑOL 
include('logic_dashboard_vendedor.php');

$conn = pg_connect("host=localhost dbname=homesafe user=postgres password=Info2025/*-");
$result_planes = pg_query($conn, "SELECT id, nombre, descripcion, precio FROM planes");

$planes = [];
if ($result_planes) {
    while ($row = pg_fetch_assoc($result_planes)) {
        $planes[$row['id']] = [
            'nombre' => $row['nombre'],
            'descripcion' => $row['descripcion'],
            'precio' => floatval($row['precio']),
            'duracion' => ($row['id'] == 4) ? '21 días' : '1 mes'  
        ];
    }
}
pg_close($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pago_paypal']) && $_POST['pago_paypal'] === 'completado') {
    $user_email = $_SESSION['email'];
    $paypal_transaction_id = $_POST['paypal_transaction_id'] ?? null;
    $telefono = trim($_POST['telefono'] ?? '');
    $plan_id_post = $_POST['plan_id'] ?? null;

    
    $ya_tuvo_trial = false;
    $conn_check = pg_connect("host=localhost dbname=homesafe user=postgres password=Info2025/*-");
    if ($conn_check) {
        $query_trial = "SELECT COUNT(*) FROM suscripcion WHERE mail_user = $1 AND tuvo_plan_trial = 'si'";
        $result_trial = pg_query_params($conn_check, $query_trial, array($user_email));
        if ($result_trial) {
            $row_trial = pg_fetch_row($result_trial);
            $ya_tuvo_trial = ($row_trial[0] > 0);
        }
        pg_close($conn_check);
    }

    if (!$plan_id_post || !$paypal_transaction_id) {
        $_SESSION['error_msg'] = "Datos inválidos para procesar la suscripción.";
    } elseif (!preg_match('/^\d{4}-\d{4}$/', $telefono)) {
        $_SESSION['error_msg'] = "El teléfono debe tener el formato XXXX-XXXX.";
    } elseif (!in_array($plan_id_post, [1, 2, 3, 4])) {
        $_SESSION['error_msg'] = "Plan seleccionado no válido. Por favor selecciona un plan válido.";
    } elseif ($plan_id_post == 4 && $ya_tuvo_trial) {
        $_SESSION['error_msg'] = "El plan de prueba solo puede usarse una vez. Por favor selecciona un plan de pago.";
    }

    if (isset($_SESSION['error_msg'])) {
        
        header('Location: dashboard_vendedor.php');
        exit;
    }

    
    $conn = pg_connect("host=localhost dbname=homesafe user=postgres password=Info2025/*-");
    if (!$conn) {
        die("Conexión fallida: " . pg_last_error());
    }

    $fecha_suscrito = date('Y-m-d H:i:s');
    if ($plan_id_post == 4) {
        $fecha_vencimiento = date('Y-m-d H:i:s', strtotime("+21 days"));
        $tuvo_plan_trial = 'si';
    } else {
        $fecha_vencimiento = date('Y-m-d H:i:s', strtotime("+1 month"));
        
        $query_trial_flag = "SELECT tuvo_plan_trial FROM suscripcion WHERE mail_user = $1 ORDER BY fecha_vencimiento DESC LIMIT 1";
        $result_trial_flag = pg_query_params($conn, $query_trial_flag, array($user_email));
        if ($result_trial_flag && pg_num_rows($result_trial_flag) > 0) {
            $row_flag = pg_fetch_assoc($result_trial_flag);
            $tuvo_plan_trial = ($row_flag['tuvo_plan_trial'] === 'si') ? 'si' : 'no';
        } else {
            $tuvo_plan_trial = 'no';
        }
    }

    
    $query = "SELECT id FROM suscripcion WHERE mail_user = $1 ORDER BY fecha_vencimiento DESC LIMIT 1";
    $result = pg_query_params($conn, $query, array($user_email));

    if (pg_num_rows($result) > 0) {
        
        $row = pg_fetch_assoc($result);
        $update = "UPDATE suscripcion SET fecha_suscrito = $1, fecha_vencimiento = $2, telefono = $3, paypal_transaction_id = $4, plan_id = $5, tuvo_plan_trial = $6 WHERE id = $7";
        $params = array($fecha_suscrito, $fecha_vencimiento, $telefono, $paypal_transaction_id, $plan_id_post, $tuvo_plan_trial, $row['id']);
        $res = pg_query_params($conn, $update, $params);
    } else {
        
        $insert = "INSERT INTO suscripcion (mail_user, telefono, fecha_suscrito, fecha_vencimiento, paypal_transaction_id, plan_id, tuvo_plan_trial) VALUES ($1, $2, $3, $4, $5, $6, $7)";
        $params = array($user_email, $telefono, $fecha_suscrito, $fecha_vencimiento, $paypal_transaction_id, $plan_id_post, $tuvo_plan_trial);
        $res = pg_query_params($conn, $insert, $params);
    }

    
    $update_rol = "UPDATE accounts SET id_rol = 2 WHERE email = $1";
    pg_query_params($conn, $update_rol, array($user_email));

    pg_close($conn);

    
    $_SESSION['suscripcion_renovada'] = true;
    header('Location: dashboard_vendedor.php');
    exit;
}
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
    <script
        src="https://www.paypal.com/sdk/js?client-id=AdYdJDj0e0M467JdootgS5YO3GOZrS3_H-BFEJcau4KTp1uRJb3JSRyVMD13ThlMy5ojBz-__S5hVTb9&currency=USD">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


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
            align-items: center;
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
            max-width: 140px;
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
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
            margin-right: 5px;
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

        /*  .topbar .top-navbar .navbar-nav>.nav-item>.nav-link {
    line-height: 17px;
}

/* ===================== PERFIL DE USUARIO RESPONSIVE (ESENCIAL) ===================== */

        /* Contenedor principal del perfil */
        .collapse-profile {
            display: flex;
            align-items: center;
            max-width: 250px;
            padding: 8px 12px;
            white-space: nowrap;
        }

        /* Link del perfil */
        .collapse-profile a.nav-link {
            display: flex !important;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            min-width: 0;
            white-space: nowrap;
            overflow: hidden;
        }

        /* Imagen del perfil */
        .collapse-profile img {
            width: 32px !important;
            height: 32px !important;
            border-radius: 50% !important;
            flex-shrink: 0;
            /* No se reduce */
        }

        /* Texto del nombre */
        .collapse-profile .user-text {
            font-size: 0.9rem;
            text-overflow: ellipsis;
            overflow: hidden;
            max-width: 120px;
            min-width: 0;
        }

        /* ===================== PERFIL DE USUARIO RESPONSIVE (ESENCIAL) ===================== */

        /* Contenedor principal del perfil */
        .collapse-profile {
            display: flex;
            align-items: center;
            max-width: 250px;
            padding: 8px 12px;
            white-space: nowrap;
        }

        /* Link del perfil */
        .collapse-profile a.nav-link {
            display: flex !important;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            min-width: 0;
            white-space: nowrap;
            overflow: hidden;
        }

        /* Imagen del perfil */
        .collapse-profile img {
            width: 32px !important;
            height: 32px !important;
            border-radius: 50% !important;
            flex-shrink: 0;
            /* No se reduce */
        }

        /* Texto del nombre */
        .collapse-profile .user-text {
            font-size: 0.9rem;
            text-overflow: ellipsis;
            overflow: hidden;
            white-space: nowrap;
            max-width: 140px;
            min-width: 0;
            line-height: 1.2;
            color: rgba(179, 179, 179, 0.95);
            transition: all 0.3s ease;
        }

        body.dark-mode .collapse-profile .user-text {
            color: var(--dark-mode-text);
        }

        /* ===================== MEDIA QUERIES ESENCIALES ===================== */

        /* Tablets grandes */
        @media (max-width: 991px) {
            .collapse-profile {
                max-width: 200px;
                padding: 6px 10px;
            }

            .collapse-profile .user-text {
                max-width: 110px;
                font-size: 0.85rem;
                font-weight: 500;
            }

            .collapse-profile img {
                width: 30px !important;
                height: 30px !important;
            }
        }

        /* Tablets */
        @media (max-width: 768px) {
            .collapse-profile {
                max-width: 170px;
                padding: 5px 8px;
            }

            .collapse-profile .user-text {
                max-width: 95px;
                font-size: 0.8rem;
                font-weight: 500;
            }

            .collapse-profile img {
                width: 28px !important;
                height: 28px !important;
            }

            .collapse-profile a.nav-link {
                gap: 6px;
                padding: 5px 8px;
            }
        }

        /* Móviles grandes */
        @media (max-width: 576px) {
            .collapse-profile {
                max-width: 150px;
                padding: 4px 6px;
            }

            .collapse-profile .user-text {
                max-width: 85px;
                font-size: 0.75rem;
                font-weight: 500;
            }

            .collapse-profile img {
                width: 26px !important;
                height: 26px !important;
            }

            .collapse-profile a.nav-link {
                gap: 5px;
                padding: 4px 6px;
            }
        }

        /* Móviles pequeños */
        @media (max-width: 480px) {
            .collapse-profile {
                max-width: 130px;
                padding: 3px 5px;
            }

            .collapse-profile .user-text {
                max-width: 75px;
                font-size: 0.7rem;
                font-weight: 500;
            }

            .collapse-profile img {
                width: 24px !important;
                height: 24px !important;
            }

            .collapse-profile a.nav-link {
                gap: 4px;
                padding: 3px 5px;
            }
        }

        /* LANGUAGE*/


        .language-selector button.language-select,
        .language-selector select.language-select {
            font-size: 1rem;
            padding: 6px 12px;
            cursor: pointer;
            white-space: nowrap;
        }

        /* Ajustes para pantallas medianas */
        @media (max-width: 768px) {
            .language-selector {
                max-width: 250px;
                gap: 8px;
            }

            .language-selector button.language-select,
            .language-selector select.language-select {
                font-size: 0.9rem;
                padding: 5px 10px;
            }
        }

        /* Ajustes para pantallas pequeñas */
        @media (max-width: 480px) {
            .language-selector {
                flex-wrap: wrap;
                /* permite que los elementos se apilen */
                max-width: 100%;
                gap: 6px;
            }

            .language-selector button.language-select,
            .language-selector select.language-select {
                /*flex: 1 1 100%;*/
                /* que ocupen todo el ancho disponible */
                font-size: 0.85rem;
                padding: 5px 8px;
                white-space: normal;
                /* para que el texto pueda partirse */
            }
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

        /* ===================== PAYPAL SECTION STYLES ===================== */
        .checkout-container {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
        }

        /* Dark Mode Styles */
        body.dark-mode .checkout-container {
            background-color: var(--dark-mode-element);
            box-shadow: 0 0 15px var(--dark-mode-shadow);
            color: var(--dark-mode-text);
        }

        .order-summary {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
        }

        /* Dark Mode Styles */
        body.dark-mode .order-summary {
            background-color: var(--dark-mode-element);
            box-shadow: 0 0 10px var(--dark-mode-shadow);
            color: var(--dark-mode-text);
        }

        .subscription-item {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
            transition: var(--transition);
        }

        /* Dark Mode Styles */
        body.dark-mode .subscription-item {
            border-bottom: 1px solid var(--dark-mode-border);
        }

        .subscription-item:last-child {
            border-bottom: none;
        }

        #paypal-button-container {
            margin-top: 20px;
        }

        .info-row {
            background-color: #fff;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #dee2e6;
            transition: var(--transition);
        }

        /* Dark Mode Styles */
        body.dark-mode .info-row {
            background-color: var(--dark-mode-element);
            border: 1px solid var(--dark-mode-border);
            color: var(--dark-mode-text);
        }

        .info-row label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
            display: block;
            transition: var(--transition);
        }

        /* Dark Mode Styles */
        body.dark-mode .info-row label {
            color: var(--dark-mode-text);
        }

        .info-row p {
            margin: 0;
            color: #6c757d;
            transition: var(--transition);
        }

        /* Dark Mode Styles */
        body.dark-mode .info-row p {
            color: #b0b0b0;
        }

        .plan-highlight {
            background-color: #1F262D;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            transition: var(--transition);
        }

        /* Dark Mode Styles */
        body.dark-mode .plan-highlight {
            background-color: #2a2a2a;
            border: 1px solid var(--dark-mode-border);
        }

        .plan-highlight h5 {
            margin: 0 0 10px 0;
            font-weight: bold;
            color: #10a37f;
            transition: var(--transition);
        }

        /* Dark Mode Styles */
        body.dark-mode .plan-highlight h5 {
            color: #4ade80;
        }

        .plan-highlight p {
            margin: 0;
            opacity: 0.9;
            color: white;
            transition: var(--transition);
        }

        /* Dark Mode Styles */
        body.dark-mode .plan-highlight p {
            color: var(--dark-mode-text);
        }

        .price-display {
            font-size: 1em;
            font-weight: bold;
            color: #28a745;
            margin-top: 10px;
            transition: var(--transition);
        }

        /* Dark Mode Styles */
        body.dark-mode .price-display {
            color: #4ade80;
        }

        .wow-outer {
            margin-bottom: 30px;
        }

        .wow {
            animation: slideInDown 0.8s ease-out;
        }

        @keyframes slideInDown {
            0% {
                transform: translateY(-30px);
                opacity: 0;
            }

            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .payment-instructions {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 12px 16px;
            margin-bottom: 15px;
            border-radius: 0 6px 6px 0;
            transition: var(--transition);
        }

        /* Dark Mode Styles */
        body.dark-mode .payment-instructions {
            background-color: rgba(33, 150, 243, 0.1);
            border-left: 4px solid #64b5f6;
            color: var(--dark-mode-text);
        }

        .payment-instructions i {
            color: #2196f3;
            margin-right: 8px;
            transition: var(--transition);
        }

        /* Dark Mode Styles */
        body.dark-mode .payment-instructions i {
            color: #64b5f6;
        }

        /* ===================== FORM CONTROLS DARK MODE ===================== */
        .form-control {
            transition: var(--transition);
        }

        /* Dark Mode Styles */
        body.dark-mode .form-control {
            background-color: var(--dark-mode-element);
            border: 1px solid var(--dark-mode-border);
            color: var(--dark-mode-text);
        }

        body.dark-mode .form-control:focus {
            background-color: var(--dark-mode-element);
            border-color: #64b5f6;
            color: var(--dark-mode-text);
            box-shadow: 0 0 0 0.2rem rgba(100, 181, 246, 0.25);
        }

        body.dark-mode .form-control::placeholder {
            color: #888;
        }

        /* ===================== ALERT DARK MODE ===================== */
        .alert {
            transition: var(--transition);
        }

        body.dark-mode .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            border-color: rgba(220, 53, 69, 0.3);
            color: #ff6b6b;
        }

        /* ===================== SECTION DARK MODE ===================== */
        .section {
            transition: var(--transition);
        }

        body.dark-mode .section {
            background-color: transparent;
            color: var(--dark-mode-text);
        }

        body.dark-mode .container {
            color: var(--dark-mode-text);
        }

        /* ===================== VIEW SWITCHER STYLES - WHITE MINIMALIST ===================== */
        .view-switcher-container {
            display: inline-flex;
            align-items: center;
            margin: 0 10px;
            position: relative;
        }

        .btn-view-switch {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: rgba(255, 255, 255, 0.95);
            color: #333;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            white-space: nowrap;
        }

        /* Dark Mode Styles */
        body.dark-mode .btn-view-switch {
            background: rgba(255, 255, 255, 0.9);
            color: #333;
            border: 2px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .btn-view-switch::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 0, 0, 0.05), transparent);
            transition: left 0.5s;
        }

        .btn-view-switch:hover::before {
            left: 100%;
        }

        .btn-view-switch:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            text-decoration: none;
            color: #333;
            background: rgba(255, 255, 255, 1);
        }

        body.dark-mode .btn-view-switch:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
            background: rgba(255, 255, 255, 0.95);
            color: #333;
        }

        .btn-view-switch:active {
            transform: translateY(0) scale(0.98);
        }

        .switch-text {
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            font-size: 0.85rem;
            color: inherit;
        }

        .switch-icon {
            font-size: 0.9rem;
            opacity: 0.8;
            transition: transform 0.3s ease;
            color: inherit;
        }

        .btn-view-switch:hover .switch-icon {
            transform: rotate(180deg);
            opacity: 1;
        }

        /* ===================== RESPONSIVE DESIGN ===================== */

        /* Tablets grandes */
        @media (max-width: 991px) {
            .view-switcher-container {
                margin: 0 8px;
            }

            .btn-view-switch {
                padding: 9px 14px;
                font-size: 0.85rem;
                gap: 6px;
            }

            .switch-text {
                font-size: 0.8rem;
            }

            .switch-icon {
                font-size: 0.85rem;
            }
        }

        /* Tablets */
        @media (max-width: 768px) {
            .view-switcher-container {
                margin: 0 6px;
            }

            .btn-view-switch {
                padding: 8px 12px;
                font-size: 0.8rem;
                gap: 5px;
            }

            .switch-text {
                font-size: 0.75rem;
                display: none;
                /* Ocultar texto en tablets */
            }

            .btn-view-switch {
                min-width: 45px;
                justify-content: center;
            }
        }

        /* Móviles grandes */
        @media (max-width: 576px) {
            .view-switcher-container {
                margin: 0 5px;
            }

            .btn-view-switch {
                padding: 7px 10px;
                font-size: 0.75rem;
                gap: 4px;
                min-width: 42px;
            }

            .switch-text {
                display: none;
                /* Ocultar texto en móviles */
            }

            .switch-icon {
                font-size: 0.8rem;
            }
        }

        /* Móviles pequeños */
        @media (max-width: 480px) {
            .view-switcher-container {
                margin: 0 4px;
            }

            .btn-view-switch {
                padding: 6px 8px;
                font-size: 0.7rem;
                gap: 3px;
                min-width: 38px;
                border-radius: 20px;
            }

            .switch-text {
                display: none;
            }

            .switch-icon {
                font-size: 0.75rem;
            }
        }

        /* Para pantallas muy pequeñas */
        @media (max-width: 320px) {
            .btn-view-switch {
                padding: 5px 7px;
                min-width: 35px;
                border-radius: 18px;
            }

            .switch-icon {
                font-size: 0.7rem;
            }
        }

        /* ===================== ADDITIONAL MINIMALIST EFFECTS ===================== */
        @keyframes pulse-white {
            0% {
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            }

            50% {
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            }

            100% {
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            }
        }

        .btn-view-switch:focus {
            animation: pulse-white 1.5s infinite;
            outline: none;
        }

        /* ===================== ACCESSIBILITY ===================== */
        .btn-view-switch:focus-visible {
            outline: 2px solid #333;
            outline-offset: 2px;
        }

        body.dark-mode .btn-view-switch:focus-visible {
            outline: 2px solid #fff;
            outline-offset: 2px;
        }

        /* ===================== SUBTLE HOVER TOOLTIP ===================== */
        .btn-view-switch[title]:hover::after {
            content: attr(title);
            position: absolute;
            bottom: -35px;
            left: 50%;
            transform: translateX(-50%);
            padding: 5px 10px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            border-radius: 4px;
            font-size: 0.75rem;
            white-space: nowrap;
            z-index: 1000;
            opacity: 0;
            animation: fadeInTooltip 0.3s ease-in-out forwards;
        }

        body.dark-mode .btn-view-switch[title]:hover::after {
            background: rgba(255, 255, 255, 0.9);
            color: #333;
        }

        @keyframes fadeInTooltip {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(-5px);
            }

            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }

        /* ===================== INTEGRATION WITH EXISTING NAVBAR ===================== */
        .navbar-nav .view-switcher-container {
            display: flex;
            align-items: center;
        }

        .navbar .view-switcher-container .btn-view-switch {
            margin: 0;
            vertical-align: middle;
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

        <header class="topbar" data-navbarbg="skin5">
            <nav class="navbar top-navbar navbar-expand-md navbar-dark">
                <div class="navbar-header" data-logobg="skin5">
                    <!-- This is for the sidebar toggle which is visible on mobile only -->
                    <a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)"><i
                            class="ti-menu ti-close"></i></a>

                    <a class="navbar-brand" href="dashboard_vendedor.php">

                        <b class="logo-icon p-l-10">

                            <img src="assets/images/logo-icon.png" alt="homepage" class="light-logo" />

                        </b>

                        <span class="logo-text">
                            <!-- dark Logo text -->
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
                        <li class="nav-item d-none d-md-block"><a
                                class="nav-link sidebartoggler waves-effect waves-light" href="javascript:void(0)"
                                data-sidebartype="mini-sidebar"><i class="mdi mdi-menu font-24"></i></a></li>
                    </ul>

                    <div class="view-switcher-container">
                        <a href="index.php" class="btn-view-switch" title="Cambiar a vista de Usuario">
                            <i class="fas fa-user-circle"></i>
                            <span class="switch-text">Vista Usuario</span>
                            <i class="fas fa-exchange-alt switch-icon"></i>
                        </a>
                    </div>

                    <ul class="navbar-nav float-right">
                        <ul class="navbar-nav float-right">
                            <div class="language-selector">

                                <div class>
                                    <button id="darkModeToggle" class="language-select">Cambiar Tema</button>
                                    <script>
                                        const darkModeToggle = document.getElementById('darkModeToggle');
                                        darkModeToggle.addEventListener('click', () => {
                                            document.body.classList.toggle('dark-mode');
                                        });
                                    </script>
                                </div>

                                <select id="language" name="language" class="language-select" onchange="changeLanguage()">
                                    <option value="" disabled hidden selected>Idioma</option>
                                    <option value="es">Español</option>
                                    <option value="en">Inglés</option>
                                </select>
                            </div>
                            <script>
                                function changeLanguage() {
                                    const lang = document.getElementById('language').value;
                                    if (lang === 'es') {
                                        window.location.href = 'dashboard_vendedor.php';
                                    } else if (lang === 'en') {
                                        window.location.href = 'EN_dashboard_vendedor.php';
                                    }
                                }
                            </script>

                            <li class="nav-item dropdown">
                                <div class="collapse-profile">
                                    <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark pro-pic" href="#"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <img src="assets/images/users/1.jpg" alt="user" class="rounded-circle">
                                        <span class="user-text">Hola, <?php echo htmlspecialchars($username); ?></span>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right user-dd animated">
                                        <!-- <a class="dropdown-item" href="javascript:void(0)"><i class="ti-user m-r-5 m-l-5"></i>-->
                                        <!--     My Profile</a> -->
                                        <!-- <div class="dropdown-divider"></div> -->
                                        <a class="dropdown-item" href="logout.p.php"><i class="fa fa-power-off m-r-5 m-l-5"></i>
                                            Cerrar Sesión</a>
                                    </div>
                                </div>
                            </li>
                        </ul>
                </div>
            </nav>
        </header>

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

            <?php
            
            $ya_tuvo_trial = false;
            $conn_check = pg_connect("host=localhost dbname=homesafe user=postgres password=Info2025/*-");
            if ($conn_check) {
                $query_trial = "SELECT COUNT(*) FROM suscripcion WHERE mail_user = $1 AND plan_id = 4";
                $result_trial = pg_query_params($conn_check, $query_trial, array($_SESSION['email']));
                if ($result_trial) {
                    $row_trial = pg_fetch_row($result_trial);
                    $ya_tuvo_trial = ($row_trial[0] > 0);
                }
                pg_close($conn_check);
            }
            ?>

            <?php if ($subs_vencida): ?>
                <section class="section novi-background section-md text-center">
                    <div class="container">
                        <div class="alert alert-danger text-center mb-4" role="alert" style="font-size:1.2em;">
                            <strong>¡Tu suscripción ha vencido!</strong>
                            <br>Renueva tu suscripción para seguir usando las funciones de vendedor.
                        </div>
                        <div class="payment-instructions">
                            <i class="fas fa-info-circle"></i>
                            <strong>Instrucciones de Pago:</strong> Haz clic en el botón de PayPal para renovar tu suscripción.<br>
                            <span style="font-size:0.95em;color:#666;">
                                Si deseas cambiar de plan, puedes hacerlo desde la <a href="suscripciones.php" style="color:#007bff;text-decoration:underline;">sección de planes</a>.
                            </span>
                        </div>
                        <h3 class="text-uppercase font-weight-bold wow-outer">
                            <span class="wow slideInDown">Renovar Suscripción</span>
                        </h3>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="checkout-container">
                                    <h4 class="mb-4">Información de la Cuenta</h4>
                                    <form id="formSuscripcion" method="POST" action="">
                                        <div class="info-row">
                                            <label>Usuario:</label>
                                            <p><?php echo htmlspecialchars($username); ?></p>
                                        </div>
                                        <div class="info-row">
                                            <label>Email:</label>
                                            <p><?php echo htmlspecialchars($_SESSION['email']); ?></p>
                                        </div>
                                        <div class="payment-instructions">
                                            <i class="fas fa-info-circle"></i>
                                            <strong>Instrucciones de Pago:</strong> Haz clic en el botón de PayPal para renovar tu suscripción.<br>
                                            <span style="font-size:0.95em;color:#666;">
                                                También puedes actualizar tu plan aquí mismo seleccionando uno diferente.
                                            </span>
                                        </div>
                                        <div class="mb-2">
                                            <h>Teléfono *campo obligatorio*</h>
                                            <label class="form-label"></label>
                                            <input type="text" class="form-control" id="telefono" name="telefono" maxlength="9"
                                                placeholder="XXXX-XXXX" required>
                                        </div>
                                        <?php
                                        $planes_disponibles = [
                                            4 => "Trial (Gratis, 21 días)",
                                            1 => "Lite ($2.00/mes)",
                                            2 => "Plus ($4.50/mes)",
                                            3 => "Pro ($7.00/mes)"
                                        ];
                                        ?>
                                        <div class="mb-2">
                                            <label for="plan_id_select">Selecciona tu plan:</label>
                                            <select class="form-control" id="plan_id_select" name="plan_id" required>
                                                <?php
                                                foreach ($planes as $id => $plan) {
                                                    $selected = ($suscripcion['plan_id'] == $id) ? "selected" : "";
                                                    echo "<option value=\"$id\" $selected>" . htmlspecialchars($plan['nombre']) . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <input type="hidden" name="paypal_transaction_id" id="paypal_transaction_id" />
                                        <input type="hidden" name="pago_paypal" value="completado" />
                                        <div id="paypal-button-container" class="mb-4"></div>
                                    </form>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="order-summary">
                                    <h4 class="mb-3">Resumen de la Suscripción</h4>
                                    <div class="subscription-item d-flex align-items-center py-2">
                                        <div class="flex-grow-1">
                                            <div class="plan-highlight">
                                                <!-- Este es el elemento para el nombre del plan -->
                                                <h5 id="plan_nombre">
                                                    <?php
                                                    $nombres_planes = [1 => "Lite", 2 => "Plus", 3 => "Pro", 4 => "Trial"];
                                                    echo isset($nombres_planes[$suscripcion['plan_id']]) ? $nombres_planes[$suscripcion['plan_id']] : "Trial";
                                                    ?> ©
                                                </h5>
                                                <!-- Este es el elemento para la descripción del plan -->
                                                <p id="plan_descripcion">
                                                    <?php
                                                    $descripciones_planes = [
                                                        1 => "Ideal para nuevos vendedores que quieren probar la plataforma y emprender.",
                                                        2 => "Pensado para vendedores activos que buscan más visibilidad y herramientas.",
                                                        3 => "Para profesionales inmobiliarios que quieren el máximo potencial.",
                                                        4 => "Prueba gratuita de 21 días para descubrir las funcionalidades."
                                                    ];
                                                    echo isset($descripciones_planes[$suscripcion['plan_id']]) ? $descripciones_planes[$suscripcion['plan_id']] : "";
                                                    ?>
                                                </p>
                                                <div class="price-display" id="plan_pago">
                                                    Pago mensual
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal:</span>
                                        <!-- Este es el elemento para el precio del plan -->
                                        <span id="plan_precio">
                                            $<?php
                                                $precios_planes = [1 => 2, 2 => 4.5, 3 => 7, 4 => 0.01];
                                                echo number_format($precios_planes[$suscripcion['plan_id']], 2);
                                                ?>
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Duración:</span>
                                        <!-- Este es el elemento para la duración del plan -->
                                        <span id="plan_duracion">
                                            <?php echo ($suscripcion['plan_id'] == 4) ? "21 días" : "1 mes"; ?>
                                        </span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between mb-0">
                                        <strong>Total:</strong>
                                        <!-- Este es el elemento para el total del plan -->
                                        <strong class="fs-5" id="plan_total">
                                            $<?php echo number_format($precios_planes[$suscripcion['plan_id']], 2); ?>
                                        </strong>
                                    </div>
                                    <div class="mt-3">
                                        <small class="text-muted">
                                            <i class="fas fa-shield-alt me-1"></i>
                                            Pago seguro con PayPal
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-12 d-flex no-block align-items-center">
                    </div>
                </div>
            </div>

            <div class="form">
                <h1 class="login-title">Lista de Propiedades</h1>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Tamaño (m²)</th>
                                <th>Precio</th>
                                <th>Fecha</th>
                                <th>Imagen</th>
                            </tr>
                        </thead>
                        <tbody>
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

                            
                            $query = "SELECT id, nombre, descripcion, bathrooms, bedrooms, size, precio, fecha, imagen, mail_user FROM propiedades WHERE mail_user = $1";
                            $result = pg_query_params($conn, $query, array($email));

                            if (!$result) {
                                die("Error en la consulta: " . pg_last_error($conn));
                            }

                            if (pg_num_rows($result) > 0) {
                                while ($row = pg_fetch_assoc($result)) {
                                    
                                    $imgQuery = "SELECT imagen FROM carrusel_propiedad WHERE propiedad_id = $1 AND orden = 1 LIMIT 1";
                                    $imgResult = pg_query_params($conn, $imgQuery, array($row['id']));
                                    $imagenData = null;
                                    if ($imgResult && pg_num_rows($imgResult) > 0) {
                                        $imgRow = pg_fetch_assoc($imgResult);
                                        $imagenData = $imgRow['imagen'];
                                    }

                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8') . "</td>";
                                    echo "<td>" . htmlspecialchars($row['descripcion'], ENT_QUOTES, 'UTF-8') . "</td>";
                                    echo "<td>" . htmlspecialchars($row['size'], ENT_QUOTES, 'UTF-8') . "</td>";
                                    echo "<td>" . htmlspecialchars($row['precio'], ENT_QUOTES, 'UTF-8') . "</td>";
                                    echo "<td>" . htmlspecialchars($row['fecha'], ENT_QUOTES, 'UTF-8') . "</td>";
                                    if ($imagenData !== null) {
                                        echo "<td><img src='data:image/jpeg;base64," . base64_encode(pg_unescape_bytea($imagenData)) . "' width='100' height='100'/></td>";
                                    } else {
                                        echo "<td>No hay imagen</td>";
                                    }
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='9' style='text-align: center;'>No hay propiedades disponibles para este vendedor</td></tr>";
                            }

                            
                            pg_close($conn);
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if (isset($_SESSION['error_msg'])): ?>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <script>
                    Swal.fire({
                        icon: 'warning',
                        title: 'Error',
                        text: <?php echo json_encode($_SESSION['error_msg']); ?>
                    });
                </script>
            <?php unset($_SESSION['error_msg']);
            endif; ?>

            <?php if (isset($_SESSION['suscripcion_renovada']) && $_SESSION['suscripcion_renovada'] === true): ?>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: '¡Suscripción renovada correctamente!',
                        confirmButtonText: 'OK'
                    });
                </script>
            <?php unset($_SESSION['suscripcion_renovada']);
            endif; ?>


            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script src="https://www.paypal.com/sdk/js?client-id=AdYdJDj0e0M467JdootgS5YO3GOZrS3_H-BFEJcau4KTp1uRJb3JSRyVMD13ThlMy5ojBz-__S5hVTb9&currency=USD"></script>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    
                    const planes = <?php echo json_encode($planes); ?>;

                    
                    const planSelect = document.getElementById('plan_id_select'); 
                    const planNombre = document.getElementById('plan_nombre');
                    const planDescripcion = document.getElementById('plan_descripcion');
                    const planPrecio = document.getElementById('plan_precio');
                    const planTotal = document.getElementById('plan_total'); 
                    const planDuracion = document.getElementById('plan_duracion');

                    function actualizarResumen() {
                        const planId = planSelect.value;
                        if (planes[planId]) {
                            planNombre.textContent = planes[planId].nombre;
                            planDescripcion.textContent = planes[planId].descripcion;
                            planPrecio.textContent = `$${planes[planId].precio.toFixed(2)}`;
                            planTotal.textContent = `$${planes[planId].precio.toFixed(2)}`; 
                            planDuracion.textContent = planes[planId].duracion;
                        }
                    }

                    
                    actualizarResumen();

                    
                    planSelect.addEventListener('change', actualizarResumen);
                });
            </script>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const precios_planes = {
                        1: 2,
                        2: 4.5,
                        3: 7,
                        4: 0.01
                    };
                    const ya_tuvo_trial = <?php echo $ya_tuvo_trial ? 'true' : 'false'; ?>;

                    
                    document.getElementById('telefono').addEventListener('input', function(e) {
                        let valor = e.target.value.replace(/\D/g, '');
                        if (valor.length > 4) {
                            valor = valor.substring(0, 4) + '-' + valor.substring(4, 8);
                        }
                        e.target.value = valor;
                    });

                    paypal.Buttons({
                        createOrder: function(data, actions) {
                            const plan_id = document.getElementById('plan_id_select').value;
                            const ya_tuvo_trial = <?php echo $ya_tuvo_trial ? 'true' : 'false'; ?>;
                            const telefono = document.getElementById('telefono').value.trim();

                            console.log('createOrder triggered');
                            console.log('plan_id:', plan_id);
                            console.log('ya_tuvo_trial:', ya_tuvo_trial);
                            console.log('telefono:', telefono);

                            if (!telefono || !/^\d{4}-\d{4}$/.test(telefono)) {
                                console.log('Teléfono inválido');
                                return Swal.fire({
                                    icon: 'warning',
                                    title: 'Error',
                                    text: 'Por favor ingresa un teléfono válido con formato XXXX-XXXX.'
                                }).then(() => Promise.reject());
                            }

                            if (plan_id == 4 && ya_tuvo_trial) {
                                console.log('Plan trial ya usado');
                                return Swal.fire({
                                    icon: 'warning',
                                    title: 'Error',
                                    text: 'El plan de prueba solo puede usarse una vez. Por favor selecciona un plan de pago.'
                                }).then(() => Promise.reject());
                            }

                            console.log('Creando orden PayPal');
                            let total = precios_planes[plan_id];
                            return actions.order.create({
                                purchase_units: [{
                                    description: 'Renovación de suscripción HomeSafe',
                                    amount: {
                                        currency_code: 'USD',
                                        value: total
                                    }
                                }]
                            });
                        },
                        onApprove: function(data, actions) {
                            console.log('Pago aprobado');
                            return actions.order.capture().then(function(details) {
                                const transactionId = details.purchase_units[0].payments.captures[0].id;
                                document.getElementById('paypal_transaction_id').value = transactionId;
                                document.getElementById('formSuscripcion').submit();
                            });
                        },
                        onError: function(err) {
                            console.error('Error en PayPal:', err);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Hubo un problema con el pago de PayPal. Intenta nuevamente.'
                            });
                        }
                    }).render('#paypal-button-container');
                });
            </script>
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
        </div>
    </div>
</body>

</html>