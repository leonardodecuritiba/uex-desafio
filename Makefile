SHELL := /bin/sh

export COMPOSE_DOCKER_CLI_BUILD=1
export DOCKER_BUILDKIT=1

PROJECT_NAME ?= uex-contacts-monolith

.PHONY: help up down restart logs ps build init init-backend init-frontend artisan composer npm-frontend test

help:
	@echo "Useful targets:"
	@echo "  make init          - Build images and bootstrap backend/frontend"
	@echo "  make up            - Start all services"
	@echo "  make down          - Stop all services"
	@echo "  make artisan ARGS= - Run php artisan with ARGS"
	@echo "  make composer ARGS=- Run composer with ARGS"
	@echo "  make npm-frontend  - Run npm in frontend container"
	@echo "  make test          - Run backend tests"

build:
	docker compose build --pull

up:
	docker compose up -d

down:
	docker compose down -v --remove-orphans

restart: down up

logs:
	docker compose logs -f --tail=200

ps:
	docker compose ps

init: build init-backend init-frontend up

init-backend:
	mkdir -p backend
	cp scripts/init-backend.sh backend/init-backend.sh
	chmod +x backend/init-backend.sh
	docker compose run --rm app sh -lc "./init-backend.sh"

init-frontend:
	mkdir -p frontend
	cp scripts/init-frontend.sh frontend/init-frontend.sh
	chmod +x frontend/init-frontend.sh
	docker compose run --rm -p 5173:5173 frontend sh -lc "./init-frontend.sh"

artisan:
	docker compose run --rm app php artisan $(ARGS)

composer:
	docker compose run --rm app composer $(ARGS)

npm-frontend:
	docker compose run --rm frontend sh -lc "npm $(ARGS)"

test:
	docker compose run --rm app sh -lc "php -v && php artisan test --parallel --stop-on-failure"
