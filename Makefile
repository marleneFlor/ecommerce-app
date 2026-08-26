# Variables del proyecto
DOCKER_COMPOSE = docker-compose
SERVICE        = app
PHP            = $(DOCKER_COMPOSE) exec -T $(SERVICE)
PHPUNIT        = bin/phpunit
CONSOLE        = bin/console

.PHONY: help up down build restart shell logs \
        install cache-clear \
        migrate migration migrate-test db-create db-create-test \
        test test-coverage test-filter

# Muestra esta ayuda
help:
	@grep -E '^[a-zA-Z_-]+:.*?#' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?#"}; {printf "  \033[36m%-18s\033[0m %s\n", $$1, $$2}'

## --- Docker ---

up: # Levantar los contenedores
	$(DOCKER_COMPOSE) up -d

down: # Apagar los contenedores
	$(DOCKER_COMPOSE) down

build: # Reconstruir las imágenes
	$(DOCKER_COMPOSE) build

restart: down up # Reiniciar los contenedores

shell: # Abrir una shell dentro del contenedor app
	$(DOCKER_COMPOSE) exec $(SERVICE) bash

logs: # Ver los logs del contenedor app
	$(DOCKER_COMPOSE) logs -f $(SERVICE)

## --- Symfony / Composer ---

install: # Instalar dependencias de Composer
	$(PHP) composer install

cache-clear: # Limpiar la cache de Symfony
	$(PHP) $(CONSOLE) cache:clear

## --- Base de datos / Doctrine ---

db-create: # Crear la base de datos (dev)
	$(PHP) $(CONSOLE) doctrine:database:create --if-not-exists

db-create-test: # Crear la base de datos de test
	$(PHP) $(CONSOLE) doctrine:database:create --if-not-exists --env=test

migrate: # Ejecutar las migraciones (dev)
	$(PHP) $(CONSOLE) doctrine:migrations:migrate --no-interaction

migrate-test: # Ejecutar las migraciones en el entorno de test
	$(PHP) $(CONSOLE) doctrine:migrations:migrate --no-interaction --env=test

migration: # Generar una nueva migración a partir de las entidades
	$(PHP) $(CONSOLE) make:migration

## --- Tests (PHPUnit) ---

test: # Correr todos los tests
	$(PHP) $(PHPUNIT)

test-coverage: # Tests con reporte de cobertura HTML en ./coverage (requiere Xdebug/pcov)
	$(PHP) $(PHPUNIT) --coverage-html coverage

# Filtrar un test concreto:  make test-filter FILTER=NombreDelTest
test-filter: # Ejecutar solo los tests que coincidan con FILTER
	$(PHP) $(PHPUNIT) --filter $(FILTER)
