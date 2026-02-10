#!/bin/bash

# ============================================
# Script de Backup Automático
# ============================================

set -e

# Configuración
BACKUP_DIR="./backups"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
DB_NAME="secure_app_db"
DB_USER="root"
DB_PASS="root"

GREEN='\033[0;32m'
NC='\033[0m'

echo -e "${GREEN}Iniciando backup...${NC}"

# Crear directorio de backups
mkdir -p "$BACKUP_DIR"

# Backup de base de datos
echo "Respaldando base de datos..."
docker-compose exec -T mysql mysqldump -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_DIR/db_backup_$TIMESTAMP.sql"
gzip "$BACKUP_DIR/db_backup_$TIMESTAMP.sql"

# Backup de archivos uploaded (si existen)
if [ -d "./uploads" ]; then
    echo "Respaldando archivos..."
    tar -czf "$BACKUP_DIR/files_backup_$TIMESTAMP.tar.gz" ./uploads
fi

# Limpiar backups antiguos (mantener últimos 7 días)
find "$BACKUP_DIR" -name "*.sql.gz" -mtime +7 -delete
find "$BACKUP_DIR" -name "*.tar.gz" -mtime +7 -delete

echo -e "${GREEN}✓ Backup completado${NC}"
echo "Archivos guardados en: $BACKUP_DIR"
ls -lh "$BACKUP_DIR" | grep "$TIMESTAMP"
