# Secure App - Sistema de Autenticación Profesional

Sistema completo de autenticación y autorización con PHP, implementando las mejores prácticas de seguridad y patrones de diseño orientados a objetos. Incluye **JWT** para APIs y **Docker** para deployment automático.

## 🚀 Inicio Rápido con Docker

```bash
# Opción 1: Usando Make (Recomendado)
make install && make dev

# Opción 2: Docker Compose
cp .env.docker .env && docker-compose --profile dev up -d

# Opción 3: Script de desarrollo
./scripts/dev-start.sh
```

**Acceder**: http://localhost:8080
**phpMyAdmin**: http://localhost:8081
**API Health**: http://localhost:8080/api.php?path=health

📖 **Documentación**:
- [DOCKER.md](DOCKER.md) - Guía completa de Docker
- [JWT.md](JWT.md) - Guía de JWT/API
- [SECURITY.md](SECURITY.md) - Guía de seguridad
- [INSTALL.md](INSTALL.md) - Instalación manual

## Características Principales

### Seguridad
- **Hasheo de Contraseñas**: Argon2ID con configuración personalizable
- **JWT Authentication**: Tokens stateless para APIs sin dependencias externas
- **Protección CSRF**: Tokens únicos por sesión
- **Protección XSS**: Sanitización de todas las entradas
- **Protección SQL Injection**: Prepared Statements en todas las consultas
- **Rate Limiting**: Límite de peticiones por IP
- **Session Security**: Regeneración de ID, validación de User-Agent
- **Headers de Seguridad**: CSP, X-Frame-Options, X-XSS-Protection, etc.

### Funcionalidades
- Login y Registro de usuarios
- Recuperación de contraseña con tokens seguros
- Dashboard con información del usuario
- Sistema RBAC (Role-Based Access Control)
- Gestión de permisos granular
- API REST con endpoints protegidos por JWT
- Refresh tokens para renovación automática
- Validación frontend y backend
- Diseño responsive y moderno
- **Docker**: Deployment con un comando
- **CI/CD**: GitHub Actions configurado

### Patrones de Diseño Implementados
- **Singleton**: Conexión a base de datos
- **Repository Pattern**: Abstracción de acceso a datos
- **Strategy Pattern**: Servicios de autenticación y autorización
- **Middleware Pattern**: Pipeline de seguridad
- **MVC**: Separación de lógica, presentación y datos
- **Dependency Injection**: En controladores y servicios
- **Factory Pattern**: Creación de objetos
- **Decorator Pattern**: Enriquecimiento de funcionalidad

## Estructura del Proyecto

```
pagina/
├── config.php                      # Configuración de la aplicación
├── autoload.php                    # PSR-4 Autoloader
├── README.md                       # Este archivo
├── database/
│   └── schema.sql                  # Schema de base de datos
├── src/
│   ├── Config/
│   │   └── Database.php            # Singleton de DB
│   ├── Models/
│   │   └── User.php                # Modelo de usuario
│   ├── Repositories/
│   │   ├── BaseRepository.php      # Repository base
│   │   ├── UserRepository.php      # Repository de usuarios
│   │   ├── RoleRepository.php      # Repository de roles
│   │   └── PermissionRepository.php# Repository de permisos
│   ├── Services/
│   │   ├── AuthService.php         # Servicio de autenticación
│   │   ├── AuthorizationService.php# Servicio de autorización
│   │   └── SecurityService.php     # Servicio de seguridad
│   ├── Controllers/
│   │   ├── BaseController.php      # Controlador base
│   │   ├── AuthController.php      # Controlador de auth
│   │   ├── DashboardController.php # Controlador dashboard
│   │   └── ApiController.php       # Controlador API
│   ├── Middleware/
│   │   ├── Middleware.php          # Interface
│   │   ├── CsrfMiddleware.php      # Protección CSRF
│   │   ├── AuthMiddleware.php      # Verificación auth
│   │   └── RateLimitMiddleware.php # Rate limiting
│   ├── Validators/
│   │   └── Validator.php           # Sistema de validación
│   └── Utils/                      # Utilidades
├── views/
│   ├── layouts/
│   │   ├── header.php              # Header HTML
│   │   └── footer.php              # Footer HTML
│   ├── auth/
│   │   ├── login.php               # Vista login
│   │   ├── register.php            # Vista registro
│   │   ├── forgot-password.php     # Vista recuperación
│   │   └── reset-password.php      # Vista reset
│   └── dashboard/
│       ├── index.php               # Dashboard principal
│       └── profile.php             # Perfil de usuario
└── public/
    ├── index.php                   # Punto de entrada
    ├── login.php                   # Login
    ├── register.php                # Registro
    ├── logout.php                  # Cerrar sesión
    ├── forgot-password.php         # Recuperar contraseña
    ├── reset-password.php          # Resetear contraseña
    ├── dashboard.php               # Dashboard
    ├── api.php                     # Router API
    ├── css/
    │   ├── main.css                # Estilos principales
    │   └── forms.css               # Estilos de formularios
    ├── js/
    │   ├── main.js                 # JavaScript principal
    │   └── validation.js           # Validación frontend
    └── assets/                     # Recursos estáticos
```

## Instalación

### Requisitos
- PHP 8.0 o superior
- MySQL 5.7 o superior
- Extensiones PHP: PDO, PDO_MySQL, OpenSSL
- Servidor web (Apache/Nginx)

### Pasos de Instalación

1. **Clonar o descargar el proyecto**
```bash
cd c:\Users\Alumno\Desktop\pagina
```

2. **Configurar la base de datos**
```bash
# Importar el schema SQL
mysql -u root -p < database/schema.sql
```

