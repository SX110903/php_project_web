<?php

namespace App\Middleware;

/**
 * Rate Limit Middleware
 * Limita el número de peticiones por IP
 */
class RateLimitMiddleware implements Middleware
{
    private int $maxRequests;
    private int $timeWindow;

    public function __construct(int $maxRequests = 100, int $timeWindow = 60)
    {
        $this->maxRequests = $maxRequests;
        $this->timeWindow = $timeWindow; // en segundos
    }

    public function handle(callable $next): mixed
    {
        $ip = $this->getClientIp();
        $key = 'rate_limit_' . md5($ip);

        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'count' => 0,
                'start_time' => time()
            ];
        }

        $rateData = $_SESSION[$key];

        // Resetear si la ventana de tiempo ha pasado
        if (time() - $rateData['start_time'] > $this->timeWindow) {
            $_SESSION[$key] = [
                'count' => 1,
                'start_time' => time()
            ];
            return $next();
        }

        // Verificar límite
        if ($rateData['count'] >= $this->maxRequests) {
            http_response_code(429);
            header('Retry-After: ' . $this->timeWindow);
            die(json_encode([
                'error' => 'Too Many Requests',
                'message' => 'Has excedido el límite de peticiones'
            ]));
        }

        // Incrementar contador
        $_SESSION[$key]['count']++;

        return $next();
    }

    /**
     * Obtiene la IP real del cliente
     */
    private function getClientIp(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                // Si hay múltiples IPs, tomar la primera
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
