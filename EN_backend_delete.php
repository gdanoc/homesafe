<?php include('0_ENvalidacion_Admin.php'); ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
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



if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    
    $query_select = "SELECT nombre, descripcion, precio, cantidad, imagen, fecha FROM muebles WHERE id = $1";
    $result = pg_query_params($conn, $query_select, array($id));
    
    if (pg_num_rows($result) > 0) {
        $row = pg_fetch_assoc($result);
        $nombre = $row['nombre'];
        $descripcion = $row['descripcion'];
        $precio = $row['precio'];
        $cantidad = $row['cantidad'];
        $imagen = $row['imagen'];
        $fecha = $row['fecha'];

        
        $query = "DELETE FROM muebles WHERE id = $1";
        $result_delete = pg_query_params($conn, $query, array($id));
        
        if ($result_delete) {                
            
            $accion = "Eliminar";
            $query_registro = "INSERT INTO registrosadmin (accion, nombre, descripcion, precio, cantidad, imagen, email, fecha) 
                              VALUES ($1, $2, $3, $4, $5, $6, $7, $8)";
            
            $historial = pg_query_params($conn, $query_registro, array(
                $accion,
                $nombre,
                $descripcion,
                $precio,
                $cantidad,
                $imagen,
                $email,
                $fecha
            ));
            if ($historial) {
                echo '<p><script>swal({
                    title: "Success",
                    text: "Deleted Furniture",
                    icon: "success",
                    button: "Close",
                
                    }).then(function() {
                    window.location = "EN_frontend_delete.php";
                    });</script></p>';
                exit();
            } else {
                echo "Error inserting record: " . pg_last_error($conn);
            exit;
            }
            
            exit();
        } else {
            echo "Error inserting record: " . pg_last_error($conn);        }
    } else {
        echo "Furniture not found.";
    }
} else {
    echo "Furniture ID not provided.";
}


pg_close($conn);
?>