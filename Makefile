.PHONY: up down status test test-file test-db-fresh composer-require cache-clear db-migrate lint lint-fix phpstan check frontend-install frontend-dev frontend-logs frontend-fmt frontend-test fix-perms

# ==============================================================================
# 🐋 CONTAINER LIFECYCLE
# ==============================================================================

# Start the Docker environment in the background
up:
	docker compose up -d

# Stop the containers cleanly while preserving the database data
down:
	docker compose down

# Show the live status of all containers
status:
	docker compose ps

# ==============================================================================
# 🐘 BACKEND (Symfony 7)
# ==============================================================================

# Run the complete test suite from the exact backend path in the container
test:
	docker compose exec -w /var/www/html/backend php bin/phpunit

# Run a specific test file (usage: make test-file file=tests/Controller/FundingProjectControllerTest.php)
test-file:
	docker compose exec -w /var/www/html/backend php bin/phpunit $(file)

# Rebuild the PostgreSQL test database using migrations
test-db-fresh:
	docker compose exec -w /var/www/html/backend php php bin/console doctrine:database:drop --env=test --force --if-exists
	docker compose exec -w /var/www/html/backend php php bin/console doctrine:database:create --env=test
	docker compose exec -w /var/www/html/backend php php bin/console doctrine:migrations:migrate --env=test --no-interaction

# Install Composer packages with correct permissions and paths (usage: make composer-require pkg="symfony/browser-kit --dev")
composer-require:
	docker compose exec -u root php chown -R www-data:www-data /var/www/html/backend
	docker compose exec -u www-data -w /var/www/html/backend php composer require $(pkg)

# Clear the Symfony cache for dev and test at the correct container path
cache-clear:
	docker compose exec -w /var/www/html/backend php php bin/console cache:clear --env=dev
	docker compose exec -w /var/www/html/backend php php bin/console cache:clear --env=test

# Run all pending database migrations for dev and test
db-migrate:
	docker compose exec -w /var/www/html/backend php php bin/console doctrine:migrations:migrate --no-interaction
	docker compose exec -w /var/www/html/backend php php bin/console doctrine:migrations:migrate --env=test --no-interaction

# Check the code for formatting errors and whitespace issues
lint:
	docker compose exec -w /var/www/html/backend php vendor/bin/phpcs --standard=PSR12 src tests

# Automatically fix cosmetic formatting issues
lint-fix:
	docker compose exec -w /var/www/html/backend php vendor/bin/phpcbf --standard=PSR12 src tests

# Check naming, logic, and type safety
phpstan:
	docker compose exec -w /var/www/html/backend php vendor/bin/phpstan analyse -c phpstan.dist.neon

# Run the complete pipeline (linter, type analysis, and test suite) before committing
check: lint phpstan test frontend-test

# ==============================================================================
# 🪐 FRONTEND (Vite / Node / Tailwind)
# ==============================================================================

# Install new npm packages in the frontend (usage: make frontend-install pkg="lucide-javascript")
# If no pkg is provided, run a standard 'npm install'
frontend-install:
	docker compose exec -w /app frontend npm install $(pkg)

# Start the Vite development server in the foreground (to view hot-reload logs)
frontend-dev:
	docker compose exec -w /app frontend npm run dev

# Run the frontend test suite through Vitest in the isolated path
frontend-test:
	docker compose exec -w /app frontend npm run test

# Show the live logs from the frontend container (useful for debugging Vite failures)
frontend-logs:
	docker compose logs -f frontend

# Format the frontend code with Prettier/linter (once configured)
frontend-fmt:
	docker compose exec -w /app frontend npm run lint -- --fix 2>/dev/null || echo "⚠️ No linter configured."

# ==============================================================================
# 🔧 RECHTE MANAGEMENT
# ==============================================================================

# Fix WSL2 and Docker file permissions throughout the monorepo
fix-perms:
	@echo "🔧 Fixing WSL 2 & Docker file permissions..."
	sudo chown -R $(shell whoami):www-data .
	find . -type d -exec sudo chmod 775 {} +
	find . -type f -not -path "*/node_modules/*" -not -path "*/.git/*" -exec sudo chmod 664 {} +
	# Make all CLI tools and vendor binaries executable
	chmod +x backend/bin/console backend/bin/phpunit backend/vendor/bin/* 2>/dev/null || true
	docker compose exec php git config --global --add safe.directory /var/www/html || true
	@echo "✅ Directory (775) and file (664) permissions fixed!"
