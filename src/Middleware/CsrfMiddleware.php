<?php

namespace App\Middleware;

use App\Services\SecurityService;

/**
 * CSRF Middleware
 * Valida tokens CSRF en peticiones POST, PUT, DELETE
 */
class CsrfMiddleware implements Middleware
{
    private SecurityService $securityService;

    public function __construct()
    {
        $this->securityService = new SecurityService();
    }

    public function handle(callable $next): mixed
    {
        // Solo validar en métodos que modifican datos
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if (in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

            if (!$this->securityService->validateCsrfToken($token)) {
                http_response_code(403);
                die(json_encode([
                    'error' => 'CSRF token validation failed',
                    'message' => 'Token de seguridad inválido'
                ]));
            }
        }

        return $next();
    }
}
