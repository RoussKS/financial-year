.PHONY: help

# List all available Makefile commands.
help:
	@echo "Available commands:"
	@echo "   make help                  : List all available Makefile commands"
	@echo "   make setup-php81           : Start the dev environment with PHP 8.1"
	@echo "   make setup-php82           : Start the dev environment with PHP 8.2"
	@echo "   make setup-php83           : Start the dev environment with PHP 8.3"
	@echo "   make setup-php84           : Start the dev environment with PHP 8.4"
	@echo "   make setup-php85           : Start the dev environment with PHP 8.5"
	@echo "   make shell                 : Get an interactive shell on the PHP container"
	@echo "   make test				     : Run PHPUnit tests"
	@echo "   make static-analysis       : Run Static Analysis (PHPStan)"
	@echo "   make start                 : Start the dev environment"
	@echo "   make stop                  : Stop the dev environment"
	@echo "   make kill                  : Stop and remove all containers"
	@echo "   make composer-install      : Install composer dependencies"

setup-php81: --prep-docker-compose-file stop --prep-dockerfile-php81 start --remove-packages composer-install
setup-php82: --prep-docker-compose-file stop --prep-dockerfile-php82 start --remove-packages composer-install
setup-php83: --prep-docker-compose-file stop --prep-dockerfile-php83 start --remove-packages composer-install
setup-php84: --prep-docker-compose-file stop --prep-dockerfile-php84 start --remove-packages composer-install
setup-php85: --prep-docker-compose-file stop --prep-dockerfile-php85 start --remove-packages composer-install

# Get a shell on the PHP container
shell:
	docker compose exec -it app /bin/bash

# Run Tests (PHPUnit)
test:
	docker compose exec app ./vendor/bin/phpunit

# Run Static Analysis (PHPStan)
static-analysis:
	docker compose exec app ./vendor/bin/phpstan analyse --memory-limit=1G

# Start the dev environment
start:
	docker compose up -d --build

# Stop the dev environment
stop:
	docker compose down

# Stop and remove all containers
kill:
	docker compose kill
	docker compose rm --force

# Install composer dependencies
composer-install:
	docker compose exec app composer install --no-interaction

# Copy Dockerfile for PHP 8.1
--prep-dockerfile-php81: --remove-dockerfile
	cp "./.docker/Dockerfile.php81" "./.docker/Dockerfile"

--prep-dockerfile-php82: --remove-dockerfile
	cp "./.docker/Dockerfile.php82" "./.docker/Dockerfile"

--prep-dockerfile-php83: --remove-dockerfile
	cp "./.docker/Dockerfile.php83" "./.docker/Dockerfile"

--prep-dockerfile-php84: --remove-dockerfile
	cp "./.docker/Dockerfile.php84" "./.docker/Dockerfile"

--prep-dockerfile-php85: --remove-dockerfile
	cp "./.docker/Dockerfile.php85" "./.docker/Dockerfile"

# Copy docker-compose.yml file
--prep-docker-compose-file:
	[ -f "./docker-compose.yml" ] || cp "./docker-compose.yml.example" "./docker-compose.yml"

# Remove Dockerfile
--remove-dockerfile:
	rm -f ./docker/Dockerfile

# Remove composer related files
--remove-packages: --remove-lockfile --remove-vendor

# Remove composer.lock file
--remove-lockfile:
	docker compose exec app rm -f ./composer.lock

# Remove vendor directory
--remove-vendor:
	docker compose exec app rm -rf ./vendor
