<?php

namespace App\Services;

/**
 * Security Service
 * Maneja aspectos de seguridad como rate limiting y failed logins
 */
class SecurityService
{
    private const FAILED_LOGIN_PREFIX = 'failed_login_';
    private const RATE_LIMIT_PREFIX = 'rate_limit_';

    /**
     * Registra un intento de login fallido
     */
    public function recordFailedLogin(string $identifier): void
    {
        $key = self::FAILED_LOGIN_PREFIX . $identifier;

        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'attempts' => 0,
                'locked_until' => null
            ];
        }

        $_SESSION[$key]['attempts']++;

        $config = require __DIR__ . '/../../config.php';

        if ($_SESSION[$key]['attempts'] >= $config['security']['max_login_attempts']) {
            $_SESSION[$key]['locked_until'] = time() + $config['security']['lockout_duration'];
        }
    }

    /**
     * Verifica si el usuario puede intentar login
     */
    public function checkRateLimit(string $identifier): bool
    {
        $key = self::FAILED_LOGIN_PREFIX . $identifier;

        if (!isset($_SESSION[$key])) {
            return true;
        }

        $data = $_SESSION[$key];

        if ($data['locked_until'] && time() < $data['locked_until']) {
            return false;
        }

        // Si el lockout expiró, limpiar
        if ($data['locked_until'] && time() >= $data['locked_until']) {
            unset($_SESSION[$key]);
        }

        return true;
    }

    /**
     * Limpia los intentos fallidos
     */
    public function clearFailedLogins(string $identifier): void
    {
        $key = self::FAILED_LOGIN_PREFIX . $identifier;
        unset($_SESSION[$key]);
    }

    /**
     * Genera un token CSRF
     */
    public function generateCsrfToken(): string
    {
        $config = require __DIR__ . '/../../config.php';
        $tokenName = $config['security']['csrf_token_name'];

        if (empty($_SESSION[$tokenName])) {
            $_SESSION[$tokenName] = bin2hex(random_bytes(32));
        }

        return $_SESSION[$tokenName];
    }

    /**
     * Valida un token CSRF
     */
    public function validateCsrfToken(string $token): bool
    {
        $config = require __DIR__ . '/../../config.php';
        $tokenName = $config['security']['csrf_token_name'];

        if (empty($_SESSION[$tokenName])) {
            return false;
        }

        return hash_equals($_SESSION[$tokenName], $token);
    }

    /**
     * Previene clickjacking
     */
    public function setSecurityHeaders(): void
    {
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

        // Content Security Policy
        $csp = "default-src 'self'; ";
        $csp .= "script-src 'self' 'unsafe-inline'; ";
        $csp .= "style-src 'self' 'unsafe-inline'; ";
        $csp .= "img-src 'self' data:; ";
        $csp .= "font-src 'self'; ";
        $csp .= "connect-src 'self'; ";
        $csp .= "frame-ancestors 'none';";

        header("Content-Security-Policy: {$csp}");
    }

    /**
     * Valida la sesión actual
     */
    public function validateSession(): bool
    {
        // Verificar si la sesión ha expirado
        if (isset($_SESSION['last_activity'])) {
            $config = require __DIR__ . '/../../config.php';
            $sessionLifetime = $config['security']['session_lifetime'];

            if (time() - $_SESSION['last_activity'] > $sessionLifetime) {
                return false;
            }
        }

        $_SESSION['last_activity'] = time();

        // Verificar User-Agent (prevención de session hijacking básica)
        if (!isset($_SESSION['user_agent'])) {
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        } elseif ($_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
            return false;
        }

        return true;
    }

    /**
     * Sanitiza la salida HTML
     */
    public static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Previene inyección SQL verificando inputs
     */
    public static function validateInput(string $input, string $type = 'string'): bool
    {
        switch ($type) {
            case 'email':
                return filter_var($input, FILTER_VALIDATE_EMAIL) !== false;
            case 'int':
                return filter_var($input, FILTER_VALIDATE_INT) !== false;
            case 'url':
                return filter_var($input, FILTER_VALIDATE_URL) !== false;
            case 'string':
            default:
                // Verificar caracteres peligrosos
                $dangerous = ['<script', 'javascript:', 'onerror=', 'onclick=', '<iframe'];
                foreach ($dangerous as $pattern) {
                    if (stripos($input, $pattern) !== false) {
                        return false;
                    }
                }
                return true;
        }
    }
}
