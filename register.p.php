<?php
session_start();

if (!isset($_POST['csrf_token'], $_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo "<script>
        alert('Error de seguridad: Solicitud inválida. Por favor, intente nuevamente.');
        window.location='index.php';
    </script>";
    exit();
}
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';


$DATABASE_HOST = 'localhost';
$DATABASE_PORT = '5432';
$DATABASE_USER = 'postgres';
$DATABASE_PASS = 'Info2025/*-';
$DATABASE_NAME = 'homesafe';

$con = pg_connect("host=$DATABASE_HOST port=$DATABASE_PORT dbname=$DATABASE_NAME user=$DATABASE_USER password=$DATABASE_PASS");

if (!$con) {
    die("Error de conexión a la base de datos");
}


if (!isset($_POST['username'], $_POST['password'], $_POST['email'], $_POST['repeat-password'])) {
    echo "<script>
        alert('Por favor, complete todos los campos requeridos');
        window.location='index.php';
    </script>";
    exit();
}

if (!isset($_POST['termsCheck']) || $_POST['termsCheck'] !== 'on') {
    echo "<script>
        alert('Debes aceptar los Términos y Condiciones para registrarte.');
        window.location='index.php';
    </script>";
    exit();
}


if ($_POST['password'] !== $_POST['repeat-password']) {
    echo "<script>
        alert('Las contraseñas no coinciden. Por favor, verifique e intente nuevamente');
        window.location='index.php';
    </script>";
    exit();
}


if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    echo "<script>
        alert('Por favor, ingrese un email válido');
        window.location='index.php';
    </script>";
    exit();
}


if (strlen($_POST['password']) < 6) {
    echo "<script>
        alert('La contraseña debe tener al menos 6 caracteres');
        window.location='index.php';
    </script>";
    exit();
}

$password = $_POST['password']; 


if (!preg_match('/[A-Z]/', $password)) {
    echo "<script>alert('La contraseña debe contener al menos una letra mayúscula.'); window.location='index.php';</script>";
    exit();
}


if (!preg_match('/[a-z]/', $password)) {
    echo "<script>alert('La contraseña debe contener al menos una letra minúscula.'); window.location='index.php';</script>";
    exit();
}


if (!preg_match('/[0-9]/', $password)) {
    echo "<script>alert('La contraseña debe contener al menos un número.'); window.location='index.php';</script>";
    exit();
}


if (!preg_match('/[\W_]/', $password)) { 
    echo "<script>alert('La contraseña debe contener al menos un carácter especial.'); window.location='index.php';</script>";
    exit();
}

$username = trim($_POST['username']);


if (!preg_match('/^[a-zA-Z0-9._-]{3,20}$/', $username)) {
    echo "<script>
        alert('El nombre de usuario solo puede contener letras, números, puntos, guiones bajos y debe tener entre 3 y 20 caracteres.');
        window.location='index.php';
    </script>";
    exit();
}


$username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');

$email = trim(strtolower($_POST['email']));
$password = $_POST['password'];


$query = 'SELECT id FROM accounts WHERE LOWER(email) = $1
          UNION
          SELECT id FROM email_verifications WHERE LOWER(email) = $1';
$result = pg_query_params($con, $query, array($email));

if ($result && pg_num_rows($result) > 0) {
    echo "<script>
        alert('Este correo electrónico ya está registrado o tiene una verificación pendiente');
        window.location='index.php';
    </script>";
    exit();
}


$query = 'SELECT id FROM accounts WHERE LOWER(username) = $1
          UNION
          SELECT id FROM email_verifications WHERE LOWER(username) = $1';
$result = pg_query_params($con, $query, array(strtolower($username)));

if ($result && pg_num_rows($result) > 0) {
    echo "<script>
        alert('El nombre de usuario ya está en uso. Por favor, elija otro');
        window.location='index.php';
    </script>";
    exit();
}


function generateSecureToken($length = 6)
{
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $token = '';
    for ($i = 0; $i < $length; $i++) {
        $token .= $characters[random_int(0, strlen($characters) - 1)];
    }
    return $token;
}


$token = generateSecureToken();
$passwordHash = password_hash($password, PASSWORD_DEFAULT);


