<?php include 'EN_verify_token_modal.php'; 
include('translator.php');
?>
<?php
$modal_plan_id = 0;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$email = $_SESSION['email'] ?? null;

if ($email) {
    try {
        $host = "localhost";
        $dbname = "homesafe";
        $user = "postgres";
        $password = "TU_PASSWORD_DE_BASE_DE_DATOS";
        $dsn = "pgsql:host=$host;dbname=$dbname";
        $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

        $stmt = $pdo->prepare("SELECT plan_id FROM suscripcion WHERE mail_user = :email ORDER BY fecha_vencimiento DESC LIMIT 1");
        $stmt->execute(['email' => $email]);
        $planRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($planRow) {
            $modal_plan_id = intval($planRow['plan_id']);
        }
    } catch (PDOException $e) {
        error_log("Error obtaining plan_id in modal: " . $e->getMessage());
    }
}
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    /* ===== ESTILOS BASE ===== */
    .row {
        margin-top: 0;
    }

    .bg_background {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    /* ===== MODALES MÁS AMPLIOS Y MINIMALISTAS ===== */
    .modal-dialog-enhanced {
        max-width: 450px;
        margin: 2rem auto;
    }

    .modal-content {
        border: none;
        border-radius: 8px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        overflow: hidden;
        margin: auto;
    }

    .modal-header {
        border: none;
        padding: 2rem 2rem 1rem;
        background: #1F262D;
        position: relative;
    }

    .modal-title {
        font-weight: 600;
        font-size: 1.5rem;
        color: white !important;
        margin: 0;
    }

    .close {
        position: absolute;
        right: 1.5rem;
        top: 1.5rem;
        color: white;
        opacity: 0.8;
        font-size: 1.5rem;
        transition: opacity 0.3s ease;
    }

    .close:hover {
        opacity: 1;
        color: white;
    }

    .modal-body {
        background: #fafafa;
    }

    /* ===== FORMULARIOS MEJORADOS ===== */
    .form-group {
        margin-bottom: 1.5rem;
    }

    .input-group {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .input-group:focus-within {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }

    .input-group-text {
        background: white;
        border: none;
        color: #1F262D;
        font-size: 1.1rem;
    }

    .form-control {
        border: none;
        padding: 0.875rem 1rem;
        font-size: 1rem;
        background: white;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        box-shadow: none;
        border: none;
        background: white;
    }

    /* ===== BOTONES MEJORADOS ===== */
    .btn-primary {
        background-color: #1F262D;
        border: none;
        padding: 0.875rem 2rem;
        font-weight: 500;
        font-size: 1rem;
        border-radius: 12px;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(77, 87, 136, 0.4);
        background: rgb(59, 72, 85);
    }

    /* ===== ENLACES MEJORADOS ===== */
    .modal-body a {
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .modal-body a:hover {
        text-decoration: none;
    }

    /* ===== PERFIL REDISEÑADO ===== */
    .profile-container {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        width: 100%;
        text-align: center;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        border: 1px solid #f0f0f0;
    }

    .profile-image {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        margin: auto;
        margin-bottom: 10px;
        background: linear-gradient(135deg, rgb(46, 56, 67), #6e869e);
        display: flex;
        justify-content: center;
        align-items: center;
        overflow: hidden;
        color: white;
    }

    .profile-image svg {
        width: 50%;
        height: 50%;
    }

    .profile-name {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
        color: #333;
        font-weight: 600;
    }

    .profile-info {
        margin-top: 2rem;
    }

    .profile-info-item .button-container {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        margin-top: 8px;
    }

    /* Estilo base para todos los botones dentro del modal */
    .profile-info-item .btn-modal-universal,
    .profile-info-item a:not(.btn-primary) {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 20px;
        background: rgba(255, 255, 255, 0.95);
        color: #333;
        text-decoration: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 2px solid #e0e0e0;
        backdrop-filter: blur(10px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        position: relative;
        overflow: hidden;
        white-space: nowrap;
        width: 100%;
        max-width: 280px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Efecto de brillo sutil */
    .profile-info-item .btn-modal-universal::before,
    .profile-info-item a:not(.btn-primary)::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(0, 0, 0, 0.03), transparent);
        transition: left 0.5s;
    }

    /* Efectos de hover */
    .profile-info-item .btn-modal-universal:hover::before,
    .profile-info-item a:not(.btn-primary):hover::before {
        left: 100%;
    }

    .profile-info-item .btn-modal-universal:hover,
    .profile-info-item a:not(.btn-primary):hover {
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        text-decoration: none;
        color: #333;
        background: rgba(255, 255, 255, 1);
        border-color: #d0d0d0;
    }

    /* Efecto de click */
    .profile-info-item .btn-modal-universal:active,
    .profile-info-item a:not(.btn-primary):active {
        transform: translateY(0) scale(0.98);
    }

    /* Estilos para íconos dentro de los botones */
    .profile-info-item .btn-modal-universal i,
    .profile-info-item a:not(.btn-primary) i {
        font-size: 0.9rem;
        opacity: 0.8;
        transition: all 0.3s ease;
    }

    .profile-info-item .btn-modal-universal:hover i,
    .profile-info-item a:not(.btn-primary):hover i {
        opacity: 1;
        transform: scale(1.1);
    }

    /* ===================== RESPONSIVE DESIGN ===================== */

    /* Tablets */
    @media (max-width: 768px) {

        .profile-info-item .btn-modal-universal,
        .profile-info-item a:not(.btn-primary) {
            padding: 10px 16px;
            font-size: 0.85rem;
            gap: 6px;
            max-width: 100%;
        }

        .profile-info-item .btn-modal-universal i,
        .profile-info-item a:not(.btn-primary) i {
            font-size: 0.85rem;
        }
    }

    /* Móviles */
    @media (max-width: 576px) {

        .profile-info-item .btn-modal-universal,
        .profile-info-item a:not(.btn-primary) {
            padding: 9px 14px;
            font-size: 0.8rem;
            gap: 5px;
            letter-spacing: 0.3px;
        }

        .profile-info-item .btn-modal-universal i,
        .profile-info-item a:not(.btn-primary) i {
            font-size: 0.8rem;
        }
    }

    /* Móviles pequeños */
    @media (max-width: 480px) {

        .profile-info-item .btn-modal-universal,
        .profile-info-item a:not(.btn-primary) {
            padding: 8px 12px;
            font-size: 0.75rem;
            gap: 4px;
            border-radius: 10px;
            letter-spacing: 0.2px;
        }

        .profile-info-item .btn-modal-universal i,
        .profile-info-item a:not(.btn-primary) i {
            font-size: 0.75rem;
        }
    }

    /* ===================== CENTRADO Y ALINEACIÓN ===================== */
    .profile-info-item:has(.btn-modal-universal),
    .profile-info-item:has(a:not(.btn-primary)) {
        text-align: center;
    }

    /* ===================== EFECTOS ADICIONALES ===================== */
    @keyframes pulse-white-universal {
        0% {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        50% {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
        }

        100% {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
    }

    .profile-info-item .btn-modal-universal:focus,
    .profile-info-item a:not(.btn-primary):focus {
        animation: pulse-white-universal 1.5s infinite;
        outline: none;
    }

    /* ===================== ACCESSIBILITY ===================== */
    .profile-info-item .btn-modal-universal:focus-visible,
    .profile-info-item a:not(.btn-primary):focus-visible {
        outline: 2px solid rgb(63, 116, 151);
        outline-offset: 2px;
    }

    /* ===================== TOOLTIP UNIVERSAL ===================== */
    .profile-info-item .btn-modal-universal[title]:hover::after,
    .profile-info-item a:not(.btn-primary)[title]:hover::after {
        content: attr(title);
        position: absolute;
        bottom: -35px;
        left: 50%;
        transform: translateX(-50%);
        padding: 5px 10px;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        border-radius: 4px;
        font-size: 0.75rem;
        white-space: nowrap;
        z-index: 1000;
        opacity: 0;
        animation: fadeInTooltipUniversal 0.3s ease-in-out forwards;
    }

    @keyframes fadeInTooltipUniversal {
        from {
            opacity: 0;
            transform: translateX(-50%) translateY(-5px);
        }

        to {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
    }

    /* ===== SELECTOR DE IDIOMA MEJORADO ===== */
    .language-selector {
        display: inline-block;
        font-family: 'Poppins', sans-serif;
        margin: 1rem 0;
    }

    .language-label {
        margin-right: 10px;
        font-weight: 600;
        color: #333;
    }

    .language-select {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        border: 2px solid #e0e0e0;
        background-color: #fff;
        font-size: 0.9rem;
        font-weight: 500;
        color: #333;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .language-select:hover,
    .language-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        outline: none;
    }

    /* ===== ANIMACIONES SUTILES ===== */
    .modal.fade .modal-dialog {
        transform: translateY(-50px);
        transition: transform 0.3s ease-out;
    }

    .modal.show .modal-dialog {
        transform: translateY(0);
    }
</style>
<!-- Modal de Inicio de Sesión -->
<div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg_background text-white">
                <h4 class="modal-title" style="color: white;">Log In</h4>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"></span>
                </button>
            </div>
            <div class="modal-body p-4">
                <form class="rd-form" method="post" action="EN_login.p.php">
                    <!-- Campo oculto para el token CSRF -->
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
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
                            <input class="form-control" id="login-password" type="password" name="password" placeholder="Password" required>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-block" type="submit" name="submit">Log In</button>
                </form>
                <hr>
                <p class="text-center">Don't you have an account? <a href="#" data-dismiss="modal" data-toggle="modal" data-target="#RegisterModal">Sign Up</a></p>
            </div>
        </div>
    </div>
</div>
<!-- Modal de Registro -->
<div class="modal fade" id="RegisterModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg_background text-white">
                <h4 class="modal-title" style="color: white;">Sign Up</h4>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"></span>
                </button>
            </div>
            <div class="modal-body p-4">
                <form class="rd-form" method="post" action="EN_register.p.php" onsubmit="return validateTerms() && validatePasswordStrength() && validateUsername()">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-user"></i></span>
                            </div>
                            <input class="form-control" id="register-name" type="text" name="username" placeholder="Username" required>
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
                            <input class="form-control" id="register-password" type="password" name="password" placeholder="Password" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-lock"></i></span>
                            </div>
                            <input class="form-control" id="register-repeat-password" type="password" name="repeat-password" placeholder="Repeat Password" required>
                        </div>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="termsCheck" name="termsCheck" required>
                        <label class="form-check-label" for="termsCheck">
                            I agree <a href="EN_terminos.php" target="_blank">Terms and Conditions</a> and the <a href="EN_privacidad.php" target="_blank">Privacy Notice</a>
                        </label>
                    </div>
                    <button id="register-btn" class="btn btn-primary btn-block" type="submit">Sign Up</button>
                </form>
                <script>
                    function validateTerms() {
                        const termsAccepted = document.getElementById('termsCheck').checked;
                        if (!termsAccepted) {
                            alert('You must accept the Terms and Conditions to register.');
                            return false;
                        }
                        
                        document.getElementById('register-btn').disabled = true;
                        document.getElementById('register-btn').innerText = 'Registering...';
                        return true;
                    }
                </script>
                <hr>
                <p class="text-center">Do you have an account? <a href="#" data-dismiss="modal" data-toggle="modal" data-target="#loginModal">Log In here</a></p>
            </div>
        </div>
    </div>
</div>
<!-- Modal de Detalles del Mueble -->
<div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">Furniture Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"></span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="furnitureId" value="">
                <div class="row">
                    <!-- Imagen del mueble -->
                    <div class="col-md-6">
                        <img id="furnitureImage" src="/placeholder.svg" alt="Imagen del mueble" style="max-width: 100%; height: auto;">
                    </div>
                    <!-- Información del mueble -->
                    <div class="col-md-6">
                        <h4 id="furnitureName"></h4>
                        <p><strong>Price:</strong> <span id="furniturePrice"></span></p>
                        <p><strong>Available Stock:</strong> <span id="furnitureQuantity"></span></p>
                        <p><strong>Description:</strong> <span id="furnitureDescription"></span></p>
                        <!-- Botón Agregar al Carrito -->
                        <div class="form-group" style="margin-top: 20px;">
                            <label for="quantity-modal" style="font-size: 14px;">Quantity:</label>
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <!-- Botón de Decremento -->
                                <button type="button" onclick="decreaseQuantity('quantity-modal')" class="quantity-btn" id="btn-decrement-modal" disabled>-</button>
                                <!-- Input de Cantidad -->
                                <input type="number" id="quantity-modal" name="quantity" value="1" min="1" max="100" readonly class="quantity-input" style="width: 60px;">
                                <!-- Botón de Incremento -->
                                <button type="button" onclick="increaseQuantity('quantity-modal')" class="quantity-btn" id="btn-increment-modal" disabled>+</button>
                            </div>
                        </div>
                        <a href="javascript:void(0);" onclick="addToCartFromModal()" class="btn btn-primary btn-block" style="margin-top: 10px;">
                            <i class="fa fa-shopping-cart"></i> Add to Shopping Cart
                        </a>
                    </div>
                </div>
                <!-- Sección de Reseñas (NUEVO) -->
                <div class="row mt-4">
                    <div class="col-12">
                        <h5>Customer reviews:</h5>
                        <div id="furnitureReviewsContainer" class="reviews-container">
                            <!-- Las reseñas se cargarán aquí mediante JavaScript -->
                        </div>
                        <p id="noReviewsMessage" class="text-muted text-center" style="display: none;">There are no reviews for this furniture yet..</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Profile Modal -->
<div class="modal fade" id="PerfilModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg_background text-white">
                <h4 class="modal-title" style="color: white;">Profile</h4>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"></span>
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
                    <h2 class="profile-name"><?php echo htmlspecialchars($username); ?></h2>

                    <div class="profile-info">
                        <div class="profile-info-item">
                            <strong>Email</strong>
                            <p><a><?php echo $_SESSION['email']; ?></a></p>
                        </div>
                        <div class="profile-info-item">
                            <strong>Orders</strong>
                            <p><a href="EN_pedidosusuario.php" title="View my orders"><i class="fas fa-shopping-bag"></i> View Orders</a></p>
                        </div>

                        <div class="profile-info-item">
                            <strong>My Properties</strong>
                            <p><a href="EN_property_user.php" title="View My Properties"><i class="fas fa-shopping-bag"></i> My Properties</a></p>
                        </div>

                        <div class="profile-info-item">
                            <strong>Chats</strong>
                            <p><a href="EN_chats_usuario.php" title="View my conversations"><i class="fas fa-comments"></i> View Chats</a></p>
                        </div>

                        <div class="profile-info-item">
                            <strong>Favorite Furniture</strong>
                            <p><a href="EN_favoritos_furniture.php" title="View my favorites"><i class="fas fa-heart"></i> View Favorite Furniture</a></p>
                        </div>
                        <div class="profile-info-item">
                            <strong>Favorite Properties</strong>
                            <p><a href="EN_favoritos_propiedades.php" title="Ver mis favoritos"><i class="fas fa-heart"></i> View Favorite Properties</a></p>
                        </div>

                        <div class="profile-info-item">
                            <strong>Subscription</strong>
                            <p><a href="EN_suscripcion.php" title="Manage subscription"><i class="fas fa-star"></i> View Subscriptions</a></p>
                        </div>

                        <?php if (isset($modal_plan_id) && $modal_plan_id > 0): ?>
                            <div class="profile-info-item">
                                <strong>Change View</strong>
                                <p><a href="EN_dashboard_vendedor.php" title="Switch to Seller view"><i class="fas fa-store"></i> Seller View</a></p>
                            </div>
                        <?php endif; ?>

                        <a href="EN_actualizar_perfil.php" class="btn btn-primary btn-block" style="margin-top: 20px; color: white;">
                            Profile Settings
                        </a>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function validatePasswordStrength() {
        const password = document.getElementById('register-password').value;
        const errors = [];

        if (password.length < 6) {
            errors.push("The password must be at least 6 characters long.");
        }
        if (!/[A-Z]/.test(password)) {
            errors.push("It must contain at least one capital letter.");
        }
        if (!/[a-z]/.test(password)) {
            errors.push("Must contain at least one lowercase letter.");
        }
        if (!/[0-9]/.test(password)) {
            errors.push("It must contain at least one number.");
        }
        if (!/[\W_]/.test(password)) {
            errors.push("Must contain at least one special character.");
        }

        if (errors.length > 0) {
            alert(errors.join("\n"));
            return false;
        }
        return true;
    }

    function validateUsername() {
        const username = document.getElementById('register-name').value.trim();
        const usernameRegex = /^[a-zA-Z0-9._-]{3,20}$/;
        if (!usernameRegex.test(username)) {
            alert('The username can only contain letters, numbers, periods, underscores, and must be between 3 and 20 characters long.');
            return false;
        }
        return true;
    }
</script>