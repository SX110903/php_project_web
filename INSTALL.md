# Guía de Instalación Rápida

## Requisitos Previos

- PHP 8.0+
- MySQL 5.7+
- Apache/Nginx
- Extensiones PHP: PDO, PDO_MySQL, OpenSSL

## Instalación en 5 Pasos

### 1. Preparar la Base de Datos

```bash
# Crear la base de datos e importar el schema
mysql -u root -p
CREATE DATABASE secure_app_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;

mysql -u root -p secure_app_db < database/schema.sql
```

### 2. Configurar la Aplicación

Editar [config.php](config.php:19-24):

```php
'database' => [
    'host' => 'localhost',
    'dbname' => 'secure_app_db',
    'username' => 'tu_usuario',     // ⚠️ CAMBIAR
    'password' => 'tu_contraseña',  // ⚠️ CAMBIAR
],
```

### 3. Configurar Permisos (Linux/Mac)

```bash
chmod -R 755 public/
chmod -R 755 src/
chown -R www-data:www-data .
```

### 4. Configurar el Servidor Web

#### Apache

El archivo `.htaccess` ya está configurado en [public/.htaccess](public/.htaccess:1-104)

Verificar que `mod_rewrite` esté habilitado:
```bash
sudo a2enmod rewrite
sudo service apache2 restart
```

#### Nginx

Agregar a la configuración del servidor:

```nginx
server {
    listen 80;
    server_name localhost;
    root /ruta/a/pagina/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\. {
        deny all;
    }
}
```

### 5. Acceder a la Aplicación

Abrir en el navegador:
```
http://localhost/
```

## Credenciales por Defecto

### Administrador
- Email: `admin@example.com`
- Contraseña: `Admin123!`

### Usuario Normal
- Email: `user@example.com`
- Contraseña: `Password123!`

**⚠️ IMPORTANTE: Cambiar estas contraseñas inmediatamente**

## Verificación de Instalación

### 1. Verificar Base de Datos

```sql
USE secure_app_db;
SHOW TABLES;
SELECT COUNT(*) FROM users;
```

Deberías ver 5 tablas y 3 usuarios.

### 2. Verificar API

```bash
curl http://localhost/api.php?path=health
```

Respuesta esperada:
```json
{"status":"ok","timestamp":"2024-...","version":"1.0.0"}
```

### 3. Verificar Login

1. Ir a `http://localhost/login.php`
2. Iniciar sesión con las credenciales de administrador
3. Deberías ser redirigido al dashboard

## Solución de Problemas

### Error: "Database connection failed"

- Verificar credenciales en [config.php](config.php:19-24)
- Verificar que MySQL esté corriendo
- Verificar que la base de datos exista

### Error: "Class not found"

- Verificar que [autoload.php](autoload.php:1-62) esté en la raíz
- Verificar permisos de lectura en `/src`

### Error 404 en todas las páginas

- Verificar configuración de Apache/Nginx
- Verificar que `mod_rewrite` esté habilitado (Apache)
- Verificar que el DocumentRoot apunte a `/public`

### Sesión no persiste

- Verificar permisos en `/tmp` o directorio de sesiones
- Verificar configuración de PHP session.save_path

## Configuración en Producción

### 1. Cambiar Modo a Producción

En [config.php](config.php:12-14):
```php
'env' => 'production',
'debug' => false,
```

### 2. Habilitar HTTPS

En [public/.htaccess](public/.htaccess:8-10), descomentar:
```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

En [autoload.php](autoload.php:27), cambiar:
```php
ini_set('session.cookie_secure', 1);
```

### 3. Cambiar Contraseñas

1. Generar hash de nueva contraseña:
```php
<?php
echo password_hash('NuevaContraseña123!', PASSWORD_ARGON2ID, [
    'memory_cost' => 65536,
    'time_cost' => 4,
    'threads' => 3
]);
```

2. Actualizar en la base de datos:
```sql
UPDATE users SET password = 'HASH_GENERADO' WHERE email = 'admin@example.com';
```

### 4. Configurar Backups Automáticos

```bash
# Crear script de backup
#!/bin/bash
mysqldump -u usuario -p secure_app_db > backup_$(date +%Y%m%d).sql
gzip backup_$(date +%Y%m%d).sql

# Agregar a crontab (diario a las 2 AM)
0 2 * * * /ruta/al/script/backup.sh
```

## Siguiente Paso

Leer [README.md](README.md) para documentación completa y [SECURITY.md](SECURITY.md) para mejores prácticas de seguridad.
