<?php
session_start();


header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');


function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function respondJson($status, $message) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => $status,
        'message' => $message
    ]);
    exit();
}


if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    respondJson('error', 'You must log in to add products to your cart.');
}

if (!isValidEmail($_SESSION['email'])) {
    respondJson('error', 'Invalid session.');
}


if (!isset($_GET['id']) || empty($_GET['id']) || !is_numeric($_GET['id']) || $_GET['id'] <= 0) {
    respondJson('error', 'No valid product has been specified.');
}

$producto_id = (int)$_GET['id'];
$cantidad = isset($_GET['quantity']) && is_numeric($_GET['quantity']) && $_GET['quantity'] > 0 ? (int)$_GET['quantity'] : 1;


if ($cantidad > 100) {
    respondJson('error', 'The amount requested is too high.');
}


$host = "localhost";
$port = "5432";
$dbname = "homesafe";
$username = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";
$conn_string = "host=$host port=$port dbname=$dbname user=$username password=$password";
$conn = pg_connect($conn_string);

if (!$conn) {
    error_log("DB connection error: " . pg_last_error());
    respondJson('error', 'Error connecting to the server.');
}

$email = $_SESSION['email'];

try {
    
    if (!isset($_SESSION['user_id'])) {
        $query = "SELECT id FROM accounts WHERE email = $1";
        $result = pg_query_params($conn, $query, [$email]);
        
        if (pg_num_rows($result) > 0) {
            $row = pg_fetch_assoc($result);
            $_SESSION['user_id'] = (int)$row['id'];
        } else {
            respondJson('error', 'The user account could not be found.');
        }
    }

    $usuario_id = (int)$_SESSION['user_id'];

    
    $query_producto = "SELECT precio, descuento, cantidad FROM muebles WHERE id = $1";
    $result_producto = pg_query_params($conn, $query_producto, [$producto_id]);

    if (pg_num_rows($result_producto) === 0) {
        respondJson('error', 'Product not found.');
    }

    $row_producto = pg_fetch_assoc($result_producto);
    $precio_original = (float)$row_producto['precio'];
    $descuento_valor = isset($row_producto['descuento']) ? (float)$row_producto['descuento'] : 0.00;
    $stock_disponible = (int)$row_producto['cantidad'];
    
    
    if ($stock_disponible <= 0) {
        respondJson('error', 'The product is currently out of stock.');
    }
    
    if ($cantidad > $stock_disponible) {
        respondJson('error', "The quantity requested ($cantidad) exceeds the available stock. ($stock_disponible).");
    }

    
    $precio_con_descuento = $precio_original;
    if ($descuento_valor > 0) {
        $precio_con_descuento = $precio_original * (1 - ($descuento_valor / 100));
    }

    
    $query_carrito = "SELECT cantidad FROM carrito WHERE usuario_id = $1 AND producto_id = $2";
    $result_carrito = pg_query_params($conn, $query_carrito, [$usuario_id, $producto_id]);
    
    $cantidad_en_carrito = 0;
    if (pg_num_rows($result_carrito) > 0) {
        $row_carrito = pg_fetch_assoc($result_carrito);
        $cantidad_en_carrito = (int)$row_carrito['cantidad'];
    }
    
    
    if (($cantidad_en_carrito + $cantidad) > $stock_disponible) {
        $cantidad_disponible = $stock_disponible - $cantidad_en_carrito;
        respondJson('error', "You can only add $cantidad_disponible more units of this product.");
    }

    
    $update_timestamp_query = "UPDATE carrito SET fecha_agregado = CURRENT_TIMESTAMP WHERE usuario_id = $1";
    pg_query_params($conn, $update_timestamp_query, [$usuario_id]);

    if ($cantidad_en_carrito > 0) {
        
        $query_update = "UPDATE carrito SET cantidad = cantidad + $1, precio_unitario = $2, fecha_agregado = CURRENT_TIMESTAMP WHERE usuario_id = $3 AND producto_id = $4";
        $result_update = pg_query_params($conn, $query_update, [$cantidad, $precio_con_descuento, $usuario_id, $producto_id]);
        
        if ($result_update) {
            respondJson('success', 'The product quantity has been updated in the cart. Expiration time reset!');
        } else {
            error_log("Error updating cart: " . pg_last_error($conn));
            respondJson('error', 'Error updating cart.');
        }
    } else {
        
        $query_insert = "INSERT INTO carrito (usuario_id, producto_id, cantidad, precio_unitario, fecha_agregado) VALUES ($1, $2, $3, $4, CURRENT_TIMESTAMP)";
        $result_insert = pg_query_params($conn, $query_insert, [$usuario_id, $producto_id, $cantidad, $precio_con_descuento]);
        
        if ($result_insert) {
            respondJson('success', 'Product added to cart. Expiration time reset for all products!');
        } else {
            error_log("Error adding to cart: " . pg_last_error($conn));
            respondJson('error', 'Error adding the product to the cart.');
        }
    }

} catch (Exception $e) {
    error_log("Exception in addcart: " . $e->getMessage());
    respondJson('error', 'Internal Server Error');
} finally {
    pg_close($conn);
}
?>