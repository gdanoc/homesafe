<?php
session_start();
if (!isset($_SESSION['email'])):
else:
    include('user.p.php');
    if ($rol == 1):
        header('Location: EN_dashboard.php');
        exit();
    elseif ($rol == 3):
        header('Location: EN_dashboard_repartidor.php');
        exit();
    endif;
endif;
?>