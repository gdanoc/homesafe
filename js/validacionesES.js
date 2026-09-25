class ValidacionesES {
    constructor() {
        this.initValidaciones();
    }

    initValidaciones() {
        const registerForm = document.querySelector('#RegisterModal form');
        if (registerForm) {
            registerForm.addEventListener('submit', (e) => this.validarFormularioRegistro(e));
            this.configurarValidacionesCampos('register');
        }
    }

    configurarValidacionesCampos(tipo) {
        if (tipo === 'register') {
            const campoNombre = document.getElementById('register-name');
            const campoEmail = document.getElementById('register-email');
            const campoPassword = document.getElementById('register-password');
            const campoRepetirPassword = document.getElementById('register-repeat-password');

            campoNombre?.addEventListener('blur', () => this.validarNombre(campoNombre));
            campoNombre?.addEventListener('input', () => this.limpiarError(campoNombre));

            campoEmail?.addEventListener('blur', () => this.validarEmail(campoEmail));
            campoEmail?.addEventListener('input', () => this.limpiarError(campoEmail));

            campoPassword?.addEventListener('blur', () => this.validarPassword(campoPassword, 'register'));
            campoPassword?.addEventListener('input', () => this.limpiarError(campoPassword));

            campoRepetirPassword?.addEventListener('blur', () => this.validarCoincidenciaPassword(campoPassword, campoRepetirPassword));
            campoRepetirPassword?.addEventListener('input', () => this.limpiarError(campoRepetirPassword));
        }
    }

    validarFormularioRegistro(event) {
        event.preventDefault();

        const campoNombre = document.getElementById('register-name');
        const campoEmail = document.getElementById('register-email');
        const campoPassword = document.getElementById('register-password');
        const campoRepetirPassword = document.getElementById('register-repeat-password');

        let esValido = true;

        if (!this.validarNombre(campoNombre)) esValido = false;
        if (!this.validarEmail(campoEmail)) esValido = false;
        if (!this.validarPassword(campoPassword, 'register')) esValido = false;
        if (!this.validarCoincidenciaPassword(campoPassword, campoRepetirPassword)) esValido = false;

        if (esValido) {
            event.currentTarget.submit();
        }
    }

    validarNombre(campo) {
        const valor = campo.value.trim();
        const min = 2;
        const max = 50;

        if (!valor) {
            this.mostrarError(campo, 'El nombre es requerido');
            return false;
        }
        if (valor.length < min) {
            this.mostrarError(campo, `El nombre debe tener al menos ${min} caracteres`);
            return false;
        }
        if (valor.length > max) {
            this.mostrarError(campo, `El nombre no puede exceder ${max} caracteres`);
            return false;
        }
        if (!/^[a-zA-ZÀ-ÿ\u00f1\u00d1\s]+$/.test(valor)) {
            this.mostrarError(campo, 'El nombre solo puede contener letras y espacios');
            return false;
        }
        if (/^\s|\s$/.test(campo.value)) {
            this.mostrarError(campo, 'El nombre no puede empezar o terminar con espacios');
            return false;
        }
        if (/\s{2,}/.test(campo.value)) {
            this.mostrarError(campo, 'El nombre no puede contener espacios consecutivos');
            return false;
        }

        this.limpiarError(campo);
        return true;
    }

    validarEmail(campo) {
        const valor = campo.value.trim();
        const max = 254;
        const regexEmail = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

        if (!valor) {
            this.mostrarError(campo, 'El email es requerido');
            return false;
        }
        if (valor.length > max) {
            this.mostrarError(campo, `El email no puede exceder ${max} caracteres`);
            return false;
        }
        if (!regexEmail.test(valor)) {
            this.mostrarError(campo, 'Por favor ingrese un email válido');
            return false;
        }
        if (valor.includes('..')) {
            this.mostrarError(campo, 'El email no puede contener puntos consecutivos');
            return false;
        }
        if (valor.startsWith('.') || valor.endsWith('.')) {
            this.mostrarError(campo, 'El email no puede empezar o terminar con punto');
            return false;
        }

        this.limpiarError(campo);
        return true;
    }

    validarPassword(campo, tipo) {
        const valor = campo.value;
        const min = 8;
        const max = 16;

        if (!valor) {
            this.mostrarError(campo, 'La contraseña es requerida');
            return false;
        }
        if (valor.length < min) {
            this.mostrarError(campo, `La contraseña debe tener al menos ${min} caracteres`);
            return false;
        }
        if (valor.length > max) {
            this.mostrarError(campo, `La contraseña no puede exceder ${max} caracteres`);
            return false;
        }
        if (!/(?=.*[a-z])/.test(valor)) {
            this.mostrarError(campo, 'La contraseña debe contener al menos una letra minúscula');
            return false;
        }
        if (!/(?=.*[A-Z])/.test(valor)) {
            this.mostrarError(campo, 'La contraseña debe contener al menos una letra mayúscula');
            return false;
        }
        if (!/(?=.*\d)/.test(valor)) {
            this.mostrarError(campo, 'La contraseña debe contener al menos un número');
            return false;
        }
        if (!/(?=.*[!@#$%^&*(),.?":{}|<>])/.test(valor)) {
            this.mostrarError(campo, 'La contraseña debe contener al menos un carácter especial');
            return false;
        }
        if (/\s/.test(valor)) {
            this.mostrarError(campo, 'La contraseña no puede contener espacios');
            return false;
        }

        this.limpiarError(campo);
        return true;
    }

    validarCoincidenciaPassword(campoPassword, campoRepetirPassword) {
        const pass = campoPassword.value;
        const repetir = campoRepetirPassword.value;

        if (!repetir) {
            this.mostrarError(campoRepetirPassword, 'Por favor confirme su contraseña');
            return false;
        }
        if (pass !== repetir) {
            this.mostrarError(campoRepetirPassword, 'Las contraseñas no coinciden');
            return false;
        }

        this.limpiarError(campoRepetirPassword);
        return true;
    }

    mostrarError(campo, mensaje) {
        this.limpiarError(campo);

        campo.classList.add('is-invalid');

        let contenedor = campo.closest('.form-group') || campo.closest('.mb-3') || campo.parentElement;

        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback d-block';
        errorDiv.textContent = mensaje;
        errorDiv.setAttribute('data-validation-error', 'true');

        contenedor.appendChild(errorDiv);
    }

    limpiarError(campo) {
        campo.classList.remove('is-invalid');

        let contenedor = campo.closest('.form-group') || campo.closest('.mb-3') || campo.parentElement;
        const errorExistente = contenedor.querySelector('[data-validation-error="true"]');
        if (errorExistente) errorExistente.remove();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new ValidacionesES();
});