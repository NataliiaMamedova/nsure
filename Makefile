.PHONY: help build build-pull build-nocache create start restart stop down shell test cs ecs-fix stan

docker_bin := $(shell command -v docker 2> /dev/null)
dc_bin := $(shell command -v docker-compose 2> /dev/null)
dc_shell_app_name = console
dc_app_name = nsure-service
cwd = $(shell pwd)

CURRENT_USER = $(shell id -u):$(shell id -g)
RUN_APP_ARGS = --rm --user "$(CURRENT_USER)" "$(dc_shell_app_name)"
DOCKER_COMPOSE=$(dc_bin)

## Help
help:
	@printf "${COLOR_COMMENT}Usage:${COLOR_RESET}\n"
	@printf " make [target]\n\n"
	@printf "${COLOR_COMMENT}Available targets:${COLOR_RESET}\n"
	@awk '/^[a-zA-Z\-_0-9\.@]+:/ { \
		helpMessage = match(lastLine, /^## (.*)/); \
		if (helpMessage) { \
			helpCommand = substr($$1, 0, index($$1, ":")); \
			helpMessage = substr(lastLine, RSTART + 3, RLENGTH); \
			printf " ${COLOR_INFO}%-16s${COLOR_RESET} %s\n", helpCommand, helpMessage; \
		} \
	} \
{ lastLine = $$0 }' $(MAKEFILE_LIST)

define print_block
	printf " \e[30;48;5;82m  %s  \033[0m\n" $1
endef

#######################
# DOCKER TASKS
#######################

## Re-build docker containers
build:
	$(DOCKER_COMPOSE) build

## Build with --pull flag
build-pull:
	$(DOCKER_COMPOSE) build --pull

## Re-build docker containers without using cache
build-nocache:
	$(DOCKER_COMPOSE) build --no-cache --pull

## Create docker containers, volumes and network, but do not start the services
create:
	$(DOCKER_COMPOSE) up --no-start ${dc_app_name} 2>/dev/null

## Start the docker containers
start:
	$(DOCKER_COMPOSE) up -d --remove-orphans ${dc_app_name}
	$(call print_block, 'Navigate your browser to ⇒ https://${dc_app_name}.local.paybis.com')

## Restart the docker containers
restart:
	$(DOCKER_COMPOSE) up -d --force-recreate ${dc_app_name}

## Stop the docker containers
stop:
	$(DOCKER_COMPOSE) stop

## Stop and remove the docker containers, volumes, networks and images
down:
	$(DOCKER_COMPOSE) down --volumes

## Start shell into app container
shell:
	$(dc_bin) run -e STARTUP_WAIT_FOR_SERVICES=false $(RUN_APP_ARGS)

## Run tests
test:
	$(dc_bin) run -e STARTUP_WAIT_FOR_SERVICES=false $(RUN_APP_ARGS) -c "php ./bin/phpunit"

## Run grumphp
cs:
	$(dc_bin) run -e STARTUP_WAIT_FOR_SERVICES=false $(RUN_APP_ARGS) -c "php vendor/bin/grumphp run"

## Run ecs
ecs-fix:
	$(dc_bin) run -e STARTUP_WAIT_FOR_SERVICES=false $(RUN_APP_ARGS) -c "php vendor/bin/ecs check src tests --fix"

stan: cache_warmup
	$(dc_bin) run -e STARTUP_WAIT_FOR_SERVICES=false $(RUN_APP_ARGS) -c "php vendor/bin/phpstan analyze -c .phpstan.neon"

cache_warmup:
	$(dc_bin) run -e STARTUP_WAIT_FOR_SERVICES=false $(RUN_APP_ARGS) -c "php ./bin/console cache:warmup --env=test"
