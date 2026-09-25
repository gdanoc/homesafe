<?php include('0_ESvalidacion_Admin.php'); //ESPAÑOL 
?>
<?php include('logic_dashboard.php'); ?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon.png">
    <title>Matrix Template - The Ultimate Multipurpose admin template</title>
    <!-- Custom CSS -->
    <link href="assets/libs/flot/css/float-chart.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="dist/css/style.min.css" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 0;
        }

        .form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 1200px;
            margin: 20px auto;
            overflow-x: auto;
            /* Permite desplazamiento horizontal en pantallas pequeñas */
        }

        .login-title {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
            /* Asegura que la tabla tenga un tamaño mínimo */
            overflow-x: auto;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: rgb(245, 88, 26);
            /* Azul más llamativo */
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #ddd;
        }

        /* Imágenes */
        img {
            max-width: 100px;
            height: auto;
            border-radius: 5px;
            display: block;
            margin: auto;
        }

        /* Responsividad */
        @media (max-width: 768px) {
            .form {
                padding: 10px;
            }

            table {
                font-size: 14px;
            }

            th,
            td {
                padding: 8px;
            }
        }

        @media (max-width: 480px) {
            .form {
                width: 95%;
                padding: 5px;
            }

            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
                /* Evita que el contenido se rompa */
            }

            th,
            td {
                font-size: 12px;
                padding: 6px;
            }
        }

        .nav-link {
            display: flex;
            /* Usar flexbox para alinear la imagen y el texto */
            align-items: center;
            /* Centrar verticalmente */
        }

        .nav-link img {
            margin-right: 10px;
            /* Espacio entre la imagen y el texto */
        }
    </style>
    <!-- ============================================================== -->
    <!-- STYLE DEL SIDEBAR -->
    <!-- ============================================================== -->

    <style>
        .left-sidebar {
            width: 250px;
            /* Ancho estándar */
            transition: width 0.3s ease-in-out;
        }

        .left-sidebar.collapsed {
            width: 80px;
            /* Sidebar reducido */
        }

        .left-sidebar.collapsed .hide-menu {
            display: none;
            /* Oculta el texto cuando se colapsa */
        }

        .left-sidebar.collapsed .sidebar-link {
            text-align: center;
            padding: 15px 10px;
            /* Ajuste mínimo */
        }

        .left-sidebar.collapsed .sidebar-link i {
            font-size: 28px;
            /* Mantiene la visibilidad */
            margin-right: 0;
            /* Sin margen extra */
        }

        #sidebarnav {
            padding-top: 20px;
            /* Espaciado superior */
        }

        .sidebar-item {
            margin-bottom: 12px;
            /* Espaciado entre elementos */
            border-radius: 10px;
            /* Bordes redondeados */
            transition: background 0.3s ease-in-out, transform 0.2s ease;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 14px 20px;
            /* Espaciado sin padding extra */
            font-weight: bold;
            font-size: 18px;
            /* Tamaño del texto */
            transition: background 0.3s ease-in-out, transform 0.2s ease;
            border-radius: 4px;
            /* Bordes suaves */
        }

        .sidebar-link i {
            font-size: 24px;
            /* Tamaño del ícono */
            margin-right: 12px;
            /* Espacio con el texto */
            color: #444;
            transition: color 0.3s ease-in-out;
        }

        .sidebar-item:hover {
            background-color: rgba(0, 0, 0, 0.12);
            transform: translateX(4px);
        }

        .sidebar-item:hover .sidebar-link i {
            color: #007bff;
        }
    </style>

</head>

