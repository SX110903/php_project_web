#!/bin/bash

# ============================================
# Script de Deployment Automático
# Secure App - Production Deployment
# ============================================

set -e

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuración
ENVIRONMENT="${1:-production}"
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DOCKER_COMPOSE_FILE="docker-compose.yml"

echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}   Secure App - Deployment Script${NC}"
echo -e "${GREEN}   Environment: ${ENVIRONMENT}${NC}"
echo -e "${GREEN}============================================${NC}"
echo ""

# Función para mostrar mensajes
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Verificar requisitos
check_requirements() {
    log_info "Verificando requisitos..."

    if ! command -v docker &> /dev/null; then
        log_error "Docker no está instalado"
        exit 1
    fi

    if ! command -v docker-compose &> /dev/null; then
        log_error "Docker Compose no está instalado"
        exit 1
    fi

    log_info "✓ Requisitos verificados"
}

# Crear archivo .env si no existe
setup_env() {
    log_info "Configurando variables de entorno..."

    if [ ! -f "$PROJECT_DIR/.env" ]; then
        if [ -f "$PROJECT_DIR/.env.docker" ]; then
            cp "$PROJECT_DIR/.env.docker" "$PROJECT_DIR/.env"
            log_warn "Archivo .env creado desde .env.docker"
            log_warn "Por favor, revisa y actualiza las credenciales"
        else
            log_error "No se encontró archivo .env ni .env.docker"
            exit 1
        fi
    else
        log_info "✓ Archivo .env encontrado"
    fi
}

# Detener contenedores existentes
stop_containers() {
    log_info "Deteniendo contenedores existentes..."
    cd "$PROJECT_DIR"
    docker-compose down --remove-orphans || true
    log_info "✓ Contenedores detenidos"
}

# Construir imágenes
build_images() {
    log_info "Construyendo imágenes Docker..."
    cd "$PROJECT_DIR"

    if [ "$ENVIRONMENT" == "production" ]; then
        docker-compose build --no-cache --build-arg BUILD_ENV=production app
    else
        docker-compose build app
    fi

    log_info "✓ Imágenes construidas"
}

# Iniciar contenedores
start_containers() {
    log_info "Iniciando contenedores..."
    cd "$PROJECT_DIR"

    if [ "$ENVIRONMENT" == "production" ]; then
        docker-compose --profile production up -d
    else
        docker-compose --profile dev up -d
    fi

    log_info "✓ Contenedores iniciados"
}

# Esperar a que MySQL esté listo
wait_for_mysql() {
    log_info "Esperando a que MySQL esté listo..."

    max_attempts=30
    attempt=0

    while [ $attempt -lt $max_attempts ]; do
        if docker-compose exec -T mysql mysqladmin ping -h localhost -u root -proot &> /dev/null; then
            log_info "✓ MySQL está listo"
            return 0
        fi

        attempt=$((attempt + 1))
        echo -n "."
        sleep 2
    done

    log_error "MySQL no respondió después de ${max_attempts} intentos"
    return 1
}

# Ejecutar migraciones
run_migrations() {
    log_info "Ejecutando migraciones de base de datos..."
    cd "$PROJECT_DIR"

    # Verificar si la base de datos ya está inicializada
    if docker-compose exec -T mysql mysql -u root -proot -e "USE secure_app_db;" &> /dev/null; then
        log_info "✓ Base de datos ya existe"
    else
        log_warn "Importando schema..."
        docker-compose exec -T mysql mysql -u root -proot secure_app_db < database/schema.sql
        log_info "✓ Schema importado"
    fi
}

# Verificar salud de la aplicación
health_check() {
    log_info "Verificando salud de la aplicación..."

    max_attempts=15
    attempt=0

    while [ $attempt -lt $max_attempts ]; do
        if curl -f http://localhost:8080/api.php?path=health &> /dev/null; then
            log_info "✓ Aplicación está respondiendo"
            return 0
        fi

        attempt=$((attempt + 1))
        echo -n "."
        sleep 2
    done

    log_error "La aplicación no respondió al health check"
    return 1
}

# Limpiar recursos antiguos
cleanup() {
    log_info "Limpiando recursos antiguos..."

    # Eliminar imágenes no utilizadas
    docker image prune -f &> /dev/null || true

    # Eliminar volúmenes huérfanos
    docker volume prune -f &> /dev/null || true

    log_info "✓ Limpieza completada"
}

# Mostrar información de deployment
show_info() {
    echo ""
    echo -e "${GREEN}============================================${NC}"
    echo -e "${GREEN}   Deployment Completado${NC}"
    echo -e "${GREEN}============================================${NC}"
    echo ""
    echo "Aplicación: http://localhost:8080"
    echo "API Health: http://localhost:8080/api.php?path=health"
    echo "phpMyAdmin: http://localhost:8081 (solo desarrollo)"
    echo ""
    echo "Comandos útiles:"
    echo "  Ver logs:        docker-compose logs -f app"
    echo "  Ver contenedores: docker-compose ps"
    echo "  Detener:         docker-compose down"
    echo "  Reiniciar:       docker-compose restart"
    echo ""
}

# Función principal
main() {
    check_requirements
    setup_env
    stop_containers
    build_images
    start_containers
    wait_for_mysql
    run_migrations

    if health_check; then
        cleanup
        show_info
        log_info "✓ Deployment exitoso!"
        exit 0
    else
        log_error "Deployment falló en health check"
        log_info "Ejecuta 'docker-compose logs app' para ver los errores"
        exit 1
    fi
}

# Ejecutar
main
