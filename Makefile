.PHONY: up down test test-file composer-require db-migrate fix-perms cache-clear frontend-install frontend-dev frontend-logs frontend-fmt

# ==============================================================================
# 🐋 CONTAINER LIFECYCLE
# ==============================================================================

# Startet die Docker-Umgebung im Hintergrund
up:
	docker compose up -d

# Fährt die Container sauber herunter, behält aber die Datenbank-Daten bei
down:
	docker compose down

# Zeigt den Live-Status aller Container an
status:
	docker compose ps

# ==============================================================================
# 🐘 BACKEND (Symfony 7)
# ==============================================================================

# Startet die komplette Testsuite im exakten Backend-Pfad des Containers
test:
	docker compose exec -w /var/www/html/backend php bin/phpunit

# Führt ein spezifisches Testfile aus (Nutzung: make test-file file=tests/Controller/FundingProjectControllerTest.php)
test-file:
	docker compose exec -w /var/www/html/backend php bin/phpunit $(file)

# Rebuilds the PostgreSQL test database from scratch using migrations
test-db-fresh:
	docker compose exec php php backend/bin/console doctrine:database:drop --env=test --force --if-exists
	docker compose exec php php backend/bin/console doctrine:database:create --env=test
	docker compose exec php php backend/bin/console doctrine:migrations:migrate --env=test --no-interaction

# Installiert Composer-Pakete absolut rechte- und pfadsicher (Nutzung: make composer-require pkg="symfony/browser-kit --dev")
composer-require:
	docker compose exec -u root php chown -R www-data:www-data /var/www/html/backend
	docker compose exec -u www-data -w /var/www/html/backend php composer require $(pkg)

# Leert den Symfony-Cache für Dev und Test im korrekten Container-Pfad
cache-clear:
	docker compose exec -w /var/www/html/backend php php bin/console cache:clear --env=dev
	docker compose exec -w /var/www/html/backend php php bin/console cache:clear --env=test

# Führt alle ausstehenden Datenbank-Migrationen für Dev und Test aus
db-migrate:
	docker compose exec -w /var/www/html/backend php php bin/console doctrine:migrations:migrate --no-interaction
	docker compose exec -w /var/www/html/backend php php bin/console doctrine:migrations:migrate --env=test --no-interaction

# ==============================================================================
# 🪐 FRONTEND (Vite / Node / Tailwind)
# ==============================================================================

# Installiert neue npm-Pakete im Frontend (Nutzung: make frontend-install pkg="lucide-javascript")
# Wenn kein pkg übergeben wird, führt es ein normales 'npm install' aus
frontend-install:
	docker compose exec -w /app frontend npm install $(pkg)

# Startet den Vite Entwicklungs-Server im Vordergrund (falls man Hot-Reload Logs sehen will)
frontend-dev:
	docker compose exec -w /app frontend npm run dev

# Zeigt die Live-Logs des Frontend-Containers an (perfekt zum Debuggen von Vite-Ausfällen)
frontend-logs:
	docker compose logs -f frontend

# Formatiert den Frontend-Code mit Prettier/Linter (sobald später eingerichtet)
frontend-fmt:
	docker compose exec -w /app frontend npm run lint -- --fix 2>/dev/null || echo "⚠️ Kein Linter konfiguriert."

# ==============================================================================
# 🔧 RECHTE MANAGEMENT
# ==============================================================================

# Repariere die WSL2/VS Code Schreibrechte im gesamten Monorepo-Root
fix-perms:
	@echo "🔧 Repariere WSL 2 & Docker Dateirechte präzise..."
	sudo chown -R $(shell whoami):www-data .
	find . -type d -exec sudo chmod 775 {} +
	# Schließt node_modules-Ordner vom radikalen 664-Datei-chmod aus:
	find . -type f -not -path "*/node_modules/*" -exec sudo chmod 664 {} +
	chmod +x backend/bin/console backend/bin/phpunit 2>/dev/null || true
	@echo "✅ Rechte für Ordner (775) und Dateien (664) präzise korrigiert!"

