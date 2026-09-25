<?php 
$quien_es = $_POST['quien_es'];
session_start();
include('../user.p.php');

if ($quien_es == "Customer"):
    header("Location: ../chats_usuario.php");
elseif ($quien_es == "Other"):
    if ($rol == 1):
        header('Location: ../dashboard.php');
    elseif ($rol == 3):
        header('Location: ../dashboard_repartidor.php');
    endif;
    header('Location: ../chats_seller.php');
else:
    header('Location: ../index.php');
endif;
?>