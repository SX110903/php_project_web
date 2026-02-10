# Guía de Seguridad

Este documento describe las medidas de seguridad implementadas y las mejores prácticas para mantener la aplicación segura.

## Medidas de Seguridad Implementadas

### 1. Protección de Contraseñas

#### Hasheo con Argon2ID
- **Algoritmo**: Argon2ID (ganador del Password Hashing Competition)
- **Configuración**:
  - Memory cost: 65536 KB (64 MB)
  - Time cost: 4 iteraciones
  - Parallelism: 3 threads
- **Salt**: Generado automáticamente por PHP
- **Ubicación**: [src/Models/User.php](src/Models/User.php:162-169)

```php
password_hash($password, PASSWORD_ARGON2ID, [
    'memory_cost' => 65536,
    'time_cost' => 4,
    'threads' => 3
]);
```

### 2. Protección SQL Injection

#### Prepared Statements
- **Todas** las consultas usan prepared statements
- **Nunca** se concatena SQL con valores de usuario
- Implementado en [src/Repositories/BaseRepository.php](src/Repositories/BaseRepository.php:1-158)

**Ejemplo seguro**:
```php
$stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute(['email' => $email]);
```

**❌ NUNCA hacer**:
```php
$query = "SELECT * FROM users WHERE email = '" . $email . "'";
```

### 3. Protección XSS (Cross-Site Scripting)

#### Sanitización de Entrada
- Todo input es sanitizado en [src/Validators/Validator.php](src/Validators/Validator.php:106-109)
- Uso de `htmlspecialchars()` con `ENT_QUOTES`
- Validación de patrones peligrosos

#### Sanitización de Salida
- Todas las vistas usan `htmlspecialchars()` para output
- Content-Security-Policy headers configurados
- Implementado en [src/Services/SecurityService.php](src/Services/SecurityService.php:123-126)

### 4. Protección CSRF (Cross-Site Request Forgery)

#### Tokens CSRF
- Token único por sesión
- Verificado en todos los POST, PUT, DELETE
- Generación: [src/Services/SecurityService.php](src/Services/SecurityService.php:64-72)
- Validación: [src/Services/SecurityService.php](src/Services/SecurityService.php:77-87)

**Uso en formularios**:
```php
<input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
```

**Verificación en controlador**:
```php
if (!$this->verifyCsrfToken()) {
    // Rechazar petición
}
```

### 5. Protección de Sesiones

#### Configuración Segura
- `session.cookie_httponly = 1` (no accesible por JavaScript)
- `session.use_only_cookies = 1` (solo cookies, no URL)
- `session.cookie_samesite = Strict` (protección CSRF)
- `session.cookie_secure = 1` (solo HTTPS en producción)
- Regeneración de ID cada 5 minutos
- Validación de User-Agent (prevención de hijacking)
- Implementado en [autoload.php](autoload.php:23-41)

#### Session Fixation
- ID regenerado en login exitoso
- ID regenerado periódicamente
- Sesión destruida en logout

### 6. Rate Limiting

#### Protección Brute Force
- Máximo 5 intentos de login fallidos
- Lockout de 15 minutos después del límite
- Rate limiting de 100 req/min por IP
- Implementado en [src/Middleware/RateLimitMiddleware.php](src/Middleware/RateLimitMiddleware.php:1-90)

### 7. Headers de Seguridad

Headers automáticamente establecidos en [src/Services/SecurityService.php](src/Services/SecurityService.php:92-111):

```
X-Frame-Options: DENY
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: geolocation=(), microphone=(), camera=()
Content-Security-Policy: default-src 'self'; ...
```

### 8. Validación de Entrada

#### Frontend
- Validación en tiempo real con JavaScript
- Prevención de caracteres peligrosos
- Implementado en [public/js/validation.js](public/js/validation.js:1-265)

#### Backend
- Validación robusta en servidor
- No se confía en validación del cliente
- Implementado en [src/Validators/Validator.php](src/Validators/Validator.php:1-212)

## Vulnerabilidades OWASP Top 10

### A01:2021 - Broken Access Control
**Protección implementada**:
- Sistema RBAC (Role-Based Access Control)
- Verificación de permisos en cada endpoint
- Middleware de autenticación y autorización

### A02:2021 - Cryptographic Failures
**Protección implementada**:
- Argon2ID para contraseñas
- Tokens aleatorios criptográficamente seguros
- Sesiones con configuración segura

