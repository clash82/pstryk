Installation
============

- clone this repo
- install Composer dependencies:

```bash
composer install
```

- create `.env.local` file based on the following configuration:

```ini
APP_ENV=dev
APP_SECRET=c13b4fa7c2db1758fefcb8b8ab03e90a

# ref. https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#connecting-using-a-url
DATABASE_URL=mysql://db_user:db_pass@127.0.0.1:3306/db_name?serverVersion=5.7
```

- install Node.js and Yarn and after that install dependencies:

```bash
yarn install
```

- compile assets (`make` will work on Windows only, check content of the `make.bat` file to get more details about the commands used below):

```bash
make assets
```

- create database structure and load fixtures:

```bash
make db-reload
``` 

Testing
=======

- use built-in command to run phpunit test suite:

```bash
make test
```

Deployment
==========

- prepare deployment:

```bash
make prod
```

- go back to work in `dev` mode:

```bash
make dev
```
