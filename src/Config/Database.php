<?php

namespace App\Config;

use PDO;
use PDOException;

/**
 * Database Connection - Singleton Pattern
 * Gestiona la conexión única a la base de datos
 */
class Database
{
    private static ?Database $instance = null;
    private ?PDO $connection = null;

    private string $host;
    private string $dbname;
    private string $username;
    private string $password;
    private string $charset = 'utf8mb4';

    /**
     * Constructor privado para implementar Singleton
     */
    private function __construct()
    {
        $config = require __DIR__ . '/../../config.php';

        $this->host = $config['database']['host'];
        $this->dbname = $config['database']['dbname'];
        $this->username = $config['database']['username'];
        $this->password = $config['database']['password'];

        $this->connect();
    }

    /**
     * Evita la clonación del objeto
     */
    private function __clone() {}

    /**
     * Evita la deserialización del objeto
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }

    /**
     * Obtiene la instancia única de Database
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Establece la conexión con la base de datos
     */
    private function connect(): void
    {
        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false,
        ];

        try {
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            throw new \Exception("Database connection failed");
        }
    }

    /**
     * Obtiene la conexión PDO
     */
    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            $this->connect();
        }

        return $this->connection;
    }

    /**
     * Inicia una transacción
     */
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Confirma una transacción
     */
    public function commit(): bool
    {
        return $this->connection->commit();
    }

    /**
     * Revierte una transacción
     */
    public function rollback(): bool
    {
        return $this->connection->rollBack();
    }
}
