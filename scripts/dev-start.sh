#!/bin/bash

# ============================================
# Script de Inicio Rápido para Desarrollo
# ============================================

set -e

GREEN='\033[0;32m'
NC='\033[0m'

echo -e "${GREEN}Iniciando entorno de desarrollo...${NC}"

# Crear .env si no existe
if [ ! -f .env ]; then
    cp .env.docker .env
    echo "✓ Archivo .env creado"
fi

# Iniciar servicios
docker-compose --profile dev up -d

echo -e "${GREEN}Esperando a que los servicios estén listos...${NC}"
sleep 10

# Verificar salud
if curl -f http://localhost:8080/api.php?path=health &> /dev/null; then
    echo ""
    echo -e "${GREEN}✓ Entorno de desarrollo listo!${NC}"
    echo ""
    echo "Aplicación:  http://localhost:8080"
    echo "phpMyAdmin:  http://localhost:8081"
    echo "API Health:  http://localhost:8080/api.php?path=health"
    echo ""
    echo "Logs en tiempo real:"
    docker-compose logs -f --tail=100
else
    echo "Error al iniciar. Verifica los logs con: docker-compose logs"
    exit 1
fi
