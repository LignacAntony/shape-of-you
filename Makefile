#---VARIABLES---------------------------------#
#---DOCKER---#
DOCKER = docker
DOCKER_RUN = $(DOCKER) run
DOCKER_COMPOSE = docker compose
DOCKER_COMPOSE_UP = $(DOCKER_COMPOSE) up -d
DOCKER_COMPOSE_STOP = $(DOCKER_COMPOSE) stop
#------------#

#---SYMFONY--#
SYMFONY = symfony
SYMFONY_SERVER_START = $(SYMFONY) serve -d
SYMFONY_SERVER_STOP = $(SYMFONY) server:stop
SYMFONY_CONSOLE = $(SYMFONY) console
SYMFONY_LINT = $(SYMFONY_CONSOLE) lint:
#------------#

#---COMPOSER-#
COMPOSER = $(SYMFONY) composer
COMPOSER_INSTALL = $(COMPOSER) install
COMPOSER_UPDATE = $(COMPOSER) update
#------------#

#---PHPQA---#
PHPQA = jakzal/phpqa
PHPQA_RUN = $(DOCKER_RUN) --init -it --rm -v "$(PWD):/project" -w /project $(PHPQA)
#------------#

#---PHPUNIT-#
PHPUNIT = APP_ENV=test $(SYMFONY) php bin/phpunit
#------------#

