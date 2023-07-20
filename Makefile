APP_CONTAINER_NAME=pstryk
APP_IMAGE_NAME=pstryk-image

define list_item
	@printf "*"
	@tput setaf 2
	@printf "$1 "
	@tput sgr0
	@echo $2
endef

help:
	@printf "Available arguments for <make> command are:\n\n"
	$(call list_item, "start", "build Docker image and start container (work in background)")
	$(call list_item, "stop", "stop and remove already working Docker container")
	$(call list_item, "logs", "show logs from Docker container")
	$(call list_item, "login", "login into working Docker container")
	$(call list_item, "status", "show status of the working Docker container")
	$(call list_item, "asset", "build assets using webpack-encore")
	$(call list_item, "asset-watch", "recompile assets automatically when files change")
	$(call list_item, "dev", "prepare development environment")
	$(call list_item, "prod", "install only prod dependencies and optimize build before deployment")
	$(call list_item, "cs-fix", "fix PHP coding standards using php-cs-fixer tool")
	$(call list_item, "rector", "fix PHP deprecations using rector tool")
	$(call list_item, "phpstan", "analyse PHP code structure using phpstan tool")
	$(call list_item, "test", "run test suite")
	@printf "\n"
.PHONY:

start:
	@docker build -t ${APP_CONTAINER_NAME} .
	@docker run --name ${APP_IMAGE_NAME} -d -p 80:80 -p 443:443 --mount type=bind,source="$(CURDIR)",target=/var/www/html ${APP_CONTAINER_NAME}
	@echo Pstryk is now being hosted under http://localhost
.PHONY:

stop:
	@docker stop ${APP_IMAGE_NAME}
	@docker rm ${APP_IMAGE_NAME}
.PHONY:

logs:
	@docker container logs ${APP_IMAGE_NAME}
.PHONY:

login:
	@docker exec -it ${APP_IMAGE_NAME} bash
.PHONY:

status:
	@docker ps -f name=${APP_IMAGE_NAME}
.PHONY:

asset:
	@docker exec -t ${APP_IMAGE_NAME} yarn encore dev
.PHONY:

asset-watch:
	@docker exec -t ${APP_IMAGE_NAME} yarn encore dev --watch
.PHONY:

dev:
	@docker exec -t ${APP_IMAGE_NAME} rm -rf var/cache/prod
	@docker exec -t ${APP_IMAGE_NAME} rm -f var/log/prod.log
	@docker exec -t ${APP_IMAGE_NAME} rm -f .env.local.php

	@docker exec -t ${APP_IMAGE_NAME} composer install
	@docker exec -t ${APP_IMAGE_NAME} yarn install

	make asset

	@echo Build optimized for development.
.PHONY:

prod:
	@docker exec -t ${APP_IMAGE_NAME} /bin/bash -c "export APP_ENV=prod && composer install -o --no-dev"
	@docker exec -t ${APP_IMAGE_NAME} composer dump-env prod
	@docker exec -t ${APP_IMAGE_NAME} yarn encore production

	@echo Build optimized for deployment.
.PHONY:

cs-fix:
	@docker exec -t ${APP_IMAGE_NAME} vendor/bin/php-cs-fixer fix
.PHONY:

rector:
	@docker exec -t ${APP_IMAGE_NAME} vendor/bin/rector process
.PHONY:

phpstan:
	@docker exec -t ${APP_IMAGE_NAME} vendor/bin/phpstan analyse --memory-limit=1G
.PHONY:

test:
	@docker exec -t ${APP_IMAGE_NAME} php bin/console doctrine:database:drop --force --env=test
	@docker exec -t ${APP_IMAGE_NAME} php bin/console doctrine:database:create --env=test
	@docker exec -t ${APP_IMAGE_NAME} php bin/console doctrine:schema:create --env=test
	@docker exec -t ${APP_IMAGE_NAME} php bin/console doctrine:fixtures:load -n --env=test

	@docker exec -t ${APP_IMAGE_NAME} vendor/bin/phpunit
.PHONY:
