-include .env
export

# ======== Naming ========
EXTENSION := IDProvider
EXTENSION_FOLDER := /var/www/html/extensions/${EXTENSION}
extension := $(shell echo $(EXTENSION) | tr A-Z a-z})
IMAGE_NAME := $(extension):test-$(MW_VERSION)

# ======== CI ENV Variables ========
MW_VERSION ?= 1.35
IMAGE_VERSION := $(MW_VERSION)
PHP_VERSION ?= 7.4
DB_TYPE ?= sqlite
DB_IMAGE ?= ""


# ======== Docker-Compose Commands ========
environment = MW_VERSION=$(MW_VERSION) \
IMAGE_NAME=$(IMAGE_NAME) \
PHP_VERSION=$(PHP_VERSION) \
DB_TYPE=$(DB_TYPE) \
DB_IMAGE=$(DB_IMAGE) \
EXTENSION_FOLDER=$(EXTENSION_FOLDER)

COMPOSE_OVERRIDE=""

ifneq (,$(wildcard ./docker-compose.override.yml))
     COMPOSE_OVERRIDE=-f docker-compose.override.yml
endif

compose = $(environment) docker-compose $(COMPOSE_OVERRIDE) $(COMPOSE_ARGS)
compose-ci = $(environment) docker-compose -f docker-compose.yml -f docker-compose-ci.yml $(COMPOSE_OVERRIDE) $(COMPOSE_ARGS)

compose-run = $(compose) run -T --rm
compose-exec-wiki = $(compose) exec -T wiki

show-current-target = @echo; echo "======= $@ ========"

# ======== CI ========
# ======== Global Targets ========
.PHONY: ci
ci: install composer-test

.PHONY: ci-coverage
ci-coverage: install composer-test-coverage

.PHONY: install
install: destroy up .install

.PHONY: up
up: .init .build .up

.PHONY: down
down: .init .down

.PHONY: destroy
destroy: .init .destroy

.PHONY: bash
bash: up .bash

# ======== General Docker-Compose Helper Targets ========

.PHONY: show-logs
show-logs: .init
	$(show-current-target)
	$(compose-ci) logs -f || true

.PHONY: .build
.build:
	$(show-current-target)
	$(compose-ci) build wiki
.PHONY: .up
.up:
	$(show-current-target)
	$(compose-ci) up -d

.PHONY: .install
.install: .wait-for-db
	$(show-current-target)
	$(compose-exec-wiki) bash -c "sudo -u www-data \
		php maintenance/install.php \
		    --pass=wiki4everyone --server=http://localhost:8080 --scriptpath='' \
    		--dbname=wiki --dbuser=wiki --dbpass=wiki $(WIKI_DB_CONFIG) wiki WikiSysop && \
		cat __setup_extension__ >> LocalSettings.php && \
		sudo -u www-data php maintenance/update.php --skip-external-dependencies --quick \
		"

.PHONY: .down
.down:
	$(show-current-target)
	$(compose-ci) down

.PHONY: .destroy
.destroy:
	$(show-current-target)
	$(compose-ci) down -v

.PHONY: .bash
.bash: .init
	$(show-current-target)
	$(compose-ci) exec wiki bash -c "cd $(EXTENSION_FOLDER) && bash"

# ======== Test Targets ========

.PHONY: composer-test
composer-test:
	$(show-current-target)
	$(compose-exec-wiki) bash -c "cd $(EXTENSION_FOLDER) && composer phpunit"

.PHONY: composer-test-coverage
composer-test-coverage:
	$(show-current-target)
	$(compose-exec-wiki) bash -c "cd $(EXTENSION_FOLDER) && composer phpunit-coverage"

# ======== Helpers ========
.PHONY: .init
.init:
	$(show-current-target)
	$(eval COMPOSE_ARGS = --project-name ${extension}-$(DB_TYPE) --profile $(DB_TYPE))
ifeq ($(DB_TYPE), sqlite)
	$(eval WIKI_DB_CONFIG = --dbtype=$(DB_TYPE) --dbpath=/tmp/sqlite)
else
	$(eval WIKI_DB_CONFIG = --dbtype=$(DB_TYPE) --dbserver=$(DB_TYPE) --installdbuser=root --installdbpass=database)
endif
	@echo "COMPOSE_ARGS: $(COMPOSE_ARGS)"

.PHONY: .wait-for-db
.wait-for-db:
	$(show-current-target)
ifeq ($(DB_TYPE), mysql)
	$(compose-run) wait-for $(DB_TYPE):3306 -t 120
else ifeq ($(DB_TYPE), postgres)
	$(compose-run) wait-for $(DB_TYPE):5432 -t 120
endif

