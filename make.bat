@echo off
set APP_ENV=dev

if ["%1"] == [""] (
    echo Available arguments for 'make' command are:
    echo.
    echo cache     - rebuild Symfony cache
    echo db-reload - drop/create database and create schema/validate
    echo prod      - install only `prod` dependencies and optimize build before deployment
    echo.
    echo Default ENV is: %APP_ENV%
)

if ["%1"] == ["cache"] (
	php bin\console cache:clear
)

if ["%1"] == ["db-reload"] (
    php bin\console doctrine:database:drop --force
    php bin\console doctrine:database:create
    php bin\console doctrine:schema:create
    php bin\console doctrine:schema:validate
)

if ["%1"] == ["prod"] (
    set APP_ENV=prod
    composer install -o --no-dev
    composer dump-env prod

    echo Build optimized for deployment.
    echo This build from now on will not work in `dev` environment.
)
