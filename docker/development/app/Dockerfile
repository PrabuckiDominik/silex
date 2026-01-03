FROM node:22.9.0 AS node
FROM php:8.3-fpm

ARG USER_NAME=host-user
ARG USER_ID=1000
ARG PHP_FPM_GROUP=www-data

RUN adduser \
        --disabled-password \
        --uid ${USER_ID} \
        ${USER_NAME} \
    && usermod \
        --append \
        --groups \
        ${PHP_FPM_GROUP} \
        ${USER_NAME}

COPY --from=node --chown=${USER_NAME}:root /usr/local/lib/node_modules /usr/local/lib/node_modules
COPY --from=node --chown=${USER_NAME}:root /usr/local/bin/node /usr/local/bin/node
RUN ln --symbolic /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && chown --no-dereference ${USER_NAME}:root /usr/local/bin/npm \
    && ln --symbolic /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx \
    && chown --no-dereference ${USER_NAME}:root /usr/local/bin/npx

COPY --from=composer/composer:2.4.4 /usr/bin/composer /usr/bin/composer

RUN apt-get update
RUN apt-get install --assume-yes bash zip git libzip-dev cron libpq-dev
RUN pecl install redis-6.1.0
RUN docker-php-ext-install zip pdo_pgsql
RUN docker-php-ext-configure zip
RUN docker-php-ext-enable redis

COPY ./docker/development/app/dev.sh /dev.sh
RUN chmod +x /dev.sh

ENTRYPOINT ["php-fpm"]
