<?php $title = 'Iniciar Sesion'; ?>
<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="auth-container">
    <div class="auth-box">
        <div class="auth-header">
            <h1>Iniciar Sesion</h1>
            <p>Bienvenido de nuevo</p>
        </div>

        <?php if (isset($flash)): ?>
            <div class="alert alert-<?= $flash['type'] ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <form action="/login.php" method="POST" class="auth-form" id="loginForm">
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

            <div class="form-group">
                <label for="password">Contrasena</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control"
                    required
                    autocomplete="current-password"
                    placeholder="********"
                >
                <span class="form-error" id="password-error"></span>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-block">
                    Iniciar Sesion
                </button>
            </div>

            <div class="form-links">
                <a href="/forgot-password.php">Olvidaste tu contrasena?</a>
                <a href="/register.php">No tienes cuenta? Registrate</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
