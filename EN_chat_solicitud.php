<?php
include('includes/CookiesSessionVEn.php');
?>
<?php

$host = "localhost";
$port = "5432";
$user = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";
$dbname = "homesafe";

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
if (!$conn) {
    die("Failed connection: " . pg_last_error());
}


$email_customer = $_SESSION['email'];
$id_casa = $_POST['property_id'];
$email_vendedor = $_POST['vendedor_email'];


$check_query = "SELECT id_chat FROM chats_manager WHERE user_gmail_customer = $1 AND user_gmail_other = $2 AND id_casa = $3";
$check_result = pg_query_params($conn, $check_query, array($email_customer, $email_vendedor, $id_casa));

if (pg_num_rows($check_result) > 0) {
    
    $chat = pg_fetch_assoc($check_result);
} else {
    
    $insert_chat = "INSERT INTO chats_manager (user_gmail_customer, user_gmail_other, id_casa) VALUES ($1, $2, $3) ";
    $result = pg_query_params($conn, $insert_chat, array($email_customer, $email_vendedor, $id_casa));

    if (!$result) {
        die("Error creating chat: " . pg_last_error());
    }
    $row = pg_fetch_assoc($result);
}


pg_close($conn);

header("Location: EN_chats_usuario.php");
exit();
?>