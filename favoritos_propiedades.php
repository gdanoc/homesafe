<?php
include('includes/CookiesSessionV.php');
$username = isset($username) ? $username : $_SESSION['email'];
?>
<!DOCTYPE html>
<html class="wide wow-animation" lang="en">

<head>
    <title>Propiedades Favoritas</title>
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
    <style>
        /* Puedes copiar aquí los estilos de properties.php para tarjetas, grid, etc. */
        .propiedades-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            justify-items: center;
            margin-bottom: 60px;
        }

        .propiedad-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #f0f0f0;
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            width: 100%;
            max-width: 380px;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .propiedad-img {
            position: relative;
            overflow: hidden;
            height: 220px;
        }

        .propiedad-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .favorite-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255, 255, 255, 0.95);
            border: 2px solid rgba(231, 76, 60, 0.3);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 3;
            padding: 0;
            outline: none;
        }

        .propiedad-content {
            padding: 25px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .propiedad-content h4 {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: #333;
            line-height: 1.3;
        }

        .propiedad-content p {
            font-size: 0.95rem;
            color: #666;
            margin-bottom: 20px;
            line-height: 1.5;
            flex-grow: 1;
        }

        .propiedad-info {
            margin-bottom: 10px;
            font-size: 0.95rem;
        }

        .propiedad-price {
            font-size: 1.2rem;
            font-weight: bold;
            color: #007bff;
        }

        .propiedad-actions {
            padding: 20px 25px;
            border-top: 1px solid #f0f0f0;
            background: #fafafa;
        }

        .btn-contactar {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            font-size: 1rem;
            font-weight: 600;
            padding: 14px 30px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            display: block;
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        .btn-vendido {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
            font-size: 1rem;
            font-weight: 600;
            padding: 14px 30px;
            border: none;
            border-radius: 12px;
            cursor: not-allowed;
            text-decoration: none;
            text-align: center;
            display: block;
            width: 100%;
            opacity: 0.8;
        }

        @media (max-width: 768px) {
            .propiedades-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .propiedad-card {
                max-width: 100%;
            }
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
                            <div class="rd-navbar-panel">
                                <button class="rd-navbar-toggle"
                                    data-rd-navbar-toggle="#rd-navbar-nav-wrap-1"><span></span></button>
                                <a class="rd-navbar-brand" href="index.php"><img
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
                                    <ul class="list-0"></ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="rd-navbar-main-outer custom">
                        <div class="rd-navbar-main">
                            <div class="rd-navbar-nav-wrap" id="rd-navbar-nav-wrap-1">
                                <ul class="rd-navbar-nav">
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="index.php">Inicio</a></li>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="properties.php">Propiedades</a></li>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="furniture.php">Muebles</a></li>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="about-us.php">Sobre nosotros</a></li>
                                    <?php if (!isset($_SESSION['email'])): ?>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-toggle="modal"
                                                data-target="#loginModal">Iniciar Sesión</a></li>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-toggle="modal"
                                                data-target="#RegisterModal">Registrarse</a></li>
                                    <?php else: ?>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="logout.p.php">Cerrar Sesión</a></li>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-toggle="modal" data-target="#PerfilModal"> Sesión actual: <?php echo htmlspecialchars($username); ?></a></li>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="mostrarcarrito.php"><i class="fa fa-shopping-cart" style="font-size: 1.5em;"></i></a></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>
        </header>
        <section class="section novi-background section-md text-center">
            <div class="container">
                <h3 class="text-uppercase font-weight-bold wow-outer">
                    <br>
                    <span class="wow slideInDown">Propiedades Favoritas</span>
                </h3>
                <br>
                <div class="propiedades-grid">
                    <?php
                    
                    $host = "localhost";
                    $port = "5432";
                    $dbuser = "postgres";
                    $dbpass = "Info2025/*-";
                    $dbname = "homesafe";
                    $conn = pg_connect("host=$host port=$port dbname=$dbname user=$dbuser password=$dbpass");
                    if (!$conn) {
                        echo '<div class="col-12 text-center py-5"><h4>Error de conexión</h4></div>';
                    } else {
                        $email = $_SESSION['email'];
                        
                        $query = "
                        SELECT 
                            p.id, p.nombre, p.descripcion, p.size, p.precio, p.estado,
                            COALESCE(bedroom_counts.count, 0) AS bedrooms,
                            COALESCE(bathroom_counts.count, 0) AS bathrooms
                        FROM propiedades p
                        INNER JOIN favoritos_propiedades f ON p.id = f.propiedad_id
                        LEFT JOIN (
                            SELECT propiedad_id, COUNT(*) AS count
                            FROM cuartos
                            WHERE LOWER(TRIM(tipo_cuarto)) = 'dormitorio'
                            GROUP BY propiedad_id
                        ) AS bedroom_counts ON p.id = bedroom_counts.propiedad_id
                        LEFT JOIN (
                            SELECT propiedad_id, COUNT(*) AS count
                            FROM cuartos
                            WHERE LOWER(TRIM(tipo_cuarto)) IN ('baño', 'bano')
                            GROUP BY propiedad_id
                        ) AS bathroom_counts ON p.id = bathroom_counts.propiedad_id
                        WHERE f.mail_user = $1
                        ORDER BY p.id
                    ";
                        $result = pg_query_params($conn, $query, array($email));
                        $propiedades = [];
                        while ($row = pg_fetch_assoc($result)) {
                            $prop_id = $row['id'];
                            $img_query = "SELECT imagen FROM carrusel_propiedad WHERE propiedad_id = $1 ORDER BY orden";
                            $img_result = pg_query_params($conn, $img_query, array($prop_id));
                            $imagenes = [];
                            while ($img_row = pg_fetch_assoc($img_result)) {
                                $imagenes[] = base64_encode(pg_unescape_bytea($img_row['imagen']));
                            }
                            $row['imagenes'] = $imagenes;
                            $propiedades[] = $row;
                        }
                        if (count($propiedades) > 0) {
                            foreach ($propiedades as $row) {
                    ?>
                                <div class="propiedad-card">
                                    <button type="button" onclick="event.stopPropagation(); toggleFavoriteProperty(<?php echo $row['id']; ?>, this);" class="favorite-btn">
                                        <i class="fa fa-heart" style="color: rgb(231,76,60); font-size: 18px;"></i>
                                    </button>
                                    <div class="propiedad-img">
                                        <div id="carousel-<?php echo $row['id']; ?>" class="carousel slide" data-ride="carousel">
                                            <div class="carousel-inner">
                                                <?php foreach ($row['imagenes'] as $idx => $img): ?>
                                                    <div class="carousel-item <?php echo $idx === 0 ? 'active' : ''; ?>">
                                                        <a href="single_property.php?id=<?php echo $row['id']; ?>">
                                                            <img src="data:image/jpeg;base64,<?php echo $img; ?>" class="d-block w-100" alt="Imagen propiedad">
                                                        </a>
                                                    </div>
                                                <?php endforeach; ?>
                                                <?php if (empty($row['imagenes'])): ?>
                                                    <div class="carousel-item active">
                                                        <a href="single_property.php?id=<?php echo $row['id']; ?>">
                                                            <img src="images/placeholder.png" class="d-block w-100" alt="Sin imagen">
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (count($row['imagenes']) > 1): ?>
                                                <a class="carousel-control-prev" href="#carousel-<?php echo $row['id']; ?>" role="button" data-slide="prev">
                                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                    <span class="sr-only">Anterior</span>
                                                </a>
                                                <a class="carousel-control-next" href="#carousel-<?php echo $row['id']; ?>" role="button" data-slide="next">
                                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                    <span class="sr-only">Siguiente</span>
                                                </a>
                                            <?php endif; ?>
                                        </div>
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
                                    <form method="post" action="single_property.php">
                                        <div class="propiedad-actions">
                                            <?php if ($row['estado'] == "Vendido"): ?>
                                                <a class="btn-vendido button button-md button-primary button-winona wow slideInDown">Vendido</a>
                                            <?php else: ?>
                                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                <button type="submit" class="btn-contactar button button-md button-primary button-winona wow slideInDown">Contactar</button>
                                            <?php endif ?>
                                        </div>
                                    </form>
                                </div>
                    <?php
                            }
                        } else {
                            echo '<div class="col-12 text-center py-5"><h4>No tienes propiedades en favoritos</h4></div>';
                        }
                        pg_close($conn);
                    }
                    ?>
                </div>
            </div>
        </section>
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
                    </div>
                </div>
            </div>
        </footer>
    </div>
    <div class="snackbars" id="form-output-global"></div>
    <script src="js/core.min.js"></script>
    <script src="js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleFavoriteProperty(propiedadId, buttonElement) {
            const heartIcon = buttonElement.querySelector('i');
            const isFavorited = heartIcon.classList.contains('fa-heart');
            fetch('toggle_favorite_property.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        propiedad_id: propiedadId,
                        action: isFavorited ? 'remove' : 'add'
                    })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        if (isFavorited) {
                            
                            buttonElement.closest('.propiedad-card').remove();
                            
                            if (document.querySelectorAll('.propiedad-card').length === 0) {
                                const grid = document.querySelector('.propiedades-grid');
                                grid.innerHTML = '<div class="col-12 text-center py-5"><h4>No tienes propiedades en favoritos</h4></div>';
                            }
                        } else {
                            heartIcon.className = 'fa fa-heart';
                            heartIcon.style.color = 'rgb(231,76,60)';
                        }
                    } else {
                        alert(result.message || 'Error al procesar favorito');
                    }
                })
                .catch(error => {
                    alert('Error de conexión');
                });
        }
    </script>
    <script src="js/validacionesES.js"></script>
</body>

</html>