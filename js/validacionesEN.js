class ValidationsEN {
    constructor() {
        this.initValidations();
    }

    initValidations() {
        const registerForm = document.querySelector('#RegisterModal form');
        if (registerForm) {
            registerForm.addEventListener('submit', (e) => this.validateRegisterForm(e));
            this.setupFieldValidations('register');
        }
    }

    setupFieldValidations(type) {
        if (type === 'register') {
            const nameField = document.getElementById('register-name');
            const emailField = document.getElementById('register-email');
            const passwordField = document.getElementById('register-password');
            const repeatPasswordField = document.getElementById('register-repeat-password');

            nameField?.addEventListener('blur', () => this.validateName(nameField));
            nameField?.addEventListener('input', () => this.clearError(nameField));

            emailField?.addEventListener('blur', () => this.validateEmail(emailField));
            emailField?.addEventListener('input', () => this.clearError(emailField));

            passwordField?.addEventListener('blur', () => this.validatePassword(passwordField, 'register'));
            passwordField?.addEventListener('input', () => this.clearError(passwordField));

            repeatPasswordField?.addEventListener('blur', () => this.validatePasswordMatch(passwordField, repeatPasswordField));
            repeatPasswordField?.addEventListener('input', () => this.clearError(repeatPasswordField));
        }
    }

    validateRegisterForm(event) {
        event.preventDefault();

        const nameField = document.getElementById('register-name');
        const emailField = document.getElementById('register-email');
        const passwordField = document.getElementById('register-password');
        const repeatPasswordField = document.getElementById('register-repeat-password');

        let isValid = true;

        if (!this.validateName(nameField)) isValid = false;
        if (!this.validateEmail(emailField)) isValid = false;
        if (!this.validatePassword(passwordField, 'register')) isValid = false;
        if (!this.validatePasswordMatch(passwordField, repeatPasswordField)) isValid = false;

        if (isValid) {
            event.target.submit();
        }
    }

    validateName(field) {
        const value = field.value.trim();
        const min = 2;
        const max = 50;

        if (!value) {
            this.showError(field, 'Name is required');
            return false;
        }
        if (value.length < min) {
            this.showError(field, `Name must be at least ${min} characters`);
            return false;
        }
        if (value.length > max) {
            this.showError(field, `Name cannot exceed ${max} characters`);
            return false;
        }
        if (!/^[a-zA-ZÀ-ÿ\u00f1\u00d1\s]+$/.test(value)) {
            this.showError(field, 'Name can only contain letters and spaces');
            return false;
        }
        if (/^\s|\s$/.test(field.value)) {
            this.showError(field, 'Name cannot start or end with spaces');
            return false;
        }
        if (/\s{2,}/.test(field.value)) {
            this.showError(field, 'Name cannot contain consecutive spaces');
            return false;
        }

        this.clearError(field);
        return true;
    }

    validateEmail(field) {
        const value = field.value.trim();
        const max = 254;
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

        if (!value) {
            this.showError(field, 'Email is required');
            return false;
        }
        if (value.length > max) {
            this.showError(field, `Email cannot exceed ${max} characters`);
            return false;
        }
        if (!emailRegex.test(value)) {
            this.showError(field, 'Please enter a valid email');
            return false;
        }
        if (value.includes('..')) {
            this.showError(field, 'Email cannot contain consecutive dots');
            return false;
        }
        if (value.startsWith('.') || value.endsWith('.')) {
            this.showError(field, 'Email cannot start or end with a dot');
            return false;
        }

        this.clearError(field);
        return true;
    }

    validatePassword(field, type) {
        const value = field.value;
        const min = 8;
        const max = 16;

        if (!value) {
            this.showError(field, 'Password is required');
            return false;
        }
        if (value.length < min) {
            this.showError(field, `Password must be at least ${min} characters`);
            return false;
        }
        if (value.length > max) {
            this.showError(field, `Password cannot exceed ${max} characters`);
            return false;
        }
        if (!/(?=.*[a-z])/.test(value)) {
            this.showError(field, 'Password must contain at least one lowercase letter');
            return false;
        }
        if (!/(?=.*[A-Z])/.test(value)) {
            this.showError(field, 'Password must contain at least one uppercase letter');
            return false;
        }
        if (!/(?=.*\d)/.test(value)) {
            this.showError(field, 'Password must contain at least one number');
            return false;
        }
        if (!/(?=.*[!@#$%^&*(),.?":{}|<>])/.test(value)) {
            this.showError(field, 'Password must contain at least one special character');
            return false;
        }
        if (/\s/.test(value)) {
            this.showError(field, 'Password cannot contain spaces');
            return false;
        }

        this.clearError(field);
        return true;
    }

    validatePasswordMatch(passwordField, repeatPasswordField) {
        const pass = passwordField.value;
        const repeat = repeatPasswordField.value;

        if (!repeat) {
            this.showError(repeatPasswordField, 'Please confirm your password');
            return false;
        }
        if (pass !== repeat) {
            this.showError(repeatPasswordField, 'Passwords do not match');
            return false;
        }

        this.clearError(repeatPasswordField);
        return true;
    }

    showError(field, message) {
        this.clearError(field);

        field.classList.add('is-invalid');

        let container = field.closest('.form-group') || field.closest('.mb-3') || field.parentElement;

        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback d-block';
        errorDiv.textContent = message;
        errorDiv.setAttribute('data-validation-error', 'true');

        container.appendChild(errorDiv);
    }

    clearError(field) {
        field.classList.remove('is-invalid');

        let container = field.closest('.form-group') || field.closest('.mb-3') || field.parentElement;
        const existingError = container.querySelector('[data-validation-error="true"]');
        if (existingError) existingError.remove();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new ValidationsEN();
});
