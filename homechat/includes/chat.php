<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '128M');
set_time_limit(60);

$historial = $_POST['historial'] ?? '';
$lat = $_POST['lat'] ?? null;
$lng = $_POST['lng'] ?? null;


const DB_CONFIG = [
    'host' => 'localhost',
    'port' => '5432',
    'dbname' => 'homesafe',
    'user' => 'postgres',
    'password' => 'Info2025/*-',
    'connect_timeout' => 5
];

class Database
{
    private static $conn = null;

    public static function connect()
    {
        if (self::$conn === null) {
            $conn_string = sprintf(
                "host=%s port=%s dbname=%s user=%s password=%s connect_timeout=%d",
                DB_CONFIG['host'],
                DB_CONFIG['port'],
                DB_CONFIG['dbname'],
                DB_CONFIG['user'],
                DB_CONFIG['password'],
                DB_CONFIG['connect_timeout']
            );

            self::$conn = pg_pconnect($conn_string);
            if (!self::$conn) {
                error_log("Error de conexión a BD");
                throw new Exception("Database connection failed");
            }
        }
        return self::$conn;
    }
}

$mensaje = trim($_POST['mensaje'] ?? '');
if (empty($mensaje)) {
    http_response_code(400);
    exit(json_encode(['error' => true, 'message' => 'Mensaje vacío']));
}

$mensaje = htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8');
$mensaje_lower = strtolower($mensaje);

class QueryAnalyzer
{
    private const KEYWORDS = [
        'propiedades' => 'casa|casas|propiedad|propiedades|inmueble|venta|comprar|precio|habitacion|cuarto|dormitorio|baño|terreno|m2|metros|ubicacion|direccion|zona|house|property|sale|buy|price|room|bedroom|bathroom|land|location',
        'muebles' => 'mueble|muebles|mesa|silla|sofa|cama|armario|stock|inventario|disponible|furniture|table|chair|bed|wardrobe|inventory|available'
    ];

    private const QUERY_PATTERNS = [
        'mas_barato' => 'mas barat[oa]|cheapest|menor precio|economico',
        'mas_caro' => 'mas car[oa]|most expensive|mayor precio|premium|lujo',
        'mas_habitaciones' => 'mas habitaciones|mas cuartos|most bedrooms',
        'menos_habitaciones' => 'menos habitaciones|menos cuartos|least bedrooms',
        'mas_banos' => 'mas baños|most bathrooms',
        'menos_banos' => 'menos baños|least bathrooms',
        'mayor_stock' => 'mayor stock|mas stock|highest stock',
        'menor_stock' => 'menor stock|menos stock|lowest stock',
        'mejores' => 'mejores|mejor|best|top|destacado|recomendado',
        'nuevos' => 'nuevos|nueva|nuevo|new|recent|reciente'
    ];  

    public static function needsDatabase($mensaje)
    {
        $mensaje_lower = strtolower($mensaje);
        foreach (self::KEYWORDS as $keywords) {
            if (preg_match("/($keywords)/i", $mensaje_lower)) {
                return true;
            }
        }
        return false;
    }

    public static function getQueryType($mensaje)
    {
        $mensaje_lower = strtolower($mensaje);
        $has_props = preg_match('/' . self::KEYWORDS['propiedades'] . '/i', $mensaje_lower);
        $has_furniture = preg_match('/' . self::KEYWORDS['muebles'] . '/i', $mensaje_lower);

        if ($has_props && $has_furniture) return 'ambos';
        if ($has_props) return 'propiedades';
        if ($has_furniture) return 'muebles';
        return 'general';
    }

    public static function getAdvancedQueryType($mensaje)
    {
        $mensaje_lower = strtolower($mensaje);
        foreach (self::QUERY_PATTERNS as $type => $pattern) {
            if (preg_match("/($pattern)/i", $mensaje_lower)) {
                return $type;
            }
        }
        return 'general';
    }

