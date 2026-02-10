<?php

namespace App\Middleware;

use App\Services\AuthService;

/**
 * Auth Middleware
 * Verifica que el usuario esté autenticado
 */
class AuthMiddleware implements Middleware
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function handle(callable $next): mixed
    {
        if (!$this->authService->isAuthenticated()) {
            // Si es una petición AJAX, retornar JSON
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                http_response_code(401);
                die(json_encode([
                    'error' => 'Unauthorized',
                    'message' => 'No autenticado'
                ]));
            }

            // Redireccionar a login
            header('Location: /login.php');
            exit;
        }

        return $next();
    }
}
