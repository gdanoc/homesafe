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


if (isset($_POST['id_tipo_cuarto']) && isset($_POST['nombre_tipo_cuarto'])) {
    $id = intval($_POST['id_tipo_cuarto']);
    $nombre = trim($_POST['nombre_tipo_cuarto']);

    
    $query = "UPDATE tipos_cuarto SET nombre = $1 WHERE id = $2";
    $result = pg_query_params($conn, $query, array($nombre, $id));

    if ($result) {
        $_SESSION['mensaje'] = 'success_edit';
    } else {
        $_SESSION['mensaje'] = 'error_edit';
    }
} else {
    
    $_SESSION['mensaje'] = 'error_edit';
}

pg_close($conn);


header("Location: tipo_cuarto.php");
exit;
?>