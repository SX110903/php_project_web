<?php

namespace App\Repositories;

use PDO;

/**
 * User Repository
 * Maneja operaciones específicas de usuarios
 */
class UserRepository extends BaseRepository
{
    protected string $table = 'users';

    /**
     * Encuentra un usuario por email
     */
    public function findByEmail(string $email): ?array
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * Encuentra un usuario por token de recuperación
     */
    public function findByResetToken(string $token): ?array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE reset_token = :token
                AND reset_token_expires > NOW()
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['token' => $token]);

        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Obtiene un usuario con sus roles y permisos
     */
    public function findByIdWithRoles(int $id): ?array
    {
        $sql = "SELECT u.*,
                       GROUP_CONCAT(DISTINCT r.id) as role_ids,
                       GROUP_CONCAT(DISTINCT r.name) as role_names
                FROM users u
                LEFT JOIN user_roles ur ON u.id = ur.user_id
                LEFT JOIN roles r ON ur.role_id = r.id
                WHERE u.id = :id
                GROUP BY u.id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Obtiene los permisos de un usuario
     */
    public function getUserPermissions(int $userId): array
    {
        $sql = "SELECT DISTINCT p.name, p.description
                FROM permissions p
                INNER JOIN role_permissions rp ON p.id = rp.permission_id
                INNER JOIN user_roles ur ON rp.role_id = ur.role_id
                WHERE ur.user_id = :user_id
                ORDER BY p.name";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    /**
     * Asigna un rol a un usuario
     */
    public function assignRole(int $userId, int $roleId): bool
    {
        $sql = "INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'user_id' => $userId,
            'role_id' => $roleId
        ]);
    }

    /**
     * Remueve un rol de un usuario
     */
    public function removeRole(int $userId, int $roleId): bool
    {
        $sql = "DELETE FROM user_roles WHERE user_id = :user_id AND role_id = :role_id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'user_id' => $userId,
            'role_id' => $roleId
        ]);
    }

    /**
     * Actualiza el token de recuperación de contraseña
     */
    public function updateResetToken(int $userId, string $token, string $expires): bool
    {
        return $this->update($userId, [
            'reset_token' => $token,
            'reset_token_expires' => $expires
        ]);
    }

    /**
     * Limpia el token de recuperación
     */
    public function clearResetToken(int $userId): bool
    {
        return $this->update($userId, [
            'reset_token' => null,
            'reset_token_expires' => null
        ]);
    }

    /**
     * Actualiza la contraseña del usuario
     */
    public function updatePassword(int $userId, string $hashedPassword): bool
    {
        return $this->update($userId, [
            'password' => $hashedPassword,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Actualiza el último login
     */
    public function updateLastLogin(int $userId): bool
    {
        return $this->update($userId, [
            'last_login' => date('Y-m-d H:i:s')
        ]);
    }
}
