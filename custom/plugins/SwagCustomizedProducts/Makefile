.DEFAULT_GOAL := help

## Variable definition
BASE_URL?=http://docker.vm:8000
PLUGIN_ROOT=$(shell cd -P -- '$(shell dirname -- "$0")' && pwd -P)
PROJECT_ROOT=$(PLUGIN_ROOT)/../../../

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
.PHONY: help

init:  ## Initialize shopware, install Custom Products, dump the test database
	cd ../../../ \
		&& ./psh.phar init\
		&& php bin/console plugin:install --activate -c SwagCustomizedProducts\
		&& ./psh.phar storefront:init\
		&& ./psh.phar administration:init\
		&& ./psh.phar init-test-databases
.PHONY: init

administration-fix: ## Run eslint on the administration files
	../../../vendor/shopware/platform/src/Administration/Resources/app/administration/node_modules/.bin/eslint --ignore-path .eslintignore --config ../../../vendor/shopware/platform/src/Administration/Resources/app/administration/.eslintrc.js --ext .js,.vue --fix src/Resources/app/administration
.PHONY: administration-fix

storefront-fix: ## Run eslint on the storefront files
	../../../vendor/shopware/platform/src/Storefront/Resources/app/storefront/node_modules/.bin/eslint --ignore-path .eslintignore --config ../../../vendor/shopware/platform/src/Storefront/Resources/app/storefront/.eslintrc.js --ext .js,.vue --fix src/Resources/app/storefront
.PHONY: storefront-fix

administration-lint: ## Run eslint on the administration files
	../../../vendor/shopware/platform/src/Administration/Resources/app/administration/node_modules/.bin/eslint --ignore-path .eslintignore --config ../../../vendor/shopware/platform/src/Administration/Resources/app/administration/.eslintrc.js --ext .js,.vue src/Resources/app/administration
.PHONY: administration-lint

storefront-lint: ## Run eslint on the storefront files
	../../../vendor/shopware/platform/src/Storefront/Resources/app/storefront/node_modules/.bin/eslint --ignore-path .eslintignore --config ../../../vendor/shopware/platform/src/Storefront/Resources/app/storefront/.eslintrc.js --ext .js,.vue src/Resources/app/storefront
.PHONY: storefront-lint

ecs-fix: ## Run easy coding style in fix mode
	php ../../../dev-ops/analyze/vendor/bin/ecs check --fix --config=../../../vendor/shopware/platform/easy-coding-standard.php src tests\
		&& php ../../../dev-ops/analyze/vendor/bin/ecs check --fix --config=easy-coding-standard.php src tests
.PHONY: ecs-fix

ecs-dry: ## Run easy coding style in dry mode
	php ../../../dev-ops/analyze/vendor/bin/ecs check --config=../../../vendor/shopware/platform/easy-coding-standard.php src tests\
		&& php ../../../dev-ops/analyze/vendor/bin/ecs check --config=easy-coding-standard.php src tests
.PHONY: ecs-dry

php-cs-dry: ## Run php-cs fixer in dry mode
	php ../../../dev-ops/analyze/vendor/bin/php-cs-fixer fix --dry-run --config=.php-cs-fixer.dist.php -vv src tests
.PHONY: php-cs-dry

php-cs-fix: ## Run php-cs fixer in fix mode
	php ../../../dev-ops/analyze/vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php -vv src tests
.PHONY: php-cs-fix

phpstan: ## Run phpstan
	php bin/phpstan-config-generator.php \
 		&& composer dump-autoload \
 		&& php ../../../dev-ops/analyze/vendor/bin/phpstan analyze --configuration phpstan.neon --autoload-file=../../../vendor/autoload.php src tests
.PHONY: phpstan

psalm: ## Run vimeo psalm
	php ../../../dev-ops/analyze/vendor/bin/psalm --config=psalm.xml --threads=$(nproc) --diff --show-info=false
.PHONY: psalm

phpunit: ## Run phpunit; Accepts an additional argument "FILTER='search term'"
	composer dump-autoload \
		&& ./../../../vendor/bin/phpunit --filter "$(FILTER)"
.PHONY: phpunit

phpunit-coverage: ## Run phpunit with coverage report; Accepts an additional argument "FILTER='search term'"
	composer dump-autoload \
		&& ./../../../vendor/bin/phpunit --coverage-html coverage --filter "$(FILTER)"
.PHONY: phpunit-coverage

