<?php include('0_ESvalidacion_Admin.php'); //ESPAÑOL ?>
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


$query = "SELECT * FROM registroslider WHERE id = $1";
$result = pg_query_params($conn, $query, array($id_registro));

if (pg_num_rows($result) > 0) {
    $row = pg_fetch_assoc($result);

    if ($accion === "Eliminar") {
        
        $insert_query = "INSERT INTO slider (nombre, imagen) 
                         VALUES ($1, $2)";
        $params = array(
            $row['nombre'],
            $row['imagen'] 
        );
        $result_insert = pg_query_params($conn, $insert_query, $params);

        if ($result_insert) {
            $rebobinado_query = "UPDATE registroslider SET rebobinado = 'si' WHERE id = $1";
            $result_rebobinado = pg_query_params($conn, $rebobinado_query, array($id_registro));
            if ($result_rebobinado) {
                echo '<p><script>swal({
                    title: "Éxito",
                    text: "El registro ha sido restaurado correctamente.",
                    icon: "success",
                    button: "Cerrar",
                }).then(function() {
                    window.location = "historialslider.php";
                });</script></p>';
            }
        } else {
            echo '<p><script>swal({
                title: "Error",
                text: "Error al restaurar el registro.",
                icon: "error",
                button: "Cerrar",
            }).then(function() {
                window.location = "historialslider.php";
            });</script></p>';
        }
    } elseif ($accion === "Modificar") {
        
        $update_query = "UPDATE slider 
        SET nombre = $1, imagen = $2
        WHERE nombre = $3";
        $params = array(
        $row['nombre_anterior'],
        $row['imagen_anterior'],
        $row['nombre']
        );
        $result_update = pg_query_params($conn, $update_query, $params);

        if ($result_update) {
            $rebobinado_query = "UPDATE registroslider SET rebobinado = 'si' WHERE id = $1";
            $result_rebobinado = pg_query_params($conn, $rebobinado_query, array($id_registro));
            if ($result_rebobinado) {
                echo '<p><script>swal({
                    title: "Éxito",
                    text: "El registro ha sido restaurado correctamente.",
                    icon: "success",
                    button: "Cerrar",
                }).then(function() {
                    window.location = "historialslider.php";
                });</script></p>';
            }
        } else {
            echo "Error al actualizar registro: " . pg_last_error($conn);
            echo '<p><script>swal({
                title: "Error",
                text: "Error al actualizar el registro.",
                icon: "error",
                button: "Cerrar",
            }).then(function() {
                window.location = "historialslider.php";
            });</script></p>';
            exit;
        }
    } else {
        echo '<p><script>swal({
            title: "Error",
            text: "Acción no válida.",
            icon: "error",
            button: "Cerrar",
        }).then(function() {
            window.location = "historialslider.php";
        });</script></p>';
    }
} else {
    echo '<p><script>swal({
        title: "Error",
        text: "No se encontró el registro en el historial.",
        icon: "error",
        button: "Cerrar",
    }).then(function() {
        window.location = "historialslider.php";
    });</script></p>';
}

pg_close($conn);
?>