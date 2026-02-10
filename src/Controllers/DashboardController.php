<?php

namespace App\Controllers;

use App\Services\AuthorizationService;

/**
 * Dashboard Controller
 * Maneja el panel de control principal
 */
class DashboardController extends BaseController
{
    private AuthorizationService $authorizationService;

    public function __construct()
    {
        parent::__construct();
        $this->authorizationService = new AuthorizationService();
    }

    /**
     * Muestra el dashboard principal
     */
    public function index(): void
    {
        $this->requireAuth();

        $user = $this->authService->getCurrentUser();

        if (!$user) {
            $this->redirect('/login.php');
        }

        $this->render('dashboard/index', [
            'flash' => $this->getFlashMessage(),
            'user' => $user,
            'roles' => $user->getRoles(),
            'permissions' => $user->getPermissions()
        ]);
    }

    /**
     * Muestra perfil del usuario
     */
    public function profile(): void
    {
        $this->requireAuth();

        $user = $this->authService->getCurrentUser();

        $this->render('dashboard/profile', [
            'flash' => $this->getFlashMessage(),
            'user' => $user
        ]);
    }

    /**
     * Panel de administración (solo admin)
     */
    public function admin(): void
    {
        $this->requireAuth();

        $user = $this->authService->getCurrentUser();

        if (!$this->authorizationService->isAdmin($user)) {
            $this->setFlashMessage('error', 'Acceso denegado');
            $this->redirect('/dashboard.php');
        }

        $this->render('dashboard/admin', [
            'flash' => $this->getFlashMessage(),
            'user' => $user
        ]);
    }
}
