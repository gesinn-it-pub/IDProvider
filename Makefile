-include .env
export

# setup for docker-compose-ci build directory
# delete "build" directory to update docker-compose-ci

ifeq (,$(wildcard ./build/))
    $(shell git submodule update --init --remote)
endif


EXTENSION=IDProvider

# docker images
MW_VERSION?=1.39
PHP_VERSION?=8.1
DB_TYPE?=mysql
DB_IMAGE?="mysql:8"

# extensions

# composer
# Enables "composer update" inside of extension
COMPOSER_EXT?=true

# Enables node.js related tests and "npm install"
# NODE_JS?=true


include build/Makefile

.PHONY: composer-phan
composer-phan: .init
ifdef COMPOSER_EXT
	$(show-current-target)
	$(compose-exec-wiki) bash -c "cd $(EXTENSION_FOLDER) && composer phan $(COMPOSER_PARAMS)"
endif
