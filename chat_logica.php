<?php
session_start();

$servername = "localhost";
$user = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";
$dbname = "homesafe";
$conn = pg_connect("host=$servername dbname=$dbname user=$user password=$password");

$vendedor_gmail = $_SESSION['email'] ?? null;
if (!$vendedor_gmail) {
    die("No has iniciado sesión");
}


$query = "SELECT id_chat 
          FROM chats_manager 
          WHERE user_gmail_other = $1 
            AND estado = 't'";
$result = pg_query_params($conn, $query, [$vendedor_gmail]);
$chats_aceptados = [];
while ($row = pg_fetch_assoc($result)) {
    $chats_aceptados[] = $row['id_chat'];
}


$query2 = "SELECT cm.id_chat, cm.user_gmail_customer, p.nombre AS propiedad
           FROM chats_manager cm
           LEFT JOIN propiedades p ON cm.id_casa = p.id
           WHERE cm.user_gmail_other = $1 
             AND (cm.estado = 'p' OR cm.estado = 't')
           ORDER BY cm.id_chat DESC";
$result2 = pg_query_params($conn, $query2, [$vendedor_gmail]);

$solicitudes = [];
while ($row = pg_fetch_assoc($result2)) {
    $solicitudes[] = $row;
}
?>
