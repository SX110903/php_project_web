<?php $title = 'Dashboard'; ?>
<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="dashboard-container">
    <nav class="dashboard-nav">
        <div class="nav-header">
            <h2>Secure App</h2>
        </div>
        <ul class="nav-menu">
            <li><a href="<?= $routeBase ?>/dashboard.php" class="active">Dashboard</a></li>
            <li><a href="<?= $routeBase ?>/profile.php">Mi Perfil</a></li>
            <?php if ($current_user && $current_user->hasRole('admin')): ?>
                <li><a href="<?= $routeBase ?>/admin.php">Administracion</a></li>
            <?php endif; ?>
            <li><a href="<?= $routeBase ?>/logout.php">Cerrar Sesion</a></li>
        </ul>
    </nav>

    <main class="dashboard-main">
        <header class="dashboard-header">
            <h1>Bienvenido, <?= htmlspecialchars($user->getName()) ?></h1>
            <div class="user-info">
                <span><?= htmlspecialchars($user->getEmail()) ?></span>
            </div>
        </header>

        <?php if (isset($flash)): ?>
            <div class="alert alert-<?= $flash['type'] ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <div class="dashboard-content">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" aria-hidden="true">USR</div>
                    <div class="stat-info">
                        <h3>Usuario</h3>
                        <p><?= $user->isActive() ? 'Activo' : 'Inactivo' ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" aria-hidden="true">ACC</div>
                    <div class="stat-info">
                        <h3>Ultimo acceso</h3>
                        <p><?= $user->getLastLogin() ? date('d/m/Y H:i', strtotime($user->getLastLogin())) : 'N/A' ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" aria-hidden="true">ROL</div>
                    <div class="stat-info">
                        <h3>Roles</h3>
                        <p><?= count($roles) ?> asignado(s)</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" aria-hidden="true">PER</div>
                    <div class="stat-info">
                        <h3>Permisos</h3>
                        <p><?= count($permissions) ?> activo(s)</p>
                    </div>
                </div>
            </div>

            <div class="info-section">
                <div class="info-card">
                    <h2>Tus Roles</h2>
                    <ul class="role-list">
                        <?php if (!empty($roles)): ?>
                            <?php foreach ($roles as $role): ?>
                                <li class="role-badge"><?= htmlspecialchars($role['name']) ?></li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>No hay roles asignados</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="info-card">
                    <h2>Tus Permisos</h2>
                    <ul class="permission-list">
                        <?php if (!empty($permissions)): ?>
                            <?php foreach ($permissions as $permission): ?>
                                <li>
                                    <strong><?= htmlspecialchars($permission['name']) ?></strong>
                                    <?php if (!empty($permission['description'])): ?>
                                        <small><?= htmlspecialchars($permission['description']) ?></small>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>No hay permisos asignados</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
