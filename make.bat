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
    echo === Coding standards tools ===
    echo phpstan      - analyse code with phpstan tool
    echo phan         - analyse code with phan tool
    echo phpmd        - analyse code with php md tool
    echo phpcs        - analyse code with phpcs tool
    echo phpcbf       - fix coding standards using phpcbf tool
    echo cs-fix       - fix coding standards using php-cs-fixer tool
    echo cs-all       - execute all available coding analysers
    echo fix-all      - execute all available fixers
    echo.
    echo === Deployment tools ===
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
    php vendor\phpstan\phpstan\phpstan analyse src --level=max --memory-limit=1G
)

if ["%1"] == ["phan"] (
    php vendor\phan\phan\phan
)

if ["%1"] == ["phpmd"] (
    php vendor\phpmd\phpmd\src\bin\phpmd src text cleancode
)

if ["%1"] == ["phpcs"] (
    php vendor\squizlabs\php_codesniffer\bin\phpcs src --standard=PSR2 -p -n
)

if ["%1"] == ["phpcbf"] (
    php vendor\squizlabs\php_codesniffer\bin\phpcbf src --standard=PSR2 -p
)

if ["%1"] == ["fix-all"] (
    make cs-fix
    make phpcbf
)

if ["%1"] == ["cs-all"] (
    make phpstan
    make phpcs
    make phpmd
)

if ["%1"] == ["dev"] (
    @RD /S /Q "var\cache\prod"
    @del "var\log\prod.log" /Q
    @del ".env.local.php"

    composer install
    yarn install
    make assets

    echo Build optimized for development.
    echo Type `make prod` before deployment.
)

if ["%1"] == ["prod"] (
    set APP_ENV=prod
    composer install -o --no-dev
    composer dump-env prod
    yarn encore production

    echo Build optimized for deployment.
    echo This build from now on will not work in `dev` environment.
)