$cleanupQuery = 'DELETE FROM email_verifications WHERE created_at < NOW() - INTERVAL \'24 hours\'';
pg_query($con, $cleanupQuery);


$query = 'INSERT INTO email_verifications (username, email, password, token, created_at)
          VALUES ($1, $2, $3, $4, NOW())';
$result = pg_query_params($con, $query, array($username, $email, $passwordHash, $token));

if (!$result) {
    echo "<script>
        alert('Error al procesar el registro. Por favor, intente nuevamente');
        window.location='index.php';
    </script>";
    exit();
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
    $mail->addAddress($email, $username);
    $mail->isHTML(true);
    $mail->Subject = 'Código de verificación para HomeSafe';
    $mail->Body = "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Verificación de Correo - HomeSafe</title>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background-color: #f4f4f4;
                margin: 0;
                padding: 20px;
                color: #333;
            }
            .container {
                max-width: 600px;
                margin: 0 auto;
                background-color: #ffffff;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 0 15px rgba(0,0,0,0.05);
                overflow: hidden;
            }
            .header {
                background-color: #343a40; /* Dark grey */
                color: white;
                padding: 30px 20px;
                text-align: center;
                border-radius: 8px 8px 0 0;
                margin: -30px -30px 30px -30px; /* Adjust to fill container width */
            }
            .header h1 {
                font-size: 28px;
                margin-bottom: 10px;
                font-weight: 300;
            }
            .header p {
                font-size: 16px;
                opacity: 0.9;
            }
            .content {
                padding: 0 10px; /* Add some horizontal padding */
            }
            .greeting {
                font-size: 20px;
                margin-bottom: 15px;
                color: #333;
            }
            .message {
                font-size: 16px;
                margin-bottom: 20px;
                line-height: 1.6;
            }
            .token-section {
                text-align: center;
                padding: 25px;
                background-color: #f8f9fa; /* Light grey */
                border: 1px solid #e9ecef;
                border-radius: 8px;
                margin: 25px 0;
            }
            .token-label {
                font-size: 18px;
                color: #555;
                margin-bottom: 10px;
                display: block;
            }
            .token {
                font-size: 36px;
                font-weight: bold;
                color: #1976d2; /* A clean blue for the token */
                letter-spacing: 4px;
                display: inline-block;
                padding: 10px 20px;
                background-color: #e3f2fd; /* Lighter blue background for token */
                border-radius: 6px;
            }
            .expiration-info {
                font-size: 14px;
                color: #777;
                margin-top: 20px;
            }
            .ignore-message {
                font-size: 14px;
                color: #777;
                margin-top: 30px;
            }
            .footer {
                margin-top: 40px;
                padding-top: 20px;
                border-top: 1px solid #e9ecef;
                color: #666;
                font-size: 12px;
                text-align: center;
            }
            .footer p {
                margin-bottom: 5px;
            }
            .footer .brand {
                font-size: 16px;
                font-weight: bold;
                color: #343a40;
                margin-bottom: 10px;
            }
            @media (max-width: 600px) {
                body {
                    padding: 10px;
                }
                .container {
                    padding: 20px;
                    border-radius: 0;
                }
                .header {
                    margin: -20px -20px 20px -20px;
                    padding: 20px 15px;
                }
                .token {
                    font-size: 28px;
                    padding: 8px 15px;
                }
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>¡Bienvenido a HomeSafe!</h1>
                <p>Tu hogar, nuestra prioridad</p>
            </div>
            <div class='content'>
                <p class='greeting'>Hola " . htmlspecialchars($username) . ",</p>
                <p class='message'>Gracias por registrarte en HomeSafe. Para completar tu registro, necesitamos verificar tu dirección de correo electrónico.</p>
                <p class='message'>Tu código de verificación es:</p>
                <div class='token-section'>
                    <span class='token-label'>CÓDIGO DE VERIFICACIÓN</span>
                    <div class='token'>" . htmlspecialchars($token) . "</div>
                    <p class='expiration-info'>Este código expirará en 24 horas por seguridad.</p>
                </div>
                <p class='ignore-message'>Si no solicitaste este registro, puedes ignorar este correo.</p>
            </div>
            <div class='footer'>
                <div class='brand'>HomeSafe</div>
                <p>5 Calle Ote. 1-1, Santa Tecla, El Salvador</p>
                <p>Este es un correo automático, por favor no responder.</p>
                <p style='font-size: 12px; margin-top: 10px;'>
                    &copy; 2025 HomeSafe. Todos los derechos reservados.
                </p>
            </div>
        </div>
    </body>
    </html>";
    $mail->AltBody = "Hola " . htmlspecialchars($username) . ",\n\nTu código de verificación para HomeSafe es: $token\n\nEste código expirará en 24 horas.\n\nGracias por registrarte en HomeSafe.";
    $mail->send();

    
    echo "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Verificación Requerida - HomeSafe</title>
        <link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'>
        <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css'>
        <style>
            .bg_background {
                background-color: #343a40; /* Dark grey */
            }
            .modal-dialog-enhanced {
                max-width: 450px;
                margin: 2rem auto;
            }
            .modal-content {
                border: none;
                border-radius: 8px;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
                overflow: hidden;
            }
            .modal-header {
                border: none;
                padding: 2rem 2rem 1rem;
                background-color: #343a40; /* Dark grey */
                position: relative;
            }
            .modal-title {
                font-weight: 600;
                font-size: 1.5rem;
                color: white !important;
                margin: 0;
            }
            .close {
                position: absolute;
                right: 1.5rem;
                top: 1.5rem;
                color: white;
                opacity: 0.8;
                font-size: 1.5rem;
                transition: opacity 0.3s ease;
            }
            .close:hover {
                opacity: 1;
                color: white;
            }
            .modal-body {
                background: #fafafa;
                padding: 2rem;
            }
            .form-group {
                margin-bottom: 1.5rem;
            }
            .input-group {
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
                transition: all 0.3s ease;
            }
            .input-group:focus-within {
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
            }
            .input-group-text {
                background: white;
                border: none;
                color: #1976d2; /* Blue */
                font-size: 1.1rem;
            }
            .form-control {
                border: none;
                padding: 0.875rem 1rem;
                font-size: 1rem;
                background: white;
                transition: all 0.3s ease;
            }
            .form-control:focus {
                box-shadow: none;
                border: none;
                background: white;
            }
            .btn-primary {
                background-color: #1976d2; /* Blue */
                border: none;
                padding: 0.875rem 2rem;
                font-weight: 500;
                font-size: 1rem;
                border-radius: 12px;
                transition: all 0.3s ease;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(25, 118, 210, 0.4); /* Blue shadow */
                background-color: #1565c0; /* Darker blue */
            }
            .modal-body a {
                color: #1976d2; /* Blue */
                text-decoration: none;
                font-weight: 500;
                transition: color 0.3s ease;
            }
            .modal-body a:hover {
                color: #1565c0; /* Darker blue */
                text-decoration: none;
            }
            .verification-info {
                background: #e8f4f8; /* Light blue */
                padding: 1rem;
                border-radius: 8px;
                margin-bottom: 1.5rem;
                border-left: 4px solid #1976d2; /* Blue */
            }
            .verification-info i {
                color: #1976d2; /* Blue */
                margin-right: 0.5rem;
            }
        </style>
    </head>
    <body>
        <script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>
        <script src='https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js'></script>
        <script>
            \$(document).ready(function() {
                
                \$('#verifyTokenModal').modal({
                    backdrop: 'static',
                    keyboard: false
                });

                
                \$('#verify-email').val('" . addslashes($email) . "');

                
                let timeLeft = 120; 
                const timer = setInterval(function() {
                    timeLeft--;
                    const minutes = Math.floor(timeLeft / 60);
                    const seconds = timeLeft % 60;
                    \$('#countdown').text(minutes + ':' + (seconds < 10 ? '0' : '') + seconds);

                    if (timeLeft <= 0) {
                        clearInterval(timer);
                        \$('#countdown').text('Expirado');
                    }
                }, 1000);
            });
        </script>
    </body>
    </html>";
    
    include 'verify_token_modal.php';
} catch (Exception $e) {
    echo "<script>
        alert('Error al enviar el correo de verificación: " . addslashes($e->getMessage()) . "');
        window.location='index.php';
    </script>";
    exit();
}
pg_close($con);
?>
