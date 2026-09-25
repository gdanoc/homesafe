<?php include('0_ESvalidacion_Admin.php'); //ESPAÑOL ?>
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



if (isset($_POST['id'])) {
    $id = $_POST['id'];
    
    
    $query_select = "SELECT id FROM repartidores WHERE id = $1";
    $result = pg_query_params($conn, $query_select, array($id));
    
    if (pg_num_rows($result) > 0) {
        
        $query = "DELETE FROM repartidores WHERE id = $1";
        $result_delete = pg_query_params($conn, $query, array($id));
        
        if ($result_delete) {
            echo '<p><script>swal({
                title: "Éxito",
                text: "Repartidor eliminado correctamente",
                icon: "success",
                button: "Cerrar",
                }).then(function() {
                window.location = "frontend_rep_delete.php";
                });</script></p>';
            exit();
        } else {
            echo '<p><script>swal({
                title: "Error",
                text: "No se pudo eliminar el repartidor",
                icon: "error",
                button: "Cerrar",
                }).then(function() {
                window.location = "frontend_rep_delete.php";
                });</script></p>';
            exit();
        }
    } else {
        echo '<p><script>swal({
            title: "Error",
            text: "Repartidor no encontrado",
            icon: "warning",
            button: "Cerrar",
            }).then(function() {
            window.location = "frontend_rep_delete.php";
            });</script></p>';
        exit();
    }
} else {
    echo '<p><script>swal({
        title: "Error",
        text: "ID de repartidor no proporcionado",
        icon: "warning",
        button: "Cerrar",
        }).then(function() {
        window.location = "frontend_rep_delete.php";
        });</script></p>';
    exit();
}


pg_close($conn);
?>