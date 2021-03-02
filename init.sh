#!/bin/bash

docker-compose exec app composer install --optimize-autoloader --no-dev
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan config:cache
