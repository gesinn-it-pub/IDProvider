EXTENSION := IDProvider

MW_VERSION ?= 1.35
IMAGE_VERSION := $(MW_VERSION)

# -------------------------------------------------------------------
extension := $(shell echo $(EXTENSION) | tr A-Z a-z})
IMAGE_NAME := $(extension):test-$(IMAGE_VERSION)
EXTENSION_FOLDER := /var/www/html/extensions/${EXTENSION}

compose = IMAGE_NAME=$(IMAGE_NAME) docker-compose $(COMPOSE_ARGS)
compose-run = $(compose) run --rm
compose-exec-wiki = $(compose) exec wiki

show-current-target = @echo; echo "======= $@ ========"

.PHONY: ci
ci: install
	$(show-current-target)
	$(compose-exec-wiki) bash -c "cd $(EXTENSION_FOLDER) && composer phpunit"

.PHONY: ci-coverage
ci-coverage: install
	$(show-current-target)
	$(compose-exec-wiki) bash -c "cd $(EXTENSION_FOLDER) && composer phpunit-coverage"

.PHONY: install
install: destroy up .install

.PHONY: up
up: .init .build .up

.PHONY: down
down: .init .down

.PHONY: destroy
destroy: .init .destroy

.PHONY: bash
bash: .init
	$(show-current-target)
	$(compose-exec-wiki) bash -c "cd $(EXTENSION_FOLDER) && bash"

.PHONY: show-logs
show-logs: .init
	$(show-current-target)
	$(compose) logs -f || true

.PHONY: .build
.build:
	$(show-current-target)
	$(compose) build --build-arg MW_VERSION=$(MW_VERSION) wiki

.PHONY: .up
.up:
	$(show-current-target)
	$(compose) up -d

.PHONY: .install
.install: .wait-for-db
	$(show-current-target)
	$(compose-exec-wiki) bash -c "sudo -u www-data \
		php maintenance/install.php \
		    --pass=wiki4everyone --server=http://localhost:8080 --scriptpath='' \
    		--dbname=wiki --dbuser=wiki --dbpass=wiki $(WIKI_DB_CONFIG) wiki WikiSysop && \
		echo 'require_once(\"\\$$IP/LocalSettings.Include.php\");' >> LocalSettings.php \
		"

.PHONY: .down
.down:
	$(show-current-target)
	$(compose) down

.PHONY: .destroy
.destroy:
	$(show-current-target)
	$(compose) down -v

.PHONY: .wait-for-db
.wait-for-db:
	$(show-current-target)
ifneq ($(DB_TYPE), sqlite)
	$(compose-run) wait-for $(DB_TYPE):3306 -t 120
endif

.PHONY: .init
.init:
	$(show-current-target)
ifeq ($(DB_TYPE), mysql)
	$(eval COMPOSE_ARGS = --project-name idprovider-mysql --profile mysql)
	$(eval WIKI_DB_CONFIG = --dbtype=mysql --dbserver=mysql --installdbuser=root --installdbpass=database)
else
	$(eval COMPOSE_ARGS = --project-name idprovider-sqlite)
	$(eval WIKI_DB_CONFIG = --dbtype=sqlite --dbpath=/data/sqlite)
endif
	@echo "COMPOSE_ARGS: $(COMPOSE_ARGS)"
