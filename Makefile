.PHONY: test test-file cache-clear composer-require db-migrate fix-perms

# Startet die Docker-Umgebung im Hintergrund
up:
	docker compose up -d

# Fährt die Container sauber herunter, behält aber die Datenbank-Daten bei
down:
	docker compose down

# Startet die komplette Testsuite im exakten Backend-Pfad des Containers
test:
	docker compose exec -w /var/www/html/backend php vendor/bin/phpunit

# Führt ein spezifisches Testfile aus (Nutzung: make test-file file=tests/Controller/FundingProjectControllerTest.php)
test-file:
	docker compose exec -w /var/www/html/backend php vendor/bin/phpunit $(file)

# Installiert Composer-Pakete absolut rechte- und pfadsicher (Nutzung: make composer-require pkg="symfony/browser-kit --dev")
composer-require:
	docker compose exec -u root php chown -R www-data:www-data /var/www/html/backend
	docker compose exec -u www-data -w /var/www/html/backend php composer require $(pkg)

# Repariert die WSL2/VS Code Schreibrechte im Monorepo-Root
fix-perms:
	sudo chown -R $(shell whoami):www-data backend/
	sudo chmod -R 775 backend/

# Leert den Symfony-Cache für Dev und Test im korrekten Container-Pfad
cache-clear:
	docker compose exec -w /var/www/html/backend php php bin/console cache:clear --env=dev
	docker compose exec -w /var/www/html/backend php php bin/console cache:clear --env=test

# Führt alle ausstehenden Datenbank-Migrationen für Dev und Test aus
db-migrate:
	docker compose exec -w /var/www/html/backend php php bin/console doctrine:migrations:migrate --no-interaction
	docker compose exec -w /var/www/html/backend php php bin/console doctrine:migrations:migrate --env=test --no-interaction
