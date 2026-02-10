# Makefile para Secure App
# Comandos rápidos para desarrollo y deployment

.PHONY: help install dev dev-stop prod deploy logs shell test backup clean

help: ## Muestra esta ayuda
	@echo "Comandos disponibles:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

install: ## Instala dependencias y configura el proyecto
	@echo "Configurando proyecto..."
	@cp -n .env.docker .env || true
	@chmod +x scripts/*.sh
	@echo "✓ Proyecto configurado"

dev: ## Inicia entorno de desarrollo
	@./scripts/dev-start.sh

dev-stop: ## Detiene entorno de desarrollo
	@./scripts/dev-stop.sh

prod: ## Deployment en producción
	@./scripts/deploy.sh production

deploy: ## Alias para deployment en producción
	@./scripts/deploy.sh production

logs: ## Muestra logs en tiempo real
	@docker-compose logs -f --tail=100

logs-app: ## Muestra logs solo de la aplicación
	@docker-compose logs -f --tail=100 app

logs-db: ## Muestra logs solo de la base de datos
	@docker-compose logs -f --tail=100 mysql

shell: ## Abre shell en el contenedor de la aplicación
	@docker-compose exec app sh

shell-db: ## Abre shell en MySQL
	@docker-compose exec mysql mysql -uroot -proot secure_app_db

ps: ## Muestra estado de contenedores
	@docker-compose ps

restart: ## Reinicia todos los servicios
	@docker-compose restart

restart-app: ## Reinicia solo la aplicación
	@docker-compose restart app

backup: ## Crea backup de la base de datos
	@./scripts/backup.sh

test: ## Ejecuta tests (placeholder)
	@echo "Tests no implementados aún"

clean: ## Limpia contenedores, volúmenes e imágenes
	@docker-compose down -v --remove-orphans
	@docker system prune -af
	@echo "✓ Limpieza completada"

rebuild: ## Reconstruye e inicia servicios
	@docker-compose down
	@docker-compose build --no-cache
	@docker-compose up -d

health: ## Verifica salud de la aplicación
	@curl -s http://localhost:8080/api.php?path=health | jq .

api-test: ## Test rápido de la API
	@echo "Testing API endpoints..."
	@echo "Health check:"
	@curl -s http://localhost:8080/api.php?path=health | jq .