#---Couleurs pour les messages dans le terminal-#
BOLD = \033[1m
RESET = \033[0m
BLUE = \033[34m
GREEN = \033[32m
YELLOW = \033[33m
RED = \033[31m
#------------#
#---------------------------------------------#

## === 🆘  HELP ==================================================
help: ## Show this help.
	@echo "Symfony-And-Docker-Makefile"
	@echo "---------------------------"
	@echo "Usage: make [target]"
	@echo ""
	@echo "Targets:"
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'
.PHONY: help
#---------------------------------------------#

## === 🐋  DOCKER ================================================
docker-up: ## Start docker containers.
	@echo "$(BLUE)🔥 Démarrage des containers Docker...$(RESET)\n"
	$(DOCKER_COMPOSE_UP)
.PHONY: docker-up

docker-up-local: ## Start docker containers in local environment.
	@echo "$(BLUE)🔥 Démarrage des containers Docker en local...$(RESET)\n"
	$(DOCKER_COMPOSE) -f docker-compose.yaml -f docker-compose.override.yaml up -d
.PHONY: docker-up-local

docker-stop: ## Stop docker containers.
	@echo "$(YELLOW)🛑 Arrêt des containers Docker...$(RESET)\n"
	$(DOCKER_COMPOSE_STOP)
.PHONY: docker-stop

docker-down: ## Stop and remove docker containers.
	@echo "$(RED) 🚨 Suppression des containers Docker...$(RESET)\n"
	$(DOCKER_COMPOSE) down

docker-logs: ## Display Docker logs.
	$(DOCKER_COMPOSE) logs -f
.PHONY: docker-logs
#---------------------------------------------#

## === 🎛️  SYMFONY ===============================================
sf: ## List and Use All Symfony commands (make sf command="commande-name").
	$(SYMFONY_CONSOLE) $(command)
.PHONY: sf

sf-start: ## Start symfony server.
	$(SYMFONY_SERVER_START)
.PHONY: sf-start

sf-stop: ## Stop symfony server.
	$(SYMFONY_SERVER_STOP)
.PHONY: sf-stop

sf-cc: ## Clear symfony cache.
	$(SYMFONY_CONSOLE) cache:clear
.PHONY: sf-cc

sf-log: ## Show symfony logs.
	$(SYMFONY) server:log
.PHONY: sf-log

sf-su: ## Update symfony schema database.
	$(SYMFONY_CONSOLE) doctrine:schema:update --force
.PHONY: sf-su

sf-mm: ## Make migrations.
	$(SYMFONY_CONSOLE) make:migration
.PHONY: sf-mm

sf-dmm: ## Migrate.
	$(SYMFONY_CONSOLE) doctrine:migrations:migrate --no-interaction
.PHONY: sf-dmm

sf-fixtures: ## Load fixtures.
	$(SYMFONY_CONSOLE) doctrine:fixtures:load --no-interaction
.PHONY: sf-fixtures

sf-me: ## Make symfony entity
	$(SYMFONY_CONSOLE) make:entity
.PHONY: sf-me

sf-mc: ## Make symfony controller
	$(SYMFONY_CONSOLE) make:controller
.PHONY: sf-mc

sf-mf: ## Make symfony Form
	$(SYMFONY_CONSOLE) make:form
.PHONY: sf-mf

sf-perm: ## Fix permissions.
	chmod -R 777 var
.PHONY: sf-perm

sf-sudo-perm: ## Fix permissions with sudo.
	sudo chmod -R 777 var
.PHONY: sf-sudo-perm

sf-dump-env: ## Dump env.
	$(SYMFONY_CONSOLE) debug:dotenv
.PHONY: sf-dump-env

sf-dump-env-container: ## Dump Env container.
	$(SYMFONY_CONSOLE) debug:container --env-vars
.PHONY: sf-dump-env-container

sf-dump-routes: ## Dump routes.
	$(SYMFONY_CONSOLE) debug:router
.PHONY: sf-dump-routes

sf-open: ## Open project in a browser.
	$(SYMFONY) open:local
.PHONY: sf-open

sf-open-email: ## Open Email catcher.
	$(SYMFONY) open:local:webmail
.PHONY: sf-open-email

sf-check-requirements: ## Check requirements.
	$(SYMFONY) check:requirements
.PHONY: sf-check-requirements
#---------------------------------------------#

## === 📦  COMPOSER ==============================================
composer-install: ## Install composer dependencies.
	$(COMPOSER_INSTALL)
.PHONY: composer-install

composer-update: ## Update composer dependencies.
	$(COMPOSER_UPDATE)
.PHONY: composer-update

composer-validate: ## Validate composer.json file.
	$(COMPOSER) validate
.PHONY: composer-validate

composer-validate-deep: ## Validate composer.json and composer.lock files in strict mode.
	$(COMPOSER) validate --strict --check-lock
.PHONY: composer-validate-deep
#---------------------------------------------#

## === 🐛  PHPQA =================================================
qa-cs-fixer-dry-run: ## Run php-cs-fixer in dry-run mode.
	$(PHPQA_RUN) php-cs-fixer fix ./src --rules=@Symfony --verbose --dry-run
.PHONY: qa-cs-fixer-dry-run

qa-cs-fixer: ## Run php-cs-fixer.
	$(PHPQA_RUN) php-cs-fixer fix ./src --rules=@Symfony --verbose
.PHONY: qa-cs-fixer

qa-phpstan: ## Run phpstan.
	$(PHPQA_RUN) phpstan analyse ./src --level=7
.PHONY: qa-phpstan

qa-security-checker: ## Run security-checker.
	$(SYMFONY) security:check
.PHONY: qa-security-checker

qa-phpcpd: ## Run phpcpd (copy/paste detector).
	$(PHPQA_RUN) phpcpd ./src
.PHONY: qa-phpcpd

qa-php-metrics: ## Run php-metrics.
	$(PHPQA_RUN) phpmetrics --report-html=var/phpmetrics ./src
.PHONY: qa-php-metrics

qa-lint-twigs: ## Lint twig files.
	$(SYMFONY_LINT)twig ./templates
.PHONY: qa-lint-twigs

qa-lint-yaml: ## Lint yaml files.
	$(SYMFONY_LINT)yaml ./config
.PHONY: qa-lint-yaml

qa-lint-container: ## Lint container.
	$(SYMFONY_LINT)container
.PHONY: qa-lint-container

qa-lint-schema: ## Lint Doctrine schema.
	$(SYMFONY_CONSOLE) doctrine:schema:validate --skip-sync -vvv --no-interaction
.PHONY: qa-lint-schema

qa-audit: ## Run composer audit.
	$(COMPOSER) audit
.PHONY: qa-audit
#---------------------------------------------#

## === 🔎  TESTS =================================================
tests: ## Run tests.
	@echo "$(BLUE)🚀 Lancement des tests...$(RESET)\n"
	$(PHPUNIT) --testdox
	@echo "$(GREEN)✅ Tests terminés!$(RESET)\n"
.PHONY: tests

tests-coverage: ## Run tests with coverage.
	$(PHPUNIT) --coverage-html var/coverage
.PHONY: tests-coverage
#---------------------------------------------#

## —— 🚀 Git et déploiement ———————————————————————————————————————
staging-deploy: ## Déploie sur l'environnement de recette
	@echo "$(YELLOW)🚀 Déploiement en recette...$(RESET)\n"
	$(GIT) checkout staging
	$(GIT) pull origin staging
	$(COMPOSER) install --no-dev --optimize-autoloader
	$(CONSOLE) doctrine:migrations:migrate --no-interaction --env=staging
	$(CONSOLE) cache:clear --env=staging
	@echo "$(GREEN)✅ Déployé en recette!$(RESET)\n"
.PHONY: staging-deploy

prod-deploy: ## Déploie sur l'environnement de production
	@echo "$(YELLOW)🚀 Déploiement en production...$(RESET)\n"
	$(GIT) checkout main
	$(GIT) pull origin main
	$(COMPOSER) install --no-dev --optimize-autoloader
	$(CONSOLE) doctrine:migrations:migrate --no-interaction --env=prod
	$(CONSOLE) cache:clear --env=prod
	@echo "$(GREEN)✅ Déployé en production!$(RESET)\n"
.PHONY: prod-deploy
#---------------------------------------------#

## === ⭐  OTHERS =================================================
before-commit: qa-cs-fixer qa-phpstan qa-security-checker qa-phpcpd qa-lint-twigs qa-lint-yaml qa-lint-container qa-lint-schema tests ## Run before commit.
.PHONY: before-commit

env-install: ## 🎯 Installe et configure le projet complet
	@echo "$(BLUE)🚀 Installation du projet...$(RESET)\n"
	$(MAKE) docker-up-local
	$(MAKE) composer-install
	$(MAKE) sf-dmm
	$(MAKE) sf-fixtures
	$(MAKE) sf-start
	$(MAKE) sf-open 
	@echo "$(GREEN)✅ Projet installé avec succès!$(RESET)\n"
.PHONY: env-install

start: docker-up-local sf-start sf-open ## Start project.
.PHONY: start

stop: docker-stop sf-stop ## Stop project.
.PHONY: stop

reset-db: ## Reset database. TODO
	$(eval CONFIRM := $(shell read -p "Are you sure you want to reset the database? [y/N] " CONFIRM && echo $${CONFIRM:-N}))
	$(if $(filter y,$(CONFIRM)),\
		$(MAKE) docker-stop
		$(MAKE) docker-down,\
		$(info $(RED)Reset database canceled$(RESET)))
.PHONY: reset-db
#---------------------------------------------#
