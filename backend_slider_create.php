<?php include('0_ESvalidacion_Admin.php'); //ESPAÑOL ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
$email = $_SESSION['email'] ?? null;

if (!$email) {
    echo '<script>
        swal({
            title: "Error",
            text: "Sesión no iniciada",
            icon: "warning",
            button: "Cerrar"
        }).then(() => { window.location = "login.php"; });
    </script>';
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
    die("Conexión fallida: " . pg_last_error());
}

if (isset($_POST['submit'])) {
    $nombre = trim($_POST['nombre'] ?? '');
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

    
    if (!isset($_FILES['imagen']) || $_FILES['imagen']['size'] === 0) {
        $errores[] = "Debe seleccionar una imagen.";
    } else {
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
            swal({
                title: 'Error de validación',
                text: '" . implode("\\n", $errores) . "',
                icon: 'warning',
                button: 'Cerrar'
            }).then(() => {
                window.location = 'frontend_slider_create.php';
            });
        </script>";
        exit;
    }

    
    $nombre = pg_escape_string($conn, $nombre);
    $imagenContenido = file_get_contents($_FILES['imagen']['tmp_name']);
    $imgContenido = pg_escape_bytea($conn, $imagenContenido);

    $query = "INSERT INTO slider (nombre, imagen) VALUES ($1, $2) RETURNING id";
    $result = pg_query_params($conn, $query, array($nombre, $imgContenido));

    if ($result) {
        $row_id = pg_fetch_assoc($result);
        $ultimo_id = $row_id['id'];

        
        $accion = "Agregado";
        $query_registro = "INSERT INTO registroslider (accion, nombre, imagen, email) VALUES ($1, $2, $3, $4)";
        $historial = pg_query_params($conn, $query_registro, array($accion, $nombre, $imgContenido, $email));

        if ($historial) {
            echo '<script>
                swal({
                    title: "Éxito",
                    text: "Imagen agregada",
                    icon: "success",
                    button: "Cerrar"
                }).then(() => { window.location = "frontend_slider_create.php"; });
            </script>';
            exit();
        } else {
            echo "Error al insertar registro: " . pg_last_error($conn);
            exit;
        }
    } else {
        echo "Error al insertar registro: " . pg_last_error($conn);
        exit;
    }
}

pg_close($conn);
?>
