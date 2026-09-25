<?php include('0_ESvalidacion_Admin.php'); //ESPAÑOL 
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<?php

session_start();
$email = $_SESSION['email'];
//mostrar debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['email'])) {
    echo '<script>swal({
        title: "Error",
        text: "Sesión no iniciada",
        icon: "warning",
        button: "Cerrar"
    }).then(function() {
        window.location = "login.php";
    });</script>';
}

$host = "localhost";
$port = "5432";
$dbname = "homesafe";
$user = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";

$conn_string = "host=$host port=$port dbname=$dbname user=$user password=$password";
$conn = pg_connect($conn_string);


if (!$conn) {
    throw new Exception("Error en la conexión PostgreSQL");
}



if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $query = "SELECT * FROM muebles WHERE id = $id";
    $result = pg_query($conn, $query);
    if (pg_num_rows($result) > 0) {
        $row = pg_fetch_assoc($result);
    } else {
        echo "Mueble no encontrado.";
        exit;
    }
}

$validacion = 0;

if (isset($_POST['id'], $_POST['nombre'], $_POST['descripcion'], $_POST['precio'], $_POST['cantidad'])) {
    $id = $_POST['id'];
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = $_POST['precio'];
    $cantidad = $_POST['cantidad'];

    
    $errores = [];

    
    if ($nombre === '') {
        $errores[] = "El nombre del mueble es obligatorio.";
    } elseif (strlen($nombre) < 2) {
        $errores[] = "El nombre debe tener al menos 2 caracteres.";
    } elseif (strlen($nombre) > 50) {
        $errores[] = "El nombre no puede exceder 50 caracteres.";
    } elseif (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\-_.,]+$/', $nombre)) {
        $errores[] = "El nombre contiene caracteres no válidos.";
    }

    
    if ($descripcion === '') {
        $errores[] = "La descripción es obligatoria.";
    } elseif (strlen($descripcion) < 10) {
        $errores[] = "La descripción debe tener al menos 10 caracteres.";
    } elseif (strlen($descripcion) > 500) {
        $errores[] = "La descripción no puede exceder 500 caracteres.";
    }

    
    if ($precio === '') {
        $errores[] = "El precio es obligatorio.";
    } elseif (!is_numeric($precio)) {
        $errores[] = "El precio debe ser un número válido.";
    } elseif ($precio < 0.01 || $precio > 99999.99) {
        $errores[] = "El precio debe estar entre $0.01 y $99,999.99.";
    } elseif (strpos($precio, '.') !== false && strlen(explode('.', $precio)[1]) > 2) {
        $errores[] = "El precio no puede tener más de 2 decimales.";
    }

    
    if ($cantidad === '') {
        $errores[] = "La cantidad es obligatoria.";
    } elseif (!ctype_digit($cantidad)) {
        $errores[] = "La cantidad debe ser un número entero.";
    } elseif ($cantidad < 1 || $cantidad > 100000) {
        $errores[] = "La cantidad debe estar entre 1 y 100,000.";
    }
    
    $descuento = isset($_POST['descuento']) ? trim($_POST['descuento']) : null;

    if ($descuento !== null && $descuento !== '') {
        if (!is_numeric($descuento) || $descuento < 0 || $descuento > 100) {
            $errores[] = "El descuento debe ser un número entre 0 y 100.";
        }
    } else {
        $descuento = null; 
    }

    if (isset($_FILES['imagen']) && $_FILES['imagen']['size'] > 0) {
        $tipoPermitido = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
        $tipoArchivo = mime_content_type($_FILES['imagen']['tmp_name']);
        $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));

        if ($extension === 'exe' || $extension === 'bat' || $extension === 'sh') {
            $errores[] = "Archivos ejecutables no estan permitidos.";
        } else if (!in_array($tipoArchivo, $tipoPermitido)) {
            $errores[] = "Solo se permiten imágenes JPG, PNG, GIF o WEBP.";
        }
    }

    
    if (!empty($errores)) {
        $erroresJS = json_encode($errores);
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    swal({
                        title: 'Error de validación',
                        text: " . $erroresJS . ".join('\\n'),
                        icon: 'warning',
                        button: 'Cerrar'
                    }).then(function() {
                        window.location = 'EN_frontend_update.php';
                });
            });
        </script>";
        exit;
    }

    $nombre = pg_escape_string($conn, $nombre);
    $descripcion = pg_escape_string($conn, $descripcion);
    $precio = (float)$precio;
    $cantidad = (int)$cantidad;

    
    $query_anterior = "SELECT * FROM muebles WHERE id = $id";
    $resultado_anterior = pg_query($conn, $query_anterior);
    $datos_anteriores = pg_fetch_assoc($resultado_anterior);

    if (!$datos_anteriores) {
        echo "Error: No se encontraron datos anteriores.";
        exit;
    }

    $nombre_anterior = $datos_anteriores['nombre'];
    $descripcion_anterior = $datos_anteriores['descripcion'];
    $precio_anterior = $datos_anteriores['precio'];
    $cantidad_anterior = $datos_anteriores['cantidad'];
    $descuento_anterior = $datos_anteriores['descuento'];
    $fecha_anterior = $datos_anteriores['fecha'];
    $imagen_anterior = $datos_anteriores['imagen'];

    if (isset($_FILES['imagen']) && $_FILES['imagen']['size'] > 0) {
        $contenido_imagen = file_get_contents($_FILES['imagen']['tmp_name']);
        $imagen_escaped = pg_escape_bytea($conn, $contenido_imagen);
        $validacion = 1;
    } else {
        $imagen_escaped = pg_escape_bytea($conn, $imagen_anterior);
    }

    
    if ($validacion == 1) {
        $query_update = "UPDATE muebles SET 
        nombre='$nombre', 
        descripcion='$descripcion', 
        precio='$precio', 
        cantidad='$cantidad', 
        imagen='$imagen_escaped',
        descuento=" . ($descuento !== null ? "'$descuento'" : "NULL") . "
        WHERE id=$id";
    } elseif ($validacion == 0) {
        $query_update = "UPDATE muebles SET 
            nombre='$nombre', 
            descripcion='$descripcion', 
            precio='$precio', 
            cantidad='$cantidad',
            descuento=" . ($descuento !== null ? "'$descuento'" : "NULL") . "
            WHERE id=$id";
    }

    if (pg_query($conn, $query_update)) {
        
        $accion = "Modificar";

        
        $query_insert = "INSERT INTO registrosadmin (
            accion, nombre, descripcion, precio, cantidad, descuento, email, fecha,
            nombre_anterior, descripcion_anterior, precio_anterior, cantidad_anterior, descuento_anterior, fecha_anterior
        ) VALUES (
            '$accion', '$nombre', '$descripcion', $precio, $cantidad, " . ($descuento !== null ? $descuento : "NULL") . ", '$email', '$fecha_anterior',
            '$nombre_anterior', '$descripcion_anterior', $precio_anterior, $cantidad_anterior, " . ($datos_anteriores['descuento'] !== null ? $datos_anteriores['descuento'] : "NULL") . ", '$fecha_anterior'
        ) RETURNING id;";

        $result_insert = pg_query($conn, $query_insert);
        if (!$result_insert) {
            echo "Error al insertar registro: " . pg_last_error($conn);
            exit;
        }

        
        $row_id = pg_fetch_row($result_insert);
        $ultimo_id = $row_id[0];

        
        if ($validacion == 1) {
            
            $imagen_actual = pg_escape_bytea($conn, $contenido_imagen);
        } elseif ($validacion == 0) {
            $imagen_actual = $imagen_anterior;
        }

        
        $query_update_img = "UPDATE registrosadmin SET imagen = $1, imagen_anterior = $2 WHERE id = $3";
        pg_prepare($conn, "update_images", $query_update_img);
        $result_img = pg_execute($conn, "update_images", array($imagen_actual, $imagen_anterior, $ultimo_id));

        if (!$result_img) {
            echo "Error en la ejecución de actualización de imagen: " . pg_last_error($conn);
            exit;
        }

        echo '<p><script>swal({
            title: "Exito",
            text: "Mueble actualizado y registrado en el historial.",
            icon: "success",
            button: "Cerrar",
        }).then(function() {
            window.location = "frontend_update.php";
        });</script></p>';
        exit();
    } else {
        echo '<script>swal({
            title: "Error",
            text: "Error al actualizar.",
            icon: "warning",
            button: "Cerrar",
        }).then(function() {
            window.location = "frontend_update.php";
        });</script>';
    }
}

