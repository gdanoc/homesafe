<?php include('0_ENvalidacion_Admin.php'); ?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">';
echo '<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>';


$host = "localhost";
$port = "5432";
$dbname = "homesafe";
$user = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Conexión fallida: " . pg_last_error());
}


$id_registro = $_GET['id'];
$accion = $_GET['accion'];


$query = "SELECT * FROM registrosadmin WHERE id = $1";
$result = pg_query_params($conn, $query, array($id_registro));

if (pg_num_rows($result) > 0) {
    $row = pg_fetch_assoc($result);

    if ($accion === "Eliminar") {
        
        $insert_query = "INSERT INTO muebles (nombre, descripcion, precio, cantidad, fecha, imagen) 
                         VALUES ($1, $2, $3, $4, $5, $6)";
        $params = array(
            $row['nombre'],
            $row['descripcion'],
            $row['precio'],
            $row['cantidad'],
            $row['fecha'],
            $row['imagen'] 
        );
        $result_insert = pg_query_params($conn, $insert_query, $params);

        if ($result_insert) {
            $rebobinado_query = "UPDATE registrosadmin SET rebobinado = 'si' WHERE id = $1";
            $result_rebobinado = pg_query_params($conn, $rebobinado_query, array($id_registro));
            if ($result_rebobinado) {
                echo '<p><script>swal({
                    title: "Success",
                    text: "The record has been rewound.",
                    icon: "success",
                    button: "Close",
                }).then(function() {
                    window.location = "EN_historial.php";
                });</script></p>';
            }
        } else {
            echo '<p><script>swal({
                title: "Error",
                text: "Error rewinding the record.",
                icon: "error",
                button: "Close",
            }).then(function() {
                window.location = "EN_historial.php";
            });</script></p>';
        }
    } elseif ($accion === "Modificar") {
        
        $update_query = "UPDATE muebles 
                         SET nombre = $1, descripcion = $2, precio = $3, cantidad = $4, fecha = $5, imagen = $6
                         WHERE fecha = $7";
        $params = array(
            $row['nombre_anterior'],
            $row['descripcion_anterior'],
            $row['precio_anterior'],
            $row['cantidad_anterior'],
            $row['fecha_anterior'],
            $row['imagen_anterior'],
            $row['fecha']
        );
        $result_update = pg_query_params($conn, $update_query, $params);

        if ($result_update) {
            $rebobinado_query = "UPDATE registrosadmin SET rebobinado = 'si' WHERE id = $1";
            $result_rebobinado = pg_query_params($conn, $rebobinado_query, array($id_registro));
            if ($result_rebobinado) {
                echo '<p><script>swal({
                    title: "Success",
                    text: "The record has been rewound.",
                    icon: "success",
                    button: "Close",
                }).then(function() {
                    window.location = "EN_historial.php";
                });</script></p>';
            }
        } else {
            echo "Error updating the record: " . pg_last_error($conn);
            echo '<p><script>swal({
                title: "Error",
                text: "Error updating the record.",
                icon: "error",
                button: "Close",
            }).then(function() {
                window.location = "EN_historial.php";
            });</script></p>';
            exit;
        }
    } else {
        echo '<p><script>swal({
            title: "Error",
            text: "Invalid action.",
            icon: "error",
            button: "Close",
        }).then(function() {
            window.location = "EN_historial.php";
        });</script></p>';
    }
} else {
    echo '<p><script>swal({
        title: "Error",
        text: "The record could not be found.",
        icon: "error",
        button: "Close",
    }).then(function() {
        window.location = "EN_historial.php";
    });</script></p>';
}

pg_close($conn);
?>