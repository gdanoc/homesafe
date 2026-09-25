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

if (!isset($_POST['email'])) {
    echo json_encode(['success' => false, 'message' => 'Email not provided']);
    exit();
}

$email = strtolower(trim($_POST['email']));


$query = 'SELECT id, username, token, resend_count, last_resend_at, created_at FROM email_verifications WHERE LOWER(email) = $1';
$result = pg_query_params($con, $query, array($email));

if (!$result || pg_num_rows($result) === 0) {
    echo json_encode(['success' => false, 'message' => 'No verification record found for this email address']);
    exit();
}

$row = pg_fetch_assoc($result);


$created_at = strtotime($row['created_at']);
if ($created_at < strtotime('-24 hours')) {
    echo json_encode(['success' => false, 'message' => 'The verification code has expired. Please register again.']);
    exit();
}


$resend_count = (int)$row['resend_count'];
$last_resend_at = $row['last_resend_at'] ? strtotime($row['last_resend_at']) : 0;
$now = time();

if ($resend_count >= 3 && ($now - $last_resend_at) < 1800) {
    echo json_encode(['success' => false, 'message' => 'You have reached your forwarding limit. Please try again later.']);
    exit();
}


if (($now - $last_resend_at) > 1800) {
    
    $resend_count = 0;
}

$resend_count++;
$updateQuery = 'UPDATE email_verifications SET resend_count = $1, last_resend_at = NOW() WHERE id = $2';
pg_query_params($con, $updateQuery, array($resend_count, $row['id']));


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
    $mail->addAddress($email, $row['username']);
    $mail->isHTML(true);

    $mail->Subject = 'Resend: HomeSafe Verification Code';
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
            <p>This is a resend of your verification code to complete your registration.</p>
            <p>Your verification code is:</p>
            <div class='token'>{$row['token']}</div>
            <p>This code will expire in 24 hours for security purposes.</p>
            <p>If you did not request this registration, you can ignore this email.</p>
            <div class='footer'>
                <p>This is an automatic email, please do not reply.</p>
                <p>&copy; 2025 HomeSafe. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>";

    $mail->AltBody = "Hello " . htmlspecialchars($row['username']) . ",\n\nYour verification code for HomeSafe is: {$row['token']}\n\nThis code will expire in 24 hours.\n\nThank you for registering with HomeSafe.";

    $mail->send();

    echo json_encode(['success' => true, 'message' => 'Code successfully resent. Check your email.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error sending mail: ' . $mail->ErrorInfo]);
}

pg_close($con);
?>