    public static function extractPriceRange($mensaje)
    {
        
        if (preg_match('/\$?(\d+(?:,\d{3})*)\s*[-a]\s*\$?(\d+(?:,\d{3})*)/', $mensaje, $matches)) {
            return [(float)str_replace(',', '', $matches[1]), (float)str_replace(',', '', $matches[2])];
        }
        
        if (preg_match('/(?:hasta|max|maximo)\s*\$?(\d+(?:,\d{3})*)/', strtolower($mensaje), $matches)) {
            return [0, (float)str_replace(',', '', $matches[1])];
        }
        
        if (preg_match('/(?:desde|min|minimo)\s*\$?(\d+(?:,\d{3})*)/', strtolower($mensaje), $matches)) {
            return [(float)str_replace(',', '', $matches[1]), PHP_FLOAT_MAX];
        }
        return [null, null];
    }
}

class QueryBuilder
{
    private const ORDER_BY = [
        'mas_barato' => 'precio ASC',
        'mas_caro' => 'precio DESC',
        'mas_habitaciones' => 'bedrooms DESC, precio ASC',
        'menos_habitaciones' => 'bedrooms ASC, precio ASC',
        'mas_banos' => 'bathrooms DESC, precio ASC',
        'menos_banos' => 'bathrooms ASC, precio ASC',
        'mayor_stock' => 'cantidad DESC',
        'menor_stock' => 'cantidad ASC',
        'mejores' => 'precio DESC',
        'nuevos' => 'fecha DESC'
    ];

    public static function buildPropertyQuery($query_type, $price_range, $lat, $lng)
    {
        $select = "nombre, descripcion, bathrooms, bedrooms, size, precio, fecha, mail_user, direccion_completa, estado";
        $where = ["estado = 'Disponible'"];
        $order = self::ORDER_BY[$query_type] ?? 'fecha DESC';

        if ($lat && $lng) {
            $select .= ", (6371 * acos(cos(radians($lat)) * cos(radians(latitud)) * cos(radians(longitud) - radians($lng)) + sin(radians($lat)) * sin(radians(latitud)))) AS distancia";
            $where[] = "(6371 * acos(cos(radians($lat)) * cos(radians(latitud)) * cos(radians(longitud) - radians($lng)) + sin(radians($lat)) * sin(radians(latitud)))) <= 15";
            $order = "distancia ASC";
        }


        if ($price_range[0] !== null && $price_range[1] !== null) {
            $where[] = "precio BETWEEN {$price_range[0]} AND {$price_range[1]}";
        } elseif ($price_range[0] !== null) {
            $where[] = "precio >= {$price_range[0]}";
        } elseif ($price_range[1] !== null) {
            $where[] = "precio <= {$price_range[1]}";
        }

        return "SELECT $select FROM propiedades WHERE " . implode(' AND ', $where) . " ORDER BY $order LIMIT 5";
    }

    public static function buildFurnitureQuery($query_type, $price_range)
    {
        $where = ["estado = 'Disponible'", "cantidad > 0"];
        $order = self::ORDER_BY[$query_type] ?? 'fecha DESC';

        if ($price_range[0] !== null && $price_range[1] !== null) {
            $where[] = "precio BETWEEN {$price_range[0]} AND {$price_range[1]}";
        } elseif ($price_range[0] !== null) {
            $where[] = "precio >= {$price_range[0]}";
        } elseif ($price_range[1] !== null) {
            $where[] = "precio <= {$price_range[1]}";
        }

        return "SELECT nombre, descripcion, precio, cantidad, estado FROM muebles WHERE " . implode(' AND ', $where) . " ORDER BY $order LIMIT 5";
    }
}

try {
    $needs_db = QueryAnalyzer::needsDatabase($mensaje_lower);
    $query_type = QueryAnalyzer::getQueryType($mensaje_lower);
    $advanced_query = QueryAnalyzer::getAdvancedQueryType($mensaje_lower);
    $price_range = QueryAnalyzer::extractPriceRange($mensaje_lower);
} catch (Exception $e) {
    http_response_code(500);
    exit(json_encode(["error" => true, "message" => "Error analizando mensaje"]));
}

$contexto = "Eres HomeCHAT, asistente de HomeSafe El Salvador.\n\nSERVICIOS:\n🏠 Propiedades\n🪑 Muebles\n";
if ($needs_db) {
    if ($query_type === 'propiedades' || $query_type === 'ambos') {
        $contexto .= "\n📋 PROPIEDADES:\n" . DataRetriever::getProperties($advanced_query, $price_range, $lat, $lng);
    }
    if ($query_type === 'muebles' || $query_type === 'ambos') {
        $contexto .= "\n📋 MUEBLES:\n" . DataRetriever::getFurniture($advanced_query, $price_range);
    }
}

