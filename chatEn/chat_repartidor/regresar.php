<?php
$quien_es = $_POST['quien_es'];
session_start();
include('../user.p.php');

if ($quien_es == "Customer"):
    header("Location: ../../EN_pedidosusuario.php");
elseif ($quien_es == "Other"):
    if ($rol == 1):
        header('Location: ../../EN_dashboard.php');
        exit;
    elseif ($rol == 3):
        header('Location: ../../EN_dashboard_repartidor.php');
        exit;
    else:
        header('Location: ../../EN_dashboard_vendedor.php');
        exit;
    endif;
else:
    header('Location: ../../EN_index.php');
    exit;
endif;
?>