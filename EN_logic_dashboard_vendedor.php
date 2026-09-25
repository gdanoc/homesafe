<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: EN_index.php");
    exit();
}

$vendedor_gmail = $_SESSION['email'];
$conn = pg_connect("host=localhost dbname=homesafe user=postgres password=Info2025/*-");
if (!$conn) {
    die("Connection failed: " . pg_last_error());
}


$query_trial = "SELECT COUNT(*) FROM suscripcion WHERE mail_user = $1 AND plan_id = 4";
$result_trial = pg_query_params($conn, $query_trial, array($vendedor_gmail));
$row_trial = pg_fetch_row($result_trial);
$ya_tuvo_trial = ($row_trial[0] > 0);

if (isset($_POST['plan_id']) && $_POST['plan_id'] == 4 && $ya_tuvo_trial) {
    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Trial Plan Used</title>
        <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    </head>
    <body>
    <script>
        swal({
            title: "Trial Plan Already Used",
            text: "The trial plan can only be used once. Please select another plan.",
            icon: "warning",
            button: "Close"
        }).then(() => { window.history.back(); });
    </script>
    </body>
    </html>
    ';
    exit;
}


$sql = "SELECT username FROM accounts WHERE email=$1";
$result = pg_query_params($conn, $sql, array($vendedor_gmail));
if ($result && pg_num_rows($result) > 0) {
    $row = pg_fetch_assoc($result);
    $username = $row['username'];
} else {
    $username = "User";
}


$query = "SELECT plan_id, fecha_vencimiento, tuvo_plan_trial FROM suscripcion WHERE mail_user = $1 ORDER BY fecha_vencimiento DESC LIMIT 1";
$result_subs = pg_query_params($conn, $query, array($vendedor_gmail));

$suscripcion = null;
if ($result_subs && pg_num_rows($result_subs) > 0) {
    $suscripcion = pg_fetch_assoc($result_subs);
}

$subs_vencida = true; 
if ($suscripcion) {
    $fecha_vencimiento = strtotime($suscripcion['fecha_vencimiento']);
    $ahora = time();
    if ($fecha_vencimiento >= $ahora) {
        $subs_vencida = false;
    }
}
pg_close($conn);
?>