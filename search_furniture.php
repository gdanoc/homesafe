<?php
/* ------------------------------------------------------------------
 |  search_furniture.php – versión hardenizada
 * ------------------------------------------------------------------*/

session_start();

/* ---------- 1) Encabezados de seguridad ---------- */
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

/* ---------- 2) Parámetros de conexión ---------- */
$dsn = "host=localhost dbname=homesafe user=postgres password=Info2025/*-";
$conn = pg_connect($dsn);
if (!$conn) {
    http_response_code(500);
    echo json_encode([]);
    exit();
}

/* ---------- 3) Obtener y validar el parámetro ---------- */
$q = $_GET['q'] ?? '';
$q = trim($q);

/* 3-a) Longitud razonable (DoS) */
if (strlen($q) === 0) {
    echo json_encode([]);    
    pg_close($conn);
    exit();
}
if (strlen($q) > 100) {      
    echo json_encode([]);
    pg_close($conn);
    exit();
}

/* 3-b) Caracteres permitidos
   – Permitimos letras, números, espacios y diéresis/acentos.
   – Cualquier cosa fuera de eso la descartamos para evitar
     %00, comillas rotas, etc. */
$clean_q = preg_replace('/[^\p{L}\p{N}\s]/u', '', $q);
if ($clean_q === '') {                    
    echo json_encode([]);
    pg_close($conn);
    exit();
}

/* ---------- 4) Consulta parametrizada ---------- */
$sql  = "SELECT id, nombre
         FROM muebles
         WHERE LOWER(nombre) LIKE LOWER($1)
         ORDER BY nombre
         LIMIT 10";

$result = pg_query_params($conn, $sql, ['%'.$clean_q.'%']);
if (!$result) {
    
    error_log('Search error: '.pg_last_error($conn));
    http_response_code(500);
    echo json_encode([]);
    pg_close($conn);
    exit();
}

/* ---------- 5) Salida segura ---------- */
$rows = [];
while ($row = pg_fetch_assoc($result)) {
    $rows[] = [
        'id'     => (int)$row['id'],
        
        'nombre' => htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8')
    ];
}
echo json_encode($rows);
pg_close($conn);

/* Valida emails (para cualquier endpoint) */
function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/* Sanitize genérico de strings */
function sanitize(string $str): string {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}
?>