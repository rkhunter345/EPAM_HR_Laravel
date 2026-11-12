#!/usr/bin/env bash
cd "$(dirname "$0")"
composer install
[ -f .env ] || cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
echo "  Abre http://127.0.0.1:8000  (admin / epam123)"
php artisan serve
