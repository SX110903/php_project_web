# Guía de Docker

Documentación completa para ejecutar y desplegar la aplicación con Docker.

## Inicio Rápido

### Opción 1: Usando Make (Recomendado)

```bash
# Ver todos los comandos disponibles
make help

# Configurar e iniciar desarrollo
make install
make dev

# Ver logs
make logs

# Detener
make dev-stop
```

### Opción 2: Usando Scripts

```bash
# Iniciar desarrollo
./scripts/dev-start.sh

# Detener
./scripts/dev-stop.sh

# Deployment producción
./scripts/deploy.sh production
```

### Opción 3: Docker Compose Manual

```bash
# Copiar variables de entorno
cp .env.docker .env

# Iniciar servicios
docker-compose --profile dev up -d

# Ver logs
docker-compose logs -f

# Detener
docker-compose down
```

## Servicios Disponibles

### Aplicación (app)
- **Puerto**: 8080
- **URL**: http://localhost:8080
- **Health Check**: http://localhost:8080/api.php?path=health
- **Descripción**: Aplicación PHP con Nginx y PHP-FPM

### Base de Datos (mysql)
- **Puerto**: 3306
- **Usuario**: root
- **Contraseña**: root (desarrollo)
- **Database**: secure_app_db
- **Descripción**: MySQL 8.0 con schema pre-cargado

### phpMyAdmin (phpmyadmin) - Solo Desarrollo
- **Puerto**: 8081
- **URL**: http://localhost:8081
- **Descripción**: Interfaz web para gestionar MySQL

### Redis (redis) - Opcional
- **Puerto**: 6379
- **Contraseña**: secret
- **Profile**: cache
- **Uso**: Caché y sesiones

## Perfiles de Docker Compose

Los perfiles permiten activar servicios opcionales:

### Perfil dev (desarrollo)
```bash
docker-compose --profile dev up -d
```
Incluye: app, mysql, phpmyadmin

### Perfil production
```bash
docker-compose --profile production up -d
```
Incluye: app, mysql, nginx (reverse proxy)

### Perfil cache
```bash
docker-compose --profile cache up -d
```
Incluye: app, mysql, redis

## Variables de Entorno

Configurar en archivo `.env`:

```env
# Application
APP_ENV=development          # development | production
APP_DEBUG=true              # true | false
APP_PORT=8080              # Puerto de la aplicación

# Database
DB_NAME=secure_app_db
DB_USER=root
DB_PASSWORD=root           # ⚠️ Cambiar en producción
MYSQL_PORT=3306

# Redis
REDIS_PASSWORD=secret      # ⚠️ Cambiar en producción
REDIS_PORT=6379

# phpMyAdmin
PMA_PORT=8081

# Nginx
NGINX_PORT=80
NGINX_SSL_PORT=443
```

## Comandos Útiles

### Gestión de Contenedores

```bash
# Ver estado de contenedores
docker-compose ps

# Ver logs de todos los servicios
docker-compose logs -f

# Ver logs de un servicio específico
docker-compose logs -f app
docker-compose logs -f mysql

# Reiniciar servicios
docker-compose restart

# Reiniciar un servicio específico
docker-compose restart app

# Detener y eliminar todo
docker-compose down

# Detener y eliminar incluyendo volúmenes
docker-compose down -v
```

### Acceso a Contenedores

```bash
# Shell en el contenedor de la app
docker-compose exec app sh

# Shell en MySQL
docker-compose exec mysql bash

# MySQL CLI directamente
docker-compose exec mysql mysql -uroot -proot secure_app_db

# Ejecutar comando PHP
docker-compose exec app php -v
docker-compose exec app php /var/www/html/public/index.php
```

### Base de Datos

```bash
# Exportar base de datos
docker-compose exec mysql mysqldump -uroot -proot secure_app_db > backup.sql

# Importar base de datos
docker-compose exec -T mysql mysql -uroot -proot secure_app_db < backup.sql

# Ver tablas
docker-compose exec mysql mysql -uroot -proot secure_app_db -e "SHOW TABLES;"

# Ejecutar query
docker-compose exec mysql mysql -uroot -proot secure_app_db -e "SELECT * FROM users;"
```

### Debugging

```bash
# Ver configuración de PHP
docker-compose exec app php --ini

# Ver extensiones PHP
docker-compose exec app php -m

# Ver variables de entorno
docker-compose exec app env

# Verificar conectividad con MySQL
docker-compose exec app ping mysql

# Health check manual
curl http://localhost:8080/api.php?path=health | jq .
```

## Estructura de Docker

```
pagina/
├── Dockerfile                      # Multi-stage Dockerfile
├── docker-compose.yml              # Orquestación de servicios
├── .dockerignore                   # Archivos ignorados
├── docker/
│   ├── nginx/
│   │   ├── nginx.conf              # Configuración global Nginx
│   │   └── default.conf            # Virtual host
│   ├── php/
│   │   ├── php.ini                 # Configuración PHP
│   │   ├── opcache.ini             # Configuración OPcache
│   │   ├── www.conf                # PHP-FPM pool
│   │   └── xdebug.ini              # Xdebug (dev only)
│   ├── mysql/
│   │   └── my.cnf                  # Configuración MySQL
│   └── supervisor/
│       └── supervisord.conf        # Supervisor para múltiples procesos
└── scripts/
    ├── deploy.sh                   # Script de deployment
    ├── dev-start.sh                # Inicio rápido desarrollo
    ├── dev-stop.sh                 # Detener desarrollo
    └── backup.sh                   # Backup automático
```

## Dockerfile Multi-Stage

