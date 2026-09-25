<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<?php
include("includes/Cookies_sessions.php");
include("validacion_cliente.php");


function mostrarAlerta($titulo, $mensaje, $icono = "warning", $redireccion = null)
{
    echo '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Alerta</title>
        <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    </head>
    <body>
    <script>
        swal({
            title: "' . addslashes($titulo) . '",
            text: "' . addslashes($mensaje) . '",
            icon: "' . $icono . '",
            button: "Cerrar"
        }).then(() => {';
    if ($redireccion) {
        echo 'window.location = "' . $redireccion . '";';
    } else {
        echo 'window.history.back();';
    }
    echo '});
    </script>
    </body>
    </html>
    ';
    exit;
}

$user_email = $_SESSION['email'];


$host = "localhost";
$dbname = "homesafe";
$user = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";

try {
    $dsn = "pgsql:host=$host;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    mostrarAlerta("Error", "Error en la conexión a la base de datos: " . $e->getMessage(), "error");
}


$plan_corto = $_GET['plan'] ?? null;
if (!$plan_corto) {
    mostrarAlerta("Error", "Plan no especificado.", "error");
}


if ($plan_corto === 'trial') {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM suscripcion WHERE mail_user = :email AND tuvo_plan_trial = 'si'");
        $stmt->execute(['email' => $user_email]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            mostrarAlerta(
                "Error",
                "El plan de prueba solo puede usarse una vez. Por favor selecciona un plan de pago.",
                "warning",
                "suscripcion.php"
            );
        }
    } catch (PDOException $e) {
        mostrarAlerta("Error", "Error en la base de datos: " . $e->getMessage(), "error");
    }
}


try {
    $stmt = $pdo->prepare("SELECT id, nombre, descripcion, precio FROM planes WHERE codigo = :codigo LIMIT 1");
    $stmt->execute(['codigo' => $plan_corto]);
    $plan_seleccionado = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$plan_seleccionado) {
        mostrarAlerta("Error", "Plan no válido.", "error");
    }
} catch (PDOException $e) {
    mostrarAlerta("Error", "Error en la base de datos: " . $e->getMessage(), "error");
}

$total = $plan_seleccionado['precio'];
$plan_id = $plan_seleccionado['id'];


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pago_paypal']) && $_POST['pago_paypal'] === 'completado') {
    $paypal_transaction_id = $_POST['paypal_transaction_id'] ?? null;
    $telefono = trim($_POST['telefono'] ?? '');
    $plan_id_post = $_POST['plan_id'] ?? null;

    if (!$plan_id_post || !$paypal_transaction_id) {
        mostrarAlerta("Error", "Datos inválidos para procesar la suscripción.", "error");
    }

    if (!preg_match('/^\d{4}-\d{4}$/', $telefono)) {
        mostrarAlerta("Error", "El teléfono debe tener el formato XXXX-XXXX.", "error");
    }

    $fecha_suscrito = date('Y-m-d H:i:s');

    
    try {
        $stmt = $pdo->prepare("SELECT tuvo_plan_trial FROM suscripcion WHERE mail_user = :email");
        $stmt->execute(['email' => $user_email]);
        $tuvo_plan_trial_actual = $stmt->fetchColumn();
    } catch (PDOException $e) {
        $tuvo_plan_trial_actual = 'no';
    }

    if ($plan_id_post == 4) { 
        $fecha_vencimiento = date('Y-m-d H:i:s', strtotime("+21 days"));
        $tuvo_plan_trial = 'si';
    } else {
        $fecha_vencimiento = date('Y-m-d H:i:s', strtotime("+1 month"));
        
        $tuvo_plan_trial = ($tuvo_plan_trial_actual === 'si') ? 'si' : 'no';
    }

    try {
        $pdo->beginTransaction();

        
        $stmt = $pdo->prepare("UPDATE accounts SET id_rol = 2 WHERE email = :email");
        $stmt->execute(['email' => $user_email]);

        
        $stmt = $pdo->prepare("SELECT id FROM suscripcion WHERE mail_user = :email");
        $stmt->execute(['email' => $user_email]);
        $existe = $stmt->fetch(PDO::FETCH_ASSOC);

        $es_actualizacion = false;

        if ($existe) {
            $es_actualizacion = true;
            $stmt = $pdo->prepare("UPDATE suscripcion SET fecha_suscrito = :fecha_suscrito, fecha_vencimiento = :fecha_vencimiento, telefono = :telefono, paypal_transaction_id = :paypal_transaction_id, plan_id = :plan_id, tuvo_plan_trial = :tuvo_plan_trial WHERE mail_user = :email");
            $stmt->execute([
                'fecha_suscrito' => $fecha_suscrito,
                'fecha_vencimiento' => $fecha_vencimiento,
                'telefono' => $telefono,
                'paypal_transaction_id' => $paypal_transaction_id,
                'plan_id' => $plan_id_post,
                'tuvo_plan_trial' => $tuvo_plan_trial,
                'email' => $user_email
            ]);

            if ($stmt->rowCount() === 0) {
                $pdo->rollBack();
                mostrarAlerta("Error", "No se pudo actualizar la suscripción. Intenta nuevamente.", "error");
            }
        } else {
            $stmt = $pdo->prepare("INSERT INTO suscripcion (mail_user, telefono, fecha_suscrito, fecha_vencimiento, paypal_transaction_id, plan_id, tuvo_plan_trial) VALUES (:email, :telefono, :fecha_suscrito, :fecha_vencimiento, :paypal_transaction_id, :plan_id, :tuvo_plan_trial)");
            $stmt->execute([
                'email' => $user_email,
                'telefono' => $telefono,
                'fecha_suscrito' => $fecha_suscrito,
                'fecha_vencimiento' => $fecha_vencimiento,
                'paypal_transaction_id' => $paypal_transaction_id,
                'plan_id' => $plan_id_post,
                'tuvo_plan_trial' => $tuvo_plan_trial
            ]);

            if ($stmt->rowCount() === 0) {
                $pdo->rollBack();
                mostrarAlerta("Error", "No se pudo crear la suscripción. Intenta nuevamente.", "error");
            }
        }

        $pdo->commit();

        $mensaje = $es_actualizacion ? "Plan actualizado correctamente." : "Suscripción activada correctamente.";
        mostrarAlerta("Éxito", $mensaje, "success", "dashboard_vendedor.php");
    } catch (PDOException $e) {
        $pdo->rollBack();
        mostrarAlerta("Error", "Error en la base de datos: " . $e->getMessage(), "error", "suscripcion.php");
    }
} ?>

