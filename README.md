This repository is a home for `Pstryk` gallery framework.

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/clash82/pstryk/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/clash82/pstryk/?branch=master)
[![Maintainability](https://api.codeclimate.com/v1/badges/8bbe8819071d7b533f33/maintainability)](https://codeclimate.com/github/clash82/pstryk/maintainability)

App was built on top of PHP 7.4 and Symfony 5.2 framework.

Type `make` in project root directory to obtain list of useful commands which will help you work with the common tasks (eg. `make cache`).

Installation
------------

- clone this repo
- install Composer dependencies:

```bash
composer install
```

- create `.env.local` file based on the following configuration:

```ini
# In all environments, the following files are loaded if they exist,
# the latter taking precedence over the former:
#
#  * .env                contains default values for the environment variables needed by the app
#  * .env.local          uncommitted file with local overrides
#  * .env.$APP_ENV       committed environment-specific defaults
#  * .env.$APP_ENV.local uncommitted environment-specific overrides
#
# Real environment variables win over .env files.
#
# DO NOT DEFINE PRODUCTION SECRETS IN THIS FILE NOR IN ANY OTHER COMMITTED FILES.
#
# Run "composer dump-env prod" to compile .env files for production use (requires symfony/flex >=1.2).
# https://symfony.com/doc/current/best_practices.html#use-environment-variables-for-infrastructure-configuration

###> symfony/framework-bundle ###
APP_ENV=dev
APP_SECRET=c13b4fa7c2db1758fefcb8b8ab03e90a
#TRUSTED_PROXIES=127.0.0.0/8,10.0.0.0/8,172.16.0.0/12,192.168.0.0/16
#TRUSTED_HOSTS='^(localhost|example\.com)$'
###< symfony/framework-bundle ###

###> symfony/mailer ###
# MAILER_DSN=smtp://localhost
###< symfony/mailer ###

###> doctrine/doctrine-bundle ###
# Format described at https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#connecting-using-a-url
# For an SQLite database, use: "sqlite:///%kernel.project_dir%/var/data.db"
# For a PostgreSQL database, use: "postgresql://db_user:db_password@127.0.0.1:5432/db_name?serverVersion=11&charset=utf8"
# IMPORTANT: You MUST configure your server version, either here or in config/packages/doctrine.yaml
DATABASE_URL=mysql://db_user:db_pass@127.0.0.1:3306/db_name?serverVersion=5.7
###< doctrine/doctrine-bundle ###
```

- install Node.js and Yarn and after that install dependencies:

```bash
yarn install
```

- compile assets:

```bash
make assets
```

- create database structure and load fixtures:

```bash
make db-reload
``` 

Testing
-------

- use built-in command to run phpunit test suite:

```bash
make test
```

Deployment
----------

- prepare deployment:

```bash
make prod
```

- go back to work in `dev` mode:

```bash
make dev
```

Gallery in action
-----------------

Sites powered by gallery framework:

- [Stalker Photo](https://stalker.toborek.info)
- [Galeria pewnego pstryka](https://pstryk.toborek.info)
- [Rafał Story](https://rafal.toborek.info)
- [Z archiwum RP Foto](https://rpfoto.toborek.info)

Who and why?
------------

Me - [Rafał Toborek](https://kontakt.toborek.info) and because I was in need for a flexible solution to build galleries for my photos. Framework was build to satisfy my expectations and for sure will not be suitable for everyone. But anyways... feel free to check what's under the hood of my websites and to learn something new.

My offer
--------

If you like my framework and/or you've seen my sites in action and you want me to help you to create a gallery for you then please do not hesitate to [contact me](https://kontakt.toborek.info).