El Dockerfile tiene 3 stages:

### Stage base
Configuración común para todos los entornos:
- Alpine Linux ligero
- PHP 8.2 FPM
- Extensiones PHP necesarias
- Nginx
- Supervisor

### Stage development
Herramientas adicionales para desarrollo:
- Git
- Vim
- Xdebug
- Variables de entorno de desarrollo

### Stage production
Optimizado para producción:
- Sin herramientas de desarrollo
- Permisos restrictivos
- OPcache optimizado
- Variables de entorno de producción

## Build de Imágenes

### Development

```bash
docker-compose build
```

### Production

```bash
docker-compose build --build-arg BUILD_ENV=production app
```

### Rebuild completo (sin caché)

```bash
docker-compose build --no-cache
```

## Volúmenes

### Volúmenes nombrados (persisten datos)

- `mysql-data`: Datos de MySQL
- `redis-data`: Datos de Redis
- `nginx-logs`: Logs de Nginx

### Bind mounts (desarrollo)

- `./`: Código de la aplicación
- `./logs`: Logs de PHP

## Networking

Todos los servicios están en la red `app-network` (bridge driver).

Los servicios pueden comunicarse usando sus nombres:
- `app` → Aplicación PHP
- `mysql` → Base de datos
- `redis` → Caché

## Health Checks

Todos los servicios tienen health checks configurados:

### App
```yaml
test: curl -f http://localhost/api.php?path=health
interval: 30s
timeout: 10s
retries: 3
```

### MySQL
```yaml
test: mysqladmin ping -h localhost -u root -proot
interval: 10s
timeout: 5s
retries: 5
```

### Redis
```yaml
test: redis-cli --raw incr ping
interval: 10s
timeout: 3s
retries: 3
```

## Troubleshooting

### La aplicación no inicia

```bash
# Ver logs detallados
docker-compose logs -f app

# Verificar configuración PHP
docker-compose exec app php --ini

# Verificar permisos
docker-compose exec app ls -la /var/www/html
```

### MySQL no conecta

```bash
# Verificar que MySQL esté listo
docker-compose exec mysql mysqladmin ping

# Ver logs de MySQL
docker-compose logs -f mysql

# Verificar variables de entorno
docker-compose exec app env | grep DB_
```

### Permisos de archivos

```bash
# Arreglar permisos (dentro del contenedor)
docker-compose exec app chown -R appuser:appuser /var/www/html
docker-compose exec app chmod -R 755 /var/www/html
docker-compose exec app chmod -R 775 /var/www/html/logs
```

### Puerto ocupado

```bash
# Ver qué proceso usa el puerto
lsof -i :8080  # Linux/Mac
netstat -ano | findstr :8080  # Windows

# Cambiar puerto en .env
APP_PORT=8081
```

## CI/CD con GitHub Actions

El proyecto incluye workflow de GitHub Actions ([.github/workflows/deploy.yml](.github/workflows/deploy.yml:1-149)).

### Configurar Secrets en GitHub

Ve a Settings → Secrets and variables → Actions y agrega:

```
STAGING_HOST=staging.example.com
STAGING_USER=deploy
STAGING_SSH_KEY=<private-key>

PRODUCTION_HOST=example.com
PRODUCTION_USER=deploy
PRODUCTION_SSH_KEY=<private-key>
```

### Workflow Pipeline

1. **Test**: Valida sintaxis PHP
2. **Build**: Construye imagen Docker
3. **Security**: Escaneo de vulnerabilidades
4. **Deploy Staging**: Auto-deploy en `develop`
5. **Deploy Production**: Auto-deploy en `main`

## Deployment en Producción

### Prerequisitos

- Servidor con Docker y Docker Compose
- Acceso SSH configurado
- Dominio apuntando al servidor

### Pasos

1. **Clonar repositorio**
```bash
ssh user@server
cd /var/www
git clone <repo-url> secure-app
cd secure-app
```

2. **Configurar entorno**
```bash
cp .env.docker .env
nano .env  # Editar con credenciales reales
```

3. **Deployment**
```bash
chmod +x scripts/deploy.sh
./scripts/deploy.sh production
```

4. **Configurar HTTPS** (Let's Encrypt)
```bash
# Instalar certbot
apt-get install certbot python3-certbot-nginx

# Obtener certificado
certbot --nginx -d yourdomain.com

# Auto-renovación
crontab -e
# Agregar: 0 3 * * * certbot renew --quiet
```

## Monitoreo

### Logs en tiempo real

```bash
docker-compose logs -f --tail=100
```

### Métricas de recursos

```bash
docker stats
```

### Health checks programados

```bash
# Agregar a crontab
*/5 * * * * curl -f http://localhost:8080/api.php?path=health || echo "Health check failed" | mail -s "Alert" admin@example.com
```

## Backup y Restore

### Backup automático

```bash
# Ejecutar manualmente
./scripts/backup.sh

# Programar (crontab)
0 2 * * * /var/www/secure-app/scripts/backup.sh
```

### Restore

```bash
# Restaurar base de datos
gunzip backups/db_backup_20240101_020000.sql.gz
docker-compose exec -T mysql mysql -uroot -proot secure_app_db < backups/db_backup_20240101_020000.sql

# Restaurar archivos
tar -xzf backups/files_backup_20240101_020000.tar.gz
```

## Recursos Adicionales

- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [PHP Docker Official Image](https://hub.docker.com/_/php)
- [MySQL Docker Official Image](https://hub.docker.com/_/mysql)
- [Nginx Docker Official Image](https://hub.docker.com/_/nginx)
