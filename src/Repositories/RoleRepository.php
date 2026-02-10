<?php

namespace App\Repositories;

/**
 * Role Repository
 * Maneja operaciones de roles
 */
class RoleRepository extends BaseRepository
{
    protected string $table = 'roles';

    /**
     * Encuentra un rol por nombre
     */
    public function findByName(string $name): ?array
    {
        return $this->findOneBy(['name' => $name]);
    }

    /**
     * Obtiene roles de un usuario
     */
    public function getUserRoles(int $userId): array
    {
        $sql = "SELECT r.*
                FROM roles r
                INNER JOIN user_roles ur ON r.id = ur.role_id
                WHERE ur.user_id = :user_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    /**
     * Obtiene un rol con sus permisos
     */
    public function findByIdWithPermissions(int $id): ?array
    {
        $sql = "SELECT r.*,
                       GROUP_CONCAT(DISTINCT p.id) as permission_ids,
                       GROUP_CONCAT(DISTINCT p.name) as permission_names
                FROM roles r
                LEFT JOIN role_permissions rp ON r.id = rp.role_id
                LEFT JOIN permissions p ON rp.permission_id = p.id
                WHERE r.id = :id
                GROUP BY r.id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Asigna un permiso a un rol
     */
    public function assignPermission(int $roleId, int $permissionId): bool
    {
        $sql = "INSERT INTO role_permissions (role_id, permission_id)
                VALUES (:role_id, :permission_id)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'role_id' => $roleId,
            'permission_id' => $permissionId
        ]);
    }

    /**
     * Remueve un permiso de un rol
     */
    public function removePermission(int $roleId, int $permissionId): bool
    {
        $sql = "DELETE FROM role_permissions
                WHERE role_id = :role_id AND permission_id = :permission_id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'role_id' => $roleId,
            'permission_id' => $permissionId
        ]);
    }
}
