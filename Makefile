PHONY : help up stop down install init

.DEFAULT_GOAL := help

help: ## Show this help
		@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)
		@echo "\n  Amazon FBA Shipping Service\n"

up: ## Start all the containers
		docker-compose up --no-recreate -d

stop: ## Stop all the containers
		docker-compose stop

down: ## Stop and remove all the containers
		docker-compose down

install: up ## Install the dependencies
		docker-compose exec php /bin/sh -c "composer install --no-interaction --ansi --no-suggest"

init: install ## Initialize and test the application
		docker-compose exec php /bin/sh -c "vendor/bin/psalm"
		docker-compose exec php /bin/sh -c "vendor/bin/phpunit"
