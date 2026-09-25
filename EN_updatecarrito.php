<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<?php
include('includes/CookiesSessionVEn.php');
?>
<?php
if (!isset($_POST['cantidad']) || !is_array($_POST['cantidad'])) {
    echo '<script>
        swal({
            title: "Error",
            text: "Quantity Data not available",
            icon: "warning",
            button: "Close",
        }).then(function() {
            window.location = "EN_mostrarcarrito.php";
        });
    </script>';
    exit();
}

$host = "localhost";
$port = "5432";
$dbname = "homesafe";
$user = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
if (!$conn) {
    die("Conexión fallida: " . pg_last_error());
}

if (!isset($_SESSION['user_id'])) {
    $email = $_SESSION['email'];
    $query_user = "SELECT id FROM accounts WHERE email = $1";
    $result_user = pg_query_params($conn, $query_user, array($email));
    if ($result_user && pg_num_rows($result_user) > 0) {
        $row = pg_fetch_assoc($result_user);
        $_SESSION['user_id'] = $row['id'];
    } else {
        echo '<script>
            swal({
                title: "Error",
                text: "Could not be found your account",
                icon: "warning",
                button: "Close",
            }).then(function() {
                window.location = "EN_mostrarcarrito.php";
            });
        </script>';
        exit();
    }
}

$usuario_id = $_SESSION['user_id'];
$errores = [];
$actualizados = 0;

foreach ($_POST['cantidad'] as $producto_id => $cantidad) {
    $producto_id = intval($producto_id);
    $cantidad = intval($cantidad);

    if ($cantidad < 1) {
        $cantidad = 1; 
    }

    
    $stock_query = "SELECT cantidad FROM muebles WHERE id = $1";
    $stock_result = pg_query_params($conn, $stock_query, array($producto_id));
    $stock_row = pg_fetch_assoc($stock_result);
    $stock_disponible = $stock_row ? (int)$stock_row['cantidad'] : 0;

    if ($cantidad > $stock_disponible) {
        $cantidad = $stock_disponible; 
    }

    $query = "UPDATE carrito SET cantidad = $1 WHERE usuario_id = $2 AND producto_id = $3";
    $result = pg_query_params($conn, $query, array($cantidad, $usuario_id, $producto_id));

    if (!$result) {
        $errores[] = "Error updating id product $producto_id";
    } else {
        $actualizados++;
    }
}

if (count($errores) > 0) {
    $mensaje = implode(", ", $errores);
    header("Location: EN_mostrarcarrito.php?update=error&msg=" . urlencode($mensaje));
    exit();
} else {
    header("Location: EN_mostrarcarrito.php?update=success&count=$actualizados");
    exit();
}

pg_close($conn);
?>