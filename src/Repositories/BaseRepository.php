<?php

namespace App\Repositories;

use PDO;
use App\Config\Database;

/**
 * Base Repository - Repository Pattern
 * Clase base para todos los repositorios con operaciones CRUD seguras
 */
abstract class BaseRepository
{
    protected PDO $db;
    protected string $table;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Encuentra un registro por ID
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Encuentra todos los registros
     */
    public function findAll(int $limit = 100, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->table} LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Encuentra registros por condición
     */
    public function findBy(array $criteria, int $limit = 100): array
    {
        $conditions = [];
        $params = [];

        foreach ($criteria as $key => $value) {
            $conditions[] = "{$key} = :{$key}";
            $params[$key] = $value;
        }

        $whereClause = implode(' AND ', $conditions);
        $sql = "SELECT * FROM {$this->table} WHERE {$whereClause} LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Encuentra un registro por condición
     */
    public function findOneBy(array $criteria): ?array
    {
        $result = $this->findBy($criteria, 1);
        return $result[0] ?? null;
    }

    /**
     * Crea un nuevo registro
     */
    public function create(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":{$col}", $columns);

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Actualiza un registro
     */
    public function update(int $id, array $data): bool
    {
        $setClause = [];
        foreach (array_keys($data) as $column) {
            $setClause[] = "{$column} = :{$column}";
        }

        $sql = sprintf(
            "UPDATE %s SET %s WHERE id = :id",
            $this->table,
            implode(', ', $setClause)
        );

        $data['id'] = $id;
        $stmt = $this->db->prepare($sql);

        return $stmt->execute($data);
    }

    /**
     * Elimina un registro
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute(['id' => $id]);
    }

    /**
     * Cuenta registros
     */
    public function count(array $criteria = []): int
    {
        if (empty($criteria)) {
            $sql = "SELECT COUNT(*) as total FROM {$this->table}";
            $stmt = $this->db->query($sql);
        } else {
            $conditions = [];
            $params = [];

            foreach ($criteria as $key => $value) {
                $conditions[] = "{$key} = :{$key}";
                $params[$key] = $value;
            }

            $whereClause = implode(' AND ', $conditions);
            $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE {$whereClause}";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
        }

        return (int) $stmt->fetch()['total'];
    }

    /**
     * Verifica si existe un registro
     */
    public function exists(array $criteria): bool
    {
        return $this->count($criteria) > 0;
    }
}
