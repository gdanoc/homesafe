<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}
$input = json_decode(file_get_contents('php://input'), true);
$mueble_id = intval($input['mueble_id']);
$action = $input['action'];
$mail_user = $_SESSION['email'];

$conn = pg_connect("host=localhost port=5432 dbname=homesafe user=postgres password=Info2025/*-");
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión']);
    exit;
}

if ($action === 'add') {
    $check = pg_query_params($conn, "SELECT id FROM favoritos WHERE mail_user = $1 AND mueble_id = $2", [$mail_user, $mueble_id]);
    if (pg_num_rows($check) > 0) {
        echo json_encode(['success' => false, 'message' => 'Ya está en favoritos']);
        pg_close($conn);
        exit;
    }
    $insert = pg_query_params($conn, "INSERT INTO favoritos (mail_user, mueble_id) VALUES ($1, $2)", [$mail_user, $mueble_id]);
    echo json_encode(['success' => $insert ? true : false]);
} elseif ($action === 'remove') {
    $delete = pg_query_params($conn, "DELETE FROM favoritos WHERE mail_user = $1 AND mueble_id = $2", [$mail_user, $mueble_id]);
    echo json_encode(['success' => $delete ? true : false]);
}
pg_close($conn);
?>