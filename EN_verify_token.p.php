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
    echo json_encode(['success' => false, 'message' => 'Database connection error']);
    exit();
}

if (!isset($_POST['token'], $_POST['email'])) {
    echo json_encode(['success' => false, 'message' => 'Incomplete data']);
    exit();
}

$token = $_POST['token'];
$email = $_POST['email'];

$query = 'SELECT username, password FROM email_verifications WHERE email = $1 AND token = $2';
$result = pg_query_params($con, $query, array($email, $token));

if ($result && pg_num_rows($result) === 1) {
    $row = pg_fetch_assoc($result);
    $username = $row['username'];
    $password = $row['password'];

    $insertQuery = 'INSERT INTO accounts (username, email, password, id_rol) VALUES ($1, $2, $3, 0)';
    $insertResult = pg_query_params($con, $insertQuery, array($username, $email, $password));

    if ($insertResult) {
        $deleteQuery = 'DELETE FROM email_verifications WHERE email = $1';
        pg_query_params($con, $deleteQuery, array($email));

        echo json_encode([
            'success' => true,
            'message' => 'Registration completed successfully. You can now log in.',
            'redirect' => 'EN_index.php'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error completing registration']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Incorrect verification code']);
}

pg_close($con);
?>