<?php
include('0_ESvalidacion_Admin.php');
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
        $errores[] = "Nombre inválido. Verifica los caracteres y el largo.";
    }

    
    if ($descripcion === '' || strlen($descripcion) < 10 || strlen($descripcion) > 500) {
        $errores[] = "La descripción debe tener entre 10 y 500 caracteres.";
    }

    
    if (!is_numeric($precio) || $precio < 0.01 || $precio > 99999.99 || (strpos($precio, '.') !== false && strlen(explode('.', $precio)[1]) > 2)) {
        $errores[] = "El precio debe ser un número válido entre 0.01 y 99999.99 con máximo 2 decimales.";
    }

    
    if (!ctype_digit($cantidad) || $cantidad < 1 || $cantidad > 100000) {
        $errores[] = "La cantidad debe ser un número entero entre 1 y 100000.";
    }

    if ($descuento !== null && $descuento !== '') {
        if (!is_numeric($descuento) || $descuento < 0 || $descuento > 100) {
            $errores[] = "El descuento debe ser un número entre 0 y 100.";
        }
    } else {
        $descuento = null; 
    }

    
    if (!isset($archivo['tmp_name']) || $archivo['error'] !== UPLOAD_ERR_OK) {
        $errores[] = "Debe subir una imagen válida.";
    } else {
        $tipoPermitido = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($archivo['type'], $tipoPermitido)) {
            $errores[] = "Formato de imagen no válido. Use JPG, PNG, GIF o WEBP.";
        }
    }

    
    if (!empty($errores)) {
        $_SESSION['alert'] = [
            'title' => 'Error en los datos',
            'text' => implode("\n", $errores),
            'icon' => 'warning',
            'button' => 'Cerrar'
        ];
        header('Location: frontend_create.php');
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
                        $mail->Subject = "¡Nuevo descuento en muebles en HomeSafe!";

                        $mail->Body = "<h2>¡Atención!</h2>"
                            . "<p>El mueble <strong>" . htmlspecialchars($nombre) . "</strong> ahora tiene un descuento del <strong>" . number_format(floatval($descuento), 2) . "%</strong>.</p>"
                            . "<p>Descripción: " . nl2br(htmlspecialchars($descripcion)) . "</p>"
                            . "<p>Precio original: $" . number_format(floatval($precio), 2) . "</p>"
                            . "<p>¡Aprovecha esta oferta exclusiva en HomeSafe!</p>"
                            . "<hr><p>Este es un correo automático, por favor no respondas.</p>";

                        $mail->send();
                    } catch (Exception $e) {
                        error_log("Error al enviar notificación de descuento: " . $mail->ErrorInfo);
                    }
                }
            }

            $_SESSION['alert'] = [
                'title' => 'Éxito',
                'text' => 'Mueble agregado correctamente',
                'icon' => 'success',
                'button' => 'Cerrar'
            ];
            header('Location: frontend_create.php');
            exit();
        } else {
            echo "Error en el historial: " . pg_last_error($conn);
            exit();
        }
    } else {
        echo "Error al insertar en muebles: " . pg_last_error($conn);
        exit();
    }
}

pg_close($conn);
?>