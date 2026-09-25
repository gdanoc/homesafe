<?php

$host = "localhost";
$user = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";
$dbname = "homesafe";

$conn = pg_connect("host=$host dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Conexión fallida: " . pg_last_error());
}

$query = "DELETE FROM carrito WHERE fecha_agregado < NOW() - INTERVAL '24 hours'";
$result = pg_query($conn, $query);

if ($result) {
    $affected_rows = pg_affected_rows($result);
    echo "Productos expirados eliminados: " . $affected_rows . "\n";
} else {
    echo "Error al limpiar carrito: " . pg_last_error($conn) . "\n";
}

pg_close($conn);
?>
