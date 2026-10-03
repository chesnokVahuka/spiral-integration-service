SHELL := /bin/bash

export HOST_UID := $(shell id -u)
export HOST_GID := $(shell id -g)

.PHONY: init install up down restart logs logs-buggregator rr-reset test

init:
	@test -f .env || cp .env.example .env
	@mkdir -p var/log

install: init
	docker compose run --rm --no-deps app composer install

up: init
	docker compose up -d --build

down:
	docker compose down

restart: down up

logs:
	docker compose logs -f app

logs-buggregator:
	docker compose logs -f buggregator

rr-reset:
	docker compose exec app rr reset

test:
	docker compose run --rm --no-deps app vendor/bin/phpunit
