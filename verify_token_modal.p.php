<meta charset="UTF-8" />
    <title>Token de Verificación- HomeSafe</title>

<link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon.png">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- verify_token_modal.php -->
<div class="modal fade" id="verifyTokenModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-enhanced modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg_background text-white">
                <h4 class="modal-title" style="color: white;">
                    <i class="fa fa-envelope-open-text mr-2"></i>
                    Verificación de Email
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <!-- Información de verificación -->
                <div class="verification-info">
                    <i class="fa fa-info-circle"></i>
                    <strong>Código enviado exitosamente</strong>
                    <p class="mb-0 mt-1">Hemos enviado un código de verificación de 6 dígitos a tu correo electrónico.</p>
                    <small class="text-muted">
                        <i class="fa fa-clock mr-1"></i>
                        Tiempo restante: <span id="countdown">30:00</span>
                    </small>
                </div>

                <!-- Formulario de verificación -->
                <form id="verifyTokenForm" method="post" action="verify_token.p.php">
                    <div class="form-group">
                        <label for="verification-token" class="font-weight-bold">
                            <i class="fa fa-key mr-1"></i>
                            Código de Verificación
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
                                   placeholder="Ingrese el código de 6 dígitos" 
                                   required 
                                   maxlength="6" 
                                   pattern="[A-Za-z0-9]{6}"
                                   style="font-size: 1.2rem; font-weight: bold; letter-spacing: 2px;"
                                   autocomplete="off">
                        </div>
                        <small class="form-text text-muted">
                            El código contiene 6 caracteres (letras y números)
                        </small>
                    </div>
                    
                    <input type="hidden" name="email" id="verify-email" value="">
                    
                    <button class="btn btn-primary btn-block" type="submit" id="verifyBtn">
                        <i class="fa fa-check-circle mr-2"></i>
                        Verificar Código
                    </button>
                </form>

                <hr>

                <!-- Opciones adicionales -->
                <div class="text-center">
                    <p class="mb-2">¿No recibiste el código?</p>
                    <div class="row">
                        <div class="col-6">
                            <a href="#" id="resendTokenLink" class="btn btn-outline-secondary btn-sm btn-block">
                                <i class="fa fa-redo mr-1"></i>
                                Reenviar
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="#" id="changeEmailLink" class="btn btn-outline-info btn-sm btn-block">
                                <i class="fa fa-edit mr-1"></i>
                                Cambiar Email
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Estado del reenvío -->
                <div id="resendStatus" class="alert alert-info mt-3" style="display: none;">
                    <i class="fa fa-spinner fa-spin mr-2"></i>
                    Reenviando código...
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
        <h5 class="modal-title" id="changeEmailModalLabel">Cambiar Email</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="changeEmailForm">
        <div class="modal-body">
          <div class="form-group">
            <label for="newEmailInput">Nuevo correo electrónico</label>
            <input type="email" class="form-control" id="newEmailInput" placeholder="Ingrese su nuevo email" required>
            <div class="invalid-feedback">
              Por favor, ingrese un email válido.
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Actualizar Email</button>
        </div>
      </form>
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

        fetch('verify_token.p.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: data.message,
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message,
                    confirmButtonText: 'Aceptar'
                });
            }
        })
        .catch(() => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error en la comunicación con el servidor',
                confirmButtonText: 'Aceptar'
            });
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
                text: 'No se pudo obtener tu correo',
                confirmButtonText: 'Aceptar'
            });
            return;
        }

        resendStatus.style.display = 'block';
        resendStatus.innerHTML = '<i class="fa fa-spinner fa-spin mr-2"></i>Reenviando código...';
        resendLink.style.pointerEvents = 'none';
        resendLink.classList.add('disabled');

        fetch('resend_token.p.php', {
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
            resendStatus.innerHTML = '<i class="fa fa-exclamation-triangle mr-2"></i>Error al reenviar el código';
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

        fetch('update_verification_email.p.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'old_email=' + encodeURIComponent(oldEmail) + '&new_email=' + encodeURIComponent(newEmail)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: 'Email actualizado y código reenviado',
                    confirmButtonText: 'Aceptar'
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
                    confirmButtonText: 'Aceptar'
                });
            }
        })
        .catch(() => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al actualizar el email',
                confirmButtonText: 'Aceptar'
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