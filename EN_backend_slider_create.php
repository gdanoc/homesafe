<?php include('0_ENvalidacion_Admin.php'); ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
$email = $_SESSION['email'];

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
    $nombre = $_POST['nombre'];
    $imagen = file_get_contents($_FILES['imagen']['tmp_name']);
    $imgContenido = pg_escape_bytea($conn, $imagen);
    
    
    $query = "INSERT INTO slider (nombre, imagen) 
          VALUES ($1, $2)
          RETURNING id";
              
    $result = pg_query_params($conn, $query, array(
        $nombre, 
        $imgContenido
    ));
    

    if ($result) {  
        $row_id = pg_fetch_assoc($result);
        $ultimo_id = $row_id['id']; 

        
        $query_fecha = "SELECT * FROM slider WHERE id = $1";
        $resultado_fecha = pg_query_params($conn, $query_fecha, array($ultimo_id));
        if ($resultado_fecha) {
            $datos_fecha = pg_fetch_assoc($resultado_fecha);
        } else {
            echo "Error al ejecutar la consulta: " . pg_last_error($conn);
        }

        $accion = "Agregado";
        $query_registro = "INSERT INTO registroslider (accion, nombre, imagen, email) 
                          VALUES ($1, $2, $3, $4)";
                          
        $historial = pg_query_params($conn, $query_registro, array(
            $accion, 
            $nombre, 
            $imgContenido,
            $email,
        ));
        if ($historial) {
            echo '<p><script>swal({
                title: "Success",
                text: "Image added",
                icon: "success",
                button: "Close",
            
                }).then(function() {
                window.location = "EN_frontend_slider_create.php";
                });</script></p>';
            exit();
        } else {
            echo "Error inserting record: " . pg_last_error($conn);
            exit;
        exit;
        }
        exit;
    } else {
        echo "Error inserting record: " . pg_last_error($conn);
            exit;
        echo '<p><script>swal({
            title: "Error",
            text: "Error adding",
            icon: "warning",
            button: "Close",

            }).then(function() {
            window.location = "EN_frontend_slider_create.php";
            });</script></p>';
    }
}


pg_close($conn);
?>