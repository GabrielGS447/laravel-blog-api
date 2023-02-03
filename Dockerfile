FROM composer:2.4 AS composer

COPY . /app

RUN composer install \
  --no-dev \
  --quiet \
  --no-interaction \
  --optimize-autoloader \
  --no-ansi \
  --no-progress

FROM php:8.1.0-alpine AS deploy

RUN apk add --quiet --no-cache libpq-dev && \
  docker-php-ext-install pdo pdo_pgsql > /dev/null

WORKDIR /app

COPY --from=composer /app /app

CMD php artisan migrate && php artisan serve --host=0.0.0.0 --port=$PORT