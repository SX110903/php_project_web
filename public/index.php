<?php

/**
 * Index - Página de inicio
 * Redirecciona al login o dashboard según el estado de autenticación
 */

require_once __DIR__ . '/../autoload.php';

use App\Services\AuthService;

$authService = new AuthService();

if ($authService->isAuthenticated()) {
    header('Location: /dashboard.php');
} else {
    header('Location: /login.php');
}
exit;
