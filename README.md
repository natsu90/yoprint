
# YoPrint File Import

https://www.notion.so/YoPrint-Laravel-Coding-Project-Spec-1e50db99ea52806fa1fde6e8fdf73b89

![UI screenshot](/screenshot.png "UI screenshot")

## Prerequisites
* PHP 8.4
* NPM 11.6
* MySQL 8.0
* Redis

### Laravel Sail

If you have Docker installed, you can run following command,
```
docker compose up
```

## Installation

```
# Setup environment variables
cp .env.example .env

# Install PHP libraries & dependencies
composer install

# Install NPM libraries
npm install

# Generate app key
php artisan key:generate

# Run DB migration
php artisan migrate
```


## Setup
Start each process in its own terminal

```
# Laravel Horizon
php artisan horizon

# Server-Sent Events regular ping
php artisan sse:ping --interval=30

# Frontend
npm run dev

```

## Test
```
php artisan test
```

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
