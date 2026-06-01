.PHONY: init up down shell dump-autoload migrate seed seed-rollback pay-order race-test offset-pagination keyset-pagination

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
	docker compose run --rm php composer dump-autoload

migrate:
	docker compose run --rm php php bin/migrate.php

seed:
	docker compose run --rm php php seed/seed.php

seed-rollback:
	docker compose run --rm php php seed/rollback.php

pay-order:
	docker compose run --rm php php bin/pay_order.php $(ORDER)

race-test:
	docker compose run --rm php php bin/race_test.php

offset-pagination:
	docker compose run --rm php php bin/offset_pagination.php

keyset-pagination:
	docker compose run --rm php php bin/keyset_pagination.php
