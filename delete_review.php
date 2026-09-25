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
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para eliminar una reseña.']);
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
$user_email = $_SESSION['email'];


if (empty($review_id) || !is_numeric($review_id) || $review_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de reseña inválido.']);
    exit();
}

$review_id = (int)$review_id;


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
        echo json_encode(['success' => false, 'message' => 'No tienes permisos para eliminar esta reseña.']);
        pg_close($conn);
        exit();
    }

    
    $deleteQuery = "DELETE FROM reviews WHERE id = $1 AND user_email = $2";
    $result = pg_query_params($conn, $deleteQuery, [$review_id, $user_email]);

    if ($result && pg_affected_rows($result) > 0) {
        echo json_encode(['success' => true, 'message' => 'Reseña eliminada con éxito.']);
    } else {
        error_log("Error al eliminar reseña: " . pg_last_error($conn));
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al eliminar la reseña.']);
    }

} catch (Exception $e) {
    error_log("Excepción en delete_review: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
} finally {
    pg_close($conn);
}
?>