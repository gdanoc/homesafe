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
    die("Conexión fallida: " . pg_last_error());
}

$email = $_SESSION['email'];

$query = "SELECT plan_id, fecha_vencimiento FROM suscripcion WHERE mail_user = $1 ORDER BY fecha_vencimiento DESC LIMIT 1";
$result = pg_query_params($conn, $query, array($email));
$suscripcion = pg_fetch_assoc($result);

$subs_vencida = false;
if ($suscripcion) {
    $fecha_vencimiento = strtotime($suscripcion['fecha_vencimiento']);
    $ahora = time();
    if ($fecha_vencimiento < $ahora) {
        $subs_vencida = true;
    }
} else {
    $subs_vencida = true;
}

pg_close($conn);

if ($subs_vencida) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Suscripción Vencida</title>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
        <script>
            Swal.fire({
                icon: 'warning',
                title: 'Suscripción vencida',
                text: 'Los formularios están deshabilitados hasta que renueves tu suscripción.',
                confirmButtonText: 'Ir a renovar',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'dashboard_vendedor.php';
                }
            });
        </script>
    </body>
    </html>
    <?php
    exit;
}
?>