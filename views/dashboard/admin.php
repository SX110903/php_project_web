<?php $title = 'Administracion'; ?>
<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="dashboard-container">
    <nav class="dashboard-nav">
        <div class="nav-header">
            <h2>Secure App</h2>
        </div>
        <ul class="nav-menu">
            <li><a href="/dashboard.php">Dashboard</a></li>
            <li><a href="/profile.php">Mi Perfil</a></li>
            <li><a href="/admin.php" class="active">Administracion</a></li>
            <li><a href="/logout.php">Cerrar Sesion</a></li>
        </ul>
    </nav>

    <main class="dashboard-main">
        <header class="dashboard-header">
            <h1>Panel de Administracion</h1>
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
            <div class="info-section">
                <div class="info-card">
                    <h2>Estado del Sistema</h2>
                    <p>Usuario autenticado como administrador.</p>
                    <p>En esta seccion puedes enlazar gestion de usuarios, roles y permisos.</p>
                </div>
                <div class="info-card">
                    <h2>Acciones Rapidas</h2>
                    <div class="action-grid">
                        <a class="btn btn-primary btn-block" href="/api.php?path=users">Ver usuarios (API)</a>
                        <a class="btn btn-outline btn-block" href="/dashboard.php">Volver al dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
