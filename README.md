# Isnaad Task

## How to install:

#### - Firstly this app use:
- PHP 8.3, Laravel 11 and MySQL 8

#### - Some steps to run the app:

- git clone https://github.com/karemshawky/isnaad.git
- cd `</app-folder>`
- We need to run some commands:
    > cp .env.example .env

    > composer install

    > php artisan key:generate

- Change url in `.env` file (as your environment)
- Change database values in `.env` file to connect it
- To migrate database
    > php artisan migrate --seed
- change the base url in postman requests like the APP_URL in env file

## Visit the endpoints

> http://<base_url>/api/

## For unit test run:
> php artisan test
