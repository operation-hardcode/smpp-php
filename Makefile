build: docker-build
up: docker-up
down: docker-down
php: docker-php

docker-build:
	docker-compose build --no-cache

docker-up:
	docker-compose up -d

docker-php:
	docker-compose exec php sh

docker-down:
	docker-compose down --remove-orphans