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
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para editar una reseña.']);
    exit();
}

if (!isValidEmail($_SESSION['email'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Sesión inválida.']);
    exit();
}


$input = file_get_contents('php://input');
if (empty($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No se recibieron datos.']);
    exit();
}

$data = json_decode($input, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Formato de datos inválido.']);
    exit();
}

$review_id = $data['review_id'] ?? null;
$rating = $data['rating'] ?? null;
$comment = $data['comment'] ?? '';
$user_email = $_SESSION['email'];


if (empty($review_id) || !is_numeric($review_id) || $review_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de reseña inválido.']);
    exit();
}

if (empty($rating) || !is_numeric($rating)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Calificación inválida.']);
    exit();
}

$review_id = (int)$review_id;
$rating = (int)$rating;

if ($rating < 1 || $rating > 5) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'La calificación debe ser entre 1 y 5 estrellas.']);
    exit();
}

if (empty(trim($comment))) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'El comentario es requerido.']);
    exit();
}

if (strlen($comment) > 255) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'El comentario no puede exceder 255 caracteres.']);
    exit();
}


$comment = strip_tags($comment);


if (preg_match('/(.)\1{5,}/', $comment)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'El comentario contiene demasiados caracteres repetidos consecutivos.']);
    exit();
}


$servername = "localhost";
$username = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";
$dbname = "homesafe";

$conn = pg_connect("host=$servername dbname=$dbname user=$username password=$password");

if (!$conn) {
    error_log("Error de conexión a BD: " . pg_last_error());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
    exit();
}

try {
    
    $checkQuery = "SELECT id FROM reviews WHERE id = $1 AND user_email = $2";
    $checkResult = pg_query_params($conn, $checkQuery, [$review_id, $user_email]);

    if (pg_num_rows($checkResult) === 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'No tienes permisos para editar esta reseña.']);
        pg_close($conn);
        exit();
    }

    
    $updateQuery = "UPDATE reviews SET rating = $1, comment = $2, created_at = NOW() WHERE id = $3 AND user_email = $4";
    $result = pg_query_params($conn, $updateQuery, [$rating, $comment, $review_id, $user_email]);

    if ($result && pg_affected_rows($result) > 0) {
        echo json_encode(['success' => true, 'message' => 'Reseña actualizada con éxito.']);
    } else {
        error_log("Error al actualizar reseña: " . pg_last_error($conn));
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al actualizar la reseña.']);
    }

} catch (Exception $e) {
    error_log("Excepción en edit_review: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
} finally {
    pg_close($conn);
}
?>