<?php

namespace App\Services;

use App\Models\User;

/**
 * Authorization Service
 * Maneja la autorización y permisos de usuarios - Strategy Pattern
 */
class AuthorizationService
{
    /**
     * Verifica si el usuario tiene un rol específico
     */
    public function hasRole(User $user, string $roleName): bool
    {
        return $user->hasRole($roleName);
    }

    /**
     * Verifica si el usuario tiene alguno de los roles especificados
     */
    public function hasAnyRole(User $user, array $roles): bool
    {
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verifica si el usuario tiene todos los roles especificados
     */
    public function hasAllRoles(User $user, array $roles): bool
    {
        foreach ($roles as $role) {
            if (!$user->hasRole($role)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Verifica si el usuario tiene un permiso específico
     */
    public function hasPermission(User $user, string $permissionName): bool
    {
        return $user->hasPermission($permissionName);
    }

    /**
     * Verifica si el usuario tiene alguno de los permisos especificados
     */
    public function hasAnyPermission(User $user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verifica si el usuario tiene todos los permisos especificados
     */
    public function hasAllPermissions(User $user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$user->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Verifica si el usuario puede acceder a un recurso
     */
    public function canAccess(User $user, string $resource, string $action = 'view'): bool
    {
        $permissionName = "{$resource}.{$action}";
        return $this->hasPermission($user, $permissionName);
    }

    /**
     * Verifica si el usuario es administrador
     */
    public function isAdmin(User $user): bool
    {
        return $this->hasRole($user, 'admin');
    }

    /**
     * Obtiene todos los permisos del usuario
     */
    public function getUserPermissions(User $user): array
    {
        return $user->getPermissions();
    }

    /**
     * Obtiene todos los roles del usuario
     */
    public function getUserRoles(User $user): array
    {
        return $user->getRoles();
    }
}
