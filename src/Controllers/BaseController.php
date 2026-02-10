<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\SecurityService;

/**
 * Base Controller
 * Clase base para todos los controladores
 */
abstract class BaseController
{
    protected AuthService $authService;
    protected SecurityService $securityService;
    protected array $data = [];

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->securityService = new SecurityService();

        // Establecer headers de seguridad
        $this->securityService->setSecurityHeaders();

        // Validar sesión
        if ($this->authService->isAuthenticated()) {
            if (!$this->securityService->validateSession()) {
                $this->authService->logout();
                $this->redirect('/login.php');
            }
        }
    }

    /**
     * Renderiza una vista
     */
    protected function render(string $view, array $data = []): void
    {
        $this->data = array_merge($this->data, $data);
        $this->data['csrf_token'] = $this->securityService->generateCsrfToken();
        $this->data['current_user'] = $this->authService->getCurrentUser();

        extract($this->data);

        $viewPath = __DIR__ . '/../../views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            throw new \Exception("View not found: {$view}");
        }

        require $viewPath;
    }

    /**
     * Redirecciona a una URL
     */
    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Retorna respuesta JSON
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Verifica autenticación
     */
    protected function requireAuth(): void
    {
        if (!$this->authService->isAuthenticated()) {
            $this->redirect('/login.php');
        }
    }

    /**
     * Verifica CSRF token
     */
    protected function verifyCsrfToken(): bool
    {
        $token = $_POST['csrf_token'] ?? '';
        return $this->securityService->validateCsrfToken($token);
    }

    /**
     * Obtiene datos POST sanitizados
     */
    protected function getPostData(): array
    {
        return \App\Validators\Validator::sanitizeArray($_POST);
    }

    /**
     * Obtiene datos GET sanitizados
     */
    protected function getQueryData(): array
    {
        return \App\Validators\Validator::sanitizeArray($_GET);
    }

    /**
     * Establece mensaje flash
     */
    protected function setFlashMessage(string $type, string $message): void
    {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }

    /**
     * Obtiene y limpia mensaje flash
     */
    protected function getFlashMessage(): ?array
    {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
}
