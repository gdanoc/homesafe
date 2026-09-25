<?php
header('Content-Type: application/json');
$host = "localhost";
$port = "5432";
$username = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";
$dbname = "homesafe";
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$username password=$password");
if (!$conn) {
    echo json_encode([]);
    exit;
}
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
if ($q === '') {
    echo json_encode([]);
    exit;
}
$query = "SELECT id, nombre FROM propiedades WHERE LOWER(nombre) LIKE LOWER($1) LIMIT 10";
$result = pg_query_params($conn, $query, ['%' . $q . '%']);
$rows = [];
while ($row = pg_fetch_assoc($result)) {
    $rows[] = ['id' => $row['id'], 'nombre' => $row['nombre']];
}
echo json_encode($rows);
pg_close($conn);
?>