<body>
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <div class="preloader">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div>
    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <div id="main-wrapper">
        <!-- ============================================================== -->
        <!-- Topbar header - style you can find in pages.scss -->
        <!-- ============================================================== -->
        <header class="topbar" data-navbarbg="skin5">
            <nav class="navbar top-navbar navbar-expand-md navbar-dark">
                <div class="navbar-header" data-logobg="skin5">
                    <!-- This is for the sidebar toggle which is visible on mobile only -->
                    <a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)"><i
                            class="ti-menu ti-close"></i></a>
                    <!-- ============================================================== -->
                    <!-- Logo -->
                    <!-- ============================================================== -->
                    <a class="navbar-brand" href="dashboard.php">
                        <!-- Logo icon -->
                        <b class="logo-icon p-l-10">
                            <!--You can put here icon as well 
                            <!-- Dark Logo icon -->
                            <img src="assets/images/logo-icon.png" alt="homepage" class="light-logo" />

                        </b>
                        <!--End Logo icon -->
                        <!-- Logo text -->
                        <span class="logo-text">
                            <!-- dark Logo text -->
                            <img src="assets/images/logo-text.png" alt="homepage" class="light-logo" />

                        </span>
                        <!-- Logo icon -->
                        <!-- <b class="logo-icon"> -->
                        <!--You can put here icon as well 
                        <!-- Dark Logo icon -->
                        <!-- <img src="assets/images/logo-text.png" alt="homepage" class="light-logo" /> -->

                        <!-- </b> -->
                        <!--End Logo icon -->
                    </a>
                    <!-- ============================================================== -->
                    <!-- End Logo -->
                    <!-- ============================================================== -->
                    <!-- ============================================================== -->
                    <!-- Toggle which is visible on mobile only -->
                    <!-- ============================================================== -->
                    <a class="topbartoggler d-block d-md-none waves-effect waves-light" href="javascript:void(0)"
                        data-toggle="collapse" data-target="#navbarSupportedContent"
                        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><i
                            class="ti-more"></i></a>
                </div>
                <!-- ============================================================== -->
                <!-- End Logo -->
                <!-- ============================================================== -->
                <div class="navbar-collapse collapse" id="navbarSupportedContent" data-navbarbg="skin5">
                    <!-- ============================================================== -->
                    <!-- toggle and nav items -->
                    <!-- ============================================================== -->
                    <ul class="navbar-nav float-left mr-auto">
                        <li class="nav-item d-none d-md-block"><a
                                class="nav-link sidebartoggler waves-effect waves-light" href="javascript:void(0)"
                                data-sidebartype="mini-sidebar"><i class="mdi mdi-menu font-24"></i></a></li>
                        <!-- ============================================================== -->
                        <!-- create new -->
                        <!-- ============================================================== -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="d-none d-md-block">Historial Vendedor <i class="page-tittle"></i></span>
                                <span class="d-block d-md-none"><i class="fa fa-plus"></i></span>
                            </a>
                        </li>
                        <!-- ============================================================== -->
                        <!-- Search -->
                        <!-- ============================================================== -->
                    </ul>
                    <!-- ============================================================== -->
                    <!-- Right side toggle and nav items -->
                    <!-- ============================================================== -->
                    <ul class="navbar-nav float-right">
                        <!-- ============================================================== -->
                        <!-- Comment -->
                        <!-- ============================================================== -->
                        <!-- ============================================================== -->
                        <!-- End Comment -->
                        <!-- ============================================================== -->
                        <!-- ============================================================== -->
                        <!-- Messages -->
                        <!-- ============================================================== -->
                        <!-- ============================================================== -->
                        <!-- End Messages -->
                        <!-- ============================================================== -->

                        <!-- ============================================================== -->
                        <!-- User profile and search -->
                        <!-- ============================================================== -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark pro-pic" href="#"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <img src="assets/images/users/1.jpg" alt="user" class="rounded-circle" width="31">
                                Hola, <?php echo htmlspecialchars($username); ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right user-dd animated">
                                <!-- <a class="dropdown-item" href="javascript:void(0)"><i class="ti-user m-r-5 m-l-5"></i>-->
                                <!--     My Profile</a> -->
                                <!-- <div class="dropdown-divider"></div> -->
                                <a class="dropdown-item" href="logout.p.php"><i class="fa fa-power-off m-r-5 m-l-5"></i>
                                    Cerrar Sesión</a>
                            </div>
                        </li>
                        <!-- ============================================================== -->
                        <!-- User profile and search -->
                        <!-- ============================================================== -->
                    </ul>
                </div>
            </nav>
        </header>
        <!-- ============================================================== -->
        <!-- End Topbar header -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <aside class="left-sidebar" data-sidebarbg="skin5">
            <div class="scroll-sidebar">
                <nav class="sidebar-nav">
                    <ul id="sidebarnav" class="p-t-30">
                        <li class="sidebar-item"> <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="fas fa-clipboard"></i><span class="hide-menu">Monitoreo</span></a>
                            <ul aria-expanded="false" class="collapse  first-level">
                                <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                        href="dashboard.php" aria-expanded="false"><i class="mdi mdi-table-large"></i>
                                        <span class="hide-menu">Panel de Control</span></a>
                                </li>
                                <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                        href="ventas.php" aria-expanded="false"><i class="mdi mdi-table-large"></i><span
                                            class="hide-menu">Ventas</span></a>
                                </li>
                            </ul>
                        </li>


                        <li class="sidebar-item"> <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="mdi mdi-view-dashboard"></i><span class="hide-menu">Historiales</span></a>
                            <ul aria-expanded="false" class="collapse  first-level">
                                <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                        href="historial.php" aria-expanded="false"><i class="mdi mdi-restore"></i><span
                                            class="hide-menu">Historial Admin</span></a></li>
                                <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                        href="historialslider.php" aria-expanded="false"><i class="mdi mdi-restore"></i><span
                                            class="hide-menu">Historial Slider</span></a></li>
                            </ul>
                        </li>

                        <li class="sidebar-item"> <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="fas fa-bicycle"></i><span class="hide-menu">Repartidores</span></a>
                            <ul aria-expanded="false" class="collapse  first-level">
                                <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                        href="mostrarepartidores.php" aria-expanded="false"><i class="mdi mdi-table-large"></i><span
                                            class="hide-menu">Lista repartidores</span></a></li>
                                <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                        href="frontend_rep_create.php" aria-expanded="false"><i class="mdi mdi-pencil"></i><span
                                            class="hide-menu">Agregar Repartidores</span></a></li>
                                <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                        href="frontend_rep_update.php" aria-expanded="false"><i class="fas fa-edit"></i><span
                                            class="hide-menu">Modificar Repartidores</span></a></li>
                                <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                        href="frontend_rep_delete.php" aria-expanded="false"><i
                                            class="fas fa-times-circle"></i><span class="hide-menu">Eliminar Repartidores</span></a></li>
                            </ul>
                        </li>

                        <li class="sidebar-item"> <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="mdi mdi-folder-multiple"></i><span class="hide-menu">Muebles</span></a>
                            <ul aria-expanded="false" class="collapse  first-level">
                                <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                        href="mostrarmuebles.php" aria-expanded="false"><i class="mdi mdi-table-large"></i><span
                                            class="hide-menu">Lista de muebles</span></a></li>
                                <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                        href="frontend_create.php" aria-expanded="false"><i class="mdi mdi-pencil"></i><span
                                            class="hide-menu">Agregar Muebles</span></a></li>
                                <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                        href="frontend_update.php" aria-expanded="false"><i class="fas fa-edit"></i><span
                                            class="hide-menu">Modificar Muebles</span></a></li>
                                <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                        href="frontend_delete.php" aria-expanded="false"><i
                                            class="fas fa-times-circle"></i><span class="hide-menu">Eliminar Muebles</span></a></li>
                            </ul>
                        </li>

                        <li class="sidebar-item"> <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="mdi mdi-folder-multiple-image"></i><span class="hide-menu">Carrusel HomePage</span></a>
                            <ul aria-expanded="false" class="collapse  first-level">
                                <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                        href="mostrarslider.php" aria-expanded="false"><i class="far fa-images"></i><span
                                            class="hide-menu">Carrusel de Imagenes</span></a></li>
                                <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                        href="frontend_slider_create.php" aria-expanded="false"><i
                                            class="mdi mdi-pencil"></i><span class="hide-menu">Agregar al Carrusel</span></a></li>
                                <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                        href="frontend_slider_update.php" aria-expanded="false"><i
                                            class="fas fa-edit"></i><span class="hide-menu">Editar al Carrusel</span></a></li>
                                <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                        href="frontend_slider_delete.php" aria-expanded="false"><i
                                            class="fas fa-times-circle"></i><span class="hide-menu">Eliminar al Carrusel</span></a></li>
                            </ul>
                        </li>

                    </ul>
                </nav>
            </div>
        </aside>
        <!-- ============================================================== -->
        <!-- End Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Page wrapper  -->
        <!-- ============================================================== -->
        <div class="page-wrapper">
            <!-- ============================================================== -->
            <!-- Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-12 d-flex no-block align-items-center">
                        <h4 class="page-title">Historial Vendedor</h4>
                    </div>
                </div>
            </div>
            <div class="form">
                <h1 class="login-title">Historial Vendedor</h1>
                <table>

                    <thead>
                        <tr>
                            <th>Acción</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Baños</th>
                            <th>Cuartos</th>
                            <th>Tamaño</th>
                            <th>Precio</th>
                            <th>Fecha</th>
                            <th>Imagen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                        
                        $servername = "localhost";
                        $username = "root";
                        $password = "TU_PASSWORD_DE_BASE_DE_DATOS";
                        $dbname = "homesafe";
                        $conn = new mysqli($servername, $username, $password, $dbname);
                        
                        if ($conn->connect_error) {
                            die("Conexión fallida: " . $conn->connect_error);
                        }
                        
                        $conn->set_charset("utf8mb4");
                        
                        $query = "SELECT accion, nombre, descripcion, bathrooms, bedrooms, size, precio, fecha, imagen FROM registrosseller";
                        $result = $conn->query($query);
                        if ($result->num_rows > 0) {
                            
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['accion']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['descripcion']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['bathrooms']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['bedrooms']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['size']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['precio']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['fecha']) . "</td>";
                                echo "<td><img src='data:image/jpeg;base64," . base64_encode($row['imagen']) . "'
                                width='100' height='100'/></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4'>No hay acciones disponibles</td></tr>";
                        }
                        
                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>
            
            <?php include('includes/homechat.php'); ?>
            <!-- ============================================================== -->
            <!-- End Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- End Wrapper -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- All Jquery -->
            <!-- ============================================================== -->
            <script src="assets/libs/jquery/dist/jquery.min.js"></script>
            <!-- Bootstrap tether Core JavaScript -->
            <script src="assets/libs/popper.js/dist/umd/popper.min.js"></script>
            <script src="assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
            <script src="assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js"></script>
            <script src="assets/extra-libs/sparkline/sparkline.js"></script>
            <!--Wave Effects -->
            <script src="dist/js/waves.js"></script>
            <!--Menu sidebar -->
            <script src="dist/js/sidebarmenu.js"></script>
            <!--Custom JavaScript -->
            <script src="dist/js/custom.min.js"></script>
            <!--This page JavaScript -->
            <!-- <script src="dist/js/pages/dashboards/dashboard1.js"></script> -->
            <!-- Charts js Files -->
            <script src="assets/libs/flot/excanvas.js"></script>
            <script src="assets/libs/flot/jquery.flot.js"></script>
            <script src="assets/libs/flot/jquery.flot.pie.js"></script>
            <script src="assets/libs/flot/jquery.flot.time.js"></script>
            <script src="assets/libs/flot/jquery.flot.stack.js"></script>
            <script src="assets/libs/flot/jquery.flot.crosshair.js"></script>
            <script src="assets/libs/flot.tooltip/js/jquery.flot.tooltip.min.js"></script>
            <script src="dist/js/pages/chart/chart-page-init.js"></script>
        </div>
    </div>
</body>

</html>