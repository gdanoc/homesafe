<?php
session_start();

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

if (isset($_POST['nombre_tipo_cuarto'])) {
    $nombre = trim($_POST['nombre_tipo_cuarto']);

    $query = "INSERT INTO tipos_cuarto (nombre) VALUES ($1) RETURNING id";
    $result = pg_query_params($conn, $query, array($nombre));

    if ($result) {
        $_SESSION['mensaje'] = 'success';
    } else {
        $_SESSION['mensaje'] = 'error';
    }
}

pg_close($conn);

header("Location: tipo_cuarto.php");
exit;
?>