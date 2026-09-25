<?php
if (!isset($_SESSION['email'])):
else:
    include('user.p.php');
    if ($rol == 1):
        header('Location: dashboard.php');
        exit();
    elseif ($rol == 3):
        header('Location: dashboard_repartidor.php');
        exit();
    endif;
endif;
?>