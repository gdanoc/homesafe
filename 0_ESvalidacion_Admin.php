<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}


$host = "localhost";
$dbname = "homesafe";
$user = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";

try {
    
    $conn = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    
    $email = $_SESSION['email'];
    $sql_rol = "SELECT id_rol FROM accounts WHERE email=:email";
    $stmt_rol = $conn->prepare($sql_rol);
    $stmt_rol->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt_rol->execute();

    if ($stmt_rol->rowCount() > 0) {
        $row = $stmt_rol->fetch(PDO::FETCH_ASSOC);
        $rol = $row['id_rol'];
    } else {
        $rol = "0";
    }
    
    
    $conn = null;

   
    if ($rol != 1):
        header('Location: index.php');
    endif;
    
    
} catch (PDOException $e) {
    die("Conexión fallida: " . $e->getMessage());
}
?>