3. **Configurar la aplicación**

Editar `config.php` con tus credenciales:
```php
'database' => [
    'host' => 'localhost',
    'dbname' => 'secure_app_db',
    'username' => 'tu_usuario',
    'password' => 'tu_contraseña',
],
```

4. **Configurar permisos**
```bash
# En Linux/Mac
chmod -R 755 public/
chmod -R 775 logs/

# Asegurarse que el servidor web tenga acceso
chown -R www-data:www-data .
```

5. **Configurar el servidor web**

**Apache (.htaccess en public/):**
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

**Nginx:**
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

6. **Acceder a la aplicación**
```
http://localhost/public/
```

## Usuarios por Defecto

### Administrador
- **Email**: admin@example.com
- **Contraseña**: Admin123!

### Usuario Normal
- **Email**: user@example.com
- **Contraseña**: Password123!

### Moderador
- **Email**: moderator@example.com
- **Contraseña**: Password123!

**⚠️ IMPORTANTE: Cambiar estas contraseñas en producción**

## Uso de la API

La API REST está disponible en `/api.php?path={endpoint}`

### Endpoints Disponibles

#### Health Check
```bash
GET /api.php?path=health
```

#### Información del Usuario Actual
```bash
GET /api.php?path=me
Headers: Cookie (sesión autenticada)
```

#### Listar Usuarios (Admin)
```bash
GET /api.php?path=users&limit=50&offset=0
Headers: Cookie (sesión autenticada)
```

#### Obtener Usuario Específico
```bash
GET /api.php?path=user&id=1
Headers: Cookie (sesión autenticada)
```

#### Actualizar Perfil
```bash
POST /api.php?path=profile
Headers:
  Cookie (sesión autenticada)
  Content-Type: application/json
Body: {
  "name": "Nuevo Nombre",
  "csrf_token": "token_csrf"
}
```

#### Cambiar Contraseña
```bash
POST /api.php?path=change-password
Headers:
  Cookie (sesión autenticada)
  Content-Type: application/json
Body: {
  "current_password": "actual",
  "new_password": "nueva",
  "confirm_password": "nueva",
  "csrf_token": "token_csrf"
}
```

## Sistema de Permisos

### Roles Predefinidos
- **admin**: Acceso total al sistema
- **moderator**: Permisos intermedios
- **user**: Permisos básicos

### Permisos Disponibles
- `users.view`, `users.create`, `users.edit`, `users.delete`
- `roles.view`, `roles.create`, `roles.edit`, `roles.delete`
- `content.view`, `content.create`, `content.edit`, `content.delete`, `content.publish`
- `settings.view`, `settings.edit`

### Verificar Permisos en el Código

```php
use App\Services\AuthorizationService;

$authService = new AuthorizationService();
$user = $authService->getCurrentUser();

// Verificar rol
if ($authService->hasRole($user, 'admin')) {
    // Código solo para admin
}

// Verificar permiso
if ($authService->hasPermission($user, 'users.delete')) {
    // Código para quien tiene permiso de eliminar usuarios
}
```

## Seguridad - Mejores Prácticas

### Protección Implementada

1. **SQL Injection**: Todos los queries usan prepared statements
2. **XSS**: Sanitización con `htmlspecialchars()` y validación de entrada
3. **CSRF**: Tokens únicos por sesión verificados en cada POST
4. **Session Fixation**: Regeneración de ID en login y periódicamente
5. **Brute Force**: Rate limiting y lockout después de intentos fallidos
6. **Password Security**: Argon2ID con salt automático

### Configuración para Producción

En [config.php](config.php:5-48), cambiar:

```php
'app' => [
    'env' => 'production',
    'debug' => false,
],

// En servidor web, activar:
ini_set('session.cookie_secure', 1); // Requiere HTTPS
```

### Headers de Seguridad

El sistema establece automáticamente:
- `X-Frame-Options: DENY`
- `X-Content-Type-Options: nosniff`
- `X-XSS-Protection: 1; mode=block`
- `Content-Security-Policy`
- `Referrer-Policy: strict-origin-when-cross-origin`

## Validación

### Frontend (JavaScript)
- Validación en tiempo real
- Feedback visual inmediato
- Prevención de envío con datos inválidos

### Backend (PHP)
- Validación robusta en servidor
- Sanitización de todas las entradas
- Mensajes de error descriptivos

## Desarrollo

### Agregar Nuevo Endpoint

1. Crear método en `ApiController.php`
2. Agregar ruta en `public/api.php`
3. Aplicar middleware necesario
4. Documentar en README

### Agregar Nuevo Permiso

```sql
INSERT INTO permissions (name, description) VALUES
('new.permission', 'Descripción del permiso');

-- Asignar a un rol
INSERT INTO role_permissions (role_id, permission_id)
VALUES (1, LAST_INSERT_ID());
```

### Agregar Nueva Vista

1. Crear archivo PHP en `views/`
2. Extender desde layouts
3. Crear controlador si es necesario
4. Agregar archivo público en `public/`

## Tecnologías Utilizadas

- **Backend**: PHP 8+ con POO
- **Base de Datos**: MySQL con InnoDB
- **Frontend**: HTML5, CSS3, JavaScript ES6+
- **Seguridad**: Argon2ID, CSRF Tokens, Rate Limiting
- **Arquitectura**: MVC, Repository Pattern, Middleware

## Licencia

Este proyecto es de código abierto para fines educativos.

## Soporte

Para problemas o preguntas, crear un issue en el repositorio del proyecto.

## Créditos

Desarrollado siguiendo las mejores prácticas de OWASP y estándares de la industria.
