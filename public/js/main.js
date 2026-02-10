/**
 * Main frontend module
 */
class App {
    constructor() {
        this.csrfToken = this.getCsrfToken();
        this.init();
    }

    init() {
        this.initAlerts();
        this.initNavigation();
        this.initProfileActions();
    }

    getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    initAlerts() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach((alert) => {
            const closeBtn = document.createElement('button');
            closeBtn.className = 'alert-close';
            closeBtn.setAttribute('type', 'button');
            closeBtn.setAttribute('aria-label', 'Cerrar alerta');
            closeBtn.textContent = 'x';
            closeBtn.addEventListener('click', () => this.closeElement(alert));
            alert.appendChild(closeBtn);

            setTimeout(() => this.closeElement(alert), 5000);
        });
    }

    closeElement(element) {
        if (!element || !element.parentNode) {
            return;
        }

        element.style.opacity = '0';
        element.style.transition = 'opacity 0.25s ease';
        window.setTimeout(() => {
            if (element.parentNode) {
                element.parentNode.removeChild(element);
            }
        }, 250);
    }

    initNavigation() {
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.nav-menu a');

        navLinks.forEach((link) => {
            const linkPath = new URL(link.href).pathname;
            if (linkPath === currentPath) {
                link.classList.add('active');
            }
        });
    }

    initProfileActions() {
        const button = document.getElementById('changePasswordBtn');
        if (!button) {
            return;
        }

        button.addEventListener('click', () => {
            this.showNotification(
                'La funcionalidad de cambio de contrasena se conecta desde la API /change-password.',
                'info'
            );
        });
    }

    async fetch(url, options = {}) {
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': this.csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        };

        const mergedOptions = {
            ...defaultOptions,
            ...options,
            headers: {
                ...defaultOptions.headers,
                ...(options.headers || {})
            }
        };

        const response = await fetch(url, mergedOptions);

        if (response.status === 401) {
            window.location.href = '/login.php';
            return null;
        }

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return response.json();
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type}`;
        notification.textContent = message;
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.zIndex = '9999';
        notification.style.maxWidth = '360px';
        document.body.appendChild(notification);

        this.initAlerts();
    }

    setButtonLoading(button, loading = true) {
        if (!button) {
            return;
        }

        if (loading) {
            button.disabled = true;
            button.classList.add('btn-loading');
            button.dataset.originalText = button.textContent;
            button.textContent = 'Cargando';
            return;
        }

        button.disabled = false;
        button.classList.remove('btn-loading');
        if (button.dataset.originalText) {
            button.textContent = button.dataset.originalText;
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.app = new App();
});

window.App = App;
