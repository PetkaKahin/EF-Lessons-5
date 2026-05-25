.PHONY: init up down shell dump-autoload migrate seed

init:
	docker compose build
	docker compose run --rm php composer install
	$(MAKE) migrate

up:
	docker compose up -d

down:
	docker compose down

shell:
	docker compose exec php sh

dump-autoload:
	docker compose exec php composer dump-autoload

migrate:
	docker compose run --rm php php bin/migrate.php
