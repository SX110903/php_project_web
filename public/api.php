<?php

/**
 * API Endpoint Router
 * Maneja las peticiones a la API REST con JWT
 */

require_once __DIR__ . '/../autoload.php';

use App\Controllers\ApiController;
use App\Controllers\JwtAuthController;
use App\Middleware\RateLimitMiddleware;
use App\Middleware\JwtMiddleware;

// Aplicar rate limiting
$rateLimiter = new RateLimitMiddleware(100, 60);
$rateLimiter->handle(function() {
    // Obtener la ruta y método
    $method = $_SERVER['REQUEST_METHOD'];
    $path = $_GET['path'] ?? '';

    // Rutas públicas (sin JWT)
    $publicRoutes = [
        'health',
        'auth/login',
        'auth/register',
        'auth/refresh'
    ];

    // Si no es ruta pública, aplicar middleware JWT
    if (!in_array($path, $publicRoutes)) {
        $jwtMiddleware = new JwtMiddleware();
        $jwtMiddleware->handle(function() use ($path, $method) {
            handleProtectedRoutes($path, $method);
        });
    } else {
        handlePublicRoutes($path, $method);
    }
});

/**
 * Maneja rutas públicas (sin autenticación)
 */
function handlePublicRoutes(string $path, string $method): void
{
    $apiController = new ApiController();
    $authController = new JwtAuthController();

    switch ($path) {
        case 'health':
            $apiController->healthCheck();
            break;

        case 'auth/login':
            if ($method === 'POST') {
                $authController->login();
            } else {
                methodNotAllowed();
            }
            break;

        case 'auth/register':
            if ($method === 'POST') {
                $authController->register();
            } else {
                methodNotAllowed();
            }
            break;

        case 'auth/refresh':
            if ($method === 'POST') {
                $authController->refresh();
            } else {
                methodNotAllowed();
            }
            break;

        default:
            notFound($path);
            break;
    }
}

/**
 * Maneja rutas protegidas (requieren JWT)
 */
function handleProtectedRoutes(string $path, string $method): void
{
    $controller = new ApiController();
    $authController = new JwtAuthController();

    switch ($path) {
        case 'auth/verify':
            if ($method === 'GET') {
                $authController->verify();
            } else {
                methodNotAllowed();
            }
            break;

        case 'me':
            if ($method === 'GET') {
                $controller->me();
            } else {
                methodNotAllowed();
            }
            break;

        case 'users':
            if ($method === 'GET') {
                $controller->listUsers();
            } else {
                methodNotAllowed();
            }
            break;

        case 'user':
            if ($method === 'GET' && isset($_GET['id'])) {
                $controller->getUser((int)$_GET['id']);
            } else {
                methodNotAllowed();
            }
            break;

        case 'profile':
            if ($method === 'PUT' || $method === 'POST') {
                $controller->updateProfile();
            } else {
                methodNotAllowed();
            }
            break;

        case 'change-password':
            if ($method === 'POST') {
                $controller->changePassword();
            } else {
                methodNotAllowed();
            }
            break;

        default:
            notFound($path);
            break;
    }
}

/**
 * Respuesta de endpoint no encontrado
 */
function notFound(string $path): void
{
    http_response_code(404);
    echo json_encode([
        'error' => 'Endpoint not found',
        'path' => $path,
        'message' => 'El endpoint solicitado no existe'
    ]);
    exit;
}

/**
 * Respuesta de método no permitido
 */
function methodNotAllowed(): void
{
    http_response_code(405);
    echo json_encode([
        'error' => 'Method not allowed',
        'message' => 'El método HTTP no está permitido para este endpoint'
    ]);
    exit;
}
