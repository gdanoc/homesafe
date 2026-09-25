<?php
session_start();
header('Content-Type: application/json');

$DATABASE_HOST = 'localhost';
$DATABASE_PORT = '5432';
$DATABASE_USER = 'postgres';
$DATABASE_PASS = 'Info2025/*-';
$DATABASE_NAME = 'homesafe';

$con = pg_connect("host=$DATABASE_HOST port=$DATABASE_PORT dbname=$DATABASE_NAME user=$DATABASE_USER password=$DATABASE_PASS");
if (!$con) {
    echo json_encode(['success' => false, 'message' => 'Connection error']);
    exit();
}

if (!isset($_POST['old_email'], $_POST['new_email'])) {
    echo json_encode(['success' => false, 'message' => 'Incomplete Data']);
    exit();
}

$old_email = strtolower(trim($_POST['old_email']));
$new_email = strtolower(trim($_POST['new_email']));


if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'New email is not valid']);
    exit();
}


$queryCheck = 'SELECT id FROM accounts WHERE LOWER(email) = $1 
               UNION 
               SELECT id FROM email_verifications WHERE LOWER(email) = $1';
$resultCheck = pg_query_params($con, $queryCheck, array($new_email));

if ($resultCheck && pg_num_rows($resultCheck) > 0) {
    echo json_encode(['success' => false, 'message' => 'New email is already in use']);
    exit();
}


$query = 'SELECT id, username, token FROM email_verifications WHERE LOWER(email) = $1';
$result = pg_query_params($con, $query, array($old_email));

if (!$result || pg_num_rows($result) === 0) {
    echo json_encode(['success' => false, 'message' => 'No verification record found for the old email address']);
    exit();
}

$row = pg_fetch_assoc($result);


$updateQuery = 'UPDATE email_verifications SET email = $1, resend_count = 0, last_resend_at = NULL WHERE id = $2';
$updateResult = pg_query_params($con, $updateQuery, array($new_email, $row['id']));

if (!$updateResult) {
    echo json_encode(['success' => false, 'message' => 'Error updating email']);
    exit();
}


require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
    $mail->addAddress($new_email, $row['username']);
    $mail->isHTML(true);

    $mail->Subject = 'Updated verification code for HomeSafe';
    $mail->Body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
            .container { max-width: 600px; margin: 0 auto; background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; margin: -30px -30px 30px -30px; }
            .token { font-size: 32px; font-weight: bold; color: #667eea; text-align: center; padding: 20px; background-color: #f8f9fa; border-radius: 8px; margin: 20px 0; letter-spacing: 5px; }
            .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Welcome to HomeSafe!</h1>
            </div>
            <h2>Hello " . htmlspecialchars($row['username']) . ",</h2>
            <p>Your verification email has been updated. Here is your code to complete the registration.</p>
            <p>Your verification code is:</p>
            <div class='token'>{$row['token']}</div>
            <p>This code will expire in 24 hours for security purposes.</p>
            <p>If you did not request this change, you can ignore this email.</p>
            <div class='footer'>
                <p>This is an automatic email, please do not reply.</p>
                <p>&copy; 2025 HomeSafe. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>";

    $mail->AltBody = "Hello " . htmlspecialchars($row['username']) . ",\n\nYour verification code for HomeSafe is: {$row['token']}\n\nEste código expirará en 24 horas.\n\nThank you for registering with HomeSafe.";

    $mail->send();

    echo json_encode(['success' => true, 'message' => 'Email updated and code forwarded correctly.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error sending mail: ' . $mail->ErrorInfo]);
}

pg_close($con);
?>