<!DOCTYPE html>
<html class="wide wow-animation" lang="en">

<head>
    <meta charset="UTF-8">
    <title>Planes de Suscripción - HomeSafe</title>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script
        src="https://www.paypal.com/sdk/js?client-id=AdYdJDj0e0M467JdootgS5YO3GOZrS3_H-BFEJcau4KTp1uRJb3JSRyVMD13ThlMy5ojBz-__S5hVTb9&currency=USD">
    </script>
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

        .bg_background {
            background-color: #212529;
        }

        .checkout-container {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .order-summary {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }

        .subscription-item {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }

        .subscription-item:last-child {
            border-bottom: none;
        }

        #paypal-button-container {
            margin-top: 20px;
        }

        .info-row {
            background-color: #fff;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #dee2e6;
        }

        .info-row label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
            display: block;
        }

        .info-row p {
            margin: 0;
            color: #6c757d;
        }

        .plan-highlight {
            background-color: #1F262D;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .plan-highlight h5 {
            margin: 0 0 10px 0;
            font-weight: bold;
            color: #10a37f;
        }

        .plan-highlight p {
            margin: 0;
            opacity: 0.9;
            color: white;
        }

        .price-display {
            font-size: 1em;
            font-weight: bold;
            color: #28a745;
            margin-top: 10px;
        }

        .section-md {
            padding: 60px 0;
        }

        .wow-outer {
            margin-bottom: 30px;
        }

        .wow {
            animation: slideInDown 0.8s ease-out;
        }

        @keyframes slideInDown {
            0% {
                transform: translateY(-30px);
                opacity: 0;
            }

            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .payment-instructions {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 12px 16px;
            margin-bottom: 15px;
            border-radius: 0 6px 6px 0;
        }

        .payment-instructions i {
            color: #2196f3;
            margin-right: 8px;
        }
    </style>

</head>

<body>

    <?php include 'modals.php'; ?>

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
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="properties.php">Propiedades</a>
                                    </li>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="furniture.php">Muebles</a>
                                    </li>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="about-us.php">Sobre
                                            nosotros</a>
                                    </li>
                                    <?php if (!isset($_SESSION['email'])): ?>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-toggle="modal"
                                                data-target="#loginModal">Iniciar Sesión</a></li>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-toggle="modal"
                                                data-target="#RegisterModal">Registrarse</a></li>
                                    <?php else: ?>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="logout.p.php">Cerrar Sesión</a>
                                        </li>
                                    <?php endif; ?>
                                    <?php if (isset($_SESSION['email'])): ?>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-toggle="modal"
                                                data-target="#PerfilModal"> Sesión actual:
                                                <?php echo htmlspecialchars($username); ?></a>
                                        <?php endif; ?>
                                        <li class="rd-nav-item">
                                            <a class="rd-nav-link" href="mostrarcarrito.php" title="Ver carrito">
                                                <i class="fa fa-shopping-cart" style="font-size: 1.5em;"></i>
                                            </a>
                                        </li>

                                        <!--<li class="rd-nav-item">
                                            <a class="rd-nav-link heart-link" href="favoritos_furniture.php" title="Ver favoritos">
                                                <i class="fa fa-heart"></i>
                                            </a>
                                        </li> -->
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>
        </header>

        <!-- Contenido del Checkout de Suscripción -->
        <section class="section novi-background section-md text-center">
            <div class="container">
                <h3 class="text-uppercase font-weight-bold wow-outer">
                    <br>
                    <span class="wow slideInDown">Comprar Suscripción</span>
                </h3>
                <div class="row">
                    <div class="col-lg-8">
                        <div class="checkout-container">
                            <h4 class="mb-4">Información de la Cuenta</h4>

                            <form id="formSuscripcion" method="POST" action="">
                                <div class="info-row">
                                    <label>Usuario:</label>
                                    <p><?php echo htmlspecialchars($username); ?></p>
                                </div>

                                <div class="info-row">
                                    <label>Email:</label>
                                    <p><?php echo htmlspecialchars($_SESSION['email']); ?></p>
                                </div>

                                <div class="payment-instructions">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Instrucciones de Pago:</strong> Haz clic en el botón de PayPal para proceder
                                    con el pago seguro de tu suscripción.
                                </div>

                                <div class="mb-2">
                                    <h>Teléfono *campo obligatorio*</h>
                                    <label class="form-label"></label>
                                    <input type="text" class="form-control" id="telefono" name="telefono" maxlength="9"
                                        placeholder="XXXX-XXXX" required>
                                </div>

                                <!-- Campos ocultos -->
                                <input type="hidden" name="plan_id" value="<?php echo htmlspecialchars($plan_id); ?>" />
                                <input type="hidden" name="paypal_transaction_id" id="paypal_transaction_id" />
                                <input type="hidden" name="pago_paypal" value="completado" />

                                <!-- Contenedor para botones de PayPal -->
                                <div id="paypal-button-container" class="mb-4"></div>

                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        
                                        document.getElementById('telefono').addEventListener('input', function(e) {
                                            let valor = e.target.value.replace(/\D/g, '');
                                            if (valor.length > 4) {
                                                valor = valor.substring(0, 4) + '-' + valor.substring(4, 8);
                                            }
                                            e.target.value = valor;
                                        });

                                        paypal.Buttons({
                                            createOrder: function(data, actions) {
                                                const telefono = document.getElementById('telefono')
                                                    .value.trim();
                                                if (!telefono || !/^\d{4}-\d{4}$/.test(telefono)) {
                                                    swal("Error",
                                                        "Por favor ingresa un teléfono válido con formato XXXX-XXXX.",
                                                        "warning");
                                                    return Promise.reject();
                                                }
                                                const total = <?php echo json_encode($total); ?>;
                                                return actions.order.create({
                                                    purchase_units: [{
                                                        description: 'Suscripción <?php echo addslashes($plan_seleccionado['nombre']); ?> en HomeSafe',
                                                        amount: {
                                                            currency_code: 'USD',
                                                            value: total
                                                        }
                                                    }]
                                                });
                                            },
                                            onApprove: function(data, actions) {
                                                return actions.order.capture().then(function(details) {
                                                    const transactionId = details
                                                        .purchase_units[0].payments.captures[0]
                                                        .id;
                                                    document.getElementById(
                                                            'paypal_transaction_id').value =
                                                        transactionId;
                                                    document.getElementById('formSuscripcion')
                                                        .submit();
                                                });
                                            },
                                            onError: function(err) {
                                                swal("Error",
                                                    "Hubo un problema con el pago de PayPal. Intenta nuevamente.",
                                                    "error");
                                            }
                                        }).render('#paypal-button-container');
                                    });
                                </script>
                            </form>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="order-summary">
                            <h4 class="mb-3">Resumen de la Suscripción</h4>

                            <div class="subscription-item d-flex align-items-center py-2">
                                <div class="flex-grow-1">
                                    <div class="plan-highlight">
                                        <h5><?php echo htmlspecialchars($plan_seleccionado['nombre']); ?> ©</h5>
                                        <p><?php echo htmlspecialchars($plan_seleccionado['descripcion']); ?></p>
                                        <div class="price-display">
                                            Pago mensual
                                        </div>
                                    </div>
                                    <?php if (isset($_GET['plan']) && $_GET['plan'] === 'trial'): ?>
                                        <div class="alert alert-warning mt-3" role="alert">
                                            <strong>Importante:</strong> PayPal no permite procesar suscripciones con valor
                                            de $0.00.
                                            Para fines de demostración, el valor será del <strong>$0.01</strong> para su
                                            correcto funcionamiento.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span>$<?php echo number_format($plan_seleccionado['precio'], 2); ?></span>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <span>Duración:</span>
                                <?php
                                $plan = $_GET['plan'] ?? ''; 
                                ?>

                                <span>
                                    <?php
                                    if (strtolower($plan) === 'trial') {
                                        echo '21 días';
                                    } else {
                                        echo '1 mes';
                                    }
                                    ?>
                                </span>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between mb-0">
                                <strong>Total:</strong>
                                <strong
                                    class="fs-5">$<?php echo number_format($plan_seleccionado['precio'], 2); ?></strong>
                            </div>

                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-shield-alt me-1"></i>
                                    Pago seguro con PayPal
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <!-- Services-->
    <!-- Best offer-->
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
                                    href="https://www.instagram.com/homesafe25 "></a></li>
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
    <script>
        
        let map;
        let marker;
        let selectedAddress = '';
        let selectedLat = null;
        let selectedLng = null;

        
        const EL_SALVADOR_BOUNDS = [
            [12.0, -90.5], 
            [14.5, -87.0] 
        ];

        
        function initMap() {
            
            map = L.map('map').setView([13.7942, -88.8965], 8);

            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 18
            }).addTo(map);

            
            map.setMaxBounds(EL_SALVADOR_BOUNDS);
            map.on('drag', function() {
                map.panInsideBounds(EL_SALVADOR_BOUNDS, {
                    animate: false
                });
            });

            
            map.on('click', function(e) {
                const lat = e.latlng.lat;
                const lng = e.latlng.lng;

                
                if (lat >= EL_SALVADOR_BOUNDS[0][0] && lat <= EL_SALVADOR_BOUNDS[1][0] &&
                    lng >= EL_SALVADOR_BOUNDS[0][1] && lng <= EL_SALVADOR_BOUNDS[1][1]) {

                    setMarker(lat, lng);
                    getAddressFromCoordinates(lat, lng);
                } else {
                    swal({
                        title: "Ubicación no válida",
                        text: "Por favor selecciona una ubicación dentro de El Salvador",
                        icon: "warning",
                        button: "Entendido",
                    });
                }
            });
        }
        
        function setMarker(lat, lng) {
            if (marker) {
                map.removeLayer(marker);
            }

            marker = L.marker([lat, lng]).addTo(map);
            selectedLat = lat;
            selectedLng = lng;

            
            document.getElementById('latitud').value = lat;
            document.getElementById('longitud').value = lng;
            document.getElementById('coordinates').textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
        }

        
        function getAddressFromCoordinates(lat, lng) {
            const url =
                `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=16&addressdetails=1`;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data && data.display_name) {
                        selectedAddress = data.display_name;
                        document.getElementById('selected-address').textContent = selectedAddress;
                        document.getElementById('direccion').value = selectedAddress;
                    } else {
                        selectedAddress = `Ubicación: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                        document.getElementById('selected-address').textContent = selectedAddress;
                        document.getElementById('direccion').value = selectedAddress;
                    }
                })
                .catch(error => {
                    console.error('Error al obtener la dirección:', error);
                    selectedAddress = `Ubicación: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                    document.getElementById('selected-address').textContent = selectedAddress;
                    document.getElementById('direccion').value = selectedAddress;
                });
        }
    </script>
    <?php
    if (!empty($alert_script)) {
        echo $alert_script;
    }
    ?>
</body>

</html>
<?php

pg_close($conn);
?>