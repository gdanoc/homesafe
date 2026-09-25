<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['email'])) {
    die("No estás logueado.");
}

$email = $_SESSION['email'];
$plan_id_actualizado = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plan_id = $_POST['plan_id'] ?? null;
    $transaction_id = $_POST['paypal_transaction_id'] ?? null;
    $telefono = $_POST['telefono'] ?? null;

    if (!$plan_id || !$transaction_id || !$telefono) {
        die("Faltan datos para procesar la suscripción.");
    }

    try {
        $host = "localhost";
        $dbname = "homesafe";
        $user = "postgres";
        $password = "TU_PASSWORD_DE_BASE_DE_DATOS";
        $dsn = "pgsql:host=$host;dbname=$dbname";
        $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

        
        $stmtCheck = $pdo->prepare("SELECT id FROM suscripcion WHERE mail_user = :email");
        $stmtCheck->execute(['email' => $email]);
        $existe = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($existe) {
            
            $stmtUpdate = $pdo->prepare("UPDATE suscripcion SET plan_id = :plan_id, fecha_suscrito = NOW(), fecha_vencimiento = NOW() + INTERVAL '1 month', paypal_transaction_id = :transaction_id, telefono = :telefono WHERE mail_user = :email");
            $stmtUpdate->execute([
                'plan_id' => $plan_id,
                'transaction_id' => $transaction_id,
                'telefono' => $telefono,
                'email' => $email
            ]);
        } else {
            
            $stmtInsert = $pdo->prepare("INSERT INTO suscripcion (mail_user, plan_id, fecha_suscrito, fecha_vencimiento, paypal_transaction_id, telefono, tuvo_plan_trial) VALUES (:email, :plan_id, NOW(), NOW() + INTERVAL '1 month', :transaction_id, :telefono, 'no')");
            $stmtInsert->execute([
                'email' => $email,
                'plan_id' => $plan_id,
                'transaction_id' => $transaction_id,
                'telefono' => $telefono
            ]);
        }

        
        $stmtPlan = $pdo->prepare("SELECT plan_id FROM suscripcion WHERE mail_user = :email ORDER BY fecha_vencimiento DESC LIMIT 1");
        $stmtPlan->execute(['email' => $email]);
        $planActual = $stmtPlan->fetch(PDO::FETCH_ASSOC);
        $plan_id_actualizado = $planActual ? $planActual['plan_id'] : null;

        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;

    } catch (PDOException $e) {
        die("Error en suscripción: " . $e->getMessage());
    }
} else {
    
    try {
        $host = "localhost";
        $dbname = "homesafe";
        $user = "postgres";
        $password = "TU_PASSWORD_DE_BASE_DE_DATOS";
        $dsn = "pgsql:host=$host;dbname=$dbname";
        $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

        $stmtPlan = $pdo->prepare("SELECT plan_id FROM suscripcion WHERE mail_user = :email ORDER BY fecha_vencimiento DESC LIMIT 1");
        $stmtPlan->execute(['email' => $email]);
        $planActual = $stmtPlan->fetch(PDO::FETCH_ASSOC);
        $plan_id_actualizado = $planActual ? $planActual['plan_id'] : null;

    } catch (PDOException $e) {
        die("Error al cargar plan: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Prueba Suscripción</title>
</head>
<body>
    <h2>Formulario de Suscripción</h2>
    <form action="" method="post">
        <label for="plan_id">Plan ID:</label>
        <input type="number" name="plan_id" id="plan_id" value="<?php echo htmlspecialchars($plan_id_actualizado ?? ''); ?>" required><br><br>

        <label for="paypal_transaction_id">PayPal Transaction ID:</label>
        <input type="text" name="paypal_transaction_id" id="paypal_transaction_id" required><br><br>

        <label for="telefono">Teléfono:</label>
        <input type="text" name="telefono" id="telefono" required><br><br>

        <button type="submit">Enviar</button>
    </form>
</body>
</html>