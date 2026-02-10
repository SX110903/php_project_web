<?php $title = 'Recuperar Contrasena'; ?>
<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="auth-container">
    <div class="auth-box">
        <div class="auth-header">
            <h1>Recuperar Contrasena</h1>
            <p>Ingresa tu email para recibir instrucciones</p>
        </div>

        <?php if (isset($flash)): ?>
            <div class="alert alert-<?= $flash['type'] ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <form action="/forgot-password.php" method="POST" class="auth-form" id="forgotPasswordForm">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

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

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-block">
                    Enviar Instrucciones
                </button>
            </div>

            <div class="form-links">
                <a href="/login.php">Volver al inicio de sesion</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
