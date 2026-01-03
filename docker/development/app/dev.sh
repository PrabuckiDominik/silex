#!/bin/bash

composer install
npm install
php artisan migrate
npm run dev
