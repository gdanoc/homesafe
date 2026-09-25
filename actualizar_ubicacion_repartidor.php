<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['repartidor_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit();
}

$repartidor_id = $_SESSION['repartidor_id'];

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['lat']) || !isset($data['lng'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit();
}

$lat = floatval($data['lat']);
$lng = floatval($data['lng']);

$host = "localhost";
$dbname = "homesafe";
$user = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";

$conn = pg_connect("host=$host dbname=$dbname user=$user password=$password");
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión']);
    exit();
}

$query = "UPDATE repartidores SET latitud_actual = $1, longitud_actual = $2 WHERE id = $3";
$result = pg_query_params($conn, $query, array($lat, $lng, $repartidor_id));

if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
}

pg_close($conn);
?>