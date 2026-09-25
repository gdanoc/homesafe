<?php
session_start();

include('includes/Cookies_sessions.php');
include("validacion_cliente.php");


$servername = "localhost";
$username_db = "postgres";
$password_db = "Info2025/*-";
$dbname = "homesafe";

try {
    $pdo = new PDO("pgsql:host=$servername;dbname=$dbname", $username_db, $password_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $error_message = "Error de conexión: " . $e->getMessage();
}

$email_sesion = $_SESSION['email'] ?? null;

if (!$email_sesion) {
    
    header("Location: index.php");
    exit();
}


$stmt = $pdo->prepare("SELECT username, email FROM accounts WHERE email = ?");
$stmt->execute([$email_sesion]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    
    session_destroy();
    header("Location: index.php");
    exit();
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    
    if (empty($username)) {
        $errors[] = "El nombre de usuario es obligatorio.";
    } else {
        if (strlen($username) < 2 || strlen($username) > 50) {
            $errors[] = "El nombre de usuario debe tener entre 2 y 50 caracteres.";
        } elseif (!preg_match('/^[\p{L}\s]+$/u', $username)) {
            $errors[] = "El nombre de usuario solo puede contener letras y espacios.";
        } elseif (preg_match('/^\s|\s$/', $username)) {
            $errors[] = "El nombre de usuario no puede empezar o terminar con espacios.";
        } elseif (preg_match('/\s{2,}/', $username)) {
            $errors[] = "El nombre de usuario no puede contener espacios consecutivos.";
        }
    }

    
    if (empty($current_password)) {
        $errors[] = "Debe ingresar su contraseña actual para actualizar su perfil.";
    } else {
        
        $stmt = $pdo->prepare("SELECT password FROM accounts WHERE email = ?");
        $stmt->execute([$email_sesion]);
        $user_password = $stmt->fetchColumn();

        if (!password_verify($current_password, $user_password)) {
            $errors[] = "La contraseña actual es incorrecta.";
        }
    }

    
    if (empty($errors)) {
        if ($new_password || $confirm_password) {
            if (empty($new_password)) {
                $errors[] = "Por favor ingrese una nueva contraseña.";
            } elseif ($new_password !== $confirm_password) {
                $errors[] = "La nueva contraseña y la confirmación no coinciden.";
            } else {
                if (strlen($new_password) < 8 || strlen($new_password) > 16) {
                    $errors[] = "La contraseña debe tener entre 8 y 16 caracteres.";
                } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>])/', $new_password)) {
                    $errors[] = "La contraseña debe contener al menos una letra minúscula, una mayúscula, un número y un carácter especial.";
                } elseif (preg_match('/\s/', $new_password)) {
                    $errors[] = "La contraseña no puede contener espacios.";
                }
            }
        }
    }

    if (empty($errors)) {
        if ($new_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE accounts SET username = ?, password = ? WHERE email = ?");
            $stmt->execute([$username, $hashed_password, $email_sesion]);
        } else {
            $stmt = $pdo->prepare("UPDATE accounts SET username = ? WHERE email = ?");
            $stmt->execute([$username, $email_sesion]);
        }
        $success = "Perfil actualizado correctamente.";

        
        $_SESSION['username'] = $username;

        
        $stmt = $pdo->prepare("SELECT username, email FROM accounts WHERE email = ?");
        $stmt->execute([$email_sesion]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html class="wide wow-animation" lang="es">

<head>
    <title>Actualizar Perfil</title>
    <meta name="format-detection" content="telephone=no">
    <meta name="viewport" content="width=device-width height=device-height initial-scale=1.0 maximum-scale=1.0 user-scalable=0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta charset="utf-8">
    <link rel="icon" href="images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Work+Sans:300,400,500,700,800%7CPoppins:300,400,700">
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/fonts.css">
    <link rel="stylesheet" href="css/style.css" id="main-styles-link">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .profile-container {
            max-width: 480px;
            margin: 40px auto;
            background: #ffffff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(33, 37, 41, 0.08);
            border: 1px solid rgba(33, 37, 41, 0.06);
            position: relative;
            overflow: hidden;
        }

        .profile-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #212529 0%, #495057 50%, #6c757d 100%);
        }

        .profile-container h2 {
            margin-bottom: 35px;
            font-weight: 700;
            color: #212529;
            text-align: center;
            font-size: 1.75rem;
            position: relative;
        }

        .profile-container h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 2px;
            background-color: #6c757d;
            border-radius: 1px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            font-weight: 600;
            color: #212529;
            margin-bottom: 8px;
            font-size: 0.95rem;
            letter-spacing: 0.3px;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
        }

        .form-control:focus {
            border-color: #495057;
            box-shadow: 0 0 0 0.2rem rgba(73, 80, 87, 0.15);
            background-color: #ffffff;
            outline: none;
        }

        .form-control::placeholder {
            color: #6c757d;
            opacity: 0.8;
        }

        .btn-primary {
            background: linear-gradient(135deg, #212529 0%, #343a40 100%);
            border: none;
            font-weight: 600;
            padding: 14px 24px;
            width: 100%;
            font-size: 1.1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 20px;
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #343a40 0%, #495057 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(33, 37, 41, 0.2);
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:active {
            transform: translateY(0);
        }
    </style>
</head>

<body>

    <!-- Navbar -->
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
                                <li class="rd-nav-item"><a class="rd-nav-link" href="properties.php">Propiedades</a>
                                </li>
                                <li class="rd-nav-item"><a class="rd-nav-link" href="furniture.php">Muebles</a>
                                </li>
                                <li class="rd-nav-item"><a class="rd-nav-link" href="about-us.php">Sobre nosotros</a>
                                </li>
                                <?php if (!isset($_SESSION['email'])): ?>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-toggle="modal" data-target="#loginModal">Iniciar Sesión</a></li>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-toggle="modal" data-target="#RegisterModal">Registrarse</a></li>
                                <?php else: ?>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="logout.p.php">Cerrar Sesión</a></li>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-toggle="modal" data-target="#PerfilModal"> Sesión actual: <?php echo htmlspecialchars($username); ?></a></li> <?php endif; ?>
                                <?php if (isset($_SESSION['email'])) : ?>
                                    <li class="rd-nav-item">
                                        <a class="rd-nav-link" href="mostrarcarrito.php" title="Ver carrito">
                                            <i class="fa fa-shopping-cart" style="font-size: 1.5em;"></i>
                                        </a>
                                    </li>
                                <?php else : ?>
                                    <li class="rd-nav-item">
                                        <a class="rd-nav-link" href="javascript:void(0);" title="Ver carrito" onclick="Swal.fire('Debes iniciar sesión para ver el carrito.', '', 'warning');">
                                            <i class="fa fa-shopping-cart" style="font-size: 1.5em;"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <!-- Contenido principal -->
    <section class="section novi-background section-md text-center">
        <div class="container">
            <h3 class="text-uppercase font-weight-bold wow-outer">
                <br>
                <span class="wow slideInDown">Actualización de datos</span>
            </h3>
            <div class="profile-container">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger"><?php echo implode("<br>", array_map("htmlspecialchars", $errors)); ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <form method="POST" action="" novalidate id="formActualizarPerfil">
                    <div class="form-group">
                        <label for="username">Nombre de Usuario (*)</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Correo Electrónico</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" readonly>
                    </div>

                    <hr>

                    <h5>Cambiar Contraseña (opcional)</h5>
                    <p>Si deseas cambiar su contraseña, por favor complete los campos a continuación.</p>
                    <br>
                    <div class="form-group">
                        <label for="current_password">Contraseña Actual (*)</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Ingrese su contraseña actual" required>
                    </div>

                    <div class="form-group">
                        <label for="new_password">Nueva Contraseña</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Ingrese nueva contraseña">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirmar Nueva Contraseña</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirme nueva contraseña">
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="section novi-background footer-advanced bg-gray-700">
        <div class="footer-advanced-main">
            <div class="container">
                <div class="row row-50">
                    <div class="col-lg-4">
                        <h5 class="font-weight-bold text-uppercase text-white">Sobre nosotros</h5>
                        <p class="footer-advanced-text">HomeSafe es una tienda online donde se pueden comprar inmuebles y muebles. Este sitio web tiene una interfaz fácil de usar e intuitiva.</p>
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
                            <li><a class="icon novi-icon icon-sm link-default mdi mdi-instagram" href="https://www.instagram.com/homesafe25"></a></li>
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
                <div class="footer-advanced-layout"><a class="brand" href="index.php"><img src="images/logo-light-115x34.png" alt="" width="115" height="34" srcset="images/logo-light-115x34.png 2x" /></a>
                    <!-- Derechos-->
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="js/core.min.js"></script>
    <script src="js/script.js"></script>
    <script src="js/validacionesES.js"></script>

    <script>
        
        <?php if (!empty($errors)): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                html: '<?php echo implode("<br>", array_map("htmlspecialchars", $errors)); ?>'
            });
        <?php endif; ?>
        <?php if ($success): ?>
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: '<?php echo htmlspecialchars($success); ?>'
            });
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?php echo htmlspecialchars($error_message); ?>'
            });
        <?php endif; ?>
    </script>

</body>

</html>