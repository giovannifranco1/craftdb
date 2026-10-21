# syntax=docker/dockerfile:1

ARG PHP_IMAGE=dunglas/frankenphp:1-php8.4-bookworm

# --- vendor: dependências PHP de produção ---
# (só zip, para o composer extrair os pacotes; as demais extensões ficam no estágio app)
FROM ${PHP_IMAGE} AS vendor
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN install-php-extensions zip
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --ignore-platform-reqs

# --- assets: build do Vite (precisa do CSS do Flux em vendor/) ---
FROM node:22-bookworm-slim AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY vite.config.js tailwind.config.js ./
COPY resources ./resources
COPY app ./app
COPY --from=vendor /app/vendor/livewire/flux ./vendor/livewire/flux
COPY --from=vendor /app/vendor/laravel/framework/src/Illuminate/Pagination/resources/views ./vendor/laravel/framework/src/Illuminate/Pagination/resources/views
RUN npm run build

# --- app: runtime ---
FROM ${PHP_IMAGE} AS app
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN install-php-extensions pdo_mysql intl zip bcmath gd pcntl opcache
WORKDIR /app

COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

RUN composer dump-autoload --no-dev --optimize --classmap-authoritative \
    && php artisan package:discover --ansi \
    && php artisan event:cache --ansi \
    && php artisan view:cache --ansi \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache /data \
    && chown -R www-data:www-data storage bootstrap/cache /data \
    && chmod +x docker/entrypoint.sh \
    && rm /usr/bin/composer

ENV SERVER_NAME=:80 \
    APP_ENV=production \
    APP_DEBUG=false

EXPOSE 80
ENTRYPOINT ["docker/entrypoint.sh"]
CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
