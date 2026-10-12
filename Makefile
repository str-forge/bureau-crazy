.PHONY: test cache-clear composer-require db-migrate

# Startet die komplette Testsuite im exakten Backend-Pfad des Containers
test:
	docker compose exec -w /var/www/html/backend php vendor/bin/phpunit

cache-clear:
	docker compose exec -w /var/www/html/backend php php bin/console cache:clear --env=dev
	docker compose exec -w /var/www/html/backend php php bin/console cache:clear --env=test

db-migrate:
	docker compose exec -w /var/www/html/backend php php bin/console doctrine:migrations:migrate --no-interaction
	docker compose exec -w /var/www/html/backend php php bin/console doctrine:migrations:migrate --env=test --no-interaction
