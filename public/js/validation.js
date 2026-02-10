/**
 * Form Validation Module
 * Validación de formularios en el frontend
 */

class FormValidator {
    constructor(formId) {
        this.form = document.getElementById(formId);
        if (!this.form) return;

        this.rules = {};
        this.init();
    }

    init() {
        // Prevenir submit por defecto para validar
        this.form.addEventListener('submit', (e) => {
            if (!this.validateForm()) {
                e.preventDefault();
            }
        });

        // Validación en tiempo real
        const inputs = this.form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', () => {
                this.validateField(input);
            });

            input.addEventListener('input', () => {
                // Limpiar error mientras escribe
                this.clearFieldError(input);
            });
        });
    }

    /**
     * Define reglas de validación
     */
    setRules(rules) {
        this.rules = rules;
        return this;
    }

    /**
     * Valida todo el formulario
     */
    validateForm() {
        let isValid = true;

        for (const [fieldName, rules] of Object.entries(this.rules)) {
            const field = this.form.querySelector(`[name="${fieldName}"]`);
            if (!field) continue;

            if (!this.validateField(field, rules)) {
                isValid = false;
            }
        }

        return isValid;
    }

    /**
     * Valida un campo individual
     */
    validateField(field, rules = null) {
        if (!rules) {
            rules = this.rules[field.name];
        }

        if (!rules) return true;

        const value = field.value.trim();
        let isValid = true;

        // Required
        if (rules.required && value === '') {
            this.showError(field, rules.messages?.required || 'Este campo es requerido');
            return false;
        }

        // Si está vacío y no es required, no validar el resto
        if (value === '') {
            this.clearFieldError(field);
            return true;
        }

        // Email
        if (rules.email && !this.isValidEmail(value)) {
            this.showError(field, rules.messages?.email || 'Email inválido');
            return false;
        }

        // Min length
        if (rules.minLength && value.length < rules.minLength) {
            this.showError(field, rules.messages?.minLength || `Mínimo ${rules.minLength} caracteres`);
            return false;
        }

        // Max length
        if (rules.maxLength && value.length > rules.maxLength) {
            this.showError(field, rules.messages?.maxLength || `Máximo ${rules.maxLength} caracteres`);
            return false;
        }

        // Pattern
        if (rules.pattern && !rules.pattern.test(value)) {
            this.showError(field, rules.messages?.pattern || 'Formato inválido');
            return false;
        }

        // Match (para confirmar contraseña)
        if (rules.match) {
            const matchField = this.form.querySelector(`[name="${rules.match}"]`);
            if (matchField && value !== matchField.value.trim()) {
                this.showError(field, rules.messages?.match || 'Los campos no coinciden');
                return false;
            }
        }

        // Custom validation
        if (rules.custom && typeof rules.custom === 'function') {
            const customResult = rules.custom(value, field);
            if (customResult !== true) {
                this.showError(field, customResult);
                return false;
            }
        }

        this.clearFieldError(field);
        field.classList.add('form-success');
        return true;
    }

    /**
     * Muestra error en un campo
     */
    showError(field, message) {
        field.classList.add('error');
        field.classList.remove('form-success');

        const errorElement = document.getElementById(`${field.id}-error`);
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.classList.add('show');
        }
    }

    /**
     * Limpia el error de un campo
     */
    clearFieldError(field) {
        field.classList.remove('error');

        const errorElement = document.getElementById(`${field.id}-error`);
        if (errorElement) {
            errorElement.textContent = '';
            errorElement.classList.remove('show');
        }
    }

    /**
     * Valida formato de email
     */
    isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    /**
     * Sanitiza entrada para prevenir XSS
     */
    sanitize(str) {
        const temp = document.createElement('div');
        temp.textContent = str;
        return temp.innerHTML;
    }
}

// Inicializar validadores cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    // Validador para login
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        const loginValidator = new FormValidator('loginForm');
        loginValidator.setRules({
            email: {
                required: true,
                email: true,
                messages: {
                    required: 'El email es requerido',
                    email: 'Ingresa un email válido'
                }
            },
            password: {
                required: true,
                minLength: 8,
                messages: {
                    required: 'La contraseña es requerida',
                    minLength: 'La contraseña debe tener al menos 8 caracteres'
                }
            }
        });
    }

    // Validador para registro
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        const registerValidator = new FormValidator('registerForm');
        registerValidator.setRules({
            name: {
                required: true,
                minLength: 3,
                maxLength: 100,
                messages: {
                    required: 'El nombre es requerido',
                    minLength: 'El nombre debe tener al menos 3 caracteres',
                    maxLength: 'El nombre no puede exceder 100 caracteres'
                }
            },
            email: {
                required: true,
                email: true,
                messages: {
                    required: 'El email es requerido',
                    email: 'Ingresa un email válido'
                }
            },
            password: {
                required: true,
                minLength: 8,
                pattern: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).*$/,
                messages: {
                    required: 'La contraseña es requerida',
                    minLength: 'La contraseña debe tener al menos 8 caracteres',
                    pattern: 'La contraseña debe contener mayúsculas, minúsculas y números'
                }
            },
            confirm_password: {
                required: true,
                match: 'password',
                messages: {
                    required: 'Confirma tu contraseña',
                    match: 'Las contraseñas no coinciden'
                }
            }
        });
    }

    // Validador para forgot password
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    if (forgotPasswordForm) {
        const forgotValidator = new FormValidator('forgotPasswordForm');
        forgotValidator.setRules({
            email: {
                required: true,
                email: true,
                messages: {
                    required: 'El email es requerido',
                    email: 'Ingresa un email válido'
                }
            }
        });
    }

    // Validador para reset password
    const resetPasswordForm = document.getElementById('resetPasswordForm');
    if (resetPasswordForm) {
        const resetValidator = new FormValidator('resetPasswordForm');
        resetValidator.setRules({
            password: {
                required: true,
                minLength: 8,
                pattern: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).*$/,
                messages: {
                    required: 'La contraseña es requerida',
                    minLength: 'La contraseña debe tener al menos 8 caracteres',
                    pattern: 'La contraseña debe contener mayúsculas, minúsculas y números'
                }
            },
            confirm_password: {
                required: true,
                match: 'password',
                messages: {
                    required: 'Confirma tu contraseña',
                    match: 'Las contraseñas no coinciden'
                }
            }
        });
    }
});

// Exportar para uso global
window.FormValidator = FormValidator;
