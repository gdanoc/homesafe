<?php
session_start();

$host = "localhost";
$port = "5432";
$user = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";
$dbname = "homesafe";

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
if (!$conn) {
    die("Conexión fallida: " . pg_last_error());
}

$email_other = $_SESSION['email'];
$id_pedido = $_POST["id_pedido"];
$email_customer = $_POST["email_customer"];


$check_query = "SELECT id_chat FROM chats_manager WHERE user_gmail_customer = $1 AND user_gmail_other = $2 AND id_pedido = $3";
$check_result = pg_query_params($conn, $check_query, array($email_customer, $email_other, $id_pedido));

if (pg_num_rows($check_result) > 0) {
    
    $chat = pg_fetch_assoc($check_result);
    $chat_id = $chat['id_chat'];
} else {
    
    $insert_chat = "INSERT INTO chats_manager (user_gmail_customer, user_gmail_other, id_pedido) VALUES ($1, $2, $3) RETURNING id_chat";
    $result = pg_query_params($conn, $insert_chat, array($email_customer, $email_other, $id_pedido));

    if (!$result) {
        die("Error al crear el chat: " . pg_last_error());
    }
    $row = pg_fetch_assoc($result);

    $chat_id = $row['id_chat'];
}
?>
<form id="redirigir" action="index.php" method="post">
    <input type="hidden" name="id_chat" value="<?php echo htmlspecialchars($chat_id); ?>">
</form>
<script>
    document.getElementById('redirigir').submit();
</script>