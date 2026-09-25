<?php
session_start();
//conexion
$servername = "localhost";
$username = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";
$dbname = "homesafe";
$conn = pg_connect("host=$servername dbname=$dbname user=$username password=$password");

$vendedor_gmail = $_SESSION['email'] ?? null;
$chat_id = $_POST['chat_id'] ?? null;

if (!$vendedor_gmail || !$chat_id) {
    die("Faltan datos");
}

$query_chat = "UPDATE chats_manager SET estado = 't' WHERE id_chat = $1";
pg_query_params($conn, $query_chat, [$chat_id]);

header("Location: EN_chats_seller.php");
exit;
