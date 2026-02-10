<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;

/**
 * Authentication Service
 * Maneja la lógica de autenticación de usuarios
 */
class AuthService
{
    private UserRepository $userRepository;
    private SecurityService $securityService;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
        $this->securityService = new SecurityService();
    }

    /**
     * Autentica un usuario
     */
    public function login(string $email, string $password): array
    {
        // Verificar rate limiting
        if (!$this->securityService->checkRateLimit($email)) {
            return [
                'success' => false,
                'message' => 'Demasiados intentos fallidos. Intente más tarde.'
            ];
        }

        // Buscar usuario por email
        $userData = $this->userRepository->findByEmail($email);

        if (!$userData) {
            $this->securityService->recordFailedLogin($email);
            return [
                'success' => false,
                'message' => 'Credenciales inválidas'
            ];
        }

        $user = new User($userData);

        // Verificar si el usuario está activo
        if (!$user->isActive()) {
            return [
                'success' => false,
                'message' => 'Usuario inactivo'
            ];
        }

        // Verificar contraseña
        if (!$user->verifyPassword($password)) {
            $this->securityService->recordFailedLogin($email);
            return [
                'success' => false,
                'message' => 'Credenciales inválidas'
            ];
        }

        // Login exitoso
        $this->securityService->clearFailedLogins($email);
        $this->userRepository->updateLastLogin($user->getId());

        // Obtener roles y permisos
        $userWithRoles = $this->userRepository->findByIdWithRoles($user->getId());
        $permissions = $this->userRepository->getUserPermissions($user->getId());

        // Crear sesión
        $this->createSession($userWithRoles, $permissions);

        return [
            'success' => true,
            'message' => 'Login exitoso',
            'user' => $user->toArray()
        ];
    }

    /**
     * Cierra la sesión del usuario
     */
    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
    }

    /**
     * Verifica si el usuario está autenticado
     */
    public function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']) && isset($_SESSION['user_email']);
    }

    /**
     * Obtiene el usuario actual
     */
    public function getCurrentUser(): ?User
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        $userData = $this->userRepository->findById($_SESSION['user_id']);

        if (!$userData) {
            return null;
        }

        $user = new User($userData);

        // Cargar roles y permisos desde la sesión
        if (isset($_SESSION['roles'])) {
            $user->setRoles($_SESSION['roles']);
        }
        if (isset($_SESSION['permissions'])) {
            $user->setPermissions($_SESSION['permissions']);
        }

        return $user;
    }

    /**
     * Registra un nuevo usuario
     */
    public function register(array $data): array
    {
        // Verificar si el email ya existe
        if ($this->userRepository->findByEmail($data['email'])) {
            return [
                'success' => false,
                'message' => 'El email ya está registrado'
            ];
        }

        // Hashear contraseña
        $data['password'] = User::hashPassword($data['password']);
        $data['is_active'] = 1;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        // Crear usuario
        $userId = $this->userRepository->create($data);

        // Asignar rol por defecto (user)
        $roleRepository = new \App\Repositories\RoleRepository();
        $userRole = $roleRepository->findByName('user');

        if ($userRole) {
            $this->userRepository->assignRole($userId, $userRole['id']);
        }

        return [
            'success' => true,
            'message' => 'Usuario registrado exitosamente',
            'user_id' => $userId
        ];
    }

    /**
     * Inicia el proceso de recuperación de contraseña
     */
    public function initiatePasswordReset(string $email): array
    {
        $userData = $this->userRepository->findByEmail($email);

        if (!$userData) {
            // Por seguridad, no revelamos si el email existe
            return [
                'success' => true,
                'message' => 'Si el email existe, recibirás instrucciones para restablecer tu contraseña'
            ];
        }

        // Generar token seguro
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Guardar token
        $this->userRepository->updateResetToken($userData['id'], $token, $expires);

        // Enviar email (implementar según tu sistema de emails)
        $this->sendPasswordResetEmail($email, $token);

        return [
            'success' => true,
            'message' => 'Si el email existe, recibirás instrucciones para restablecer tu contraseña'
        ];
    }

    /**
     * Restablece la contraseña con el token
     */
    public function resetPassword(string $token, string $newPassword): array
    {
        $userData = $this->userRepository->findByResetToken($token);

        if (!$userData) {
            return [
                'success' => false,
                'message' => 'Token inválido o expirado'
            ];
        }

        // Actualizar contraseña
        $hashedPassword = User::hashPassword($newPassword);
        $this->userRepository->updatePassword($userData['id'], $hashedPassword);

        // Limpiar token
        $this->userRepository->clearResetToken($userData['id']);

        return [
            'success' => true,
            'message' => 'Contraseña actualizada exitosamente'
        ];
    }

    /**
     * Crea la sesión del usuario
     */
    private function createSession(array $userData, array $permissions): void
    {
        // Regenerar ID de sesión para prevenir session fixation
        session_regenerate_id(true);

        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['user_email'] = $userData['email'];
        $_SESSION['user_name'] = $userData['name'];

        // Guardar roles
        if (!empty($userData['role_names'])) {
            $roleNames = explode(',', $userData['role_names']);
            $roleIds = explode(',', $userData['role_ids']);

            $_SESSION['roles'] = array_map(function($id, $name) {
                return ['id' => $id, 'name' => $name];
            }, $roleIds, $roleNames);
        } else {
            $_SESSION['roles'] = [];
        }

        // Guardar permisos
        $_SESSION['permissions'] = $permissions;

        $_SESSION['last_activity'] = time();
    }

    /**
     * Envía email de recuperación de contraseña
     */
    private function sendPasswordResetEmail(string $email, string $token): void
    {
        // Implementar según tu sistema de emails
        // Por ahora solo registramos en logs
        $config = require __DIR__ . '/../../config.php';
        $resetUrl = $config['app']['url'] . '/reset-password.php?token=' . $token;

        error_log("Password reset URL for {$email}: {$resetUrl}");

        // Aquí deberías enviar el email real
        // mail($email, "Password Reset", "Reset URL: " . $resetUrl);
    }
}
