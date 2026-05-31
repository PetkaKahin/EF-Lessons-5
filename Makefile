.PHONY: init up down shell dump-autoload migrate seed seed-rollback pay-order race-test

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
	docker compose exec php php bin/migrate.php

seed:
	docker compose exec php php seed/seed.php

seed-rollback:
	docker compose exec php php seed/rollback.php

pay-order:
	docker compose exec php php bin/pay_order.php $(ORDER)

race-test:
	docker compose exec php php bin/race_test.php
