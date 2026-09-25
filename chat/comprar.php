<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<?php
session_start();
include('../user.p.php');

//conexion
$servername = "localhost";
$username = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";
$dbname = "homesafe";
$conn = pg_connect("host=$servername dbname=$dbname user=$username password=$password");

$id_casa = $_POST['id_casa'];
$customer_gmail = $_POST['customer_gmail'];

$query_propiedades = "SELECT * FROM propiedades WHERE id = $1 ";
$result = pg_query_params($conn, $query_propiedades, [$id_casa]);
$row = pg_fetch_assoc($result);


if ($row['estado'] == "Disponible" and $_SESSION['email'] != $row['mail_user']) {
    $query_pendiente = "UPDATE propiedades SET estado = 'Pendiente0' WHERE id = $1";
    pg_query_params($conn, $query_pendiente, [$id_casa]);

    echo '<p><script>swal({
        title: "Éxito",
        text: "Estas de acuerdo con la compra de la propiedad, espera al vendedor",
        icon: "info",
        button: "Close",
    
        }).then(function() {
        window.location = "../chats_usuario.php";
    });</script></p>';
} elseif ($row['estado'] == "Disponible" and $_SESSION['email'] == $row['mail_user']) {
    $query_pendiente = "UPDATE propiedades SET estado = 'Pendiente2' WHERE id = $1";
    pg_query_params($conn, $query_pendiente, [$id_casa]);

    echo '<p><script>swal({
        title: "Éxito",
        text: "Estas de acuerdo con la venta de la propiedad, espera al comprador.",
        icon: "info",
        button: "Close",
    
        }).then(function() {
        window.location = "../chats_seller.php";
    });</script></p>';
}

elseif ($row['estado'] == "Pendiente2" and $_SESSION['email'] != $row['mail_user']) {
    $query_comprar = "UPDATE propiedades SET estado = 'Vendido' WHERE id = $1";
    pg_query_params($conn, $query_comprar, [$id_casa]);

    $query_sales = "INSERT INTO sales_seller (id_propiedad, comprador ) VALUES ($1, $2)";
    pg_query_params($conn, $query_sales, [$id_casa, $customer_gmail]);

    echo '<p><script>swal({
        title: "Éxito",
        text: "Estas de acuerdo con la compra de la propiedad, compra exitosa por ambas partes",
        icon: "success",
        button: "Close",
    
        }).then(function() {
        window.location = "../chats_usuario.php";
    });</script></p>';

} elseif ($row['estado'] == "Pendiente0" and $_SESSION['email'] == $row['mail_user']) {
    $query_comprar = "UPDATE propiedades SET estado = 'Vendido' WHERE id = $1";
    pg_query_params($conn, $query_comprar, [$id_casa]);

    $query_sales = "INSERT INTO sales_seller (id_propiedad, comprador ) VALUES ($1, $2)";
    pg_query_params($conn, $query_sales, [$id_casa, $customer_gmail]);

    echo '<p><script>swal({
        title: "Éxito",
        text: "Estas de acuerdo con la venta de la propiedad, compra exitosa por ambas partes",
        icon: "success",
        button: "Close",
    
        }).then(function() {
        window.location = "../chats_seller.php";
    });</script></p>';
}

elseif ($_SESSION['email'] != $row['mail_user']) {
    echo '<p><script>swal({
        title: "Infomación",
        text: "Ya lo confirmaste",
        icon: "info",
        button: "Close",
        }).then(function() {
        window.location = "../chats_usuario.php";
    });</script></p>';
    exit();
} elseif ($_SESSION['email'] == $row['mail_user']) {
    echo '<p><script>swal({
        title: "Infomation",
        text: "You have already confirmed",
        icon: "info",
        button: "Close",
        }).then(function() {
        window.location = "../chats_seller.php";
    });</script></p>';
    exit();
}