pg_close($conn);
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
    <title>Matrix Template - The Ultimate Multipurpose admin template</title>
    <!-- Custom CSS -->
    <link href="assets/libs/flot/css/float-chart.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="dist/css/style.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/dropify/dist/css/dropify.min.css" />

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

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
                    <a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)"><i class="ti-menu ti-close"></i></a>
                    <!-- ============================================================== -->
                    <!-- Logo -->
                    <!-- ============================================================== -->
                    <a class="navbar-brand" href="dashboard.php">
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
                            <div class="logo-container">
                                <img src="assets/images/logo-text.png" alt="homepage" class="light-logo" />
                            </div>
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
                    <a class="topbartoggler d-block d-md-none waves-effect waves-light" href="javascript:void(0)" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><i class="ti-more"></i></a>
                </div>
                <!-- ============================================================== -->
                <!-- End Logo -->
                <!-- ============================================================== -->
                <div class="navbar-collapse collapse" id="navbarSupportedContent" data-navbarbg="skin5">
                    <!-- ============================================================== -->
                    <!-- toggle and nav items -->
                    <!-- ============================================================== -->
                    <ul class="navbar-nav float-left mr-auto">
                        <li class="nav-item d-none d-md-block"><a class="nav-link sidebartoggler waves-effect waves-light" href="javascript:void(0)" data-sidebartype="mini-sidebar"><i class="mdi mdi-menu font-24"></i></a></li>
                        <!-- ============================================================== -->
                        <!-- create new -->
                        <!-- ============================================================== -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="d-none d-md-block">Modificar Muebles<i class="page-tittle"></i></span>
                                <span class="d-block d-md-none"><i class="fa fa-plus"></i></span>
                            </a>
                        </li>
                        <!-- ============================================================== -->
                        <!-- Search -->
                        <!-- ============================================================== -->
                    </ul>
                    <!-- ============================================================== -->
                    <!-- Right side toggle and nav items -->
                    <!-- ============================================================== -->
                    <ul class="navbar-nav float-right">
                        <!-- ============================================================== -->
                        <!-- Comment -->
                        <!-- ============================================================== -->
                        <!-- ============================================================== -->
                        <!-- End Comment -->
                        <!-- ============================================================== -->
                        <!-- ============================================================== -->
                        <!-- Messages -->
                        <!-- ============================================================== -->
                        <!-- ============================================================== -->
                        <!-- End Messages -->
                        <!-- ============================================================== -->

                        <!-- ============================================================== -->
                        <!-- User profile and search -->
                        <!-- ============================================================== -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark pro-pic" href="#"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <img src="assets/images/users/1.jpg" alt="user" class="rounded-circle" width="31">
                                Hola, <?php echo htmlspecialchars($_SESSION['email'], ENT_QUOTES, 'UTF-8'); ?> </a>
                            <div class="dropdown-menu dropdown-menu-right user-dd animated">
                                <!-- <a class="dropdown-item" href="javascript:void(0)"><i class="ti-user m-r-5 m-l-5"></i>-->
                                <!--     My Profile</a> -->
                                <!-- <div class="dropdown-divider"></div> -->
                                <a class="dropdown-item" href="logout.p.php"><i class="fa fa-power-off m-r-5 m-l-5"></i>
                                    Cerrar Sesión</a>
                            </div>
                        </li>
                        <!-- ============================================================== -->
                        <!-- User profile and search -->
                        <!-- ============================================================== -->
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
        require("left_sidebar/admin.php");
        ?>
        <!-- ============================================================== -->
        <!-- End Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Page wrapper  -->
        <!-- ============================================================== -->

        <div class="page-wrapper">
            <!-- ============================================================== -->
            <!-- Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <div class="form">
                <h1 class="login-title">Modificar Muebles</h1>
                <form id="Inputs" action="backend_update.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>" />
                    <input type="text" id="nombre" name="nombre" class="login-input" placeholder="Nombre del Mueble" value="<?php echo

                                                                                                                            htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8'); ?>" required />

                    <input type="text" id="descripcion" name="descripcion" class="login-input" placeholder="Descripción" value="<?php echo

                                                                                                                                htmlspecialchars($row['descripcion'], ENT_QUOTES, 'UTF-8'); ?>" required />

                    <input type="number" id="precio" name="precio" class="login-input" placeholder="Precio" value="<?php echo

                                                                                                                    htmlspecialchars($row['precio'], ENT_QUOTES, 'UTF-8'); ?>" required />
                    <input type="number" id="cantidad" name="cantidad" class="login-input" placeholder="Cantidad" value="<?php echo

                                                                                                                            htmlspecialchars($row['cantidad'], ENT_QUOTES, 'UTF-8'); ?>" required />
                    <input type="number" id="descuento" name="descuento" class="login-input" placeholder="Descuento (%) - Opcional" min="0" max="100" step="0.01" value="<?php
                                                                                                                                                                            echo htmlspecialchars($row['descuento'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />

                    <?php if (!empty($row['imagen'])): ?>
                        <?php
                        $imagen_data = function_exists('pg_unescape_bytea') ? pg_unescape_bytea($row['imagen']) : $row['imagen'];
                        ?>
                    <?php endif; ?>
                    <div class="imagen-container">
                        <input type="file" name="imagen" id="imagen" class="dropify" accept="image/*" />
                        <div id="imagenError" class="error-message" style="color: red; font-size: 12px; display: none;"></div>


                        <div id="previewContainer" style="margin-top: 10px;">
                            <?php if (!empty($row['imagen'])): ?>
                                <!-- Imagen actual de la base de datos -->
                                <p>Imagen actual:</p>
                                <img id="currentImage" src="data:image/jpeg;base64,<?php echo base64_encode($imagen_data); ?>"
                                    alt="Imagen actual" style="max-width: 200px; max-height: 200px;" />
                            <?php else: ?>
                                <p id="noImageText">No hay imagen</p>
                            <?php endif; ?>

                            <!-- Contenedor para la vista previa de la nueva imagen -->
                            <div id="newImagePreview" style="display: none; margin-top: 15px;">
                                <p>Nueva imagen seleccionada:</p>
                                <img id="previewImage" style="max-width: 200px; max-height: 200px;" />
                                <div id="imageInfo" style="font-size: 12px; color: #666;"></div>
                            </div>
                        </div>
                    </div>
                    <input type="submit" name="submit" value="Actualizar" class="login-button" />
                </form>
            </div>
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const form = document.getElementById("Inputs");
                    const nombreInput = document.getElementById("nombre");
                    const descripcionInput = document.getElementById("descripcion");
                    const precioInput = document.getElementById("precio");
                    const cantidadInput = document.getElementById("cantidad");
                    const errorMsg = document.getElementById("errorMsg");

                    form.addEventListener("submit", function(event) {
                        let isValid = true;
                        let errorMessage = "";

                        
                        if (nombreInput.value.trim() === "") {
                            isValid = false;
                            errorMessage += "El campo 'Nombre del Mueble' no puede estar vacío.\n";
                        } else if (nombreInput.value.length > 50) {
                            isValid = false;
                            errorMessage += "El campo 'Nombre del Mueble' no puede tener más de 50 caracteres.\n";
                        } else if (!/^[a-zA-Z0-9]+$/.test(nombreInput.value)) {
                            isValid = false;
                            errorMessage += "El campo 'Nombre del Mueble' solo puede contener letras y números.\n";
                        }

                        
                        if (descripcionInput.value.trim() === "") {
                            isValid = false;
                            errorMessage += "El campo 'Descripción' no puede estar vacío.\n";
                        } else if (descripcionInput.value.length > 500) {
                            isValid = false;
                            errorMessage += "El campo 'Descripción' no puede tener más de 500 caracteres.\n";
                        } else if (!/^[a-zA-Z0-9\s]+$/.test(descripcionInput.value)) {
                            isValid = false;
                            errorMessage += "El campo 'Descripción' solo puede contener letras, números y espacios.\n";
                        }

                        
                        if (precioInput.value.trim() === "") {
                            isValid = false;
                            errorMessage += "El campo 'Precio' no puede estar vacío.\n";
                        } else if (parseInt(precioInput.value) < 1 || parseInt(precioInput.value) > 9999) {
                            isValid = false;
                            errorMessage += "El campo 'Precio' debe estar entre 1 y 9999.\n";
                        }

                        
                        if (cantidadInput.value.trim() === "") {
                            isValid = false;
                            errorMessage += "El campo 'Cantidad' no puede estar vacío.\n";
                        } else if (parseInt(cantidadInput.value) < 1 || parseInt(cantidadInput.value) > 100000) {
                            isValid = false;
                            errorMessage += "El campo 'Cantidad' debe estar entre 1 y 100000.\n";
                        }

                        
                        if (!isValid) {
                            errorMsg.textContent = errorMessage;
                            event.preventDefault(); 
                        } else {
                            errorMsg.textContent = "";
                        }
                    });

                    
                    nombreInput.addEventListener("input", function() {
                        let isValid = true;
                        let errorMessage = "";

                        if (nombreInput.value.trim() === "") {
                            isValid = false;
                            errorMessage += "El campo 'Nombre del Mueble' no puede estar vacío.\n";
                        } else if (nombreInput.value.length > 50) {
                            isValid = false;
                            errorMessage += "El campo 'Nombre del Mueble' no puede tener más de 50 caracteres.\n";
                        } else if (!/^[a-zA-Z0-9]+$/.test(nombreInput.value)) {
                            isValid = false;
                            errorMessage += "El campo 'Nombre del Mueble' solo puede contener letras y números.\n";
                        }

                        if (!isValid) {
                            errorMsg.textContent = errorMessage;
                        } else {
                            errorMsg.textContent = "";
                        }
                    });

                    descripcionInput.addEventListener("input", function() {
                        let isValid = true;
                        let errorMessage = "";

                        if (descripcionInput.value.trim() === "") {
                            isValid = false;
                            errorMessage += "El campo 'Descripción' no puede estar vacío.\n";
                        } else if (descripcionInput.value.length > 500) {
                            isValid = false;
                            errorMessage += "El campo 'Descripción' no puede tener más de 500 caracteres.\n";
                        } else if (!/^[a-zA-Z0-9\s]+$/.test(descripcionInput.value)) {
                            isValid = false;
                            errorMessage += "El campo 'Descripción' solo puede contener letras, números y espacios.\n";
                        }

                        if (!isValid) {
                            errorMsg.textContent = errorMessage;
                        } else {
                            errorMsg.textContent = "";
                        }
                    });

                    precioInput.addEventListener("input", function() {
                        let isValid = true;
                        let errorMessage = "";

                        if (precioInput.value.trim() === "") {
                            isValid = false;
                            errorMessage += "El campo 'Precio' no puede estar vacío.\n";
                        } else if (parseInt(precioInput.value) < 1 || parseInt(precioInput.value) > 9999) {
                            isValid = false;
                            errorMessage += "El campo 'Precio' debe estar entre 1 y 9999.\n";
                        }

                        if (!isValid) {
                            errorMsg.textContent = errorMessage;
                        } else {
                            errorMsg.textContent = "";
                        }
                    });

                    cantidadInput.addEventListener("input", function() {
                        let isValid = true;
                        let errorMessage = "";

                        if (cantidadInput.value.trim() === "") {
                            isValid = false;
                            errorMessage += "El campo 'Cantidad' no puede estar vacío.\n";
                        } else if (parseInt(cantidadInput.value) < 1 || parseInt(cantidadInput.value) > 100000) {
                            isValid = false;
                            errorMessage += "El campo 'Cantidad' debe estar entre 1 y 100000.\n";
                        }

                        if (!isValid) {
                            errorMsg.textContent = errorMessage;
                        } else {
                            errorMsg.textContent = "";
                        }
                    });
                });
            </script>
            <script>
                document.getElementById('imagenInput').addEventListener('change', function() {
                    const input = this;
                    const errorMsg = document.getElementById('errorMsg');
                    const newImagePreview = document.getElementById('newImagePreview');
                    const previewImage = document.getElementById('previewImage');
                    const imageInfo = document.getElementById('imageInfo');
                    const currentImage = document.getElementById('currentImage');
                    const noImageText = document.getElementById('noImageText');

                    errorMsg.textContent = '';

                    if (!input.files || input.files.length === 0) {
                        newImagePreview.style.display = 'none';

                        if (currentImage) {
                            const currentImageLabel = currentImage.previousElementSibling;
                            currentImageLabel.style.display = 'block';
                            currentImage.style.display = 'block';
                        } else if (noImageText) {
                            noImageText.style.display = 'block';
                        }
                        return;
                    }

                    const file = input.files[0];
                    const tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    

                    if (!tiposPermitidos.includes(file.type)) {
                        errorMsg.textContent = 'Error: Solo archivos de imagenes estan permitidos (JPEG, PNG, GIF, WEBP)';
                        input.value = '';
                        newImagePreview.style.display = 'none';

                        if (currentImage) {
                            const currentImageLabel = currentImage.previousElementSibling;
                            currentImageLabel.style.display = 'block';
                            currentImage.style.display = 'block';
                        } else if (noImageText) {
                            noImageText.style.display = 'block';
                        }
                        return;
                    }
                    const descuentoInput = document.getElementById('descuento');

                    function validateDescuento() {
                        const descuento = descuentoInput.value.trim();
                        if (descuento === '') {
                            return true; 
                        }
                        const descuentoNum = parseFloat(descuento);
                        if (isNaN(descuentoNum) || descuentoNum < 0 || descuentoNum > 100) {
                            return false;
                        }
                        return true;
                    }

                    form.addEventListener('submit', function(e) {
                        if (!validateDescuento()) {
                            e.preventDefault();
                            alert('El descuento debe ser un número entre 0 y 100.');
                            descuentoInput.focus();
                        }
                    });

                    
                    //if (file.size > tamañoMaximo) {
                    
                    
                    

                    
                    
                    
                    
                    
                    
                    
                    
                    //}

                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = new Image();
                        img.onload = function() {

                            if (currentImage) {
                                const currentImageLabel = currentImage.previousElementSibling;
                                currentImageLabel.style.display = 'none';
                                currentImage.style.display = 'none';
                            }
                            if (noImageText) {
                                noImageText.style.display = 'none';
                            }

                            previewImage.src = e.target.result;
                            imageInfo.textContent = `${file.name} (${(file.size/1024).toFixed(1)} KB)`;
                            newImagePreview.style.display = 'block';
                        };

                        img.onerror = function() {
                            errorMsg.textContent = 'Error: El archivo no es una imagen válida';
                            input.value = '';
                            newImagePreview.style.display = 'none';

                            if (currentImage) {
                                const currentImageLabel = currentImage.previousElementSibling;
                                currentImageLabel.style.display = 'block';
                                currentImage.style.display = 'block';
                            } else if (noImageText) {
                                noImageText.style.display = 'block';
                            }
                        };

                        img.src = e.target.result;
                    };

                    reader.readAsDataURL(file);
                });
            </script>
            <script src="assets/libs/jquery/dist/jquery.min.js"></script>

            <script src="https://cdn.jsdelivr.net/npm/dropify/dist/js/dropify.min.js"></script>
            <script>
                $(document).ready(function() {
                    
                    $('.dropify').dropify({
                        messages: {
                            'default': 'Arrastra y suelta un archivo aquí o haz clic',
                            'replace': 'Arrastra y suelta o haz clic para reemplazar',
                            'remove': 'Eliminar',
                            'error': 'Ooops, algo salió mal.'
                        },
                        error: {
                            'fileSize': 'El tamaño del archivo es demasiado grande ({{ value }} max).',
                            'fileExtension': 'El archivo no está permitido. Solo se permiten imágenes.'
                        }
                    });

                    
                });
            </script>



            <?php include('includes/homechat.php'); ?>
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

</body>

</html>