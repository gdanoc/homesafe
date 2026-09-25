<?php
session_start();
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');


function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}


if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'You must log in to leave a review.']);
    exit();
}


if (!isValidEmail($_SESSION['email'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid session.']);
    exit();
}


$input = file_get_contents('php://input');
if (empty($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No data received.']);
    exit();
}

$data = json_decode($input, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid data format.']);
    exit();
}


$mueble_id = $data['mueble_id'] ?? null;
$rating = $data['rating'] ?? null;
$comment = $data['comment'] ?? '';
$user_email = $_SESSION['email'];


if (empty($mueble_id) || !is_numeric($mueble_id) || $mueble_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid furniture ID.']);
    exit();
}

if (empty($rating) || !is_numeric($rating)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid rating.']);
    exit();
}

$mueble_id = (int)$mueble_id;
$rating = (int)$rating;


if ($rating < 1 || $rating > 5) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'The rating must be between 1 and 5 stars.']);
    exit();
}


if (empty(trim($comment))) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Comment is required.']);
    exit();
}

if (strlen($comment) > 255) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'The comment cannot exceed 255 characters.']);
    exit();
}


$comment = strip_tags($comment);


if (preg_match('/(.)\1{5,}/', $comment)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'The comment contains too many consecutive repeated characters.']);
    exit();
}


$servername = "localhost";
$username = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";
$dbname = "homesafe";

$conn = pg_connect("host=$servername dbname=$dbname user=$username password=$password");

if (!$conn) {
    error_log("Database connection error: " . pg_last_error());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal Server error']);
    exit();
}

try {
    
    $checkMuebleQuery = "SELECT id FROM muebles WHERE id = $1";
    $checkMuebleResult = pg_query_params($conn, $checkMuebleQuery, [$mueble_id]);
    
    if (pg_num_rows($checkMuebleResult) === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'The specified piece of furniture does not exist.']);
        pg_close($conn);
        exit();
    }

    
    $checkExistingQuery = "SELECT id FROM reviews WHERE mueble_id = $1 AND user_email = $2";
    $checkExistingResult = pg_query_params($conn, $checkExistingQuery, [$mueble_id, $user_email]);
    
    if (pg_num_rows($checkExistingResult) > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'You have already left a review for this piece of furniture.']);
        pg_close($conn);
        exit();
    }

    
    $insertQuery = "INSERT INTO reviews (mueble_id, user_email, rating, comment, created_at) VALUES ($1, $2, $3, $4, NOW())";
    $result = pg_query_params($conn, $insertQuery, [$mueble_id, $user_email, $rating, $comment]);

    if ($result) {
        http_response_code(201);
        echo json_encode(['success' => true, 'message' => 'Review successfully saved.']);
    } else {
        error_log("Error inserting review: " . pg_last_error($conn));
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error saving review.']);
    }

} catch (Exception $e) {
    error_log("Exception in submit_review: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error.']);
} finally {
    pg_close($conn);
}
?>