### A03:2021 - Injection
**Protección implementada**:
- Prepared statements (SQL Injection)
- Sanitización de entrada (XSS, Command Injection)
- Validación de tipos de datos

### A04:2021 - Insecure Design
**Protección implementada**:
- Arquitectura basada en patrones de diseño
- Separación de responsabilidades (MVC)
- Principio de menor privilegio

### A05:2021 - Security Misconfiguration
**Protección implementada**:
- Headers de seguridad configurados
- Errores de PHP ocultos en producción
- Archivos sensibles protegidos

### A06:2021 - Vulnerable Components
**Protección implementada**:
- Código sin dependencias externas vulnerables
- PHP 8+ con últimas características de seguridad
- Actualización regular recomendada

### A07:2021 - Authentication Failures
**Protección implementada**:
- Contraseñas fuertes requeridas
- Rate limiting en login
- Sesiones seguras
- Logout apropiado

### A08:2021 - Software/Data Integrity
**Protección implementada**:
- Validación de todos los inputs
- CSRF tokens
- Transacciones de base de datos

### A09:2021 - Logging Failures
**Protección implementada**:
- Error logging configurado
- Logs de intentos fallidos de login
- No se expone información sensible en logs

### A10:2021 - Server-Side Request Forgery (SSRF)
**Protección implementada**:
- No se realizan requests externos con input de usuario
- Validación de URLs si se implementa

## Checklist de Seguridad para Producción

### Antes de Desplegar

- [ ] Cambiar `'env' => 'production'` en [config.php](config.php:13)
- [ ] Cambiar `'debug' => false` en [config.php](config.php:14)
- [ ] Cambiar todas las contraseñas por defecto
- [ ] Configurar `session.cookie_secure = 1`
- [ ] Habilitar HTTPS (Let's Encrypt gratuito)
- [ ] Configurar backups automáticos
- [ ] Revisar permisos de archivos (755 directorios, 644 archivos)
- [ ] Deshabilitar listado de directorios
- [ ] Configurar firewall (UFW/iptables)
- [ ] Limitar acceso SSH (solo key-based)
- [ ] Actualizar PHP y MySQL a últimas versiones
- [ ] Configurar logs de acceso y errores
- [ ] Implementar monitoreo (opcional: New Relic, Datadog)

### Mantenimiento Regular

- [ ] Revisar logs semanalmente
- [ ] Actualizar PHP mensualmente
- [ ] Revisar intentos de login fallidos
- [ ] Verificar integridad de archivos
- [ ] Probar restauración de backups
- [ ] Revisar usuarios activos
- [ ] Auditar permisos asignados

## Reporte de Vulnerabilidades

Si encuentras una vulnerabilidad de seguridad:

1. **NO** crear issue público
2. Enviar email privado con detalles
3. Incluir pasos para reproducir
4. Esperar respuesta en 48 horas

## Mejores Prácticas de Uso

### Para Desarrolladores

1. **Nunca** confiar en entrada de usuario
2. **Siempre** validar en backend
3. **Nunca** exponer información sensible en errores
4. **Siempre** usar prepared statements
5. **Nunca** logear contraseñas o tokens
6. **Siempre** sanitizar output HTML

### Para Administradores

1. Usar contraseñas únicas y fuertes (min 12 caracteres)
2. Habilitar 2FA cuando esté disponible
3. Revisar usuarios y permisos regularmente
4. Mantener sistema actualizado
5. Monitorear logs de acceso
6. Realizar backups regulares

## Recursos Adicionales

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [OWASP Cheat Sheets](https://cheatsheetseries.owasp.org/)
- [PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html)
- [MySQL Security Guide](https://dev.mysql.com/doc/refman/8.0/en/security.html)

## Auditorías de Seguridad

Se recomienda realizar auditorías de seguridad periódicas:

```bash
# Escaneo de vulnerabilidades PHP
composer require --dev roave/security-advisories:dev-latest

# Análisis estático de código
composer require --dev phpstan/phpstan
./vendor/bin/phpstan analyse src/

# Pruebas de penetración
# Contratar empresa especializada anualmente
```

## Actualizaciones de Seguridad

Mantener registro de parches de seguridad aplicados:

| Fecha | Versión | Descripción | Severidad |
|-------|---------|-------------|-----------|
| - | 1.0.0 | Versión inicial | - |

---

**Última actualización**: 2024
**Próxima revisión**: Trimestral
