<?php
include('0_ENvalidacion_Admin.php');
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$email = $_SESSION['email'];

$host = "localhost";
$port = "5432";
$dbname = "homesafe";
$username = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";

$conn_string = "host=$host port=$port dbname=$dbname user=$username password=$password";
$conn = pg_connect($conn_string);

if (!$conn) {
    die("Conexión fallida: " . pg_last_error());
}

if (isset($_POST['submit_button'])) {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = $_POST['precio'];
    $cantidad = $_POST['cantidad'];
    $archivo = $_FILES['imagen'];
    $descuento = isset($_POST['descuento']) ? trim($_POST['descuento']) : null;

    
    $errores = [];

    
    if ($nombre === '' || strlen($nombre) < 2 || strlen($nombre) > 50 || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\-_.,]+$/', $nombre)) {
        $errores[] = "Invalid name. Check the characters and length.";
    }

    
    if ($descripcion === '' || strlen($descripcion) < 10 || strlen($descripcion) > 500) {
        $errores[] = "The description must be between 10 and 500 characters long.";
    }

    
    if (!is_numeric($precio) || $precio < 0.01 || $precio > 99999.99 || (strpos($precio, '.') !== false && strlen(explode('.', $precio)[1]) > 2)) {
        $errores[] = "The price must be a valid number between 0.01 and 99999.99 with a maximum of 2 decimal places.";
    }

    
    if (!ctype_digit($cantidad) || $cantidad < 1 || $cantidad > 100000) {
        $errores[] = "The amount must be an integer between 1 and 100,000.";
    }

    if ($descuento !== null && $descuento !== '') {
        if (!is_numeric($descuento) || $descuento < 0 || $descuento > 100) {
            $errores[] = "The discount must be a number between 0 and 100.";
        }
    } else {
        $descuento = null; 
    }

    
    if (!isset($archivo['tmp_name']) || $archivo['error'] !== UPLOAD_ERR_OK) {
        $errores[] = "You must upload a valid image.";
    } else {
        $tipoPermitido = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($archivo['type'], $tipoPermitido)) {
            $errores[] = "Invalid image format. Use JPG, PNG, GIF, or WEBP.";
        }
    }

    
    if (!empty($errores)) {
        $_SESSION['alert'] = [
            'title' => 'Data error',
            'text' => implode("\n", $errores),
            'icon' => 'warning',
            'button' => 'Close'
        ];
        header('Location: EN_frontend_create.php');
        exit();
    }

    
    $imagen = file_get_contents($archivo['tmp_name']);
    $imgContenido = pg_escape_bytea($conn, $imagen);

    $query = "INSERT INTO muebles (nombre, descripcion, precio, cantidad, imagen, descuento) 
              VALUES ($1, $2, $3, $4, $5, $6)
              RETURNING id";

    $result = pg_query_params($conn, $query, array(
        $nombre, $descripcion, $precio, $cantidad, $imgContenido, $descuento
    ));

    if ($result) {
        $row_id = pg_fetch_assoc($result);
        $ultimo_id = $row_id['id'];

        $query_fecha = "SELECT * FROM muebles WHERE id = $1";
        $resultado_fecha = pg_query_params($conn, $query_fecha, array($ultimo_id));
        $fecha_mueble = pg_fetch_assoc($resultado_fecha)['fecha'];

        $accion = "Agregado";
        $query_registro = "INSERT INTO registrosadmin (accion, nombre, descripcion, precio, cantidad, imagen, descuento, email, fecha) 
                           VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9)";
        $historial = pg_query_params($conn, $query_registro, array(
            $accion, $nombre, $descripcion, $precio, $cantidad, $imgContenido, $descuento, $email, $fecha_mueble
        ));

        if ($historial) {
            if ($descuento !== null && floatval($descuento) > 0) {
                
                $query_emails = "SELECT email FROM accounts";
                $result_emails = pg_query($conn, $query_emails);

                if ($result_emails && pg_num_rows($result_emails) > 0) {
                    $emails = [];
                    while ($row = pg_fetch_assoc($result_emails)) {
                        $emails[] = $row['email'];
                    }

                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'homesafe2025contact@gmail.com';
                        $mail->Password = 'ecqfzpwrvcuoxmnf';
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = 587;
                        $mail->CharSet = 'UTF-8';

                        $mail->setFrom('homesafe2025contact@gmail.com', 'HomeSafe');

                        foreach ($emails as $email_dest) {
                            $mail->addBCC($email_dest);
                        }

                        $mail->isHTML(true);
                        $mail->Subject = "New discount on furniture at HomeSafe!";

                        $mail->Body = "<h2>Attention!</h2>"
                            . "<p>The furniture <strong>" . htmlspecialchars($nombre) . "</strong> now has a discount of <strong>" . number_format(floatval($descuento), 2) . "%</strong>.</p>"
                            . "<p>Description: " . nl2br(htmlspecialchars($descripcion)) . "</p>"
                            . "<p>Original Price: $" . number_format(floatval($precio), 2) . "</p>"
                            . "<p>Take advantage of this exclusive offer at HomeSafe!</p>"
                            . "<hr><p>This is an automated email. Please do not reply.</p>";

                        $mail->send();
                    } catch (Exception $e) {
                        error_log("Error sending discount notification: " . $mail->ErrorInfo);
                    }
                }
            }

            $_SESSION['alert'] = [
                'title' => 'Success',
                'text' => 'Furniture added successfully',
                'icon' => 'success',
                'button' => 'Close'
            ];
            header('Location: EN_frontend_create.php');
            exit();
        } else {
            echo "Historial data: " . pg_last_error($conn);
            exit();
        }
    } else {
        echo "Error when inserting into furniture: " . pg_last_error($conn);
        exit();
    }
}

pg_close($conn);
?>