class DataRetriever
{
    private static $cache = [];
    private const CACHE_TTL = 300;

    private static function getCacheKey($type, $params)
    {
        return md5($type . serialize($params));
    }

    private static function getFromCache($key)
    {
        return isset(self::$cache[$key]) && (time() - self::$cache[$key]['time'] < self::CACHE_TTL) ? self::$cache[$key]['data'] : null;
    }

    private static function setCache($key, $data)
    {
        self::$cache[$key] = ['data' => $data, 'time' => time()];
    }

    public static function getProperties($query_type,  $price_range, $lat, $lng)
    {
        $key = self::getCacheKey('props', [$query_type,  $price_range, $lat, $lng]);
        if ($cached = self::getFromCache($key)) return $cached;

        $conn = Database::connect();
        $query = QueryBuilder::buildPropertyQuery($query_type, $price_range, $lat, $lng);
        $result = pg_query($conn, $query);
        if (!$result) return "Error al consultar propiedades.";

        $data = "";
        while ($row = pg_fetch_assoc($result)) {
            $data .= "🏠 {$row['nombre']}: " .
                ($row['descripcion'] ?: 'Sin descripción') . " - " .
                "{$row['bathrooms']} baños, {$row['bedrooms']} hab, {$row['size']} m² - \$" . number_format($row['precio'], 2) .
                ". 📍 {$row['direccion_completa']}. 📧 {$row['mail_user']}";
            if (isset($row['distancia'])) $data .= ". 📏 " . round($row['distancia'], 1) . " km";
            $data .= ".\n";
        }
        pg_free_result($result);
        $data = $data ?: "No se encontraron propiedades.";
        self::setCache($key, $data);
        return $data;
    }

    public static function getFurniture($query_type, $price_range)
    {
        $key = self::getCacheKey('furniture', [$query_type, $price_range]);
        if ($cached = self::getFromCache($key)) return $cached;

        $conn = Database::connect();
        $query = QueryBuilder::buildFurnitureQuery($query_type, $price_range);
        $result = pg_query($conn, $query);
        if (!$result) return "Error al consultar muebles.";

        $data = "";
        while ($row = pg_fetch_assoc($result)) {
            $data .= "🪑 {$row['nombre']}: \$" . number_format($row['precio'], 2) .
                " - Stock: {$row['cantidad']} unidades";
            if ($row['descripcion']) $data .= ". {$row['descripcion']}";
            $data .= ".\n";
        }
        pg_free_result($result);
        $data = $data ?: "No se encontraron muebles.";
        self::setCache($key, $data);
        return $data;
    }
}

if (!empty($historial)) {
    $contexto .= "\n\nHistorial:\n$historial";
}

$contexto .= "\n\nPregunta: $mensaje\nRespuesta:";

$payload = json_encode([
    "model" => "qwen2:1.5b",
    "prompt" => $contexto,
    "stream" => false,
    "options" => [
        "temperature" => 0.2,
        "top_p" => 0.8,
        "num_predict" => strlen($mensaje) < 10 ? 60 : 120,
        "top_k" => 40,
        "repeat_penalty" => 1.1,
        "num_ctx" => 1024,
        "num_thread" => 2,
        "stop" => ["Pregunta:", "\n\nPregunta", "\nRespuesta:", "Respuesta:"]
    ]
]);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => "http://localhost:11434/api/generate",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_TIMEOUT => 60,
    CURLOPT_CONNECTTIMEOUT => 10
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($response === false || $curl_error) {
    http_response_code(503);
    exit(json_encode(["error" => true, "message" => "IA no disponible"]));
}

$data = json_decode($response, true);
$respuesta = trim($data['response'] ?? '');
$respuesta = preg_replace('/^(Respuesta:|R:)\s*/i', '', $respuesta);
if (strlen($respuesta) < 20) {
    $respuesta = "Disculpa, no pude procesar tu consulta. ¿Podrías reformularla?";
} elseif (!preg_match('/[.!?]$/', $respuesta)) {
    $respuesta .= '.';
}

echo json_encode([
    "error" => false,
    "response" => $respuesta,
    "metadata" => [
        "query_type" => $query_type,
        "advanced_query" => $advanced_query,
        "used_coordinates" => ($lat !== null && $lng !== null),
        "used_database" => $needs_db
    ]
]);
