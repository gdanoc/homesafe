<?php
$quien_es = $_POST['quien_es'];
session_start();
include('../../user.p.php');

if ($quien_es == "Customer"):
    header("Location: ../../pedidosusuario.php");
elseif ($quien_es == "Other"):
    if ($rol == 1):
        header('Location: ../../dashboard.php');
        exit;
    elseif ($rol == 3):
        header('Location: ../../dashboard_repartidor.php');
        exit;
    else:
        header('Location: ../../dashboard_vendedor.php');
        exit;
    endif;
else:
    header('Location: ../../index.php');
    exit;
endif;
?>