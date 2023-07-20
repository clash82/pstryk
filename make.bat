@echo off
set APP_ENV=dev

if ["%1"] == [""] (
    echo Available arguments for 'make' command are:
    echo.
    echo cache        - rebuild Symfony cache
    echo db-reload    - rebuild database structure and load fixtures
    echo assets       - build assets using webpack-encore
    echo assets-watch - recompile assets automatically when files change
    echo.
    echo Coding standards tools:
    echo phpstan      - analyse code with phpstan
    echo rector       - fix code deprecations using rector tool
    echo cs-fix       - fix coding standards using php-cs-fixer tool
    echo.
    echo Tests:
    echo test         - execute PhpUnit tests
    echo.
    echo Deployment tools:
    echo dev          - prepare development environment
    echo prod         - install only `prod` dependencies and optimize build before deployment
    echo pass         - start user password generator
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
    php vendor\phpstan\phpstan\phpstan analyse --memory-limit=1G
)

if ["%1"] == ["rector"] (
    vendor\bin\rector process
)

if ["%1"] == ["test"] (
    php bin\console doctrine:database:drop --force --env=test
    php bin\console doctrine:database:create --env=test
    php bin\console doctrine:schema:create --env=test
    php bin\console doctrine:fixtures:load -n --env=test

    php vendor\bin\phpunit

    del var\test.db3
)

if ["%1"] == ["dev"] (
    @RD /S /Q "var\cache\prod"
    @del "var\log\prod.log" /Q
    @del ".env.local.php"

    composer install
    yarn install
    make assets

    echo Build optimized for development.
)

if ["%1"] == ["prod"] (
    set APP_ENV=prod
    composer install -o --no-dev
    composer dump-env prod
    yarn encore production

    echo Build optimized for deployment.
)
