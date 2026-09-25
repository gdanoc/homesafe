<?php
include('includes/CookiesSessionV.php');

$host = "localhost";
$port = "5432";
$user = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";
$dbname = "homesafe";

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
if (!$conn) {
    die("Conexión fallida: " . pg_last_error());
}

if (!isset($_POST['property_id'], $_POST['vendedor_email'])) {
    die("Datos incompletos");
}

$email_customer = $_SESSION['email'];
$id_casa = $_POST['property_id'];
$email_vendedor = $_POST['vendedor_email'];


$check_query = "SELECT id_chat FROM chats_manager 
                WHERE user_gmail_customer = $1 AND user_gmail_other = $2 AND id_casa = $3";
$check_result = pg_query_params($conn, $check_query, array($email_customer, $email_vendedor, $id_casa));

if (pg_num_rows($check_result) > 0) {
    $chat = pg_fetch_assoc($check_result);
    $chat_id = $chat['id_chat'];
} else {
    
    $insert_chat = "INSERT INTO chats_manager (user_gmail_customer, user_gmail_other, id_casa) 
                    VALUES ($1, $2, $3) RETURNING id_chat";
    $result = pg_query_params($conn, $insert_chat, array($email_customer, $email_vendedor, $id_casa));

    if (!$result) {
        die("Error al crear el chat: " . pg_last_error());
    }
    $row = pg_fetch_assoc($result);
    $chat_id = $row['id_chat'];
}

pg_close($conn);


header("Location: chats_usuario.php?chat_id=" . $chat_id);
exit();
?>
