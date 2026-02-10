<?php

namespace App\Controllers;

use App\Services\AuthorizationService;
use App\Repositories\UserRepository;
use App\Validators\Validator;

/**
 * API Controller
 * Maneja endpoints de la API REST
 */
class ApiController extends BaseController
{
    private AuthorizationService $authorizationService;
    private UserRepository $userRepository;

    public function __construct()
    {
        parent::__construct();
        $this->authorizationService = new AuthorizationService();
        $this->userRepository = new UserRepository();

        // Asegurar que las respuestas sean JSON
        header('Content-Type: application/json');
    }

    /**
     * Obtiene información del usuario actual
     */
    public function me(): void
    {
        $this->requireAuth();

        $user = $this->authService->getCurrentUser();

        if (!$user) {
            $this->json(['error' => 'Usuario no encontrado'], 404);
        }

        $this->json([
            'success' => true,
            'data' => [
                'user' => $user->toArray(),
                'roles' => $user->getRoles(),
                'permissions' => $user->getPermissions()
            ]
        ]);
    }

    /**
     * Lista todos los usuarios (solo admin)
     */
    public function listUsers(): void
    {
        $this->requireAuth();

        $user = $this->authService->getCurrentUser();

        if (!$this->authorizationService->isAdmin($user)) {
            $this->json(['error' => 'Acceso denegado'], 403);
        }

        $queryData = $this->getQueryData();
        $limit = isset($queryData['limit']) ? (int)$queryData['limit'] : 50;
        $offset = isset($queryData['offset']) ? (int)$queryData['offset'] : 0;

        $users = $this->userRepository->findAll($limit, $offset);
        $total = $this->userRepository->count();

        // Remover contraseñas de la respuesta
        $users = array_map(function($user) {
            unset($user['password']);
            unset($user['reset_token']);
            unset($user['reset_token_expires']);
            return $user;
        }, $users);

        $this->json([
            'success' => true,
            'data' => [
                'users' => $users,
                'pagination' => [
                    'total' => $total,
                    'limit' => $limit,
                    'offset' => $offset
                ]
            ]
        ]);
    }

    /**
     * Obtiene un usuario por ID (solo admin)
     */
    public function getUser(int $id): void
    {
        $this->requireAuth();

        $currentUser = $this->authService->getCurrentUser();

        // Puede ver su propio perfil o ser admin
        if ($currentUser->getId() !== $id && !$this->authorizationService->isAdmin($currentUser)) {
            $this->json(['error' => 'Acceso denegado'], 403);
        }

        $userData = $this->userRepository->findByIdWithRoles($id);

        if (!$userData) {
            $this->json(['error' => 'Usuario no encontrado'], 404);
        }

        unset($userData['password']);
        unset($userData['reset_token']);
        unset($userData['reset_token_expires']);

        $permissions = $this->userRepository->getUserPermissions($id);

        $this->json([
            'success' => true,
            'data' => [
                'user' => $userData,
                'permissions' => $permissions
            ]
        ]);
    }

    /**
     * Actualiza perfil del usuario
     */
    public function updateProfile(): void
    {
        $this->requireAuth();

        if (!$this->verifyCsrfToken()) {
            $this->json(['error' => 'Token CSRF inválido'], 403);
        }

        $currentUser = $this->authService->getCurrentUser();
        $data = $this->getPostData();

        // Validar datos
        $validator = new Validator($data);
        $validator
            ->required('name', 'El nombre es requerido')
            ->minLength('name', 3, 'El nombre debe tener al menos 3 caracteres')
            ->maxLength('name', 100, 'El nombre no puede exceder 100 caracteres');

        if (isset($data['email'])) {
            $validator->email('email', 'Email inválido');
        }

        if ($validator->fails()) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
        }

        // Actualizar solo campos permitidos
        $updateData = [
            'name' => $data['name'],
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $result = $this->userRepository->update($currentUser->getId(), $updateData);

        if ($result) {
            $this->json([
                'success' => true,
                'message' => 'Perfil actualizado exitosamente'
            ]);
        } else {
            $this->json([
                'success' => false,
                'error' => 'Error al actualizar perfil'
            ], 500);
        }
    }

    /**
     * Cambia contraseña del usuario
     */
    public function changePassword(): void
    {
        $this->requireAuth();

        if (!$this->verifyCsrfToken()) {
            $this->json(['error' => 'Token CSRF inválido'], 403);
        }

        $currentUser = $this->authService->getCurrentUser();
        $data = $this->getPostData();

        // Validar datos
        $validator = new Validator($data);
        $validator
            ->required('current_password', 'La contraseña actual es requerida')
            ->required('new_password', 'La nueva contraseña es requerida')
            ->minLength('new_password', 8, 'La contraseña debe tener al menos 8 caracteres')
            ->required('confirm_password', 'Confirma la nueva contraseña')
            ->matches('confirm_password', 'new_password', 'Las contraseñas no coinciden');

        if ($validator->fails()) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
        }

        // Verificar contraseña actual
        if (!$currentUser->verifyPassword($data['current_password'])) {
            $this->json([
                'success' => false,
                'error' => 'Contraseña actual incorrecta'
            ], 400);
        }

        // Actualizar contraseña
        $hashedPassword = \App\Models\User::hashPassword($data['new_password']);
        $result = $this->userRepository->updatePassword($currentUser->getId(), $hashedPassword);

        if ($result) {
            $this->json([
                'success' => true,
                'message' => 'Contraseña actualizada exitosamente'
            ]);
        } else {
            $this->json([
                'success' => false,
                'error' => 'Error al actualizar contraseña'
            ], 500);
        }
    }

    /**
     * Health check endpoint
     */
    public function healthCheck(): void
    {
        $this->json([
            'status' => 'ok',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0.0'
        ]);
    }
}
