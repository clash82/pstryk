@echo off
set APP_ENV=dev

if ["%1"] == [""] (
    echo Available arguments for 'make' command are:
    echo.
    echo cache        - rebuild Symfony cache
    echo db-reload    - rebuild database structure and load fixtures
    echo pass         - start user password generator
    echo assets       - build assets using webpack-encore
    echo assets-watch - recompile assets automatically when files change
    echo prod         - install only `prod` dependencies and optimize build before deployment
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
    php bin\console doctrine:fixtures:load -n
)

if ["%1"] == ["pass"] (
    php bin/console security:encode-password
)

if ["%1"] == ["assets"] (
    yarn encore dev
)

if ["%1"] == ["assets-watch"] (
    yarn encore dev --watch
)

if ["%1"] == ["prod"] (
    set APP_ENV=prod
    composer install -o --no-dev
    composer dump-env prod
    yarn encore production

    echo Build optimized for deployment.
    echo This build from now on will not work in `dev` environment.
)
