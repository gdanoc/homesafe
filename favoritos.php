<?php
include('includes/CookiesSessionV.php');
?>
<?php
$host = "localhost";
$dbname = "homesafe";
$user = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";

try {
    
    $conn = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    
    if (!isset($_SESSION['email'])) {
        die("Error: No has iniciado sesión. Por favor, inicia sesión para continuar.");
    }

    $email = $_SESSION['email'];

    
    $sql = "SELECT p.* 
            FROM propiedades p
            INNER JOIN favoritos f ON p.id = f.propiedad_id
            INNER JOIN accounts a ON f.user_id = a.id
            WHERE a.email = $1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$email]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html class="wide wow-animation" lang="en">

<head>
    <title>Propiedades</title>
    <meta name="format-detection" content="telephone=no">
    <meta name="viewport"
        content="width=device-width height=device-height initial-scale=1.0 maximum-scale=1.0 user-scalable=0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta charset="utf-8">
    <link rel="icon" href="images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" type="text/css"
        href="//fonts.googleapis.com/css?family=Work+Sans:300,400,500,700,800%7CPoppins:300,400,700">
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/fonts.css">
    <link rel="stylesheet" href="css/style.css" id="main-styles-link">
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <style>
    .ie-panel {
        display: none;
        background: #212121;
        padding: 10px 0;
        box-shadow: 3px 3px 5px 0 rgba(0, 0, 0, .3);
        clear: both;
        text-align: center;
        position: relative;
        z-index: 1;
    }

    html.ie-10 .ie-panel,
    html.lt-ie-10 .ie-panel {
        display: block;
    }
    </style>

    <style>
    .propiedades-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 35px 20px;
        /* Se mantiene un buen margen sin exagerar */
    }

    .propiedades-container h3 {
        font-size: 24px;
        margin-bottom: 25px;
        /* Ajuste más natural del espacio */
        text-align: center;
    }

    .propiedades-grid {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 20px;
        /* Espaciado más equilibrado */
    }

    .propiedad-card {
        background: white;
        border-radius: 12px;
        /* Bordes suavizados */
        overflow: hidden;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        /* Sombra más ligera */
        border: 1px solid #ccc;
        /* Borde más claro y menos marcado */
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        width: 320px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .propiedad-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        /* Efecto hover aún más sutil */
    }

    .propiedad-img {
        background-color: #f8f9fa;
        padding: 12px;
        border-radius: 10px 10px 0 0;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .propiedad-img img {
        width: 100%;
        height: 190px;
        object-fit: cover;
        border-radius: 6px;
    }

    .propiedad-content {
        padding: 15px;
    }

    .propiedad-content h4 {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 8px;
        color: #333;
    }

    .propiedad-content p {
        font-size: 14px;
        color: #555;
        margin-bottom: 10px;
    }

    .propiedad-info {
        display: flex;
        justify-content: space-between;
        font-size: 14px;
        color: #444;
        margin-bottom: 5px;
    }

    .propiedad-price {
        font-size: 19px;
        font-weight: bold;
        color: #007bff;
        margin-top: 10px;
        text-align: center;
        /* Centra el precio */
        display: block;
        /* Asegura que ocupe toda la línea */
    }

    /* Estilos para el botón "Contactar" */
    .propiedad-actions {
        display: flex;
        justify-content: center;
        padding: 12px;
        border-top: 1.5px solid #666;
        /* Borde más sutil */
    }

    .btn-contactar {
        background-color: #007bff;
        color: white;
        font-size: 15px;
        padding: 10px 22px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: background 0.3s ease;
        text-decoration: none;
        text-align: center;
        display: inline-block;
        width: 80%;
        font-weight: bold;
    }

    .btn-contactar:hover {
        background-color: #0056b3;
    }

    .bg_background {
        background-color: #212529;
    }
    </style>

</head>

<body>
    <!-- Modal de Inicio de Sesión -->
    <div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg_background text-white">
                    <h4 class="modal-title" style="color: white;">Iniciar Sesión</h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <form class="rd-form" method="post" action="login.p.php">
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                                </div>
                                <input class="form-control" id="login-email" type="email" name="email" placeholder="E-mail" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-lock"></i></span>
                                </div>
                                <input class="form-control" id="login-password" type="password" name="password" placeholder="Contraseña" required>
                            </div>
                        </div>
                        <button class="btn btn-primary btn-block" type="submit" name="submit">Iniciar Sesión</button>
                    </form>
                    <hr>
                    <p class="text-center">¿No tienes una cuenta? <a href="#" data-dismiss="modal" data-toggle="modal" data-target="#RegisterModal">Regístrate aquí</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Registro -->
    <div class="modal fade" id="RegisterModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg_background text-white">
                    <h4 class="modal-title" style="color: white;">Registrarse</h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <form class="rd-form" method="post" action="register.p.php">
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-user"></i></span>
                                </div>
                                <input class="form-control" id="register-name" type="text" name="username" placeholder="Nombre" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                                </div>
                                <input class="form-control" id="register-email" type="email" name="email" placeholder="E-mail" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-lock"></i></span>
                                </div>
                                <input class="form-control" id="register-password" type="password" name="password" placeholder="Contraseña" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-lock"></i></span>
                                </div>
                                <input class="form-control" id="register-repeat-password" type="password" name="repeat-password" placeholder="Repetir contraseña" required>
                            </div>
                        </div>
                        <button class="btn btn-primary btn-block" type="submit" name="submit">Registrarse</button>
                    </form>
                    <hr>
                    <p class="text-center">¿Ya tienes una cuenta? <a href="#" data-dismiss="modal" data-toggle="modal" data-target="#loginModal">Inicia sesión aquí</a></p>
                </div>
            </div>
        </div>
    </div>    
    <!-- Modal de Perfil -->
    <div class="modal fade" id="PerfilModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg_background text-white">
                    <h4 class="modal-title" style="color: white;">Perfil</h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="profile-container">
                        <div class="profile-image">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </div>
                        <strong>Username</strong>
                        <h2 class="profile-name"><?php echo htmlspecialchars($username);?></h2>

                        <div class="profile-info">
                            <div class="profile-info-item">
                                <strong>Email</strong>
                                <p><a><?php echo $_SESSION['email'];?></a></p>
                            </div>
                            <div class="profile-info-item">
                                <strong>Listings</strong>
                                <p><a href="">View Listings</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>           
    
    <div class="page">
        <!-- Page Header-->
        <header class="section novi-background page-header">
            <!-- RD Navbar-->
            <div class="rd-navbar-wrap">
                <nav class="rd-navbar rd-navbar-corporate" data-layout="rd-navbar-fixed"
                    data-sm-layout="rd-navbar-fixed" data-md-layout="rd-navbar-fixed"
                    data-md-device-layout="rd-navbar-fixed" data-lg-layout="rd-navbar-static"
                    data-lg-device-layout="rd-navbar-static" data-lg-stick-up="true" data-lg-stick-up-offset="118px"
                    data-xl-layout="rd-navbar-static" data-xl-device-layout="rd-navbar-static" data-xl-stick-up="true"
                    data-xl-stick-up-offset="118px" data-xxl-layout="rd-navbar-static"
                    data-xxl-device-layout="rd-navbar-static" data-xxl-stick-up-offset="118px" data-xxl-stick-up="true">
                    <div class="rd-navbar-aside-outer">
                        <div class="rd-navbar-aside">
                            <!-- RD Navbar Panel-->
                            <div class="rd-navbar-panel">
                                <!-- RD Navbar Toggle-->
                                <button class="rd-navbar-toggle"
                                    data-rd-navbar-toggle="#rd-navbar-nav-wrap-1"><span></span></button>
                                <!-- RD Navbar Brand--><a class="rd-navbar-brand" href="index.php"><img
                                        src="images/logo-default-151x44.png" alt="" width="151" height="44"
                                        srcset="images/logo-default-151x44.png 2x" /></a>
                            </div>
                            <div class="rd-navbar-collapse">
                                <button class="rd-navbar-collapse-toggle rd-navbar-fixed-element-1"
                                    data-rd-navbar-toggle="#rd-navbar-collapse-content-1"><span></span></button>
                                <div class="rd-navbar-collapse-content" id="rd-navbar-collapse-content-1">
                                    <article class="unit align-items-center">
                                        <div class="unit-left"><span
                                                class="icon novi-icon icon-md icon-modern mdi mdi-phone"></span></div>
                                        <div class="unit-body">
                                            <ul class="list-0">
                                                <li><a class="link-default" href="tel:#">1-800-1234-567</a></li>
                                                <li><a class="link-default" href="tel:#">1-800-8763-765</a></li>
                                            </ul>
                                        </div>
                                    </article>
                                    <article class="unit align-items-center">
                                        <div class="unit-left"><span
                                                class="icon novi-icon icon-md icon-modern mdi mdi-map-marker"></span>
                                        </div>
                                        <div class="unit-body"><a class="link-default" href="tel:#">5 Calle Ote. 1-1,
                                                <br>Santa Tecla, El Salvador</a></div>
                                    </article>
                                    <ul class="list-0">
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="rd-navbar-main-outer custom">
                        <div class="rd-navbar-main">
                            <div class="rd-navbar-nav-wrap" id="rd-navbar-nav-wrap-1">
                                <!-- RD Navbar Nav-->
                                <ul class="rd-navbar-nav">
                                    <li class="rd-nav-item "><a class="rd-nav-link" href="index.php">Inicio</a>
                                    </li>
                                    <li class="rd-nav-item active"><a class="rd-nav-link"
                                            href="properties.php">Propiedades</a>
                                    </li>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="furniture.php">Muebles</a>
                                    </li>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="about-us.php">Sobre
                                            nosotros</a>
                                    </li>
                                    <?php if(!isset($_SESSION['email'])): ?>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-toggle="modal"
                                            data-target="#loginModal">Iniciar Sesión</a></li>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-toggle="modal"
                                            data-target="#RegisterModal">Registrarse</a></li>
                                    <?php else: ?>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="logout.p.php">Cerrar Sesión</a>
                                    </li>
                                    <?php endif; ?>
                                    <?php if(isset($_SESSION['email'])): ?>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="account.php"> Sesión actual:
                                            <?php echo htmlspecialchars($username);?></a>
                                        <?php endif; ?>
                                        <li class="rd-nav-item">
                                        <a class="rd-nav-link" href="mostrarcarrito.php">
                                            <i class="fa fa-shopping-cart" style="font-size: 1.5em;"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>
        </header>
        <?php

$host = "localhost";
$dbname = "homesafe";
$user = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";

try {
    
    $conn = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    
    $sql = "SELECT * FROM propiedades";
    $stmt = $conn->query($sql);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Conexión fallida: " . $e->getMessage());
}
?>

<?php

$host = "localhost";
$dbname = "homesafe";
$user = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";

try {
    
    $conn = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    
    $query = "SELECT id, nombre, descripcion, bathrooms, bedrooms, size, precio, imagen FROM propiedades";
    $stmt = $conn->query($query);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Conexión fallida: " . $e->getMessage());
}
?>

<section class="section">
    <div class="propiedades-container">
    <br><br>
                <h3 class="text-uppercase font-weight-bold wow-outer">
                    <br>
                    <span class="wow slideInDown">Propiedades favoritas</span>
                </h3>
        <div class="propiedades-grid">
            <?php
            if (count($result) > 0) {
                foreach ($result as $row) { ?>
                    <div class="propiedad-card">
                        <div class="propiedad-img">
                            <img src="data:image/jpeg;base64,<?php 
                            $imagen_data = pg_unescape_bytea($row['imagen']);
                            echo base64_encode($imagen_data); ?>"
                                alt="<?php echo htmlspecialchars($row['nombre']); ?>" />
                        </div>
                        <div class="propiedad-content">
                            <h4><?php echo htmlspecialchars($row['nombre']); ?></h4>
                            <p><?php echo htmlspecialchars($row['descripcion']); ?></p>
                            <div class="propiedad-info">
                                <span>🛏 Habitaciones: <?php echo htmlspecialchars($row['bedrooms']); ?></span>
                                <span>🛁 Baños: <?php echo htmlspecialchars($row['bathrooms']); ?></span>
                            </div>
                            <div class="propiedad-info">
                                <span>📏 Medida: <?php echo htmlspecialchars($row['size']); ?> m²</span>
                            </div>
                            <p class="propiedad-price">$<?php echo number_format($row['precio'], 2); ?></p>
                        </div>
                        <div class="propiedad-actions">
    <a href="single_property.php?id=<?php echo $row['id']; ?>"
        class="btn-contactar button button-md button-primary button-winona wow slideInDown">Contactar</a>
</div>
                    </div>
                <?php }
            } else {
                echo '<div class="no-propiedades"><h3>No tienes propiedades favoritas.</h3></div>';
            }
            ?>
        </div>
    </div>
</section>

<?php $conn = null; ?>
        <!-- Services-->
        <footer class="section novi-background footer-advanced bg-gray-700">
            <div class="footer-advanced-main">
                <div class="container">
                    <div class="row row-50">
                        <div class="col-lg-4">
                            <h5 class="font-weight-bold text-uppercase text-white">Sobre nosotros</h5>
                            <p class="footer-advanced-text">HomeSafe es una tienda online donde se pueden comprar
                                inmuebles y muebles. Este sitio web tiene una interfaz fácil de usar e intuitiva.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-advanced-aside">
                <div class="container">
                    <div class="footer-advanced-layout">
                        <div>
                            <ul class="list-nav">
                                <li><a href="index.php">Inicio</a></li>
                                <li><a href="about-us.php">Sobre nosotros</a></li>
                                <li><a href="properties.php">Propiedades</a></li>
                                <li><a href="furniture.php">Muebles</a></li>
                            </ul>
                        </div>
                        <div>
                            <ul class="foter-social-links list-inline list-inline-md">
                                <li><a class="icon novi-icon icon-sm link-default mdi mdi-instagram"
                                        href="https://www.instagram.com/homesafe25"></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container">
                <hr>
            </div>
            <div class="footer-advanced-aside">
                <div class="container">
                    <div class="footer-advanced-layout"><a class="brand" href="index.php"><img
                                src="images/logo-light-115x34.png" alt="" width="115" height="34"
                                srcset="images/logo-light-115x34.png 2x" /></a>
                        <!-- Rights-->
                    </div>
                </div>
            </div>
        </footer>
    </div>
    <!-- Global Mailform Output-->
    <div class="snackbars" id="form-output-global"></div>
    <!-- Javascript-->
    <script src="js/core.min.js"></script>
    <script src="js/script.js"></script>
    <script src="js/validacionesES.js"></script>
</body>

</html>

<?php

$stmt = null;
$conn = null;
?>