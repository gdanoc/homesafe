<?php


$host = "localhost";
$port = "5432"; 
$dbname = "homesafe";
$user = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
if (!$conn) {
    throw new Exception("Conexión fallida a la base de datos");
}


if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];

    $sql = "SELECT username, id_rol FROM accounts WHERE email = $1";
    $result = pg_query_params($conn, $sql, array($email));

    if ($result && pg_num_rows($result) > 0) {
        $row = pg_fetch_assoc($result);
        $username = $row['username'];
        $rol = $row['id_rol'];
    } else {
        $username = "Usuario";
        $rol = "0";
    }
} else {
    
    $username = "Invitado";
    $rol = "0";
    
    
    
}


pg_close($conn);
?>