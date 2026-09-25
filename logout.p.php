<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<?php
    session_start();
    session_destroy();
    echo "<p>
    <script>
    swal({

        title: 'Cerrando sesión',
        text: 'Se cerró la sesión',
        icon: 'success',
        button: 'Cerrar',

    }).then(function() {
    window.location = 'index.php';
    });</script></p>";
?>