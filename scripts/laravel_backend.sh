#!/bin/bash

# Install dependencies
rm -rf ./vendor
rm ./composer.lock
composer install

# Environment
cp .env.example .env
php artisan key:generate 

# Configure app
rm -rf public/storage
php artisan storage:link

# Create database
touch ./database/database.sqlite
php artisan migrate:fresh
read -n 1 -s -r -p "Check database and press any key to continue..."

# Rollback migrations
php artisan migrate:rollback
read -n 1 -s -r -p "Check database and press any key to continue..."

# Seed database
php artisan migrate
php artisan db:seed

# Tests
php artisan test ./tests/Feature/AdminTest.php

# Deploy app
php artisan serve