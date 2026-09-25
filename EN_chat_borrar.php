<?php
session_start();
//conexion
$servername = "localhost";
$username = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";
$dbname = "homesafe";
$conn = pg_connect("host=$servername dbname=$dbname user=$username password=$password");
//conexion

$chat_id = $_POST['chat_id'] ?? null;

if (!$chat_id) {
    die("Faltan datos");
}
$query_update = "UPDATE chats_manager SET estado = 'f' WHERE id_chat = $1";
pg_query_params($conn, $query_update, [$chat_id]);


header("Location: EN_chats_seller.php");
exit;
