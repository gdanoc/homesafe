<?php include('0_ESvalidacion_Seller.php'); //ESPAÑOL ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <?php
session_start();

if (!isset($_SESSION['email'])) {
    echo '<p><script>swal({
        title: "Error",
        text: "Sesión no iniciada",
        icon: "warning",
        button: "Cerrar",
    }).then(function() {
        window.location = "login.p.php";
    });</script></p>';
    exit();
}

$servername = "localhost";
$username = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";
$dbname = "homesafe";
$conn = pg_connect("host=$servername dbname=$dbname user=$username password=$password");

if (!$conn) {
    die("Conexión fallida: " . pg_last_error());
}



$email = $_SESSION['email']; 

if (isset($_POST['id'])) {
    $id = intval($_POST['id']); 
    
    
    $query_select = "SELECT nombre, descripcion, bathrooms, bedrooms, size, precio, fecha, imagen FROM propiedades WHERE id = $1 AND mail_user = $2";
    $result = pg_query_params($conn, $query_select, array($id, $email));
    
    if (pg_num_rows($result) > 0) {
        $row = pg_fetch_assoc($result);
        $nombre = $row['nombre'];
        $descripcion = $row['descripcion'];
        $bathrooms = $row['bathrooms'];
        $bedrooms = $row['bedrooms'];
        $size = $row['size'];
        $precio = $row['precio'];
        $fecha = $row['fecha'];
        $imagen = $row['imagen'];
        
        $query_delete = "DELETE FROM propiedades WHERE id = $1 AND mail_user = $2";
        $result_delete = pg_query_params($conn, $query_delete, array($id, $email));
        
        if ($result_delete) {
            $accion = "Eliminar";
            $query_registro = "INSERT INTO registrosseller (accion, nombre, descripcion, bathrooms, bedrooms, size, precio, imagen, mail_user) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9)";
            $result_registro = pg_query_params($conn, $query_registro, array(
                $accion, $nombre, $descripcion, $bathrooms, $bedrooms, $size, $precio, $imagen, $email
            ));
            
            echo '<p><script>swal({
                title: "Éxito",
                text: "Propiedad eliminada",
                icon: "success",
                button: "Cerrar",
            }).then(function() {
                window.location = "frontend_delete_vendedor.php";
            });</script></p>';
        } else {
            echo '<p><script>swal({
                title: "Error",
                text: "Error al eliminar: ' . pg_last_error($conn) . '",
                icon: "warning",
                button: "Cerrar",
            }).then(function() {
                window.location = "frontend_delete_vendedor.php";
            });</script></p>';
        }
    } else {
        echo '<p><script>swal({
            title: "Error",
            text: "No tienes permiso para eliminar esta propiedad o no existe",
            icon: "warning",
            button: "Cerrar",
        }).then(function() {
            window.location = "frontend_delete_vendedor.php";
        });</script></p>';
    }
} else {
    echo '<p><script>swal({
        title: "Error",
        text: "ID de propiedad no proporcionada",
        icon: "warning",
        button: "Cerrar",
    }).then(function() {
        window.location = "frontend_delete_vendedor.php";
    });</script></p>';
}

pg_close($conn);
?>