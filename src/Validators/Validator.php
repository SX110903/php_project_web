<?php

namespace App\Validators;

/**
 * Validator Class
 * Validación y sanitización de datos de entrada
 */
class Validator
{
    private array $errors = [];
    private array $data = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Valida un campo requerido
     */
    public function required(string $field, string $message = null): self
    {
        if (!isset($this->data[$field]) || trim($this->data[$field]) === '') {
            $this->errors[$field] = $message ?? "El campo {$field} es requerido";
        }
        return $this;
    }

    /**
     * Valida formato de email
     */
    public function email(string $field, string $message = null): self
    {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message ?? "El email no es válido";
        }
        return $this;
    }

    /**
     * Valida longitud mínima
     */
    public function minLength(string $field, int $min, string $message = null): self
    {
        if (isset($this->data[$field]) && strlen($this->data[$field]) < $min) {
            $this->errors[$field] = $message ?? "El campo {$field} debe tener al menos {$min} caracteres";
        }
        return $this;
    }

    /**
     * Valida longitud máxima
     */
    public function maxLength(string $field, int $max, string $message = null): self
    {
        if (isset($this->data[$field]) && strlen($this->data[$field]) > $max) {
            $this->errors[$field] = $message ?? "El campo {$field} no debe exceder {$max} caracteres";
        }
        return $this;
    }

    /**
     * Valida que las contraseñas coincidan
     */
    public function matches(string $field, string $matchField, string $message = null): self
    {
        if (isset($this->data[$field]) && isset($this->data[$matchField]) &&
            $this->data[$field] !== $this->data[$matchField]) {
            $this->errors[$field] = $message ?? "Los campos no coinciden";
        }
        return $this;
    }

    /**
     * Valida patrón regex
     */
    public function pattern(string $field, string $pattern, string $message = null): self
    {
        if (isset($this->data[$field]) && !preg_match($pattern, $this->data[$field])) {
            $this->errors[$field] = $message ?? "El formato del campo {$field} no es válido";
        }
        return $this;
    }

    /**
     * Valida número entero
     */
    public function integer(string $field, string $message = null): self
    {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_INT)) {
            $this->errors[$field] = $message ?? "El campo {$field} debe ser un número entero";
        }
        return $this;
    }

    /**
     * Verifica si la validación pasó
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * Verifica si la validación falló
     */
    public function fails(): bool
    {
        return !$this->passes();
    }

    /**
     * Obtiene los errores
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Obtiene el primer error
     */
    public function getFirstError(): ?string
    {
        return !empty($this->errors) ? reset($this->errors) : null;
    }

    /**
     * Sanitiza un string (previene XSS)
     */
    public static function sanitizeString(string $value): string
    {
        return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitiza un email
     */
    public static function sanitizeEmail(string $email): string
    {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }

    /**
     * Sanitiza un array completo
     */
    public static function sanitizeArray(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = self::sanitizeArray($value);
            } elseif (is_string($value)) {
                $sanitized[$key] = self::sanitizeString($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }

    /**
     * Valida y sanitiza datos de entrada
     */
    public static function validate(array $data, array $rules): array
    {
        $sanitized = self::sanitizeArray($data);
        $validator = new self($sanitized);
        $errors = [];

        foreach ($rules as $field => $ruleSet) {
            $ruleArray = explode('|', $ruleSet);

            foreach ($ruleArray as $rule) {
                if (strpos($rule, ':') !== false) {
                    [$ruleName, $param] = explode(':', $rule, 2);
                } else {
                    $ruleName = $rule;
                    $param = null;
                }

                switch ($ruleName) {
                    case 'required':
                        $validator->required($field);
                        break;
                    case 'email':
                        $validator->email($field);
                        break;
                    case 'min':
                        $validator->minLength($field, (int)$param);
                        break;
                    case 'max':
                        $validator->maxLength($field, (int)$param);
                        break;
                }
            }
        }

        if ($validator->fails()) {
            throw new \InvalidArgumentException(json_encode($validator->getErrors()));
        }

        return $sanitized;
    }
}
