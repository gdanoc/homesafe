<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
      integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<?php


$host = "localhost";
$dbname = "homesafe";
$user = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";

try {
    
    $conn = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_POST['submit'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        if ($email == "" || $password == "") {
            echo "<p><script>swal({
                title: 'This field is empty',
                text: 'Empty Field',
                icon: 'warning',
                button: 'Close',
            }).then(function() {
                window.location = 'EN_index.php';
            });</script></p>";
        } else {
            
            $query = $conn->prepare("SELECT * FROM accounts WHERE email = :email");
            $query->bindParam(':email', $email, PDO::PARAM_STR);
            $query->execute();

            if ($query->rowCount() == 1) {
                $row = $query->fetch(PDO::FETCH_ASSOC);
                $hashed_password = $row['password'];

                
                if (password_verify($password, $hashed_password)) {
                    
                    $cookieParams = session_get_cookie_params();
                    session_set_cookie_params([
                        'lifetime' => $cookieParams['lifetime'],
                        'path' => $cookieParams['path'],
                        'domain' => $cookieParams['domain'],
                        'secure' => isset($_SERVER['HTTPS']),
                        'httponly' => true,
                        'samesite' => 'Strict'
                    ]);

                    session_start();

                    
                    session_regenerate_id(true);

                    $_SESSION['email'] = $email;
                    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

                    
                    $sql_rol = "SELECT id_rol FROM accounts WHERE email = :email";
                    $stmt_rol = $conn->prepare($sql_rol);
                    $stmt_rol->bindParam(':email', $email, PDO::PARAM_STR);
                    $stmt_rol->execute();
                    $rol = 0; 
                    if ($stmt_rol->rowCount() > 0) {
                        $row_rol = $stmt_rol->fetch(PDO::FETCH_ASSOC);
                        $rol = $row_rol['id_rol'];
                    }

                    
                    if ($rol == 1) {
                        $redirect_url = 'EN_dashboard.php'; 
                    } elseif ($rol == 2) {
                        $redirect_url = 'EN_dashboard_vendedor.php'; 
                    } elseif ($rol == 3) {
                        $redirect_url = 'EN_dashboard_repartidor.php'; 
                    } else {
                        $redirect_url = 'EN_index.php'; 
                    }

                    echo "<p>
                    <script>
                    swal({
                        title: 'Log In',
                        text: 'Successful Log In',
                        icon: 'success',
                        button: 'Close',
                    }).then(function() {
                        window.location = '$redirect_url';
                    });
                    </script></p>";
                } else {
                    
                    echo "<p>
                    <script>
                    swal({
                        title: 'Log In',
                        text: 'Incorrect username or password',
                        icon: 'warning',
                        button: 'Close',
                    }).then(function() {
                        window.location = 'EN_index.php';
                    });
                    </script></p>";
                }
            } else {
                
                echo "<p>
                <script>
                swal({
                    title: 'Log In',
                    text: 'Incorrect username or password',
                    icon: 'warning',
                    button: 'Close',
                }).then(function() {
                    window.location = 'EN_index.php';
                });
                </script></p>";
            }
        }
    }
} catch (PDOException $e) {
    die("Failed connection: " . $e->getMessage());
}

$conn = null;
?>