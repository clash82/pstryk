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
    echo cs-fix       - fix coding standards using php-cs-fixer tool
    echo phpstan      - analyse code with phpstan tool
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

if ["%1"] == ["cs-fix"] (
    php vendor\friendsofphp\php-cs-fixer\php-cs-fixer fix
)

if ["%1"] == ["phpstan"] (
    php vendor\phpstan\phpstan\phpstan analyse src --level=max --memory-limit=1G
)

if ["%1"] == ["prod"] (
    set APP_ENV=prod
    composer install -o --no-dev
    composer dump-env prod
    yarn encore production

    echo Build optimized for deployment.
    echo This build from now on will not work in `dev` environment.
)
