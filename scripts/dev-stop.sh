#!/bin/bash

# ============================================
# Script para Detener Desarrollo
# ============================================

set -e

GREEN='\033[0;32m'
NC='\033[0m'

echo -e "${GREEN}Deteniendo entorno de desarrollo...${NC}"

docker-compose down

echo -e "${GREEN}✓ Entorno detenido${NC}"
echo ""
echo "Para eliminar también los volúmenes: docker-compose down -v"
