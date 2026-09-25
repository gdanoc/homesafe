<?php
include('includes/Cookies_sessions.php');
?>
<?php

$host = "localhost";
$port = "5432";
$user = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";
$dbname = "homesafe";

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
if (!$conn) {
    die("Conexión fallida: " . pg_last_error());
}


$id_propiedad = 0;
if (isset($_POST['id'])) {
    $id_propiedad = intval($_POST['id']);
} elseif (isset($_GET['id'])) {
    $id_propiedad = intval($_GET['id']);
}

$query = "SELECT p.id, p.nombre, p.descripcion, p.bathrooms, p.bedrooms, p.size, p.precio, p.fecha, p.imagen, 
p.latitud, p.longitud, p.direccion_completa, p.estado,
a.username AS vendedor_nombre, a.email AS vendedor_email
FROM propiedades p
LEFT JOIN accounts a ON p.mail_user = a.email
WHERE p.id = $1";


$result = pg_query_params($conn, $query, array($id_propiedad));

if ($result && pg_num_rows($result) > 0) {
    $propiedad = pg_fetch_assoc($result);
} else {
    header("Location: properties.php");
    exit();
}

if ($_SESSION['email'] == $propiedad['vendedor_email']) {
    echo '<p><script>swal({
        title: "Error",
        text: "No puedes comprar tu misma propiedad",
        icon: "error",
        button: "Cerrar",
    
        }).then(function() {
        window.location = "properties.php";
    });</script></p>';
    exit();
}


$query_cuartos = "SELECT id, nombre_cuarto, descripcion_cuarto, tipo_cuarto FROM cuartos WHERE propiedad_id = $1";
$result_cuartos = pg_query_params($conn, $query_cuartos, array($id_propiedad));

$total_rooms = 0;
$total_bedrooms = 0;
$total_bathrooms = 0;

$cuartos = [];
if ($result_cuartos) {
    while ($cuarto = pg_fetch_assoc($result_cuartos)) {

        $tipo = strtolower(trim($cuarto['tipo_cuarto'] ?? ''));

        if ($tipo === 'dormitorio') {
            $total_bedrooms++;
            $total_rooms++;
        } elseif ($tipo === 'baño' || $tipo === 'bano') {
            $total_bathrooms++;
            $total_rooms++;
        } else {
            $total_rooms++;
        }


        $query_imgs = "SELECT imagen FROM imagenes_cuartos WHERE cuarto_id = $1 ORDER BY id";
        $result_imgs = pg_query_params($conn, $query_imgs, array($cuarto['id']));
        $imagenes = [];
        if ($result_imgs) {
            while ($img = pg_fetch_assoc($result_imgs)) {
                $imagenes[] = $img['imagen'];
            }
        }
        $cuarto['imagenes'] = $imagenes;
        $cuartos[] = $cuarto;
    }
}

$latitud = $propiedad['latitud'];
$longitud = $propiedad['longitud'];
$direccion = $propiedad['direccion_completa'];
?>
<!DOCTYPE html>
<html class="wide wow-animation" lang="en">

