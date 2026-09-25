<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<?php
include('includes/CookiesSessionVEn.php');
?>
<?php
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo '<script>
        swal({
            title: "Error",
            text: "The product id is not valid",
            icon: "warning",
            button: "Close",
        }).then(function() {
            window.location = "EN_mostrarcarrito.php";
        });
    </script>';
    exit();
}


$host = "localhost";
$username = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";
$dbname = "homesafe";


$conn = pg_connect("host=$host dbname=$dbname user=$username password=$password");


if (!$conn) {
    die("Conexión fallida: " . pg_last_error());
}


if (!isset($_SESSION['user_id'])) {
    
    $email = $_SESSION['email'];
    $query_user = "SELECT id FROM accounts WHERE email = $1";
    $result_user = pg_query_params($conn, $query_user, array($email));

    if (pg_num_rows($result_user) > 0) {
        $row = pg_fetch_assoc($result_user);
        $_SESSION['user_id'] = $row['id'];
    } else {
        echo '<p><script>swal({
            title: "Error",
            text: "User account could not be found",
            icon: "warning",
            button: "Close",
        
            }).then(function() {
            window.location = "EN_mostrarcarrito.php";
            });</script></p>';
            exit();
    }
}

$usuario_id = $_SESSION['user_id'];
$producto_id = intval($_GET['id']);


$query = "DELETE FROM carrito WHERE usuario_id = $1 AND producto_id = $2";
$result = pg_query_params($conn, $query, array($usuario_id, $producto_id));

if ($result) {
    echo '<p><script>swal({
        title: "Successfull",
        text: "Product from the shopping cart deleted",
        icon: "success",
        button: "Close",
    
        }).then(function() {
        window.location = "EN_mostrarcarrito.php";
        });</script></p>';
} else {
    echo '<p><script>swal({
        title: "Error",
        text: "Error on delete",
        icon: "warning",
        button: "Cerrar",
    
        }).then(function() {
        window.location = "EN_mostrarcarrito.php";
        });</script></p>';
}


pg_close($conn);
?>