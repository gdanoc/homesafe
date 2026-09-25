<?php 
$quien_es = $_POST['quien_es'];
session_start();
include('../user.p.php');

if ($quien_es == "Customer"):
    header("Location: ../EN_chats_usuario.php");
elseif ($quien_es == "Other"):
    if ($rol == 1):
        header('Location: ../EN_dashboard.php');
    elseif ($rol == 3):
        header('Location: ../EN_dashboard_repartidor.php');
    endif;
    header('Location: ../EN_chats_seller.php');
else:
    header('Location: ../EN_index.php');
endif;
?>