<head>
    <title><?php echo htmlspecialchars($propiedad['nombre']); ?> - HomeSafe</title>
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
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin="" />

    <style>
        /* Variables CSS para consistencia */
        :root {
            --primary-color: #0078ff;
            --primary-dark: #0062cc;
            --primary-light: #4da3ff;
            --secondary-color: #f8f9fa !important;
            --text-color: #333 !important;
            --text-light: #666;
            --light-gray: #e9ecef !important;
            --border-color: #dee2e6 !important;
            --shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 4px 20px rgba(0, 0, 0, 0.15);
            --shadow-light: 0 1px 3px rgba(0, 0, 0, 0.1);
            --border-radius: 8px;
            --border-radius-lg: 12px;
            --transition: all 0.3s ease;
            --max-width: 1400px;
            --gradient-primary: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            --gradient-light: linear-gradient(135deg, #f8f9fa, #e9ecef);
        }

        /* Reset y configuración base */
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Work Sans', sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--secondary-color) !important;
            margin: 0;
            padding: 0;
        }

        /* Contenedor principal responsive */
        .main-container {
            max-width: var(--max-width);
            margin: 0 auto;
            padding: 1rem;
        }

        /* Header de la propiedad - Carrusel responsive */
        .property-carousel-container {
            width: 100%;
            max-width: 900px;
            margin: 0 auto 2rem;
            position: relative;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12) !important;
        }

        .property-header {
            width: 100%;
            height: 60vh;
            min-height: 400px;
            max-height: 500px;
            background-color: white !important;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
            position: relative;
        }

        .property-main-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            transition: var(--transition);
        }

        .property-main-image:hover {
            transform: scale(1.02);
        }

        /* Controles del carrusel mejorados y responsive */
        .carousel-control-prev,
        .carousel-control-next {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, rgba(0, 120, 255, 0.9), rgba(0, 163, 255, 0.9));
            border: 2px solid rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            top: 50%;
            transform: translateY(-50%);
            transition: var(--transition);
            box-shadow: 0 4px 12px rgba(0, 120, 255, 0.3);
            backdrop-filter: blur(10px);
            opacity: 0.8;
        }

        .carousel-control-prev:hover,
        .carousel-control-next:hover {
            opacity: 1;
            transform: translateY(-50%) scale(1.1);
            box-shadow: 0 6px 20px rgba(0, 120, 255, 0.4);
        }

        .carousel-control-prev {
            left: 15px;
        }

        .carousel-control-next {
            right: 15px;
        }

        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            width: 20px;
            height: 20px;
            background-size: 100% 100%;
        }

        /* Indicadores del carrusel */
        .carousel-indicators [data-bs-target] {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.8);
            background-color: rgba(255, 255, 255, 0.3);
            transition: var(--transition);
            margin: 0 4px;
        }

        .carousel-indicators [data-bs-target].active {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            transform: scale(1.2);
        }

        /* Layout responsive principal */
        .property-content {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 2rem;
            margin-top: 2rem;
        }

        /* Información principal de la propiedad */
        .property-main-info {
            background: white !important;
            color: #333 !important;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: 0 6px 20px rgba(0, 120, 255, 0.08) !important;
            border: 1px solid rgba(0, 0, 0, 0.08) !important;
            margin-bottom: 1.5rem;
            transition: var(--transition);
            border-left: 4px solid rgb(74, 132, 255) !important;

        }

        .property-main-info:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15) !important;
            transform: translateY(-2px);
        }

        .property-title-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .property-title-container h1 {
            font-size: clamp(1.5rem, 4vw, 2rem);
            font-weight: 700;
            margin: 0;
            color: #222 !important;
            flex: 1;
            min-width: 250px;
        }

        .property-price {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .property-price h2 {
            font-size: clamp(1.5rem, 5vw, 2rem);
            color: var(--primary-color);
            font-weight: 700;
            margin: 0;
        }

        .property-specs {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            color: #666;
            font-size: 0.9rem;
        }

        .property-specs span {
            background: #e9ecef !important;
            color: #555 !important;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            white-space: nowrap;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06) !important;
            border: 1px solid rgba(0, 0, 0, 0.04) !important;
        }

        /* Secciones de contenido */
        .content-section {
            background: white !important;
            color: #333 !important;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
            border: 1px solid rgba(0, 0, 0, 0.08) !important;
            margin-bottom: 1.5rem;
            transition: var(--transition);
        }

        .content-section:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15) !important;
            transform: translateY(-2px);
        }

        .content-section h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #222 !important;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 0.5rem;
        }

        /* Grid responsive para overview */
        .overview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .overview-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            background-color: #f8f9fa !important;
            border-radius: var(--border-radius);
            transition: var(--transition);
            border-left: 4px solid var(--primary-color);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06) !important;
            border: 1px solid rgba(0, 0, 0, 0.04) !important;
        }

        .overview-item:hover {
            background-color: #e9ecef !important;
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
        }

        .overview-item .label {
            font-weight: 500;
            color: #666;
        }

        .overview-item .value {
            font-weight: 600;
            color: var(--text-color);
        }

        /* Sección de cuartos responsive */
        .room {
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: #f8f9fa !important;
            border-radius: var(--border-radius);
            border-left: 4px solid var(--primary-color);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08) !important;
            border: 1px solid rgba(0, 0, 0, 0.05) !important;
        }

        .room h4 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .room-images {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
            justify-items: center;
        }

        .room-images img {
            width: 100%;
            height: 150px;
            object-fit: contain;
            border-radius: var(--border-radius);
            transition: var(--transition);
            box-shadow: var(--shadow);
            background-color: #f0f0f0;
        }

        .room-images img:hover {
            transform: scale(1.05);
            box-shadow: var(--shadow-hover);
        }

        /* Sidebar de contacto responsive */
        .contact-sidebar {
            position: sticky;
            top: 2rem;
            height: fit-content;
        }

        .contact-section {
            background: white !important;
            color: #333 !important;
            padding: 2.5rem;
            border-radius: var(--border-radius-lg);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12) !important;
            transition: var(--transition);
            border: 1px solid rgba(0, 0, 0, 0.08) !important;
            position: relative;
            overflow: hidden;
        }

        .contact-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-primary);
        }

        .contact-section:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15) !important;
            transform: translateY(-2px);
        }

        /* Información del agente mejorada */
        .agent-info {
            text-align: center;
            padding: 2rem 0;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 2rem;
            position: relative;
        }

        .agent-avatar {
            position: relative;
            display: inline-block;
            margin-bottom: 1.5rem;
        }

        .agent-info img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--primary-color);
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .agent-info img:hover {
            transform: scale(1.05);
            box-shadow: var(--shadow-hover);
        }

        .agent-status {
            position: absolute;
            bottom: 10px;
            right: 10px;
            width: 20px;
            height: 20px;
            background: #28a745;
            border: 3px solid white;
            border-radius: 50%;
            box-shadow: var(--shadow-light);
        }

        .agent-info h3 {
            font-size: 1.4rem;
            margin: 0.5rem 0;
            color: #222 !important;
            font-weight: 600;
        }

        .agent-role {
            color: var(--primary-color);
            font-weight: 500;
            font-size: 0.95rem;
            margin-bottom: 1rem;
        }

        .agent-contact {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            align-items: center;
        }

        .agent-contact-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: #f8f9fa !important;
            color: #666 !important;
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            transition: var(--transition);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05) !important;
            border: 1px solid rgba(0, 0, 0, 0.04) !important;
        }

        .agent-contact-item:hover {
            background: #e9ecef !important;
            color: #333 !important;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08) !important;
        }

        .agent-contact-item i {
            color: var(--primary-color);
            width: 16px;
            text-align: center;
        }

        /* Formulario mejorado */
        .contact-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            width: 100%;
        }

        .contact-form-header {
            text-align: center;
            margin-bottom: 1rem;
        }

        .contact-form-header h4 {
            color: var(--text-color);
            font-size: 1.2rem;
            font-weight: 600;
            margin: 0 0 0.5rem 0;
        }

        .contact-form-header p {
            color: var(--text-light);
            font-size: 0.9rem;
            margin: 0;
        }

        .form-group {
            position: relative;
            margin-bottom: 1rem;
        }

        .form-group label {
            font-weight: 500;
            font-size: 0.9rem;
            color: var(--text-color);
            margin-bottom: 0.5rem;
            display: block;
            transition: var(--transition);
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid #dee2e6 !important;
            border-radius: var(--border-radius);
            font-family: inherit;
            font-size: 1rem;
            transition: var(--transition);
            background: white !important;
            color: #333 !important;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05) !important;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            background: white !important;
            color: #333 !important;
            border-color: var(--primary-color) !important;
            box-shadow: 0 0 0 3px rgba(0, 120, 255, 0.1), inset 0 1px 3px rgba(0, 0, 0, 0.05) !important;
            transform: translateY(-1px);
        }

        .form-group input:focus+label,
        .form-group textarea:focus+label,
        .form-group select:focus+label {
            color: var(--primary-color);
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
            font-family: inherit;
        }

        .form-group select {
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.75rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
            appearance: none;
        }

        /* Botones mejorados */
        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .send-message-btn {
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 1.25rem 2rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transition);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .send-message-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .send-message-btn:hover::before {
            left: 100%;
        }

        .send-message-btn:hover {
            background: linear-gradient(135deg, var(--primary-dark), #004494);
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        .send-message-btn:active {
            transform: translateY(0);
        }

        .send-message-btn i {
            margin-right: 0.5rem;
        }

        .btn-secondary {
            background: var(--gradient-light);
            color: var(--text-color);
            border: 2px solid var(--border-color);
            padding: 1rem 2rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            font-size: 0.95rem;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-secondary:hover {
            background: white;
            border-color: var(--primary-color);
            color: var(--primary-color);
            transform: translateY(-1px);
            box-shadow: var(--shadow-light);
        }

        /* Información adicional */
        .contact-info {
            background: #f8f9fa !important;
            color: #333 !important;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            margin-top: 1.5rem;
            border-left: 4px solid var(--primary-color);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08) !important;
            border: 1px solid rgba(0, 0, 0, 0.05) !important;
        }

        .contact-info h5 {
            color: var(--text-color);
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .contact-info-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            font-size: 0.9rem;
            color: var(--text-light);
        }

        .contact-info-item:last-child {
            margin-bottom: 0;
        }

        .contact-info-item i {
            color: var(--primary-color);
            width: 16px;
            text-align: center;
        }

        .simple-carousel-control {
            width: auto !important;
            height: auto !important;
            background: none !important;
            border: none !important;
            border-radius: 0 !important;
            top: 50%;
            transform: translateY(-50%);
            transition: all 0.3s ease;
            box-shadow: none !important;
            backdrop-filter: none !important;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
        }

        .simple-carousel-control:hover {
            background: none !important;
            border: none !important;
            transform: translateY(-50%) scale(1.2);
            box-shadow: none !important;
        }

        .simple-carousel-control:active {
            transform: translateY(-50%) scale(1.1);
        }

        .carousel-control-prev.simple-carousel-control {
            left: 15px;
        }

        .carousel-control-next.simple-carousel-control {
            right: 15px;
        }

        .simple-carousel-control .carousel-control-prev-icon,
        .simple-carousel-control .carousel-control-next-icon {
            width: 30px;
            height: 30px;
            background-size: 100% 100%;
            filter: drop-shadow(2px 2px 4px rgba(0, 0, 0, 0.5));
        }

        .simple-carousel-control .carousel-control-prev-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23ffffff'%3e%3cpath d='m3.86 8.753 5.482 4.796c.646.566 1.658.106 1.658-.753V3.204a1 1 0 0 0-1.659-.753l-5.48 4.796a1 1 0 0 0 0 1.506z'/%3e%3c/svg%3e");
        }

        .simple-carousel-control .carousel-control-next-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23ffffff'%3e%3cpath d='m12.14 8.753-5.482 4.796c-.646.566-1.658.106-1.658-.753V3.204a1 1 0 0 1 1.659-.753l5.48 4.796a1 1 0 0 1 0 1.506z'/%3e%3c/svg%3e");
        }

        .simple-carousel-control::before {
            display: none;
        }

        #map {
            height: 400px;
            width: 100%;
            border-radius: 10px;
            border: 2px solid #dee2e6;
            margin-bottom: 10px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1) !important;
        }

        .location-info {
            background: white !important;
            color: #333 !important;
            padding: 1rem 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
            border: 1px solid rgba(0, 0, 0, 0.08) !important;
            margin-bottom: 2rem;
            font-size: 1rem;
            border-left: 4px solid rgb(74, 132, 255) !important;

        }

        /* Responsive Design - Mobile First */

        /* Variables CSS para consistencia */
        :root {
            --primary-color: #0078ff;
            --primary-dark: #0062cc;
            --primary-light: #4da3ff;
            --secondary-color: #f8f9fa !important;
            --text-color: #333 !important;
            --text-light: #666;
            --light-gray: #e9ecef !important;
            --border-color: #dee2e6 !important;
            --shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 4px 20px rgba(0, 0, 0, 0.15);
            --shadow-light: 0 1px 3px rgba(0, 0, 0, 0.1);
            --border-radius: 8px;
            --border-radius-lg: 12px;
            --transition: all 0.3s ease;
            --max-width: 1400px;
            --gradient-primary: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            --gradient-light: linear-gradient(135deg, #f8f9fa, #e9ecef);
        }

        /* Reset y configuración base */
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Work Sans', sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--secondary-color) !important;
            margin: 0;
            padding: 0;
        }

        /* Contenedor principal responsive */
        .main-container {
            max-width: var(--max-width);
            margin: 0 auto;
            padding: 1rem;
        }

        /* Header de la propiedad - Carrusel responsive */
        .property-carousel-container {
            width: 100%;
            max-width: 900px;
            margin: 0 auto 2rem;
            position: relative;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12) !important;
        }

        .property-header {
            width: 100%;
            height: 60vh;
            min-height: 400px;
            max-height: 500px;
            background-color: white !important;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
            position: relative;
        }

        .property-main-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            transition: var(--transition);
        }

        .property-main-image:hover {
            transform: scale(1.02);
        }

        /* Controles del carrusel mejorados y responsive */
        .carousel-control-prev,
        .carousel-control-next {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, rgba(0, 120, 255, 0.9), rgba(0, 163, 255, 0.9));
            border: 2px solid rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            top: 50%;
            transform: translateY(-50%);
            transition: var(--transition);
            box-shadow: 0 4px 12px rgba(0, 120, 255, 0.3);
            backdrop-filter: blur(10px);
            opacity: 0.8;
        }

        .carousel-control-prev:hover,
        .carousel-control-next:hover {
            opacity: 1;
            transform: translateY(-50%) scale(1.1);
            box-shadow: 0 6px 20px rgba(0, 120, 255, 0.4);
        }

        .carousel-control-prev {
            left: 15px;
        }

        .carousel-control-next {
            right: 15px;
        }

        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            width: 20px;
            height: 20px;
            background-size: 100% 100%;
        }

        /* Indicadores del carrusel */
        .carousel-indicators [data-bs-target] {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.8);
            background-color: rgba(255, 255, 255, 0.3);
            transition: var(--transition);
            margin: 0 4px;
        }

        .carousel-indicators [data-bs-target].active {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            transform: scale(1.2);
        }

        /* Layout responsive principal */
        .property-content {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 2rem;
            margin-top: 2rem;
        }


        .property-title-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .property-title-container h1 {
            font-size: clamp(1.5rem, 4vw, 2rem);
            font-weight: 700;
            margin: 0;
            color: #222 !important;
            flex: 1;
            min-width: 250px;
        }

        .property-price {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .property-price h2 {
            font-size: clamp(1.5rem, 5vw, 2rem);
            color: var(--primary-color);
            font-weight: 700;
            margin: 0;
        }

        .property-specs {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            color: #666;
            font-size: 0.9rem;
        }

        .property-specs span {
            background: #e9ecef !important;
            color: #555 !important;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            white-space: nowrap;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06) !important;
            border: 1px solid rgba(0, 0, 0, 0.04) !important;
        }

        /* Secciones de contenido */
        .content-section {
            background: white !important;
            color: #333 !important;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
            border: 1px solid rgba(0, 0, 0, 0.08) !important;
            margin-bottom: 1.5rem;
            transition: var(--transition);
        }

        .content-section:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15) !important;
            transform: translateY(-2px);
        }

        .content-section h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #222 !important;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 0.5rem;
        }

        /* Grid responsive para overview */
        .overview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .overview-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            background-color: rgb(206, 206, 206)t;
            border-radius: var(--border-radius);
            transition: var(--transition);
            border-left: 4px solid var(--primary-color);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06) !important;
            border-left: 4px solid rgb(74, 132, 255) !important;
        }

        .overview-item:hover {
            background-color: #e9ecef !important;
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
        }

        .overview-item .label {
            font-weight: 500;
            color: #666;
        }

        .overview-item .value {
            font-weight: 600;
            color: var(--text-color);
        }

        /* Sección de cuartos responsive */
        .room {
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: #f8f9fa !important;
            border-radius: var(--border-radius);
            border-left: 4px solid var(--primary-color);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08) !important;
            border: 1px solid rgba(0, 0, 0, 0.05) !important;
            border-left: 4px solid rgb(74, 132, 255) !important;

        }

        .room h4 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .room-images {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
            justify-items: center;
        }

        .room-images img {
            width: 100%;
            height: 150px;
            object-fit: contain;
            border-radius: var(--border-radius);
            transition: var(--transition);
            box-shadow: var(--shadow);
            background-color: #f0f0f0;
        }

        .room-images img:hover {
            transform: scale(1.05);
            box-shadow: var(--shadow-hover);
        }

        /* Sidebar de contacto responsive */
        .contact-sidebar {
            position: sticky;
            top: 2rem;
            height: fit-content;
        }

        .contact-section {
            background: white !important;
            color: #333 !important;
            padding: 2.5rem;
            border-radius: var(--border-radius-lg);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12) !important;
            transition: var(--transition);
            border: 1px solid rgba(0, 0, 0, 0.08) !important;
            position: relative;
            overflow: hidden;
        }

        .contact-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-primary);
        }

        .contact-section:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15) !important;
            transform: translateY(-2px);
        }

        /* Información del agente mejorada */
        .agent-info {
            text-align: center;
            padding: 2rem 0;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 2rem;
            position: relative;
        }

        .agent-avatar {
            position: relative;
            display: inline-block;
            margin-bottom: 1.5rem;
        }

        .agent-info img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--primary-color);
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .agent-info img:hover {
            transform: scale(1.05);
            box-shadow: var(--shadow-hover);
        }

        .agent-status {
            position: absolute;
            bottom: 10px;
            right: 10px;
            width: 20px;
            height: 20px;
            background: #28a745;
            border: 3px solid white;
            border-radius: 50%;
            box-shadow: var(--shadow-light);
        }

        .agent-info h3 {
            font-size: 1.4rem;
            margin: 0.5rem 0;
            color: #222 !important;
            font-weight: 600;
        }

        .agent-role {
            color: var(--primary-color);
            font-weight: 500;
            font-size: 0.95rem;
            margin-bottom: 1rem;
        }

        .agent-contact {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            align-items: center;
        }

        .agent-contact-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: #f8f9fa !important;
            color: #666 !important;
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            transition: var(--transition);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05) !important;
            border: 1px solid rgba(0, 0, 0, 0.04) !important;
        }

        .agent-contact-item:hover {
            background: #e9ecef !important;
            color: #333 !important;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08) !important;
        }

        .agent-contact-item i {
            color: var(--primary-color);
            width: 16px;
            text-align: center;
        }

        /* Formulario mejorado */
        .contact-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            width: 100%;
        }

        .contact-form-header {
            text-align: center;
            margin-bottom: 1rem;
        }

        .contact-form-header h4 {
            color: var(--text-color);
            font-size: 1.2rem;
            font-weight: 600;
            margin: 0 0 0.5rem 0;
        }

        .contact-form-header p {
            color: var(--text-light);
            font-size: 0.9rem;
            margin: 0;
        }

        .form-group {
            position: relative;
            margin-bottom: 1rem;
        }

        .form-group label {
            font-weight: 500;
            font-size: 0.9rem;
            color: var(--text-color);
            margin-bottom: 0.5rem;
            display: block;
            transition: var(--transition);
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid #dee2e6 !important;
            border-radius: var(--border-radius);
            font-family: inherit;
            font-size: 1rem;
            transition: var(--transition);
            background: white !important;
            color: #333 !important;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05) !important;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            background: white !important;
            color: #333 !important;
            border-color: var(--primary-color) !important;
            box-shadow: 0 0 0 3px rgba(0, 120, 255, 0.1), inset 0 1px 3px rgba(0, 0, 0, 0.05) !important;
            transform: translateY(-1px);
        }

        .form-group input:focus+label,
        .form-group textarea:focus+label,
        .form-group select:focus+label {
            color: var(--primary-color);
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
            font-family: inherit;
        }

        .form-group select {
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.75rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
            appearance: none;
        }

        /* Botones mejorados */
        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .send-message-btn {
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 1.25rem 2rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transition);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .send-message-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .send-message-btn:hover::before {
            left: 100%;
        }

        .send-message-btn:hover {
            background: linear-gradient(135deg, var(--primary-dark), #004494);
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        .send-message-btn:active {
            transform: translateY(0);
        }

        .send-message-btn i {
            margin-right: 0.5rem;
        }

        .btn-secondary {
            background: var(--gradient-light);
            color: var(--text-color);
            border: 2px solid var(--border-color);
            padding: 1rem 2rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            font-size: 0.95rem;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-secondary:hover {
            background: white;
            border-color: var(--primary-color);
            color: var(--primary-color);
            transform: translateY(-1px);
            box-shadow: var(--shadow-light);
        }

        /* Información adicional */
        .contact-info {
            background: #f8f9fa !important;
            color: #333 !important;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            margin-top: 1.5rem;
            border-left: 4px solid var(--primary-color);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08) !important;
            border: 1px solid rgba(0, 0, 0, 0.05) !important;
        }

        .contact-info h5 {
            color: var(--text-color);
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .contact-info-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            font-size: 0.9rem;
            color: var(--text-light);
        }

        .contact-info-item:last-child {
            margin-bottom: 0;
        }

        .contact-info-item i {
            color: var(--primary-color);
            width: 16px;
            text-align: center;
        }

        .simple-carousel-control {
            width: auto !important;
            height: auto !important;
            background: none !important;
            border: none !important;
            border-radius: 0 !important;
            top: 50%;
            transform: translateY(-50%);
            transition: all 0.3s ease;
            box-shadow: none !important;
            backdrop-filter: none !important;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
        }

        .simple-carousel-control:hover {
            background: none !important;
            border: none !important;
            transform: translateY(-50%) scale(1.2);
            box-shadow: none !important;
        }

        .simple-carousel-control:active {
            transform: translateY(-50%) scale(1.1);
        }

        .carousel-control-prev.simple-carousel-control {
            left: 15px;
        }

        .carousel-control-next.simple-carousel-control {
            right: 15px;
        }

        .simple-carousel-control .carousel-control-prev-icon,
        .simple-carousel-control .carousel-control-next-icon {
            width: 30px;
            height: 30px;
            background-size: 100% 100%;
            filter: drop-shadow(2px 2px 4px rgba(0, 0, 0, 0.5));
        }

        .simple-carousel-control .carousel-control-prev-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23ffffff'%3e%3cpath d='m3.86 8.753 5.482 4.796c.646.566 1.658.106 1.658-.753V3.204a1 1 0 0 0-1.659-.753l-5.48 4.796a1 1 0 0 0 0 1.506z'/%3e%3c/svg%3e");
        }

        .simple-carousel-control .carousel-control-next-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23ffffff'%3e%3cpath d='m12.14 8.753-5.482 4.796c-.646.566-1.658.106-1.658-.753V3.204a1 1 0 0 1 1.659-.753l5.48 4.796a1 1 0 0 1 0 1.506z'/%3e%3c/svg%3e");
        }

        .simple-carousel-control::before {
            display: none;
        }

        #map {
            height: 400px;
            width: 100%;
            border-radius: 10px;
            border: 2px solid #dee2e6;
            margin-bottom: 10px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1) !important;
        }

        /* Pantallas grandes - 1200px y arriba */
        @media (min-width: 1200px) {
            .property-content {
                grid-template-columns: 1fr 400px;
                gap: 3rem;
            }

            .property-main-info,
            .content-section {
                padding: 2.5rem;
            }

            .overview-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Pantallas extra grandes - 1400px y arriba */
        @media (min-width: 1400px) {
            .main-container {
                padding: 2rem;
            }

            .property-carousel-container {
                max-width: 1000px;
            }

            .room-images {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }

            .room-images img {
                height: 200px;
            }
        }

        /* Mejoras de accesibilidad */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            :root {
                --text-color: #e0e0e0;
                --secondary-color: #1a1a1a;
                --light-gray: #2a2a2a;
                --border-color: #404040;
            }

            body {
                background-color: #121212;
            }

            .content-section,
            .property-main-info,
            .contact-section {
                background: #1e1e1e;
                color: var(--text-color);
            }
        }

        /* Animaciones suaves */
        .fade-in {
            animation: fadeIn 0.6s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Utilidades responsive */
        .d-none-mobile {
            display: block;
        }

        .d-block-mobile {
            display: none;
        }

        @media (max-width: 768px) {
            .d-none-mobile {
                display: none;
            }

            .d-block-mobile {
                display: block;
            }
        }

        .send-message-btn:disabled {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
            cursor: not-allowed;
            opacity: 0.7;
        }

        .send-message-btn:disabled:hover {
            transform: none;
            box-shadow: var(--shadow);
        }

        .alert-warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 1rem;
            border-radius: var(--border-radius);
            font-weight: 500;
        }

        .location-info a.btn {
            font-weight: 600;
            text-decoration: none;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            transition: background-color 0.3s ease;
        }

        .location-info a.btn:hover {
            background-color: #0056b3;
            color: white;
            text-decoration: none;
        }

        .property-description {
            border-left: 4px solid rgb(74, 132, 255) !important;

        }

        .galeria-button {
            /* Diseño base */
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;

            /* Centrado */
            margin: 0 auto;

            /* Tamaño compacto */
            padding: 10px 16px;
            min-width: 44px;
            height: 44px;

            /* Tipografía */
            font-size: 14px;
            font-weight: 500;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            text-decoration: none;
            color: rgb(69, 85, 112);

            /* Estilo visual minimalista */
            background: #ffffff;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);

            /* Transiciones suaves */
            transition: all 0.2s ease-in-out;
            cursor: pointer;
            user-select: none;
            margin-top: 10px;
        }

        /* Estados de interacción */
        .galeria-button:hover {
            background: #f9fafb;
            border-color: #d1d5db;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transform: translateY(-1px);
            color: #111827;
        }

        .galeria-button:active {
            transform: translateY(0);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            background: #f3f4f6;
        }

        .galeria-button:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            border-color: #3b82f6;
        }

        /* Versión solo icono (para botón muy pequeño) */
        .galeria-button.icon-only {
            width: 40px;
            height: 40px;
            padding: 8px;
            min-width: unset;
        }

        /* Icono dentro del botón */
        .galeria-button .icon {
            width: 18px;
            height: 18px;
            stroke-width: 1.5;
        }

        /* Contenedor para centrar el botón */
        .galeria-button-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .galeria-button {
                padding: 8px 12px;
                font-size: 13px;
            }

            .galeria-button.icon-only {
                width: 36px;
                height: 36px;
            }
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
                                    <li class="rd-nav-item"><a class="rd-nav-link"
                                            href="properties.php">Propiedades</a>
                                    </li>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="furniture.php">Muebles</a>
                                    </li>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="about-us.php">Sobre
                                            Nosotros</a>
                                    </li>
                                    <?php if (!isset($_SESSION['email'])): ?>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-bs-toggle="modal"
                                                data-bs-target="#loginModal">Iniciar Sesión</a></li>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-bs-toggle="modal"
                                                data-bs-target="#RegisterModal">Registrarse</a></li>
                                    <?php else: ?>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="logout.p.php">Cerrar Sesión</a>
                                        <?php endif; ?>
                                        <?php if (isset($_SESSION['email'])): ?>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-bs-toggle="modal" data-bs-target="#PerfilModal"> Sesión Actual: <?php echo htmlspecialchars($username); ?></a>
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

        <?php

        $query_carrusel = "SELECT imagen FROM carrusel_propiedad WHERE propiedad_id = $1 ORDER BY orden";
        $result_carrusel = pg_query_params($conn, $query_carrusel, array($id_propiedad));

        $carrusel_imagenes = [];
        if ($result_carrusel) {
            while ($img = pg_fetch_assoc($result_carrusel)) {
                $carrusel_imagenes[] = $img['imagen'];
            }
        }
        ?>

        <div id="propertyCarousel" class="carousel slide property-header mb-0 position-relative" data-bs-ride="carousel" style="max-width: 900px; margin: auto;">
            <div class="carousel-indicators">
                <?php foreach ($carrusel_imagenes as $index => $imagen): ?>
                    <button type="button" data-bs-target="#propertyCarousel" data-bs-slide-to="<?php echo $index; ?>" <?php if ($index === 0) echo 'class="active" aria-current="true"'; ?> aria-label="Slide <?php echo $index + 1; ?>"></button>
                <?php endforeach; ?>
            </div>
            <div class="carousel-inner rounded overflow-hidden shadow-sm">
                <?php foreach ($carrusel_imagenes as $index => $imagen): ?>
                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                        <img src="data:image/jpeg;base64,<?php echo base64_encode(pg_unescape_bytea($imagen)); ?>"
                            class="d-block w-100 property-main-image rounded" alt="Imagen <?php echo $index + 1; ?>">
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev simple-carousel-control" type="button" data-bs-target="#propertyCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
                <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next simple-carousel-control" type="button" data-bs-target="#propertyCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
                <span class="visually-hidden">Siguiente</span>
            </button>
        </div>
        <!-- Botón "Ver más fotos" alineado a la derecha debajo del carrusel -->
        <div class="d-flex justify-content-end" style="max-width:900px;margin:auto;margin-bottom:1.5rem;">
            <button type="button" class="btn btn-success galeria-button" id="openGalleryBtn">
                <i class="fa fa-camera"></i> Ver más fotos
            </button>
        </div>
        <!-- Contenedor principal -->
        <div class="main-container">
            <!-- Mapa Leaflet -->
            <div id="map"></div>
            <div class="location-info">
                <strong>Dirección:</strong> <?php echo htmlspecialchars($direccion); ?><br>
                <strong>Coordenadas:</strong> <?php echo number_format($latitud, 6); ?>, <?php echo number_format($longitud, 6); ?><br><br>
                <a href="https://www.google.com/maps?q=<?php echo urlencode($latitud . ',' . $longitud); ?>"
                    target="_blank" rel="noopener noreferrer"
                    class="btn btn-primary btn-sm d-inline-flex align-items-center"
                    title="Haz clic para ver la ubicación en Google Maps">
                    <i class="fa fa-map-marker-alt me-2"></i> Ver ubicación en Google Maps
                </a>
            </div>
            <div class="row">
                <div class="col-lg-8">
                    <section class="property-main-info bg-white p-4 rounded shadow-sm mb-4">
                        <div class="property-title-container">
                            <h1><?php echo htmlspecialchars($propiedad['nombre']); ?></h1>
                            <div class="property-actions">
                                <!-- Aquí puedes poner botones de acción si quieres -->
                            </div>
                        </div>

                        <div class="property-price">
                            <h2>$<?php echo number_format($propiedad['precio'], 2); ?></h2>
                            <div class="property-specs">
                                <span><b>Tamaño:</b> <?php echo htmlspecialchars($propiedad['size']); ?> m²</span>
                                <span><b>Cuartos Totales:</b> <?php echo $total_rooms; ?></span>
                            </div>
                        </div>
                    </section>

                    <section class="property-description bg-white p-4 rounded shadow-sm mb-4">
                        <h3 class="mb-3">Descripción</h3>
                        <p><?php echo htmlspecialchars($propiedad['descripcion']); ?></p>
                    </section>
                    <section class="property-rooms bg-white p-4 rounded shadow-sm mb-4">
                        <h3 class="mb-4">Cuartos y Espacios</h3>
                        <?php if (count($cuartos) > 0): ?>
                            <?php foreach ($cuartos as $cuarto): ?>
                                <div class="room mb-4">
                                    <h4>
                                        <?php echo htmlspecialchars($cuarto['nombre_cuarto']); ?>
                                        <small style="font-weight: 700; font-size: 1rem; color: #007bff; margin-left: 8px; text-transform: capitalize;">
                                            (<?php echo htmlspecialchars($cuarto['tipo_cuarto']); ?>)
                                        </small>
                                    </h4>
                                    <p><?php echo nl2br(htmlspecialchars($cuarto['descripcion_cuarto'])); ?></p>
                                    <div class="room-images d-flex gap-2 flex-wrap">
                                        <?php foreach ($cuarto['imagenes'] as $imagen): ?>
                                            <img src="data:image/jpeg;base64,<?php echo base64_encode(pg_unescape_bytea($imagen)); ?>"
                                                alt="Room Image" class="room-image" style="max-width:120px;max-height:120px;">
                                        <?php endforeach; ?>
                                    </div>
                                    <?php if (count($cuarto['imagenes']) > 0): ?>
                                        <div class="d-flex justify-content-end mt-2">
                                            <button type="button" class="btn btn-success openRoomGalleryBtn galeria-button"
                                                data-room-id="<?php echo $cuarto['id']; ?>">
                                                <i class="fa fa-camera"></i> Ver más fotos de este cuarto
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                    <!-- Modal galería para este cuarto -->
                                    <div class="modal fade" id="roomGalleryModal-<?php echo $cuarto['id']; ?>" tabindex="-1"
                                        aria-labelledby="roomGalleryModalLabel-<?php echo $cuarto['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header bg_background text-white" style="padding:1.5rem 2rem 1rem;">
                                                    <h5 class="modal-title" id="roomGalleryModalLabel-<?php echo $cuarto['id']; ?>">
                                                        Galería de Fotos - <?php echo htmlspecialchars($cuarto['nombre_cuarto']); ?>
                                                    </h5>
                                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true"></span>
                                                    </button>
                                                </div>
                                                <div class="modal-body d-flex flex-column align-items-center justify-content-center" style="background:#fafafa;">
                                                    <div class="w-100 d-flex justify-content-between align-items-center mb-3" style="max-width:500px;">
                                                        <button class="btn btn-light roomGalleryPrevBtn" data-room-id="<?php echo $cuarto['id']; ?>"><i class="fa fa-arrow-left"></i></button>
                                                        <span id="roomGalleryCounter-<?php echo $cuarto['id']; ?>" class="fw-bold"></span>
                                                        <button class="btn btn-light roomGalleryNextBtn" data-room-id="<?php echo $cuarto['id']; ?>"><i class="fa fa-arrow-right"></i></button>
                                                    </div>
                                                    <img id="roomGalleryImage-<?php echo $cuarto['id']; ?>" src="" alt="Foto cuarto"
                                                        class="img-fluid rounded shadow" style="max-height:60vh;max-width:100%;object-fit:contain;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                            <?php else: ?>
                                <p>No hay cuartos añadidos a esta propiedad.</p>
                            <?php endif; ?>
                    </section>
                    <section class="property-overview bg-white p-4 rounded shadow-sm mb-4">
                        <h3 class="mb-4">Visión General</h3>
                        <div class="overview-grid">
                            <div class="overview-item">
                                <span class="label">Estado:</span>
                                <span class="value">En venta</span>
                            </div>
                            <div class="overview-item">
                                <span class="label">Precio de Venta:</span>
                                <span class="value">$<?php echo number_format($propiedad['precio'], 2); ?></span>
                            </div>
                            <div class="overview-item">
                                <span class="label">Tamaño de la Propiedad:</span>
                                <span class="value"><?php echo htmlspecialchars($propiedad['size']); ?> m²</span>
                            </div>
                            <div class="overview-item">
                                <span class="label">Fecha de publicación:</span>
                                <span class="value"><?php echo htmlspecialchars($propiedad['fecha']); ?></span>
                            </div>
                            <div class="overview-item">
                                <span><b>Dormitorios:</b> <?php echo $total_bedrooms; ?></span>
                            </div>
                            <div class="overview-item">
                                <span><b>Baños:</b> <?php echo $total_bathrooms; ?></span>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="col-lg-4">
                    <!-- Agent information and contact form -->
                    <section class="contact-section bg-white p-4 rounded shadow-sm">
                        <div class="agent-info mb-4">
                            <img src="images/agent-default.jpg" alt="Real Estate Agent" class="float rounded-circle" style="width:120px; height:120px; object-fit:cover;">
                            <h3 class="mt-3"><?php echo htmlspecialchars($propiedad['vendedor_nombre'] ?? 'No disponible'); ?></h3>
                            <p><i class="fa fa-envelope"></i> <?php echo htmlspecialchars($propiedad['vendedor_email'] ?? 'No disponible'); ?></p>
                        </div>

                        <?php if ($propiedad['estado'] == 'Vendido'): ?>
                            <!-- Mostrar estado vendido -->
                            <div class="alert alert-warning text-center mb-3">
                                <i class="fa fa-info-circle"></i> Esta propiedad ya ha sido vendida
                            </div>
                            <button type="button" class="send-message-btn btn btn-secondary btn-block" disabled>
                                <i class="fa fa-ban"></i> Propiedad Vendida
                            </button>
                        <?php else: ?>
                            <!-- Formulario normal para propiedades disponibles -->
                            <form class="contact-form" method="post" action="chat_solicitud.php">
                                <input type="hidden" name="property_id" value="<?php echo $propiedad['id']; ?>">
                                <input type="hidden" name="vendedor_email" value="<?php echo $propiedad['vendedor_email']; ?>">
                                <button type="submit" class="send-message-btn btn btn-primary btn-block">
                                    <i class="fa fa-comments"></i> Solicitar chat
                                </button>
                            </form>
                        <?php endif; ?>
                    </section>
                </div>
            </div>
        </div>

        <!-- Leaflet JS -->
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const lat = parseFloat('<?php echo $latitud; ?>');
                const lng = parseFloat('<?php echo $longitud; ?>');


                const map = L.map('map', {
                    center: [lat, lng],
                    zoom: 15,
                    zoomControl: true,
                    dragging: false,
                    scrollWheelZoom: false,
                    doubleClickZoom: false,
                    boxZoom: false,
                    keyboard: false,
                    tap: false,
                    touchZoom: false
                });


                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    maxZoom: 19,
                }).addTo(map);


                L.marker([lat, lng]).addTo(map)
                    .bindPopup('Ubicación de la propiedad')
                    .openPopup();
            });
        </script>
        <!-- Global Mailform Output-->
        <div class="snackbars" id="form-output-global"></div>
        <!-- Javascript-->
        <script src="js/core.min.js"></script>
        <script src="js/script.js"></script>
    </div>
    <!-- Modal Galería de Fotos -->
    <div class="modal fade" id="galleryModal" tabindex="-1" aria-labelledby="galleryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-enhanced">
            <div class="modal-content">
                <div class="modal-header bg_background text-white" style="padding:1.5rem 2rem 1rem;">
                    <h5 class="modal-title" id="galleryModalLabel" style="color:white;font-weight:600;">
                        Galería de Fotos
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body d-flex flex-column align-items-center justify-content-center" style="background:#fafafa;">
                    <div class="w-100 d-flex justify-content-between align-items-center mb-3" style="max-width:500px;">
                        <button class="btn btn-light" id="galleryPrevBtn"><i class="fa fa-arrow-left"></i></button>
                        <span id="galleryCounter" class="fw-bold"></span>
                        <button class="btn btn-light" id="galleryNextBtn"><i class="fa fa-arrow-right"></i></button>
                    </div>
                    <img id="galleryImage" src="" alt="Foto propiedad" class="img-fluid rounded shadow" style="max-height:60vh;max-width:100%;object-fit:contain;">
                </div>
            </div>
        </div>
    </div>
    <script>
        const galleryImages = [
            <?php foreach ($carrusel_imagenes as $img): ?> "data:image/jpeg;base64,<?php echo base64_encode(pg_unescape_bytea($img)); ?>",
            <?php endforeach; ?>
        ];

        let currentGalleryIndex = 0;

        function showGalleryImage(idx) {
            if (galleryImages.length === 0) return;
            currentGalleryIndex = (idx + galleryImages.length) % galleryImages.length;
            document.getElementById('galleryImage').src = galleryImages[currentGalleryIndex];
            document.getElementById('galleryCounter').textContent = (currentGalleryIndex + 1) + ' / ' + galleryImages.length;
        }

        document.getElementById('openGalleryBtn').addEventListener('click', function() {
            showGalleryImage(0);
            var modal = new bootstrap.Modal(document.getElementById('galleryModal'));
            modal.show();
        });

        document.getElementById('galleryPrevBtn').addEventListener('click', function() {
            showGalleryImage(currentGalleryIndex - 1);
        });

        document.getElementById('galleryNextBtn').addEventListener('click', function() {
            showGalleryImage(currentGalleryIndex + 1);
        });
    </script>
    <script>
        const roomGalleries = {};
        <?php foreach ($cuartos as $cuarto): ?>
            roomGalleries[<?php echo $cuarto['id']; ?>] = [
                <?php foreach ($cuarto['imagenes'] as $img): ?> "data:image/jpeg;base64,<?php echo base64_encode(pg_unescape_bytea($img)); ?>",
                <?php endforeach; ?>
            ];
        <?php endforeach; ?>
    </script>
    <script>
        const roomGalleryState = {};

        document.querySelectorAll('.openRoomGalleryBtn').forEach(btn => {
            btn.addEventListener('click', function() {
                const roomId = this.getAttribute('data-room-id');
                roomGalleryState[roomId] = 0;
                showRoomGalleryImage(roomId, 0);
                const modal = new bootstrap.Modal(document.getElementById('roomGalleryModal-' + roomId));
                modal.show();
            });
        });

        document.querySelectorAll('.roomGalleryPrevBtn').forEach(btn => {
            btn.addEventListener('click', function() {
                const roomId = this.getAttribute('data-room-id');
                showRoomGalleryImage(roomId, roomGalleryState[roomId] - 1);
            });
        });

        document.querySelectorAll('.roomGalleryNextBtn').forEach(btn => {
            btn.addEventListener('click', function() {
                const roomId = this.getAttribute('data-room-id');
                showRoomGalleryImage(roomId, roomGalleryState[roomId] + 1);
            });
        });

        function showRoomGalleryImage(roomId, idx) {
            const images = roomGalleries[roomId];
            if (!images || images.length === 0) return;
            roomGalleryState[roomId] = (idx + images.length) % images.length;
            document.getElementById('roomGalleryImage-' + roomId).src = images[roomGalleryState[roomId]];
            document.getElementById('roomGalleryCounter-' + roomId).textContent =
                (roomGalleryState[roomId] + 1) + ' / ' + images.length;
        }
    </script>

</body>

</html>
<?php pg_close($conn); ?>