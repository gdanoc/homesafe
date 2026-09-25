<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<?php
session_start();
if (!isset($_SESSION['email'])) {
    echo '<p><script>swal({
        title: "Error",
        text: "Debes iniciar sesión para añadir propiedades a favoritos.",
        icon: "warning",
        button: "Cerrar",
    }).then(function() {
        window.location = "index.php";
    });</script></p>';
    exit();
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


$email = $_SESSION['email'];
$propiedad_id = $_POST['propiedad_id'];


$sql_user = "SELECT id FROM accounts WHERE email = $1";
$result_user = pg_query_params($conn, $sql_user, array($email));

if (pg_num_rows($result_user) > 0) {
    $user = pg_fetch_assoc($result_user);
    $user_id = $user['id']; 
} else {
    echo '<p><script>swal({
        title: "Error",
        text: "Usuario no encontrado.",
        icon: "warning",
        button: "Cerrar",
    }).then(function() {
        window.location = "index.php";
    });</script></p>';
    exit();
}


$sql_check = "SELECT * FROM favoritos WHERE user_id = $1 AND propiedad_id = $2";
$result_check = pg_query_params($conn, $sql_check, array($user_id, $propiedad_id));

if (pg_num_rows($result_check) > 0) {
    echo '<p><script>swal({
        title: "Error",
        text: "Esta propiedad ya está en tus favoritos",
        icon: "warning",
        button: "Cerrar",
    }).then(function() {
        window.location = "index.php";
    });</script></p>';
} else {
    
    $sql_insert = "INSERT INTO favoritos (user_id, propiedad_id) VALUES ($1, $2)";
    $result_insert = pg_query_params($conn, $sql_insert, array($user_id, $propiedad_id));

    if ($result_insert) {
        echo '<p><script>swal({
            title: "Éxito",
            text: "Propiedad añadida a favoritos!",
            icon: "success",
            button: "Cerrar",
        }).then(function() {
            window.location = "properties.php";
        });</script></p>';
    } else {
        echo '<p><script>swal({
            title: "Error",
            text: "Error al añadir a favoritos",
            icon: "warning",
            button: "Cerrar",
        }).then(function() {
            window.location = "index.php";
        });</script></p>';
    }
}


pg_close($conn);
?>