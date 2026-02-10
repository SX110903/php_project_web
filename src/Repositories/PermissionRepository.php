<?php

namespace App\Repositories;

/**
 * Permission Repository
 * Maneja operaciones de permisos
 */
class PermissionRepository extends BaseRepository
{
    protected string $table = 'permissions';

    /**
     * Encuentra un permiso por nombre
     */
    public function findByName(string $name): ?array
    {
        return $this->findOneBy(['name' => $name]);
    }

    /**
     * Obtiene permisos de un rol
     */
    public function getRolePermissions(int $roleId): array
    {
        $sql = "SELECT p.*
                FROM permissions p
                INNER JOIN role_permissions rp ON p.id = rp.permission_id
                WHERE rp.role_id = :role_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['role_id' => $roleId]);

        return $stmt->fetchAll();
    }
}
