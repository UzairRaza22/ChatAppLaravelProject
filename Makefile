# Whistle-It Laravel Docker Commands

.PHONY: help up down build logs shell test clean

help: ## Show this help message
	@echo "Whistle-It Laravel Docker Commands:"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

up: ## Start all services
	docker compose up --build

up-d: ## Start all services in detached mode
	docker compose up -d --build

down: ## Stop all services
	docker compose down

build: ## Build all images
	docker compose build

logs: ## Show logs for all services
	docker compose logs -f

logs-app: ## Show Laravel app logs
	docker compose logs -f app

logs-nginx: ## Show NGINX logs
	docker compose logs -f nginx

logs-mongo: ## Show MongoDB logs
	docker compose logs -f mongodb

logs-mailhog: ## Show Mailhog logs
	docker compose logs -f mailhog

shell: ## Access Laravel container shell
	docker compose exec app bash

shell-mongo: ## Access MongoDB shell
	docker compose exec mongodb mongo whistle_it

artisan: ## Run artisan command (usage: make artisan COMMAND="migrate")
	@if [ -z "$(COMMAND)" ]; then \
		echo "Usage: make artisan COMMAND='your-command'"; \
		exit 1; \
	fi
	docker compose exec app php artisan $(COMMAND)

tinker: ## Access Laravel tinker
	docker compose exec app php artisan tinker

test: ## Test the complete setup
	@echo "🧪 Testing Docker Setup..."
	@echo "1. Testing Laravel API..."
	@curl -f http://localhost:8000 > /dev/null 2>&1 && echo "✅ Laravel API: Working" || echo "❌ Laravel API: Failed"
	@echo "2. Testing MongoDB connection..."
	@docker compose exec -T app php artisan tinker --execute="echo 'MongoDB: ' . \DB::connection('mongodb')->getDatabaseName()" 2>/dev/null && echo "✅ MongoDB: Connected" || echo "❌ MongoDB: Failed"
	@echo "3. Testing Mailhog..."
	@curl -f http://localhost:8025 > /dev/null 2>&1 && echo "✅ Mailhog: Working" || echo "❌ Mailhog: Failed"
	@echo ""
	@echo "🎉 Docker setup test completed!"

status: ## Show status of all services
	docker compose ps

restart: ## Restart all services
	docker compose restart

restart-app: ## Restart Laravel app only
	docker compose restart app

restart-nginx: ## Restart NGINX only
	docker compose restart nginx

restart-mongo: ## Restart MongoDB only
	docker compose restart mongodb

restart-mailhog: ## Restart Mailhog only
	docker compose restart mailhog

clean: ## Clean up Docker resources
	docker compose down -v
	docker system prune -f

clean-all: ## Clean up everything including volumes
	docker compose down -v --volumes --remove-orphans
	docker system prune -af

migrate: ## Run database migrations
	docker compose exec app php artisan migrate

seed: ## Run database seeders
	docker compose exec app php artisan db:seed

optimize: ## Optimize Laravel performance
	docker compose exec app php artisan optimize

cache-clear: ## Clear Laravel cache
	docker compose exec app php artisan cache:clear

config-clear: ## Clear Laravel config cache
	docker compose exec app php artisan config:clear

key-generate: ## Generate Laravel application key
	docker compose exec app php artisan key:generate

storage-link: ## Create storage symbolic link
	docker compose exec app php artisan storage:link

mail-test: ## Test email functionality
	@echo "📧 Testing email functionality..."
	@echo "Send a test email via Laravel or check Mailhog at http://localhost:8025"
	@echo "Example API call:"
	@echo "curl -X POST http://localhost:8000/api/auth/register \\"
	@echo "  -H 'Content-Type: application/json' \\"
	@echo "  -d '{\"name\":\"Test\",\"email\":\"test@example.com\",\"password\":\"password123\",\"password_confirmation\":\"password123\"}'"

# Production commands
prod-up: ## Start production environment
	APP_ENV=production docker compose up -d --build

prod-down: ## Stop production environment
	APP_ENV=production docker compose down

# Development shortcuts
dev: ## Start development environment
	docker compose up --build

dev-d: ## Start development in background
	docker compose up -d --build

watch: ## Watch for changes (requires additional setup)
	@echo "To watch for changes, use dev-d and run:"
	@echo "docker compose exec app php artisan queue:work"
