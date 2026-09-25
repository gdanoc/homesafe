<?php include('0_ESvalidacion_Admin.php');  ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<?php
session_start();
$email = $_SESSION['email'] ?? null;

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!$email) {
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
      swal({
        title: 'Error',
        text: 'Sesión no iniciada',
        icon: 'warning',
        button: 'Cerrar'
      }).then(function() {
        window.location = 'index.php';
      });
    });
    </script>";
    exit;
}

$host = "localhost";
$port = "5432";
$dbname = "homesafe";
$username = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";

$conn_string = "host=$host port=$port dbname=$dbname user=$username password=$password";
$conn = pg_connect($conn_string);

if (!$conn) {
    throw new Exception("Error en la conexión PostgreSQL");
}

if (isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $query = "SELECT * FROM slider WHERE id = $id";
    $result = pg_query($conn, $query);
    if (pg_num_rows($result) > 0) {
        $row = pg_fetch_assoc($result);
    } else {
        echo "Imagen no encontrada.";
        exit;
    }
}

if (isset($_POST['id'], $_POST['nombre'])) {
    $id = (int)$_POST['id'];
    $nombre = trim($_POST['nombre']);

    $errores = [];


    if ($nombre === '') {
        $errores[] = "El título de la imagen es obligatorio.";
    } elseif (strlen($nombre) < 2) {
        $errores[] = "El título debe tener al menos 2 caracteres.";
    } elseif (strlen($nombre) > 50) {
        $errores[] = "El título no puede exceder 50 caracteres.";
    } elseif (preg_match('/[<>"\'&]/', $nombre)) {
        $errores[] = "El título contiene caracteres no permitidos.";
    } elseif (preg_match('/^\s+$/', $nombre)) {
        $errores[] = "El título no puede contener solo espacios.";
    } elseif (preg_match('/^\d/', $nombre)) {
        $errores[] = "El título no puede comenzar con números.";
    }


    $query_anterior = "SELECT * FROM slider WHERE id = $id";
    $resultado_anterior = pg_query($conn, $query_anterior);
    $datos_anteriores = pg_fetch_assoc($resultado_anterior);

    if (!$datos_anteriores) {
        echo "Error: No se encontraron datos anteriores.";
        exit;
    }

    $nombre_anterior = $datos_anteriores['nombre'];
    $imagen_anterior = $datos_anteriores['imagen'];


    $validacion = 0;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['size'] > 0) {
        $archivo = $_FILES['imagen'];
        $tipoPermitido = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $tipoArchivo = mime_content_type($archivo['tmp_name']);
        $tamanoArchivo = $archivo['size'];

        if (!in_array($tipoArchivo, $tipoPermitido)) {
            $errores[] = "Formato de imagen no válido. Use: JPEG, PNG, GIF o WebP.";
        }
        if ($tamanoArchivo === 0) {
            $errores[] = "El archivo está vacío o corrupto.";
        }

        list($width, $height) = getimagesize($archivo['tmp_name']);
        if ($width < 200 || $height < 200) {
            $errores[] = "La imagen debe ser de al menos 200x200 píxeles.";
        }
    }

    if (!empty($errores)) {
            echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
            swal({
                title: 'Error de validación',
                text: '" . implode("\\n", $errores) . "',
                icon: 'warning',
                button: 'Cerrar'
            }).then(() => {
                window.location = 'frontend_slider_update.php?id=$id';
            });
            });
        </script>";
        exit;
    }


    $nombre = pg_escape_string($conn, $nombre);

    if (isset($_FILES['imagen']) && $_FILES['imagen']['size'] > 0) {
        $contenido_imagen = file_get_contents($_FILES['imagen']['tmp_name']);
        $imagen_escaped = pg_escape_bytea($conn, $contenido_imagen);
        $validacion = 1;
    } else {
        $imagen_escaped = pg_escape_bytea($conn, $imagen_anterior);
    }

    if ($validacion == 1) {
        $query_update = "UPDATE slider SET 
            nombre='$nombre', 
            imagen='$imagen_escaped'
            WHERE id=$id";
    } else {
        $query_update = "UPDATE slider SET 
            nombre='$nombre'
            WHERE id=$id";
    }

    if (pg_query($conn, $query_update)) {
        $accion = "Modificar";
        $query_insert = "INSERT INTO registroslider (
            accion, nombre, email, nombre_anterior
        ) VALUES (
            '$accion', '$nombre', '$email', '$nombre_anterior'
        ) RETURNING id";

        $result_insert = pg_query($conn, $query_insert);
        if (!$result_insert) {
            echo "Error al insertar registro: " . pg_last_error($conn);
            exit;
        }

        $row_id = pg_fetch_row($result_insert);
        $ultimo_id = $row_id[0];

        if ($validacion == 1) {
            $imagen_actual = pg_escape_bytea($conn, $contenido_imagen);
        } else {
            $imagen_actual = $imagen_anterior;
        }

        $query_update_img = "UPDATE registroslider SET imagen = $1, imagen_anterior = $2 WHERE id = $3";
        pg_prepare($conn, "update_images", $query_update_img);
        $result_img = pg_execute($conn, "update_images", array($imagen_actual, $imagen_anterior, $ultimo_id));

        if (!$result_img) {
            echo "Error en la ejecución de actualización de imagen: " . pg_last_error($conn);
            exit;
        }

        echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
        swal({
            title: 'Éxito',
            text: 'Imagen actualizada y registrada en el historial.',
            icon: 'success',
            button: 'Cerrar'
        }).then(() => {
            window.location = 'frontend_slider_update.php';
        });
        });
        </script>";
        exit();
    } else {
        echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            swal({
            title: 'Error',
            text: 'Error al actualizar.',
            icon: 'warning',
            button: 'Cerrar'
            }).then(() => {
            window.location = 'frontend_slider_update.php';
            });
        });
        </script>";
    }
}

