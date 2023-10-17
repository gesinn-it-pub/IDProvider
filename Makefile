-include .env
export

# setup for docker-compose-ci build directory
# delete "build" directory to update docker-compose-ci

ifeq (,$(wildcard ./build/))
    $(shell git submodule update --init --remote)
endif


EXTENSION=IDProvider

# docker images
MW_VERSION?=1.35
PHP_VERSION?=7.4
DB_TYPE?=sqlite
DB_IMAGE?=""

# extensions

# composer
# Enables "composer update" inside of extension
COMPOSER_EXT?=true

# Enables node.js related tests and "npm install"
# NODE_JS?=true


include build/Makefile

