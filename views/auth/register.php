<?php $title = 'Registro'; ?>
<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="auth-container">
    <div class="auth-box">
        <div class="auth-header">
            <h1>Crear Cuenta</h1>
            <p>Unete a la plataforma</p>
        </div>

        <?php if (isset($flash)): ?>
            <div class="alert alert-<?= $flash['type'] ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <form action="/register.php" method="POST" class="auth-form" id="registerForm">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

            <div class="form-group">
                <label for="name">Nombre Completo</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control"
                    required
                    minlength="3"
                    autocomplete="name"
                    placeholder="Juan Perez"
                >
                <span class="form-error" id="name-error"></span>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    required
                    autocomplete="email"
                    placeholder="tu@email.com"
                >
                <span class="form-error" id="email-error"></span>
            </div>

            <div class="form-group">
                <label for="password">Contrasena</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control"
                    required
                    minlength="8"
                    autocomplete="new-password"
                    placeholder="Minimo 8 caracteres"
                >
                <span class="form-error" id="password-error"></span>
                <small class="form-help">Debe tener al menos 8 caracteres</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmar Contrasena</label>
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    class="form-control"
                    required
                    autocomplete="new-password"
                    placeholder="Repite tu contrasena"
                >
                <span class="form-error" id="confirm_password-error"></span>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-block">
                    Crear Cuenta
                </button>
            </div>

            <div class="form-links">
                <a href="/login.php">Ya tienes cuenta? Inicia sesion</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
