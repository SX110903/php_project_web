<?php

namespace App\Services;

/**
 * JWT Service
 * Servicio para generar y validar JSON Web Tokens (JWT)
 * Implementación sin dependencias externas
 */
class JwtService
{
    private string $secretKey;
    private string $algorithm = 'HS256';
    private int $expirationTime = 3600; // 1 hora por defecto

    public function __construct()
    {
        $config = require __DIR__ . '/../../config.php';
        $this->secretKey = $config['jwt']['secret_key'] ?? $this->generateSecretKey();
        $this->expirationTime = $config['jwt']['expiration'] ?? 3600;
    }

    /**
     * Genera un JWT para un usuario
     */
    public function generateToken(array $payload): string
    {
        // Header
        $header = [
            'typ' => 'JWT',
            'alg' => $this->algorithm
        ];

        // Payload con claims estándar
        $now = time();
        $payload = array_merge($payload, [
            'iat' => $now,                          // Issued at
            'exp' => $now + $this->expirationTime,  // Expiration
            'nbf' => $now,                          // Not before
            'jti' => $this->generateJti()           // JWT ID único
        ]);

        // Codificar header y payload
        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));

        // Crear firma
        $signature = $this->sign($headerEncoded . '.' . $payloadEncoded);

        // Retornar JWT completo
        return $headerEncoded . '.' . $payloadEncoded . '.' . $signature;
    }

    /**
     * Valida y decodifica un JWT
     */
    public function validateToken(string $token): ?array
    {
        try {
            $parts = explode('.', $token);

            if (count($parts) !== 3) {
                return null;
            }

            [$headerEncoded, $payloadEncoded, $signature] = $parts;

            // Verificar firma
            $expectedSignature = $this->sign($headerEncoded . '.' . $payloadEncoded);

            if (!hash_equals($expectedSignature, $signature)) {
                return null;
            }

            // Decodificar header y payload
            $header = json_decode($this->base64UrlDecode($headerEncoded), true);
            $payload = json_decode($this->base64UrlDecode($payloadEncoded), true);

            if (!$header || !$payload) {
                return null;
            }

            // Verificar algoritmo
            if ($header['alg'] !== $this->algorithm) {
                return null;
            }

            // Verificar expiración
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                return null;
            }

            // Verificar nbf (not before)
            if (isset($payload['nbf']) && $payload['nbf'] > time()) {
                return null;
            }

            return $payload;
        } catch (\Exception $e) {
            error_log('JWT Validation Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Refresca un token (genera uno nuevo si es válido)
     */
    public function refreshToken(string $token): ?string
    {
        $payload = $this->validateToken($token);

        if (!$payload) {
            return null;
        }

        // Remover claims de tiempo
        unset($payload['iat'], $payload['exp'], $payload['nbf'], $payload['jti']);

        // Generar nuevo token
        return $this->generateToken($payload);
    }

    /**
     * Extrae el token del header Authorization
     */
    public function extractTokenFromHeader(): ?string
    {
        $headers = $this->getAuthorizationHeader();

        if (!$headers) {
            return null;
        }

        // Formato: "Bearer {token}"
        if (preg_match('/Bearer\s+(.*)$/i', $headers, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Obtiene el header de autorización
     */
    private function getAuthorizationHeader(): ?string
    {
        $headers = null;

        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER['Authorization']);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(
                array_map('ucwords', array_keys($requestHeaders)),
                array_values($requestHeaders)
            );

            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }

        return $headers;
    }

    /**
     * Firma el contenido con HMAC-SHA256
     */
    private function sign(string $data): string
    {
        return $this->base64UrlEncode(
            hash_hmac('sha256', $data, $this->secretKey, true)
        );
    }

    /**
     * Codificación Base64 URL-safe
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Decodificación Base64 URL-safe
     */
    private function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * Genera un JWT ID único
     */
    private function generateJti(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Genera una clave secreta aleatoria
     */
    private function generateSecretKey(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Obtiene información del payload sin validar (usar con precaución)
     */
    public function getPayload(string $token): ?array
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return null;
            }

            return json_decode($this->base64UrlDecode($parts[1]), true);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Verifica si un token ha expirado
     */
    public function isExpired(string $token): bool
    {
        $payload = $this->getPayload($token);

        if (!$payload || !isset($payload['exp'])) {
            return true;
        }

        return $payload['exp'] < time();
    }

    /**
     * Obtiene el tiempo restante de un token en segundos
     */
    public function getTimeRemaining(string $token): int
    {
        $payload = $this->getPayload($token);

        if (!$payload || !isset($payload['exp'])) {
            return 0;
        }

        $remaining = $payload['exp'] - time();
        return max(0, $remaining);
    }
}
