<?php

namespace App\Middleware;

use App\Services\JwtService;

/**
 * JWT Middleware
 * Valida tokens JWT en peticiones API
 */
class JwtMiddleware implements Middleware
{
    private JwtService $jwtService;

    public function __construct()
    {
        $this->jwtService = new JwtService();
    }

    public function handle(callable $next): mixed
    {
        // Extraer token del header
        $token = $this->jwtService->extractTokenFromHeader();

        if (!$token) {
            http_response_code(401);
            die(json_encode([
                'error' => 'Unauthorized',
                'message' => 'No se proporcionó token de autenticación'
            ]));
        }

        // Validar token
        $payload = $this->jwtService->validateToken($token);

        if (!$payload) {
            http_response_code(401);
            die(json_encode([
                'error' => 'Unauthorized',
                'message' => 'Token inválido o expirado'
            ]));
        }

        // Guardar payload en variable global para acceso en controladores
        $_SERVER['JWT_PAYLOAD'] = $payload;

        return $next();
    }

    /**
     * Obtiene el payload del token actual
     */
    public static function getPayload(): ?array
    {
        return $_SERVER['JWT_PAYLOAD'] ?? null;
    }

    /**
     * Obtiene el ID del usuario del token
     */
    public static function getUserId(): ?int
    {
        $payload = self::getPayload();
        return $payload['user_id'] ?? null;
    }

    /**
     * Obtiene el email del usuario del token
     */
    public static function getUserEmail(): ?string
    {
        $payload = self::getPayload();
        return $payload['email'] ?? null;
    }
}
