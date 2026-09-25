<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<?php
session_start();
$servername = "localhost";
$user = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";
$dbname = "homesafe";
$conn = pg_connect("host=$servername dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Connection failed: " . pg_last_error());
}

$email = $_SESSION['email'];

$query = "SELECT plan_id, fecha_vencimiento FROM suscripcion WHERE mail_user = $1 ORDER BY fecha_vencimiento DESC LIMIT 1";
$result = pg_query_params($conn, $query, array($email));
$subscription = pg_fetch_assoc($result);

$subscription_expired = false;
if ($subscription) {
    $expiration_date = strtotime($subscription['fecha_vencimiento']);
    $now = time();
    if ($expiration_date < $now) {
        $subscription_expired = true;
    }
} else {
    $subscription_expired = true;
}

pg_close($conn);

if ($subscription_expired) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Subscription Expired</title>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
        <script>
            Swal.fire({
                icon: 'warning',
                title: 'Subscription Expired',
                text: 'Forms are disabled until you renew your subscription.',
                confirmButtonText: 'Go to Renew',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'EN_dashboard_vendedor.php';
                }
            });
        </script>
    </body>
    </html>
    <?php
    exit;
}
?>