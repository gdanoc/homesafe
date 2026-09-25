<?php include('0_ESvalidacion_Admin.php'); //ESPAÑOL 
?>
<?php include('logic_dashboard.php'); ?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon.png">
    <title>HomeSafe Dashboard</title>
    <link href="assets/libs/flot/css/float-chart.css" rel="stylesheet">
    <link href="dist/css/style.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style>
        /* ===================== VARIABLES CSS ===================== */
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #7c3aed;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
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
            --border-radius: 12px;
            --border-radius-lg: 16px;
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
            background: var(--white);
            padding: 35px;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-xl);
            width: 92%;
            max-width: 1400px;
            margin: 25px auto;
            overflow: hidden;
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
            background: linear-gradient(90deg, var(--primary-color), var(--info-color));
        }

        .form:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-xl);
        }

        .login-title {
            margin-bottom: 30px;
            font-size: 2rem;
            font-weight: 700;
            color: var(--gray-800);
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
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            border-radius: 2px;
        }

        /* ===================== TABLE STYLES ===================== */
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 700px;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            background: var(--white);
        }

        /* Dark Mode Styles */
        body.dark-mode table {
            background: var(--dark-mode-element);
        }

        th {
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: var(--white);
            padding: 18px 16px;
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
            padding: 16px;
            border-bottom: 1px solid var(--gray-200);
            font-size: 0.9rem;
            transition: var(--transition);
        }

        /* Dark Mode Styles */
        body.dark-mode td {
            color: var(--dark-mode-text);
        }

        tr:nth-child(even) {
            background: linear-gradient(90deg, #f8fafc 0%, #f1f5f9 100%);
        }

        /* Dark Mode Styles */
        body.dark-mode tr:nth-child(even) {
            background: rgba(255, 255, 255, 0.05);
        }

        tr:hover {
            background: linear-gradient(90deg, #e0f2fe 0%, #e1f5fe 100%);
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

        /* ===================== BOX COLOR VARIANTS - PASTEL COLORS ===================== */

        /* Deeper Pastel Coral for Muebles */
        .bg-info {
            background: linear-gradient(135deg, #ff9a8b 0%, #ffb8a8 100%);
            color: var(--white) !important;
            box-shadow: 0 10px 30px rgba(255, 154, 139, 0.5);
            transition: var(--transition);
        }

        .bg-info:hover {
            box-shadow: 0 20px 40px rgba(255, 154, 139, 0.7);
            transform: translateY(-8px) scale(1.02);
        }

        .bg-info h3,
        .bg-info h6 {
            color: var(--white) !important;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        /* Deep Pastel Teal for Pedidos */
        .bg-success {
            background: linear-gradient(135deg, #6bcf7f 0%, #86e29b 100%);
            color: var(--white) !important;
            box-shadow: 0 10px 30px rgba(107, 207, 127, 0.5);
            transition: var(--transition);
        }

        .bg-success:hover {
            box-shadow: 0 20px 40px rgba(107, 207, 127, 0.7);
            transform: translateY(-8px) scale(1.02);
        }

        .bg-success h3,
        .bg-success h6 {
            color: var(--white) !important;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        /* Deep Pastel Orange for Usuarios */
        .bg-warning {
            background: linear-gradient(135deg, #ffbe76 0%, #ffd93d 100%);
            color: var(--white) !important;
            box-shadow: 0 10px 30px rgba(255, 190, 118, 0.5);
            transition: var(--transition);
        }

        .bg-warning:hover {
            box-shadow: 0 20px 40px rgba(255, 190, 118, 0.7);
            transform: translateY(-8px) scale(1.02);
        }

        .bg-warning h3,
        .bg-warning h6 {
            color: var(--white) !important;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        /* Deep Pastel Lavender for Propiedades */
        .bg-danger {
            background: linear-gradient(135deg, rgb(226, 122, 247) 0%, rgb(245, 106, 233) 100%);
            color: var(--white) !important;
            box-shadow: 0 10px 30px rgba(210, 100, 238, 0.5);
            transition: var(--transition);
        }

        .bg-danger:hover {
            box-shadow: 0 20px 40px rgba(168, 230, 207, 0.7);
            transform: translateY(-8px) scale(1.02);
        }

        .bg-danger h3,
        .bg-danger h6 {
            color: var(--white) !important;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        /* Deep Pastel Purple for Ganancias */
        .bg-gradient-blue {
            background: linear-gradient(135deg, #9b59b6 0%, #bb7bd9 100%);
            color: var(--white) !important;
            box-shadow: 0 10px 30px rgba(155, 89, 182, 0.5);
            transition: var(--transition);
        }

        .bg-gradient-blue:hover {
            box-shadow: 0 20px 40px rgba(155, 89, 182, 0.7);
            transform: translateY(-8px) scale(1.02);
        }

        .bg-gradient-blue h3,
        .bg-gradient-blue h6 {
            color: var(--white) !important;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        /* Deep Pastel Blue for Entregadas */
        .bg-gradient-orange {
            background: linear-gradient(135deg, #74b9ff 0%, #95c5ff 100%);
            color: var(--white) !important;
            box-shadow: 0 10px 30px rgba(116, 185, 255, 0.5);
            transition: var(--transition);
        }

        .bg-gradient-orange:hover {
            box-shadow: 0 20px 40px rgba(116, 185, 255, 0.7);
            transform: translateY(-8px) scale(1.02);
        }

        .bg-gradient-orange h3,
        .bg-gradient-orange h6 {
            color: var(--white) !important;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        /* Dark Mode Styles for Pastel Colors */
        body.dark-mode .bg-info {
            background: linear-gradient(135deg, #4a6fa5 0%, #5d7fb8 100%);
            color: var(--white) !important;
            box-shadow: 0 10px 30px rgba(74, 111, 165, 0.6);
        }

        body.dark-mode .bg-info h3,
        body.dark-mode .bg-info h6 {
            color: var(--white) !important;
        }

        body.dark-mode .bg-success {
            background: linear-gradient(135deg, #4a7c59 0%, #5d8f6b 100%);
            color: var(--white) !important;
            box-shadow: 0 10px 30px rgba(74, 124, 89, 0.6);
        }

        body.dark-mode .bg-success h3,
        body.dark-mode .bg-success h6 {
            color: var(--white) !important;
        }

        body.dark-mode .bg-warning {
            background: linear-gradient(135deg, #b8860b 0%, #d4a017 100%);
            color: var(--white) !important;
            box-shadow: 0 10px 30px rgba(184, 134, 11, 0.6);
        }

        body.dark-mode .bg-warning h3,
        body.dark-mode .bg-warning h6 {
            color: var(--white) !important;
        }

        body.dark-mode .bg-danger {
            background: linear-gradient(135deg, #8b4a6b 0%, #a85d7e 100%);
            color: var(--white) !important;
            box-shadow: 0 10px 30px rgba(139, 74, 107, 0.6);
        }

        body.dark-mode .bg-danger h3,
        body.dark-mode .bg-danger h6 {
            color: var(--white) !important;
        }

        body.dark-mode .bg-gradient-blue {
            background: linear-gradient(135deg, #6a4c93 0%, #8b5cb8 100%);
            color: var(--white) !important;
            box-shadow: 0 10px 30px rgba(106, 76, 147, 0.6);
        }

        body.dark-mode .bg-gradient-blue h3,
        body.dark-mode .bg-gradient-blue h6 {
            color: var(--white) !important;
        }

        body.dark-mode .bg-gradient-orange {
            background: linear-gradient(135deg, #047857 0%, #059669 100%);
            color: var(--white) !important;
            box-shadow: 0 10px 30px rgba(4, 120, 87, 0.6);
        }

        body.dark-mode .bg-gradient-orange h3,
        body.dark-mode .bg-gradient-orange h6 {
            color: var(--white) !important;
        }

        /* Icon color adjustments for better contrast */
        .bg-info i,
        .bg-success i,
        .bg-warning i,
        .bg-danger i,
        .bg-gradient-blue i,
        .bg-gradient-orange i {
            opacity: 0.8;
            transition: var(--transition);
        }

        .box:hover i {
            opacity: 1;
            transform: scale(1.1);
        }

        /* ===================== IMAGE STYLES ===================== */
        img {
            max-width: 140px;
            height: auto;
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
            margin-right: 12px;
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
                padding: 25px 20px;
                width: 95%;
            }

            .login-title {
                font-size: 1.75rem;
            }

            table {
                font-size: 0.85rem;
                min-width: 600px;
            }

            th,
            td {
                padding: 12px 10px;
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
                width: 98%;
                padding: 20px 15px;
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
                font-size: 0.8rem;
            }

            th,
            td {
                font-size: 0.8rem;
                padding: 10px 8px;
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

        #navbarSupportedContent {
            background: #1F262D !important;
        }

        /* Dark Mode Styles */
        body.dark-mode #navbarSupportedContent {
            background: #333 !important;
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

        .page-wrapper {
            background: #ffffff;
            /* fondo blanco por defecto */
            transition: background 0.3s ease;
        }

        body.dark-mode .page-wrapper {
            background: rgb(46, 46, 46);
            /* fondo oscuro refinado para modo oscuro */
        }

        /* nuevo estilovro */

        .bg-gradient-blue {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: var(--white) !important;
            box-shadow: 0 10px 30px rgba(46, 82, 152, 0.4);
            transition: var(--transition);
        }

        .bg-gradient-blue:hover {
            box-shadow: 0 20px 40px rgba(46, 82, 152, 0.6);
        }

        body.dark-mode .bg-gradient-blue {
            background: linear-gradient(135deg, #274060, #3a5a8a);
            box-shadow: 0 10px 30px rgba(20, 40, 70, 0.6);
        }

        body.dark-mode .bg-gradient-blue:hover {
            box-shadow: 0 20px 40px rgba(20, 40, 70, 0.8);
        }

        .bg-gradient-orange {
            background: linear-gradient(135deg, #ff7e5f, #feb47b);
            color: var(--white) !important;
            box-shadow: 0 10px 30px rgba(254, 180, 123, 0.4);
            transition: var(--transition);
        }

        .bg-gradient-orange:hover {
            box-shadow: 0 20px 40px rgba(254, 180, 123, 0.6);
        }

        body.dark-mode .bg-gradient-orange {
            background: linear-gradient(135deg, #cc6a4f, #dca86a);
            box-shadow: 0 10px 30px rgba(140, 90, 50, 0.6);
        }

        body.dark-mode .bg-gradient-orange:hover {
            box-shadow: 0 20px 40px rgba(140, 90, 50, 0.8);
        }
    </style>
</head>

<body>
    <div class="preloader">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div>
    <div id="main-wrapper">
        <header class="topbar" data-navbarbg="skin5">
            <nav class="navbar top-navbar navbar-expand-md navbar-dark">
                <div class="navbar-header" data-logobg="skin5">
                    <a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)"><i
                            class="ti-menu ti-close"></i></a>
                    <a class="navbar-brand" href="dashboard.php">
                        <b class="logo-icon p-l-10">
                            <img src="assets/images/logo-icon.png" alt="homepage" class="light-logo" />
                        </b>
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
                        <li class="nav-item d-none d-md-block"><a
                                class="nav-link sidebartoggler waves-effect waves-light" href="javascript:void(0)"
                                data-sidebartype="mini-sidebar"><i class="mdi mdi-menu font-24"></i></a></li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="d-none d-md-block">Panel de Control <i class="page-tittle"></i></span>
                                <span class="d-block d-md-none"><i class="fa fa-plus"></i></span>
                            </a>
                        </li>

                    </ul>

                    <div>
                        <button id="darkModeToggle" class="language-select">Cambiar Tema</button>
                        <script>
                            const darkModeToggle = document.getElementById('darkModeToggle');
                            darkModeToggle.addEventListener('click', () => {
                                document.body.classList.toggle('dark-mode');
                            });
                        </script>
                    </div>

                    <div class="language-selector">
                        <label for="language" class="language-label">Idioma:</label>
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
                                window.location.href = 'dashboard.php';
                            } else if (lang === 'en') {
                                window.location.href = 'EN_dashboard.php';
                            }
                        }
                    </script>

                    <ul class="navbar-nav float-right">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark pro-pic" href="#"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <img src="assets/images/users/1.jpg" alt="user" class="rounded-circle" width="31">
                                Hola, <?php echo htmlspecialchars($username); ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right user-dd animated">
                                <a class="dropdown-item" href="logout.p.php"><i class="fa fa-power-off m-r-5 m-l-5"></i>
                                    Cerrar Sesión</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>

        <?php
        require("left_sidebar/admin.php");
        ?>

        <div class="page-wrapper">
            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-12 d-flex no-block align-items-center">
                        <?php

                        $host = "localhost";
                        $port = "5432";
                        $dbname = "homesafe";
                        $username = "postgres";
                        $password = "TU_PASSWORD_DE_BASE_DE_DATOS";

                        $conn_string = "host=$host port=$port dbname=$dbname user=$username password=$password";
                        $conn = pg_connect($conn_string);

                        if (!$conn) {
                            die("Conexión fallida: " . pg_last_error());
                        }


                        $countQuery = "SELECT SUM(cantidad) AS total_muebles FROM muebles";
                        $countResult = pg_query($conn, $countQuery);
                        $totalMuebles = 0;
                        if (pg_num_rows($countResult) > 0) {
                            $row = pg_fetch_assoc($countResult);
                            $totalMuebles = $row['total_muebles'];
                        }

                        $countQuery = "SELECT COUNT(*) AS total_pedidos FROM pedidos";
                        $countResult = pg_query($conn, $countQuery);
                        $totalpedidos = 0;
                        if (pg_num_rows($countResult) > 0) {
                            $row = pg_fetch_assoc($countResult);
                            $totalpedidos = $row['total_pedidos'];
                        }

                        $countQuery = "SELECT COUNT(*) AS total_usuarios FROM accounts";
                        $countResult = pg_query($conn, $countQuery);
                        $totalusuarios = 0;
                        if (pg_num_rows($countResult) > 0) {
                            $row = pg_fetch_assoc($countResult);
                            $totalusuarios = $row['total_usuarios'];
                        }

                        $countQuery = "SELECT COUNT(*) AS total_propiedades FROM propiedades";
                        $countResult = pg_query($conn, $countQuery);
                        $totalpropiedades = 0;
                        if (pg_num_rows($countResult) > 0) {
                            $row = pg_fetch_assoc($countResult);
                            $totalpropiedades = $row['total_propiedades'];
                        }

                        $countQuery = "SELECT COALESCE(SUM(total), 0) AS ganancias_entregadas FROM pedidos WHERE estado = 'entregado'";
                        $countResult = pg_query($conn, $countQuery);
                        $gananciasEntregadas = 0;
                        if (pg_num_rows($countResult) > 0) {
                            $row = pg_fetch_assoc($countResult);
                            $gananciasEntregadas = $row['ganancias_entregadas'];
                        }


                        $countQuery = "SELECT COUNT(*) AS compras_entregadas FROM pedidos WHERE estado = 'entregado'";
                        $countResult = pg_query($conn, $countQuery);
                        $comprasEntregadas = 0;
                        if (pg_num_rows($countResult) > 0) {
                            $row = pg_fetch_assoc($countResult);
                            $comprasEntregadas = $row['compras_entregadas'];
                        }
                        pg_close($conn);
                        ?>

                        <div class="row">
                            <div class="col-md-3 col-sm-6">
                                <a href="mostrarmuebles.php" style="text-decoration:none;">
                                    <div class="box card-hover bg-info text-center">
                                        <h3 class="font-light text-white"><i class="mdi mdi-sofa"></i></h3>
                                        <h6 class="text-white">Muebles</h6>
                                        <h3 class="text-white"><?php echo htmlspecialchars($totalMuebles); ?></h3>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <a href="ventas.php" style="text-decoration:none;">
                                    <div class="box card-hover bg-success text-center">
                                        <h3 class="font-light text-white"><i class="mdi mdi-chart-areaspline"></i></h3>
                                        <h6 class="text-white">Pedidos</h6>
                                        <h3 class="text-white"><?php echo htmlspecialchars($totalpedidos); ?></h3>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <a href="usuarios_lista.php" style="text-decoration:none;">
                                    <div class="box card-hover bg-warning text-center">
                                        <h3 class="font-light text-white"><i class="mdi mdi-account"></i></h3>
                                        <h6 class="text-white">Usuarios</h6>
                                        <h3 class="text-white"><?php echo htmlspecialchars($totalusuarios); ?></h3>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <a href="propiedades_lista.php" style="text-decoration:none;">
                                    <div class="box card-hover bg-danger text-center">
                                        <h3 class="font-light text-white"><i class="mdi mdi-home"></i></h3>
                                        <h6 class="text-white">Propiedades</h6>
                                        <h3 class="text-white"><?php echo htmlspecialchars($totalpropiedades); ?></h3>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <a style="text-decoration:none;">
                                    <div class="box card-hover bg-gradient-blue text-center">
                                        <h3 class="font-light text-white"><i class="mdi mdi-cash-multiple"></i></h3>
                                        <h6 class="text-white">Ganancias</h6>
                                        <h3 class="text-white">$<?php echo number_format($gananciasEntregadas, 2); ?></h3>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <a href="pedidos_entregados.php" style="text-decoration:none;">
                                    <div class="box card-hover bg-gradient-orange text-center">
                                        <h3 class="font-light text-white"><i class="mdi mdi-cart"></i></h3>
                                        <h6 class="text-white">Entregadas</h6>
                                        <h3 class="text-white"><?php echo htmlspecialchars($comprasEntregadas); ?></h3>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráfica de Barras -->
            <div class="chart-container">
                <canvas id="myBarChart"></canvas>
            </div>

            <script>
                const labels = ['Muebles', 'Pedidos', 'Usuarios', 'Propiedades'];
                const data = {
                    labels: labels,
                    datasets: [{
                        label: 'Total',
                        data: [<?php echo htmlspecialchars($totalMuebles); ?>,
                            <?php echo htmlspecialchars($totalpedidos); ?>,
                            <?php echo htmlspecialchars($totalusuarios); ?>,
                            <?php echo htmlspecialchars($totalpropiedades); ?>
                        ],
                        backgroundColor: [
                            'rgba(23, 162, 184, 0.6)',
                            'rgba(40, 167, 69, 0.6)',
                            'rgba(255, 193, 7, 0.6)',
                            'rgba(0, 123, 255, 0.6)'
                        ],
                        borderColor: [
                            'rgba(23, 162, 184, 1)',
                            'rgba(40, 167, 69, 1)',
                            'rgba(255, 193, 7, 1)',
                            'rgba(0, 123, 255, 1)'
                        ],
                        borderWidth: 1
                    }]
                };

                const config = {
                    type: 'bar',
                    data: data,
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                };

                const myBarChart = new Chart(
                    document.getElementById('myBarChart'),
                    config
                );
            </script>
            <?php include('includes/homechat.php'); ?>
            <script src="assets/libs/jquery/dist/jquery.min.js"></script>
            <script src="assets/libs/popper.js/dist/umd/popper.min.js"></script>
            <script src="assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
            <script src="assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js"></script>
            <script src="assets/extra-libs/sparkline/sparkline.js"></script>
            <script src="dist/js/waves.js"></script>
            <script src="dist/js/sidebarmenu.js"></script>
            <script src="dist/js/custom.min.js"></script>
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