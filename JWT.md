# Guía de JWT (JSON Web Tokens)

Documentación completa de la implementación de JWT para autenticación de API.

## ¿Qué es JWT?

JSON Web Token (JWT) es un estándar abierto (RFC 7519) para transmitir información de forma segura entre partes como un objeto JSON. Es comúnmente usado para autenticación y intercambio de información.

## Implementación

Nuestra implementación JWT es **completamente nativa** sin dependencias externas, implementada en [src/Services/JwtService.php](src/Services/JwtService.php:1-230).

### Características

✅ **Sin dependencias externas**: Código puro PHP
✅ **Algoritmo HS256**: HMAC con SHA-256
✅ **Claims estándar**: iat, exp, nbf, jti
✅ **Refresh tokens**: Sistema de renovación
✅ **Middleware**: Protección automática de rutas
✅ **Extracción de headers**: Bearer token support

## Estructura de un JWT

Un JWT consta de tres partes separadas por puntos (.):

```
header.payload.signature
```

### Header
```json
{
  "typ": "JWT",
  "alg": "HS256"
}
```

### Payload (Claims)
```json
{
  "user_id": 1,
  "email": "user@example.com",
  "name": "Juan Pérez",
  "iat": 1640000000,    // Issued At
  "exp": 1640003600,    // Expiration Time (1h después)
  "nbf": 1640000000,    // Not Before
  "jti": "unique-id"    // JWT ID
}
```

### Signature
```
HMACSHA256(
  base64UrlEncode(header) + "." +
  base64UrlEncode(payload),
  secret_key
)
```

## Configuración

En [config.php](config.php:41-47):

```php
'jwt' => [
    'secret_key' => 'tu-clave-secreta-muy-segura',  // ⚠️ Cambiar en producción
    'expiration' => 3600,           // 1 hora (access token)
    'refresh_expiration' => 604800, // 7 días (refresh token)
    'algorithm' => 'HS256',
    'issuer' => 'secure-app',
],
```

### Generar Secret Key

```php
<?php
echo bin2hex(random_bytes(32));
// Output: 3f8c7a9e5d2b1f6a4e8c9d7b3a5f2e1c8d4b6a9e7f3c5d1a2b8e6f4c9a7d3b5e
```

O desde terminal:
```bash
php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"
```

## Endpoints de API

### 1. Login con JWT

**Endpoint**: `POST /api.php?path=auth/login`

**Request**:
```json
{
  "email": "user@example.com",
  "password": "Password123!"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Login exitoso",
  "data": {
    "user": {
      "id": 1,
      "email": "user@example.com",
      "name": "Juan Pérez"
    },
    "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "refresh_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "token_type": "Bearer",
    "expires_in": 3600
  }
}
```

### 2. Registro

**Endpoint**: `POST /api.php?path=auth/register`

**Request**:
```json
{
  "name": "Juan Pérez",
  "email": "juan@example.com",
  "password": "Password123!"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Usuario registrado exitosamente",
  "data": {
    "user_id": 2
  }
}
```

### 3. Refresh Token

**Endpoint**: `POST /api.php?path=auth/refresh`

**Request**:
```json
{
  "refresh_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

**Response**:
```json
{
  "success": true,
  "data": {
    "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "token_type": "Bearer",
    "expires_in": 3600
  }
}
```

### 4. Verificar Token

**Endpoint**: `GET /api.php?path=auth/verify`
**Headers**: `Authorization: Bearer <token>`

**Response**:
```json
{
  "valid": true,
  "data": {
    "user_id": 1,
    "email": "user@example.com",
    "expires_at": 1640003600,
    "time_remaining": 3456
  }
}
```

### 5. Endpoints Protegidos

Requieren header de autorización:

**Request Headers**:
```
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

**Endpoints**:
- `GET /api.php?path=me` - Información del usuario actual
- `GET /api.php?path=users` - Listar usuarios (admin)
- `GET /api.php?path=user&id=1` - Usuario específico
- `POST /api.php?path=profile` - Actualizar perfil
- `POST /api.php?path=change-password` - Cambiar contraseña

## Uso en Código

### Generar Token

```php
use App\Services\JwtService;

$jwtService = new JwtService();

$token = $jwtService->generateToken([
    'user_id' => 1,
    'email' => 'user@example.com',
    'name' => 'Juan Pérez'
]);

echo $token;
```

### Validar Token

```php
$payload = $jwtService->validateToken($token);

if ($payload) {
    echo "Token válido!";
    echo "User ID: " . $payload['user_id'];
    echo "Email: " . $payload['email'];
} else {
    echo "Token inválido o expirado";
}
```

### Extraer de Headers

```php
$token = $jwtService->extractTokenFromHeader();

if ($token) {
    $payload = $jwtService->validateToken($token);
}
```

### Refresh Token

```php
$newToken = $jwtService->refreshToken($oldToken);

if ($newToken) {
    echo "Token renovado: " . $newToken;
}
```

### Verificar Expiración

```php
if ($jwtService->isExpired($token)) {
    echo "Token expirado";
}

$remaining = $jwtService->getTimeRemaining($token);
echo "Tiempo restante: {$remaining} segundos";
```

## Middleware JWT

El middleware [src/Middleware/JwtMiddleware.php](src/Middleware/JwtMiddleware.php:1-68) protege rutas automáticamente.

### Uso del Middleware

```php
use App\Middleware\JwtMiddleware;

$jwtMiddleware = new JwtMiddleware();

$jwtMiddleware->handle(function() {
    // Este código solo se ejecuta si el token es válido
    $userId = JwtMiddleware::getUserId();
    $email = JwtMiddleware::getUserEmail();
    $payload = JwtMiddleware::getPayload();

    // Tu lógica aquí
});
```