pg_close($conn);
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon.png">
    <title>Matrix Template - The Ultimate Multipurpose admin template</title>
    <link href="assets/libs/flot/css/float-chart.css" rel="stylesheet">
    <link href="dist/css/style.min.css" rel="stylesheet">
    <style>
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

            --dark-mode-bg: #121212;
            --dark-mode-text: #f8f8f2;
            --dark-mode-element: #1e1e1e;
            --dark-mode-shadow: rgba(0, 0, 0, 0.5);
            --dark-mode-border: #333;
        }

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

        body.dark-mode {
            background: rgb(46, 46, 46);
            color: var(--dark-mode-text);
        }

        .container-fluid {
            padding: 30px 25px;
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(10px);
            min-height: 100vh;
        }

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
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .login-button {
            width: 100%;
            padding: 10px;
            background-color: rgb(63, 116, 151);
            border: none;
            border-radius: 4px;
            color: white;
            font-size: 16px;
            margin-top: 20px;
        }

        .login-button:hover {
            background-color: rgb(56, 81, 97);
        }

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

        body.dark-mode td {
            color: var(--dark-mode-text);
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

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

        body.dark-mode tr:hover td {
            color: var(--dark-mode-text);
        }

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

        .left-sidebar {
            transition: var(--transition);
            background: var(--white);
            box-shadow: var(--shadow-xl);
        }

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

        .pdf-download {
            padding: 10px;
            text-align: center;
        }

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

        body.dark-mode .language-selector {
            background: rgba(0, 0, 0, 0.3);
        }

        .language-label {
            margin-right: 12px;
            font-weight: 600;
            color: var(--white);
            font-size: 0.9rem;
        }

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

        body.dark-mode .language-select option {
            background: var(--dark-mode-element);
            color: var(--dark-mode-text);
        }

        #navbarSupportedContent {
            background: #1F262D !important;
        }

        body.dark-mode #navbarSupportedContent {
            background: #333 !important;
        }

        .page-wrapper {
            background: #ffffff;
            transition: background 0.3s ease;
        }

        body.dark-mode .page-wrapper {
            background: rgb(46, 46, 46);
        }

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

        ::-webkit-scrollbar {
            width: 14px;
        }

        ::-webkit-scrollbar-track {
            background: var(--gray-100);
        }

        body.dark-mode ::-webkit-scrollbar-track {
            background: #333;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, rgb(46, 56, 67), #6e869e);
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #6e869e, rgb(46, 56, 67));
        }

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
                    <a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)"><i class="ti-menu ti-close"></i></a>
                    <a class="navbar-brand" href="dashboard.php">
                        <b class="logo-icon p-l-10">
                            <img src="assets/images/logo-icon.png" alt="homepage" class="light-logo" />

                        </b>
                        <span class="logo-text">
                            <div class="logo-container">
                                <img src="assets/images/logo-text.png" alt="homepage" class="light-logo" />
                            </div>
                        </span>
                    </a>
                    <a class="topbartoggler d-block d-md-none waves-effect waves-light" href="javascript:void(0)" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><i class="ti-more"></i></a>
                </div>
                <div class="navbar-collapse collapse" id="navbarSupportedContent" data-navbarbg="skin5">
                    <ul class="navbar-nav float-left mr-auto">
                        <li class="nav-item d-none d-md-block"><a class="nav-link sidebartoggler waves-effect waves-light" href="javascript:void(0)" data-sidebartype="mini-sidebar"><i class="mdi mdi-menu font-24"></i></a></li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="d-none d-md-block">Modificar Carrusel<i class="page-tittle"></i></span>
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
                                <a class="dropdown-item" href="logout.p.php"><i class="fa fa-power-off m-r-5 m-l-5"></i>
                                    Logout</a>
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
            <div class="form">
                <h1 class="login-title">Modificar Imagen</h1>
                <form id="updateForm" action="backend_slider_update.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>" />
                    <input type="text" id="nombre" name="nombre" class="login-input" placeholder="Nombre de la Imagen"
                        value="<?php echo
                                htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8'); ?>" />
                    <span class="error-message" id="nombreError"></span>

                    <?php if (!empty($row['imagen'])): ?>
                        <?php
                        $imagen_data = function_exists('pg_unescape_bytea') ? pg_unescape_bytea($row['imagen']) : $row['imagen'];
                        ?>
                    <?php endif; ?>
                    <div class="imagen-container">
                        <input type="file" name="imagen" class="login-input" id="imagenInput"
                            accept="image/jpeg,image/png,image/gif,image/webp" />
                        <span class="error-message" id="imagenError"></span>

                        <div id="previewContainer" style="margin-top: 10px;">
                            <?php if (!empty($row['imagen'])): ?>
                                <p>Imagen actual:</p>
                                <img id="currentImage"
                                    src="data:image/jpeg;base64,<?php echo base64_encode($imagen_data); ?>"
                                    alt="Imagen actual" style="max-width: 200px; max-height: 200px;" />
                            <?php else: ?>
                                <p id="noImageText">No hay imagen</p>
                            <?php endif; ?>

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
                    const form = document.getElementById("updateForm");
                    const nombreInput = document.getElementById("nombre");
                    const imagenInput = document.getElementById('imagenInput');
                    const nombreError = document.getElementById("nombreError");
                    const imagenError = document.getElementById("imagenError");
                    const newImagePreview = document.getElementById('newImagePreview');
                    const previewImage = document.getElementById('previewImage');
                    const imageInfo = document.getElementById('imageInfo');
                    const currentImage = document.getElementById('currentImage');
                    const noImageText = document.getElementById('noImageText');


                    const LIMITES = {
                        nombre: {
                            min: 2,
                            max: 50
                        },
                        archivo: {
                            maxSize: 5 * 1024 * 1024,
                            minSize: 1024,
                            allowedTypes: ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
                            minWidth: 100,
                            maxWidth: 4000,
                            minHeight: 100,
                            maxHeight: 4000
                        }
                    };


                    nombreInput.addEventListener("input", function() {
                        validarNombre();
                    });


                    imagenInput.addEventListener('change', function() {
                        validarYPrevisualizarImagen();
                    });


                    form.addEventListener("submit", function(event) {
                        event.preventDefault();

                        const esNombreValido = validarNombre();
                        const esImagenValida = imagenInput.files.length > 0 ? validarImagen(imagenInput.files[0]) : true;

                        if (esNombreValido && esImagenValida) {
                            HTMLFormElement.prototype.submit.call(form);
                        }
                    });

                    function validarNombre() {
                        const valor = nombreInput.value.trim();
                        let esValido = true;
                        let mensajeError = '';


                        if (valor === '') {
                            mensajeError = 'El nombre de la imagen es obligatorio';
                            esValido = false;
                        } else if (valor.length < LIMITES.nombre.min) {
                            mensajeError = `El nombre debe tener al menos ${LIMITES.nombre.min} caracteres`;
                            esValido = false;
                        } else if (valor.length > LIMITES.nombre.max) {
                            mensajeError = `El nombre no puede exceder ${LIMITES.nombre.max} caracteres`;
                            esValido = false;
                        } else if (/[<>\"'&]/.test(valor)) {
                            mensajeError = 'El nombre contiene caracteres no permitidos';
                            esValido = false;
                        } else if (/^\s+$/.test(valor)) {
                            mensajeError = 'El nombre no puede contener solo espacios';
                            esValido = false;
                        } else if (/^\d/.test(valor)) {
                            mensajeError = 'El nombre no puede comenzar con números';
                            esValido = false;
                        } else if (/^[^a-zA-Z]|[^a-zA-Z0-9]$/.test(valor)) {
                            mensajeError = 'El nombre debe comenzar con una letra y terminar con letra o número';
                            esValido = false;
                        }

                        mostrarError(nombreInput, nombreError, mensajeError, esValido);
                        return esValido;
                    }

                    function validarImagen(archivo) {
                        let esValido = true;
                        let mensajeError = '';


                        if (!LIMITES.archivo.allowedTypes.includes(archivo.type)) {
                            mensajeError = 'Formato de imagen no válido. Use: JPEG, PNG, GIF o WEBP';
                            esValido = false;
                        } else if (archivo.size < LIMITES.archivo.minSize) {
                            mensajeError = 'El archivo de imagen es muy pequeño (mínimo 1KB)';
                            esValido = false;
                        } else if (archivo.size > LIMITES.archivo.maxSize) {
                            mensajeError = `La imagen es muy grande. Máximo ${LIMITES.archivo.maxSize / (1024 * 1024)}MB`;
                            esValido = false;
                        } else if (archivo.size === 0) {
                            mensajeError = 'El archivo está vacío o corrupto';
                            esValido = false;
                        }

                        mostrarError(imagenInput, imagenError, mensajeError, esValido);
                        return esValido;
                    }

                    function validarYPrevisualizarImagen() {
                        limpiarErrorImagen();

                        if (!imagenInput.files || imagenInput.files.length === 0) {
                            restaurarVistaPrevia();
                            return;
                        }

                        const archivo = imagenInput.files[0];


                        if (!validarImagen(archivo)) {
                            restaurarVistaPrevia();
                            imagenInput.value = '';
                            return;
                        }


                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const img = new Image();
                            img.onload = function() {

                                let errorDimensiones = '';
                                if (this.width < LIMITES.archivo.minWidth || this.height < LIMITES.archivo.minHeight) {
                                    errorDimensiones = `Imagen muy pequeña. Mínimo ${LIMITES.archivo.minWidth}x${LIMITES.archivo.minHeight} píxeles`;
                                } else if (this.width > LIMITES.archivo.maxWidth || this.height > LIMITES.archivo.maxHeight) {
                                    errorDimensiones = `Imagen muy grande. Máximo ${LIMITES.archivo.maxWidth}x${LIMITES.archivo.maxHeight} píxeles`;
                                }

                                if (errorDimensiones) {
                                    mostrarError(imagenInput, imagenError, errorDimensiones, false);
                                    restaurarVistaPrevia();
                                    imagenInput.value = '';
                                    return;
                                }


                                ocultarImagenActual();
                                previewImage.src = e.target.result;
                                imageInfo.textContent = `${archivo.name} (${(archivo.size / 1024).toFixed(1)} KB, ${this.width}x${this.height}px)`;
                                newImagePreview.style.display = 'block';
                                mostrarError(imagenInput, imagenError, '', true);
                            };

                            img.onerror = function() {
                                mostrarError(imagenInput, imagenError, 'El archivo no es una imagen válida', false);
                                restaurarVistaPrevia();
                                imagenInput.value = '';
                            };

                            img.src = e.target.result;
                        };

                        reader.onerror = function() {
                            mostrarError(imagenInput, imagenError, 'Error al leer el archivo', false);
                            restaurarVistaPrevia();
                            imagenInput.value = '';
                        };

                        reader.readAsDataURL(archivo);
                    }

                    function mostrarError(input, elementoError, mensaje, esValido) {
                        elementoError.textContent = mensaje;

                        if (esValido) {
                            input.classList.remove('error');
                            input.classList.add('success');
                        } else {
                            input.classList.remove('success');
                            input.classList.add('error');
                        }
                    }

                    function limpiarErrorImagen() {
                        imagenError.textContent = '';
                        imagenInput.classList.remove('error', 'success');
                    }

                    function ocultarImagenActual() {
                        if (currentImage) {
                            const currentImageLabel = currentImage.previousElementSibling;
                            if (currentImageLabel) currentImageLabel.style.display = 'none';
                            currentImage.style.display = 'none';
                        }
                        if (noImageText) {
                            noImageText.style.display = 'none';
                        }
                    }

                    function restaurarVistaPrevia() {
                        newImagePreview.style.display = 'none';

                        if (currentImage) {
                            const currentImageLabel = currentImage.previousElementSibling;
                            if (currentImageLabel) currentImageLabel.style.display = 'block';
                            currentImage.style.display = 'block';
                        } else if (noImageText) {
                            noImageText.style.display = 'block';
                        }
                    }


                    validarNombre();
                });
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