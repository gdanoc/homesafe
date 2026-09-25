<?php
include('0_ESvalidacion_Seller.php'); //ESPAÑOL
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$servername = "localhost";
$username = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";
$dbname = "homesafe";

$conn = pg_connect("host=$servername dbname=$dbname user=$username password=$password");
if (!$conn) {
    echo '<p><script>swal({
        title: "Error",
        text: "Error de conexión",
        icon: "warning",
        button: "Close",
    }).then(function() {
        window.location = "frontend_create_vendedor.php";
    });</script></p>';
    exit;
}

if (isset($_POST['submit'])) {
    $email = $_SESSION['email'] ?? null;

    if (!$email) {
        echo '<p><script>swal({
            title: "Error",
            text: "Error de Login",
            icon: "warning",
            button: "Close",
        }).then(function() {
            window.location = "frontend_create_vendedor.php";
        });</script></p>';
        exit;
    }

    
    $plan_id = null;
    $planQuery = "SELECT plan_id FROM suscripcion WHERE mail_user = $1 ORDER BY fecha_vencimiento DESC LIMIT 1";
    $planResult = pg_query_params($conn, $planQuery, array($email));
    if ($planResult && pg_num_rows($planResult) > 0) {
        $planRow = pg_fetch_assoc($planResult);
        $plan_id = intval($planRow['plan_id']);
    }

    
    if ($plan_id === 1) {
        $countQuery = "SELECT COUNT(*) AS total FROM propiedades WHERE mail_user = $1";
        $countResult = pg_query_params($conn, $countQuery, array($email));
        $count = 0;
        if ($countResult && pg_num_rows($countResult) > 0) {
            $row = pg_fetch_assoc($countResult);
            $count = intval($row['total']);
        }

        if ($count >= 5) {
            echo '
                <!DOCTYPE html>
                <html lang="es">
                <head>
                    <meta charset="UTF-8">
                    <title>Límite alcanzado</title>
                    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
                </head>
                <body>
                <script>
                    swal({
                        title: "Límite alcanzado",
                        text: "Tu plan HomeSafe Lite solo permite registrar cinco propiedades.",
                        icon: "warning",
                        button: "Cerrar"
                    }).then(() => { window.location.href = "dashboard_vendedor.php"; });
                </script>
                </body>
                </html>
                ';
            exit;
        }
    }

    
    if ($plan_id === 2) {
        $countQuery = "SELECT COUNT(*) AS total FROM propiedades WHERE mail_user = $1";
        $countResult = pg_query_params($conn, $countQuery, array($email));
        $count = 0;
        if ($countResult && pg_num_rows($countResult) > 0) {
            $row = pg_fetch_assoc($countResult);
            $count = intval($row['total']);
        }

        if ($count >= 10) {
            echo '
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Límite alcanzado</title>
            <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
        </head>
        <body>
        <script>
            swal({
                title: "Límite alcanzado",
                text: "Tu plan HomeSafe Plus solo permite registrar hasta diez propiedades.",
                icon: "warning",
                button: "Cerrar"
            }).then(() => { window.location.href = "dashboard_vendedor.php"; });
        </script>
        </body>
        </html>
        ';
            exit;
        }
    }

    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $size = intval($_POST['size']);
    $precio = floatval($_POST['precio']);
    $fecha = $_POST['fecha'];

    $room_types = $_POST['room_types'] ?? [];

    
    $latitud = isset($_POST['latitud']) && $_POST['latitud'] !== '' ? floatval($_POST['latitud']) : null;
    $longitud = isset($_POST['longitud']) && $_POST['longitud'] !== '' ? floatval($_POST['longitud']) : null;
    $direccion_completa = $_POST['direccion_completa'] ?? null;

    
    if ($latitud === null || $longitud === null) {
        echo '<p><script>swal({
            title: "Error",
            text: "Debes seleccionar una ubicación en el mapa",
            icon: "warning",
            button: "Cerrar",
        }).then(function() {
            window.location = "frontend_create_vendedor.php";
        });</script></p>';
        exit;
    }

    if (!isset($_FILES['main_images']) || count($_FILES['main_images']['name']) == 0) {
        echo '<p><script>swal({
            title: "Error",
            text: "Debes subir al menos una imagen",
            icon: "warning",
            button: "Cerrar",
        }).then(function() {
            window.location = "frontend_create_vendedor.php";
        });</script></p>';
        exit;
    }
    
    $tipos_permitidos = ['Dormitorio', 'Sala', 'Comedor', 'Cocina', 'Baño', 'Otro'];

    
    foreach ($room_types as $room_type) {
        if (!in_array($room_type, $tipos_permitidos)) {
            echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                swal({
                    title: "Error",
                    text: "Tipo de cuarto no válido: ' . htmlspecialchars($room_type) . '",
                    icon: "warning",
                    button: "Cerrar"
                }).then(() => { window.history.back(); });
            });
        </script>';
            exit;
        }
    }

    
    $query = "INSERT INTO propiedades (nombre, descripcion, size, precio, fecha, mail_user, latitud, longitud, direccion_completa) 
              VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9) RETURNING id";
    $result = pg_query_params($conn, $query, array(
        $nombre,
        $descripcion,
        $size,
        $precio,
        $fecha,
        $email,
        $latitud,
        $longitud,
        $direccion_completa
    ));

    if (!$result) {
        $error = pg_last_error($conn);
        echo '<p><script>swal({
            title: "Error creando la propiedad",
            text: "' . addslashes($error) . '",
            icon: "error",
            button: "Cerrar",
        }).then(function() {
            window.location = "frontend_create_vendedor.php";
        });</script></p>';
        exit;
    }

    $propiedad_id = pg_fetch_result($result, 0, 'id');

    
    for ($i = 0; $i < count($_FILES['main_images']['name']); $i++) {
        if ($_FILES['main_images']['error'][$i] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['main_images']['tmp_name'][$i];
            $imgContenido = pg_escape_bytea($conn, file_get_contents($tmpName));

            $nombreImagen = $_FILES['main_images']['name'][$i];
            $orden = $i + 1;

            $query_img = "INSERT INTO carrusel_propiedad (propiedad_id, imagen, nombre_imagen, orden) VALUES ($1, $2, $3, $4)";
            $result_img = pg_query_params($conn, $query_img, array($propiedad_id, $imgContenido, $nombreImagen, $orden));
        }
    }

    $room_names = $_POST['room_names'] ?? [];
    $room_descriptions = $_POST['room_descriptions'] ?? [];
    $room_types = $_POST['room_types'] ?? [];

    
    if ($plan_id === 1 && count($room_names) > 1) {
        echo '<script>
            swal({
                title: "Error",
                text: "Tu plan HomeSafe Lite solo permite agregar un cuarto por propiedad.",
                icon: "warning",
                button: "Cerrar"
            }).then(() => { window.history.back(); });
        </script>';
        exit;
    }

    
    if ($plan_id === 2 && count($room_names) > 3) {
        echo '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Límite de cuartos</title>
        <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    </head>
    <body>
    <script>
        swal({
            title: "Límite de cuartos",
            text: "Tu plan HomeSafe Plus solo permite agregar hasta 3 cuartos por propiedad.",
            icon: "warning",
            button: "Cerrar"
        }).then(() => { window.history.back(); });
    </script>
    </body>
    </html>
    ';
        exit;
    }

    
    foreach ($room_names as $index => $room_name) {
        $room_name = trim($room_name);
        $room_description = $room_descriptions[$index] ?? '';
        $room_type = $room_types[$index] ?? 'Otro';

        
        $query_room = "INSERT INTO cuartos (propiedad_id, nombre_cuarto, descripcion_cuarto, tipo_cuarto) VALUES ($1, $2, $3, $4) RETURNING id";
        $result_room = pg_query_params($conn, $query_room, array($propiedad_id, $room_name, $room_description, $room_type));
        if (!$result_room) {
            continue;
        }
        $room_id = pg_fetch_result($result_room, 0, 'id');

        $room_index = $index + 1;
        $input_name = "room_images_" . $room_index;

        if (isset($_FILES[$input_name])) {
            for ($j = 0; $j < count($_FILES[$input_name]['name']); $j++) {
                if ($_FILES[$input_name]['error'][$j] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES[$input_name]['tmp_name'][$j];
                    $imgContenido = pg_escape_bytea($conn, file_get_contents($tmpName));

                    $nombreImagen = $_FILES[$input_name]['name'][$j];

                    $query_room_img = "INSERT INTO imagenes_cuartos (cuarto_id, imagen, nombre_imagen) VALUES ($1, $2, $3)";
                    $result_room_img = pg_query_params($conn, $query_room_img, array($room_id, $imgContenido, $nombreImagen));
                }
            }
        }
    }

    echo '<p><script>swal({
        title: "Éxito",
        text: "¡Propiedad creada exitosamente!",
        icon: "success",
        button: "Cerrar",
    }).then(function() {
        window.location = "frontend_create_vendedor.php";
    });</script></p>';
}

pg_close($conn);
?>