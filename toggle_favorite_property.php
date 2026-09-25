<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}
$data = json_decode(file_get_contents('php://input'), true);
$propiedad_id = $data['propiedad_id'];
$action = $data['action'];
$conn = pg_connect("host=localhost dbname=homesafe user=postgres password=Info2025/*-");
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión']);
    exit;
}
if ($action === 'add') {
    $result = pg_query_params($conn, "INSERT INTO favoritos_propiedades (mail_user, propiedad_id) VALUES ($1, $2) ON CONFLICT DO NOTHING", [$_SESSION['email'], $propiedad_id]);
    echo json_encode(['success' => $result ? true : false]);
} else if ($action === 'remove') {
    $result = pg_query_params($conn, "DELETE FROM favoritos_propiedades WHERE mail_user = $1 AND propiedad_id = $2", [$_SESSION['email'], $propiedad_id]);
    echo json_encode(['success' => $result ? true : false]);
}
pg_close($conn);
?>