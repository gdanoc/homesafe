<?php include('0_ESvalidacion_Admin.php'); ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$emailSesion = $_SESSION['email'];

$host = "localhost";
$port = "5432";
$dbname = "homesafe";
$username = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$username password=$password");

if (!$conn) {
    die("Conexión fallida: " . pg_last_error());
}

if (isset($_POST['submit'])) {
    
    $nombre = trim($_POST['nombre']);
    $telefono = trim($_POST['telefono']);
    $email = trim($_POST['email']);
    $estado = trim($_POST['estado']);
    $pass = $_POST['password'];
    $repeat_pass = $_POST['repeat_password'];

    $errores = [];

    
    if (empty($nombre) || strlen($nombre) < 2 || strlen($nombre) > 50 || !preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/", $nombre)) {
        $errores[] = "Nombre inválido.";
    }

    if (empty($telefono) || !preg_match("/^\d{4}-\d{4}$/", $telefono)) {
        $errores[] = "Teléfono inválido. Debe tener el formato 0000-0000.";
    }

    if (empty($email) || strlen($email) < 5 || strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "Email inválido.";
    }

    if (empty($estado) || !in_array($estado, ["Disponible", "Ocupado", "Inactivo"])) {
        $errores[] = "Estado inválido.";
    }

    if (strlen($pass) < 6) {
        $errores[] = "La contraseña debe tener al menos 6 caracteres.";
    }

    if ($pass !== $repeat_pass) {
        $errores[] = "Las contraseñas no coinciden.";
    }

    
    $check_username = pg_query_params($conn, "SELECT 1 FROM accounts WHERE username = $1", array($nombre));
    if (pg_num_rows($check_username) > 0) {
        $errores[] = "Ya existe un usuario con ese nombre.";
    }

    $check_telefono = pg_query_params($conn, "SELECT 1 FROM repartidores WHERE telefono = $1", array($telefono));
    if (pg_num_rows($check_telefono) > 0) {
        $errores[] = "El número de teléfono ya está registrado.";
    }

    $check_email = pg_query_params($conn, "SELECT 1 FROM accounts WHERE email = $1", array($email));
    if (pg_num_rows($check_email) > 0) {
        $errores[] = "El correo electrónico ya está registrado.";
    }

    
    if (!empty($errores)) {
        echo '<script>swal({
            title: "Errores de validación",
            text: "' . implode('\n', $errores) . '",
            icon: "warning",
            button: "Cerrar"
        }).then(() => { window.location = "frontend_rep_create.php"; });</script>';
        exit();
    }

    
    $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

    
    pg_query($conn, "BEGIN");

    $query_accounts = "INSERT INTO accounts (username, email, password, id_rol) VALUES ($1, $2, $3, 3)";
    $result_accounts = pg_query_params($conn, $query_accounts, array($nombre, $email, $hashed_password));
    
    if (!$result_accounts) {
        pg_query($conn, "ROLLBACK");
        echo '<script>swal({
            title: "Error",
            text: "Error al crear cuenta: ' . pg_last_error($conn) . '",
            icon: "error",
            button: "Cerrar"
        }).then(() => { window.location = "frontend_rep_create.php"; });</script>';
        exit();
    }

    $query_repartidores = "INSERT INTO repartidores (nombre, telefono, email, estado) VALUES ($1, $2, $3, $4)";
    $result_repartidores = pg_query_params($conn, $query_repartidores, array($nombre, $telefono, $email, $estado));

    if (!$result_repartidores) {
        pg_query($conn, "ROLLBACK");
        echo '<script>swal({
            title: "Error",
            text: "Error al crear repartidor: ' . pg_last_error($conn) . '",
            icon: "error",
            button: "Cerrar"
        }).then(() => { window.location = "frontend_rep_create.php"; });</script>';
        exit();
    }

    pg_query($conn, "COMMIT");

    echo '<script>swal({
        title: "Éxito",
        text: "Repartidor agregado correctamente.",
        icon: "success",
        button: "Cerrar"
    }).then(() => { window.location = "frontend_rep_create.php"; });</script>';
    exit();
}

pg_close($conn);
?>
