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

- Change url in `.env` file (as your environment).
- Change database values in `.env` file to connect it.
- To migrate database
    > php artisan migrate --seed
- Change the `<base-url>` in postman requests like the APP_URL in your `.env` file from order-endpoint.txt file.
- Change `MAIL_MAILER` value in `.env` file to `log` to view the notified mail.

## Visit the endpoint

> http://<base_url>/api/v1/orders

## Import the endpoint to postman from
- `Isnaad.postman_collection.json` file

## Or import cURL request to postman from
- `order-endpoint.txt` file

## For unit test run:
> php artisan test
