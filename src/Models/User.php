<?php

namespace App\Models;

/**
 * User Model
 * Representa un usuario del sistema
 */
class User
{
    private ?int $id = null;
    private string $email;
    private string $password;
    private string $name;
    private bool $isActive = true;
    private ?string $resetToken = null;
    private ?string $resetTokenExpires = null;
    private ?string $lastLogin = null;
    private ?string $createdAt = null;
    private ?string $updatedAt = null;

    private array $roles = [];
    private array $permissions = [];

    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->hydrate($data);
        }
    }

    /**
     * Hidrata el modelo con datos
     */
    public function hydrate(array $data): void
    {
        if (isset($data['id'])) {
            $this->id = (int) $data['id'];
        }
        if (isset($data['email'])) {
            $this->email = $data['email'];
        }
        if (isset($data['password'])) {
            $this->password = $data['password'];
        }
        if (isset($data['name'])) {
            $this->name = $data['name'];
        }
        if (isset($data['is_active'])) {
            $this->isActive = (bool) $data['is_active'];
        }
        if (isset($data['reset_token'])) {
            $this->resetToken = $data['reset_token'];
        }
        if (isset($data['reset_token_expires'])) {
            $this->resetTokenExpires = $data['reset_token_expires'];
        }
        if (isset($data['last_login'])) {
            $this->lastLogin = $data['last_login'];
        }
        if (isset($data['created_at'])) {
            $this->createdAt = $data['created_at'];
        }
        if (isset($data['updated_at'])) {
            $this->updatedAt = $data['updated_at'];
        }
    }

    /**
     * Convierte el modelo a array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'is_active' => $this->isActive,
            'last_login' => $this->lastLogin,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getEmail(): string { return $this->email; }
    public function getPassword(): string { return $this->password; }
    public function getName(): string { return $this->name; }
    public function isActive(): bool { return $this->isActive; }
    public function getResetToken(): ?string { return $this->resetToken; }
    public function getResetTokenExpires(): ?string { return $this->resetTokenExpires; }
    public function getLastLogin(): ?string { return $this->lastLogin; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }
    public function getRoles(): array { return $this->roles; }
    public function getPermissions(): array { return $this->permissions; }

    // Setters
    public function setId(?int $id): void { $this->id = $id; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function setPassword(string $password): void { $this->password = $password; }
    public function setName(string $name): void { $this->name = $name; }
    public function setIsActive(bool $isActive): void { $this->isActive = $isActive; }
    public function setResetToken(?string $token): void { $this->resetToken = $token; }
    public function setResetTokenExpires(?string $expires): void { $this->resetTokenExpires = $expires; }
    public function setLastLogin(?string $lastLogin): void { $this->lastLogin = $lastLogin; }
    public function setRoles(array $roles): void { $this->roles = $roles; }
    public function setPermissions(array $permissions): void { $this->permissions = $permissions; }

    /**
     * Verifica si el usuario tiene un rol específico
     */
    public function hasRole(string $roleName): bool
    {
        foreach ($this->roles as $role) {
            if ($role['name'] === $roleName) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verifica si el usuario tiene un permiso específico
     */
    public function hasPermission(string $permissionName): bool
    {
        foreach ($this->permissions as $permission) {
            if ($permission['name'] === $permissionName) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verifica la contraseña
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    /**
     * Hash de contraseña
     */
    public static function hashPassword(string $password): string
    {
        $config = require __DIR__ . '/../../config.php';

        return password_hash(
            $password,
            $config['security']['password_algo'],
            $config['security']['password_options']
        );
    }
}