## Ejemplos con cURL

### Login

```bash
curl -X POST http://localhost:8080/api.php?path=auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"Admin123!"}'
```

### Acceder con Token

```bash
# Guardar token en variable
TOKEN="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."

# Usar token
curl -X GET http://localhost:8080/api.php?path=me \
  -H "Authorization: Bearer $TOKEN"
```

### Refresh Token

```bash
REFRESH_TOKEN="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."

curl -X POST http://localhost:8080/api.php?path=auth/refresh \
  -H "Content-Type: application/json" \
  -d "{\"refresh_token\":\"$REFRESH_TOKEN\"}"
```

## Ejemplos con JavaScript/Fetch

### Login

```javascript
async function login(email, password) {
  const response = await fetch('/api.php?path=auth/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ email, password })
  });

  const data = await response.json();

  if (data.success) {
    // Guardar token en localStorage
    localStorage.setItem('access_token', data.data.access_token);
    localStorage.setItem('refresh_token', data.data.refresh_token);
    return data.data.user;
  }

  throw new Error(data.message);
}
```

### Request Autenticado

```javascript
async function getProfile() {
  const token = localStorage.getItem('access_token');

  const response = await fetch('/api.php?path=me', {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });

  return await response.json();
}
```

### Interceptor para Auto-Refresh

```javascript
async function fetchWithAuth(url, options = {}) {
  const token = localStorage.getItem('access_token');

  options.headers = {
    ...options.headers,
    'Authorization': `Bearer ${token}`
  };

  let response = await fetch(url, options);

  // Si el token expiró, intentar refresh
  if (response.status === 401) {
    const refreshToken = localStorage.getItem('refresh_token');

    const refreshResponse = await fetch('/api.php?path=auth/refresh', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ refresh_token: refreshToken })
    });

    if (refreshResponse.ok) {
      const data = await refreshResponse.json();
      localStorage.setItem('access_token', data.data.access_token);

      // Reintentar request original
      options.headers['Authorization'] = `Bearer ${data.data.access_token}`;
      response = await fetch(url, options);
    } else {
      // Refresh falló, redirigir a login
      window.location.href = '/login.php';
    }
  }

  return response;
}
```

## Seguridad

### ✅ Buenas Prácticas Implementadas

1. **Secret Key Fuerte**: Mínimo 32 bytes aleatorios
2. **HTTPS Only**: Tokens solo en HTTPS en producción
3. **Tiempo de Expiración**: Access tokens de corta duración (1h)
4. **Refresh Tokens**: Tokens de larga duración separados
5. **Validación Estricta**: Verificación de firma y claims
6. **No Almacenar Datos Sensibles**: Solo identificadores en payload
7. **CORS Configurado**: Headers de seguridad apropiados

### ⚠️ Consideraciones de Seguridad

**NO almacenar en localStorage si hay riesgo XSS**:
- Preferir httpOnly cookies cuando sea posible
- O usar sessionStorage para menor persistencia

**Rotar Secret Key**:
```bash
# Generar nueva clave
php -r "echo bin2hex(random_bytes(32));"

# Actualizar en config.php
# Invalidará todos los tokens existentes
```

**Revocar Tokens**:
Implementar blacklist (opcional):
```php
// Guardar JTI de tokens revocados
$redis->set("revoked_token:{$jti}", 1, 3600);

// Verificar en validación
if ($redis->exists("revoked_token:{$payload['jti']}")) {
    return null;
}
```

## Testing con Postman

### 1. Configurar Variables de Entorno

```
url = http://localhost:8080
access_token = (vacío inicialmente)
refresh_token = (vacío inicialmente)
```

### 2. Request de Login

```
POST {{url}}/api.php?path=auth/login
Body (JSON):
{
  "email": "admin@example.com",
  "password": "Admin123!"
}

Tests:
var data = pm.response.json();
pm.environment.set("access_token", data.data.access_token);
pm.environment.set("refresh_token", data.data.refresh_token);
```

### 3. Request Protegido

```
GET {{url}}/api.php?path=me
Headers:
Authorization: Bearer {{access_token}}
```

## Troubleshooting

### Token inválido

```json
{
  "error": "Unauthorized",
  "message": "Token inválido o expirado"
}
```

**Causas**:
- Token expirado (verificar `exp` claim)
- Firma inválida (secret key incorrecta)
- Formato incorrecto

### No se proporcionó token

```json
{
  "error": "Unauthorized",
  "message": "No se proporcionó token de autenticación"
}
```

**Solución**: Agregar header `Authorization: Bearer <token>`

### Firma no coincide

**Causa**: Secret key cambió o token fue modificado
**Solución**: Generar nuevo token con login

## Comparación: JWT vs Sesiones

| Aspecto | JWT | Sesiones |
|---------|-----|----------|
| Estado | Stateless | Stateful |
| Escalabilidad | Excelente | Requiere sticky sessions |
| Revocación | Complejo | Simple |
| Tamaño | Mayor (headers) | Menor (cookie ID) |
| Performance | Sin DB queries | Query en cada request |
| Mobile Apps | Ideal | Complicado |

**Nuestra aplicación soporta ambos**:
- Sesiones: Para web tradicional (login.php)
- JWT: Para API y apps móviles

## Referencias

- [RFC 7519 - JWT](https://tools.ietf.org/html/rfc7519)
- [JWT.io](https://jwt.io/)
- [OWASP JWT Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/JSON_Web_Token_for_Java_Cheat_Sheet.html)
