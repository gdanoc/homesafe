<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.p.php");
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
    $sql = "SELECT username FROM accounts WHERE email=:email";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $username = $row['username'];
    } else {
        $username = "Usuario";
    }
    
    
    $conn = null;
    
} catch (PDOException $e) {
    die("Conexión fallida: " . $e->getMessage());
}
?>