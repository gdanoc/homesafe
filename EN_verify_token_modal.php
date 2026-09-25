
<meta charset="UTF-8" />
    <title>Verification Token- HomeSafe</title>

<link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon.png">
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

    .profile-info-item {
        margin-bottom: 1.5rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 12px;
        border-left: 4px solid rgb(63, 116, 151);
    }

    .profile-info-item strong {
        display: block;
        margin-bottom: 0.5rem;
        color: rgb(63, 116, 151);
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .profile-info-item p {
        margin: 0;
        color: #555;
    }

    .profile-info-item a {
        color: rgb(63, 116, 151);
        text-decoration: none;
        font-weight: 500;
    }

    .profile-info-item a:hover {
        color: rgb(75, 98, 162);
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

    .discount-percentage-badge {
    background-color: #FFC107;
    color: #333;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: bold;
    display: inline-block;
    margin-top: 5px;
    margin-bottom: 10px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- verify_token_modal.php -->
<div class="modal fade" id="verifyTokenModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-enhanced modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg_background text-white">
                <h4 class="modal-title" style="color: white;">
                    <i class="fa fa-envelope-open-text mr-2"></i>
                    Email Verification
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <!-- Información de verificación -->
                <div class="verification-info">
                    <i class="fa fa-info-circle"></i>
                    <strong>Code successfully sent</strong>
                    <p class="mb-0 mt-1">We have sent a 6-digit verification code to your email address.</p>
                    <small class="text-muted">
                        <i class="fa fa-clock mr-1"></i>
                        Time remaining: <span id="countdown">02:00</span>
                    </small>
                </div>

                <!-- Formulario de verificación -->
                <form id="verifyTokenForm" method="post" action="EN_verify_token.p.php">
                    <div class="form-group">
                        <label for="verification-token" class="font-weight-bold">
                            <i class="fa fa-key mr-1"></i>
                            Verification Code
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="fa fa-hashtag"></i>
                                </span>
                            </div>
                            <input class="form-control text-center" 
                                   id="verification-token" 
                                   type="text" 
                                   name="token" 
                                   placeholder="Enter the 6-digit code" 
                                   required 
                                   maxlength="6" 
                                   pattern="[A-Za-z0-9]{6}"
                                   style="font-size: 1.2rem; font-weight: bold; letter-spacing: 2px;"
                                   autocomplete="off">
                        </div>
                        <small class="form-text text-muted">
                        The code contains 6 characters (letters and numbers)
                        </small>
                    </div>
                    
                    <input type="hidden" name="email" id="verify-email" value="">
                    
                    <button class="btn btn-primary btn-block" type="submit" id="verifyBtn">
                        <i class="fa fa-check-circle mr-2"></i>
                        Verify Code
                    </button>
                </form>

                <hr>

                <!-- Opciones adicionales -->
                <div class="text-center">
                    <p class="mb-2">Didn't receive the code?</p>
                    <div class="row">
                        <div class="col-6">
                            <a href="#" id="resendTokenLink" class="btn btn-outline-secondary btn-sm btn-block">
                                <i class="fa fa-redo mr-1"></i>
                                Resend
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="#" id="changeEmailLink" class="btn btn-outline-info btn-sm btn-block">
                                <i class="fa fa-edit mr-1"></i>
                                Change Email
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Estado del reenvío -->
                <div id="resendStatus" class="alert alert-info mt-3" style="display: none;">
                    <i class="fa fa-spinner fa-spin mr-2"></i>
                    Resending code...
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal para cambiar email -->
<div class="modal fade" id="changeEmailModal" tabindex="-1" role="dialog" aria-labelledby="changeEmailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg_background text-white">
        <h5 class="modal-title" id="changeEmailModalLabel">Change Email</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="changeEmailForm">
        <div class="modal-body">
          <div class="form-group">
            <label for="newEmailInput">New email</label>
            <input type="email" class="form-control" id="newEmailInput" placeholder="Enter your new email address" required>
            <div class="invalid-feedback">
            Please enter a valid email address.
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Update Email</button>
        </div>
      </form>
    </div>
  </div>
</div>
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
                <p class="text-center">Don't have an account? <a href="#" data-dismiss="modal" data-toggle="modal" data-target="#RegisterModal">Register here</a></p>
            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const tokenInput = document.getElementById('verification-token');
    const verifyBtn = document.getElementById('verifyBtn');
    const form = document.getElementById('verifyTokenForm');
    const resendLink = document.getElementById('resendTokenLink');
    const changeEmailLink = document.getElementById('changeEmailLink');
    const resendStatus = document.getElementById('resendStatus');
    const changeEmailForm = document.getElementById('changeEmailForm');
    const newEmailInput = document.getElementById('newEmailInput');

    
    $('#verifyTokenModal').on('shown.bs.modal', function () {
        resetCountdown();
    });


form.addEventListener('submit', function(e) {
    e.preventDefault();

    verifyBtn.disabled = true;

    const formData = new FormData(form);

    fetch('EN_verify_token.p.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
    if (data.success) {
        Swal.fire({
    icon: 'success',
    title: 'Success!',
    text: data.message,
    confirmButtonText: 'Accept'
}).then(() => {
    
    setTimeout(function() {
        
        let tempInput = document.createElement('input');
        tempInput.style.position = 'absolute';
        tempInput.style.opacity = 0;
        tempInput.style.pointerEvents = 'none';
        document.body.appendChild(tempInput);
        tempInput.focus();
        
        $('#verifyTokenModal').one('hidden.bs.modal', function () {
            $('#loginModal').modal('show');
            $('#login-email').val($('#verify-email').val());
            
            tempInput.remove();
        });
        $('#verifyTokenModal').modal('hide');
    }, 10);
});
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: data.message,
            confirmButtonText: 'Accept'
        });
    }
})
.finally(() => {
        verifyBtn.disabled = false;
    });
});

    
    tokenInput.addEventListener('input', function(e) {
        let value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        e.target.value = value;
        verifyBtn.disabled = value.length !== 6;
    });

    tokenInput.addEventListener('keypress', function(e) {
        const char = String.fromCharCode(e.which);
        if (!/[A-Za-z0-9]/.test(char)) {
            e.preventDefault();
        }
    });

    
    resendLink.addEventListener('click', function(e) {
        e.preventDefault();
        const email = document.getElementById('verify-email').value;

        if (!email) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'We were unable to obtain your email address',
                confirmButtonText: 'Accept'
            });
            return;
        }

        resendStatus.style.display = 'block';
        resendStatus.innerHTML = '<i class="fa fa-spinner fa-spin mr-2"></i>Resending code...';
        resendLink.style.pointerEvents = 'none';
        resendLink.classList.add('disabled');

        fetch('EN_resend_token.p.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'email=' + encodeURIComponent(email)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resendStatus.className = 'alert alert-success mt-3';
                resendStatus.innerHTML = '<i class="fa fa-check-circle mr-2"></i>' + data.message;
                resetCountdown();
            } else {
                resendStatus.className = 'alert alert-danger mt-3';
                resendStatus.innerHTML = '<i class="fa fa-exclamation-triangle mr-2"></i>' + data.message;
            }
        })
        .catch(() => {
            resendStatus.className = 'alert alert-danger mt-3';
            resendStatus.innerHTML = '<i class="fa fa-exclamation-triangle mr-2"></i>Error resending code';
        })
        .finally(() => {
            setTimeout(() => {
                resendLink.style.pointerEvents = 'auto';
                resendLink.classList.remove('disabled');
            }, 30000);
        });
    });

    
    changeEmailLink.addEventListener('click', function(e) {
        e.preventDefault();
        $('#changeEmailModal').modal('show');
    });

    
    changeEmailForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const newEmail = newEmailInput.value.trim();

        if (!validateEmail(newEmail)) {
            newEmailInput.classList.add('is-invalid');
            return;
        } else {
            newEmailInput.classList.remove('is-invalid');
        }

        const oldEmail = document.getElementById('verify-email').value;

        fetch('EN_update_verification_email.p.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'old_email=' + encodeURIComponent(oldEmail) + '&new_email=' + encodeURIComponent(newEmail)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Email updated and code resent',
                    confirmButtonText: 'Accept'
                });
                document.getElementById('verify-email').value = newEmail;
                resetCountdown();
                $('#changeEmailModal').modal('hide');
                newEmailInput.value = '';
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message,
                    confirmButtonText: 'Accept'
                });
            }
        })
        .catch(() => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error updating email',
                confirmButtonText: 'Accept'
            });
        });
    });

    
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    
    function resetCountdown() {
        const countdownElement = document.getElementById('countdown');
        if (countdownElement) {
            let timeLeft = 180; 
            countdownElement.style.color = '';

            updateDisplay(timeLeft);

            const timer = setInterval(function() {
                timeLeft--;
                if (timeLeft < 0) {
                    clearInterval(timer);
                    countdownElement.textContent = 'Expirado';
                    countdownElement.style.color = 'red';
                    return;
                }
                updateDisplay(timeLeft);
            }, 1000);

            function updateDisplay(seconds) {
                const minutes = Math.floor(seconds / 60);
                const secs = seconds % 60;
                countdownElement.textContent = minutes + ':' + (secs < 10 ? '0' : '') + secs;
            }
        }
    }

    
    tokenInput.focus();
});
</script>