<?php

namespace App\Controllers;

use App\Services\JwtService;
use App\Services\AuthService;
use App\Validators\Validator;

/**
 * JWT Auth Controller
 * Maneja autenticación basada en JWT para la API
 */
class JwtAuthController
{
    private JwtService $jwtService;
    private AuthService $authService;

    public function __construct()
    {
        $this->jwtService = new JwtService();
        $this->authService = new AuthService();

        header('Content-Type: application/json');
    }

    /**
     * Login con JWT
     * POST /api/auth/login
     */
    public function login(): void
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data) {
            $this->json(['error' => 'Datos inválidos'], 400);
        }

        // Sanitizar datos
        $data = Validator::sanitizeArray($data);

        // Validar datos
        $validator = new Validator($data);
        $validator
            ->required('email', 'El email es requerido')
            ->email('email', 'El email no es válido')
            ->required('password', 'La contraseña es requerida');

        if ($validator->fails()) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
        }

        // Intentar autenticación
        $result = $this->authService->login($data['email'], $data['password']);

        if (!$result['success']) {
            $this->json([
                'success' => false,
                'message' => $result['message']
            ], 401);
        }

        // Generar JWT
        $user = $result['user'];
        $token = $this->jwtService->generateToken([
            'user_id' => $user['id'],
            'email' => $user['email'],
            'name' => $user['name']
        ]);

        // Generar refresh token
        $refreshToken = $this->jwtService->generateToken([
            'user_id' => $user['id'],
            'type' => 'refresh'
        ]);

        $this->json([
            'success' => true,
            'message' => 'Login exitoso',
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer',
                'expires_in' => 3600
            ]
        ]);
    }

    /**
     * Registro con JWT
     * POST /api/auth/register
     */
    public function register(): void
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data) {
            $this->json(['error' => 'Datos inválidos'], 400);
        }

        // Sanitizar datos
        $data = Validator::sanitizeArray($data);

        // Validar datos
        $validator = new Validator($data);
        $validator
            ->required('name', 'El nombre es requerido')
            ->minLength('name', 3, 'El nombre debe tener al menos 3 caracteres')
            ->required('email', 'El email es requerido')
            ->email('email', 'El email no es válido')
            ->required('password', 'La contraseña es requerida')
            ->minLength('password', 8, 'La contraseña debe tener al menos 8 caracteres');

        if ($validator->fails()) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
        }

        // Registrar usuario
        $result = $this->authService->register([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password']
        ]);

        if (!$result['success']) {
            $this->json([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }

        $this->json([
            'success' => true,
            'message' => 'Usuario registrado exitosamente',
            'data' => [
                'user_id' => $result['user_id']
            ]
        ], 201);
    }

    /**
     * Refrescar token
     * POST /api/auth/refresh
     */
    public function refresh(): void
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data || !isset($data['refresh_token'])) {
            $this->json([
                'error' => 'Refresh token no proporcionado'
            ], 400);
        }

        $newToken = $this->jwtService->refreshToken($data['refresh_token']);

        if (!$newToken) {
            $this->json([
                'error' => 'Refresh token inválido o expirado'
            ], 401);
        }

        $this->json([
            'success' => true,
            'data' => [
                'access_token' => $newToken,
                'token_type' => 'Bearer',
                'expires_in' => 3600
            ]
        ]);
    }

    /**
     * Verificar token
     * GET /api/auth/verify
     */
    public function verify(): void
    {
        $token = $this->jwtService->extractTokenFromHeader();

        if (!$token) {
            $this->json([
                'valid' => false,
                'message' => 'No se proporcionó token'
            ], 401);
        }

        $payload = $this->jwtService->validateToken($token);

        if (!$payload) {
            $this->json([
                'valid' => false,
                'message' => 'Token inválido o expirado'
            ], 401);
        }

        $this->json([
            'valid' => true,
            'data' => [
                'user_id' => $payload['user_id'] ?? null,
                'email' => $payload['email'] ?? null,
                'expires_at' => $payload['exp'] ?? null,
                'time_remaining' => $this->jwtService->getTimeRemaining($token)
            ]
        ]);
    }

    /**
     * Retorna respuesta JSON
     */
    private function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
}
