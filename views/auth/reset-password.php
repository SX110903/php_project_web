<?php $title = 'Restablecer Contrasena'; ?>
<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="auth-container">
    <div class="auth-box">
        <div class="auth-header">
            <h1>Restablecer Contrasena</h1>
            <p>Ingresa tu nueva contrasena</p>
        </div>

        <?php if (isset($flash)): ?>
            <div class="alert alert-<?= $flash['type'] ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <form action="/reset-password.php" method="POST" class="auth-form" id="resetPasswordForm">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">

            <div class="form-group">
                <label for="password">Nueva Contrasena</label>
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
                    Restablecer Contrasena
                </button>
            </div>

            <div class="form-links">
                <a href="/login.php">Volver al inicio de sesion</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
