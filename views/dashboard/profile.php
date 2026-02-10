<?php $title = 'Mi Perfil'; ?>
<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="dashboard-container">
    <nav class="dashboard-nav">
        <div class="nav-header">
            <h2>Secure App</h2>
        </div>
        <ul class="nav-menu">
            <li><a href="/dashboard.php">Dashboard</a></li>
            <li><a href="/profile.php" class="active">Mi Perfil</a></li>
            <?php if ($current_user && $current_user->hasRole('admin')): ?>
                <li><a href="/admin.php">Administracion</a></li>
            <?php endif; ?>
            <li><a href="/logout.php">Cerrar Sesion</a></li>
        </ul>
    </nav>

    <main class="dashboard-main">
        <header class="dashboard-header">
            <h1>Mi Perfil</h1>
        </header>

        <?php if (isset($flash)): ?>
            <div class="alert alert-<?= $flash['type'] ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <div class="dashboard-content">
            <div class="info-section">
                <div class="info-card">
                    <h2>Informacion Personal</h2>
                    <div class="profile-info">
                        <div class="info-row">
                            <strong>Nombre:</strong>
                            <span><?= htmlspecialchars($user->getName()) ?></span>
                        </div>
                        <div class="info-row">
                            <strong>Email:</strong>
                            <span><?= htmlspecialchars($user->getEmail()) ?></span>
                        </div>
                        <div class="info-row">
                            <strong>Estado:</strong>
                            <span class="<?= $user->isActive() ? 'status-active' : 'status-inactive' ?>">
                                <?= $user->isActive() ? 'Activo' : 'Inactivo' ?>
                            </span>
                        </div>
                        <div class="info-row">
                            <strong>Miembro desde:</strong>
                            <span><?= date('d/m/Y', strtotime($user->getCreatedAt())) ?></span>
                        </div>
                        <div class="info-row">
                            <strong>Ultimo acceso:</strong>
                            <span><?= $user->getLastLogin() ? date('d/m/Y H:i', strtotime($user->getLastLogin())) : 'N/A' ?></span>
                        </div>
                    </div>
                </div>

                <div class="info-card">
                    <h2>Seguridad</h2>
                    <p>Manten tu cuenta segura actualizando tu contrasena regularmente.</p>
                    <button class="btn btn-primary" type="button" id="changePasswordBtn">
                        Cambiar Contrasena
                    </button>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