storefront-cypress: ## Opens the storefront e2e test suite
	cd $(PROJECT_ROOT) && ./psh.phar e2e:dump-db
	cd src/Resources/app/storefront/test/e2e && npm i -ci
	ps aux  |  grep -i '[n]ode src/Resources/app/storefront/test/e2e/routes/cypress.js'  |  awk '{print $2}'  |  xargs kill
	PROJECT_ROOT=$(PROJECT_ROOT) node src/Resources/app/storefront/test/e2e/routes/cypress.js 2>&1 &
	cd ./src/Resources/app/storefront/test/e2e/ && CYPRESS_baseUrl="$(BASE_URL)" npm run open
.PHONY: storefront-cypress

storefront-cypress-docker: # Opens the storefront e2e test suite in docker
	set -x ;\
	PLUGIN_FULL_PATH=$(shell cd -P -- '$(shell dirname -- "$0")' && pwd -P); \
	PLUGIN_DIR_NAME=$$(basename $$PLUGIN_FULL_PATH) ;\
	APP_SERVER_ID=$(shell docker-compose ps -q app_server) ;\
	CYPRESS_SERVER_ID=$(shell docker-compose ps -q cypress) ;\
	USER_KEY=$(shell echo "$(shell id -u):$(shell id -g)") ;\
	docker exec -u "$$USER_KEY" "$$APP_SERVER_ID" ./psh.phar e2e:dump-db; \
	docker exec -u "$$USER_KEY" "$$APP_SERVER_ID" /bin/bash -c "export HOME=/app && npm install --prefix /app/custom/plugins/$$PLUGIN_DIR_NAME/src/Resources/app/storefront/test/e2e"; \
	docker exec -u "$$USER_KEY" "$$APP_SERVER_ID" FOREVER_ROOT=/tmp forever start /app/custom/plugins/$$PLUGIN_DIR_NAME/src/Resources/app/storefront/test/e2e/routes/cypress.js; \
	docker exec -u "$$USER_KEY" "$$APP_SERVER_ID" /bin/bash -c "export HOME=/app && npm run --prefix /app/custom/plugins/$$PLUGIN_DIR_NAME/src/Resources/app/storefront/test/e2e open";
.PHONY: storefront-cypress-docker

administration-cypress: ## Opens the administration e2e test suite
	cd $(PROJECT_ROOT) && ./psh.phar e2e:dump-db
	cd src/Resources/app/administration/test/e2e && npm i -ci
	kill $(ps aux | grep '[n]ode src/Resources/app/storefront/test/e2e/routes/cypress.js' | awk '{print $2}')
	PROJECT_ROOT=$(PROJECT_ROOT) node src/Resources/app/storefront/test/e2e/routes/cypress.js 2>&1 &
	cd ./src/Resources/app/administration/test/e2e/ && CYPRESS_baseUrl="$(BASE_URL)" npm run open
.PHONY: administration-cypress

administration-cypress-docker: # Opens the storefront e2e test suite in docker
	set -x ;\
	PLUGIN_FULL_PATH=$(shell cd -P -- '$(shell dirname -- "$0")' && pwd -P); \
	PLUGIN_DIR_NAME=$$(basename $$PLUGIN_FULL_PATH) ;\
	APP_SERVER_ID=$(shell docker-compose ps -q app_server) ;\
	CYPRESS_SERVER_ID=$(shell docker-compose ps -q cypress) ;\
	USER_KEY=$(shell echo "$(shell id -u):$(shell id -g)") ;\
	docker exec -u "$$USER_KEY" "$$APP_SERVER_ID" ./psh.phar e2e:dump-db; \
	docker exec -u "$$USER_KEY" "$$APP_SERVER_ID" /bin/bash -c "export HOME=/app && npm install --prefix /app/custom/plugins/$$PLUGIN_DIR_NAME/src/Resources/app/administration/test/e2e"; \
	docker exec -u "$$USER_KEY" "$$APP_SERVER_ID" FOREVER_ROOT=/tmp forever start /app/custom/plugins/$$PLUGIN_DIR_NAME/src/Resources/app/administration/test/e2e/routes/cypress.js; \
	docker exec -u "$$USER_KEY" "$$APP_SERVER_ID" /bin/bash -c "export HOME=/app && npm run --prefix /app/custom/plugins/$$PLUGIN_DIR_NAME/src/Resources/app/administration/test/e2e open";
.PHONY: storefront-cypress-docker
