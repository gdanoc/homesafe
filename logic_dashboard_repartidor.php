<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.p.php");
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

$sql = "SELECT username FROM accounts WHERE email=$1";
$result = pg_query_params($conn, $sql, array($email));

if (pg_num_rows($result) > 0) {
    $row = pg_fetch_assoc($result);
    $username = $row['username'];
} else {
    $username = "Usuario";
}

pg_close($conn);
?>