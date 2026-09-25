<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
      integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<?php
session_start();

if (isset($_SESSION['email'])) {
    if (!isset($_SESSION['user_agent']) || $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        session_unset();
        session_destroy();
        header('Location: index.php?error=session_invalid');
        exit();
    }
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if (!isset($_SESSION['email'])):
    
else:
    include('user.p.php'); 
    if ($rol == 1):
        header('Location: dashboard.php');
        exit();
    elseif ($rol == 3):
        header('Location: EN_dashboard_repartidor.php');
        exit();
    endif;
endif;
?>