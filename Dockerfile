# syntax=docker/dockerfile:1

##
## Stage 1: build. Needs both PHP (for composer + the wayfinder artisan
## introspection that the Vite build depends on) and Node (for the frontend
## bundle). Nothing from this stage ships in the final image except the
## installed vendor/ and the compiled public/build assets.
##
FROM serversideup/php:8.4-fpm-alpine AS builder

USER root

RUN apk add --no-cache libstdc++ libgcc

COPY --from=mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/bin/
RUN install-php-extensions bcmath

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=node:22-alpine /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22-alpine /usr/local/lib/node_modules/npm /usr/local/lib/node_modules/npm
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --no-progress --prefer-dist

COPY . .

# Wayfinder (the Vite plugin that generates typed routes/actions) introspects
# the app's real routes via artisan, so the framework must be bootable here.
# APP_KEY is a throwaway build-time value; the real one is set at runtime.
ENV APP_ENV=production
ENV APP_KEY=base64:MDEyMzQ1Njc4OTAxMjM0NTY3ODkwMTIzNDU2Nzg5MDE=
ENV DB_CONNECTION=sqlite
ENV DB_DATABASE=/tmp/build.sqlite
ENV CACHE_STORE=array
ENV SESSION_DRIVER=array
ENV QUEUE_CONNECTION=sync

RUN touch /tmp/build.sqlite \
    && composer dump-autoload --optimize \
    && npm ci \
    && npm run build

##
## Stage 2: runtime. A lean PHP-FPM image with only the compiled application.
##
FROM serversideup/php:8.4-fpm-alpine AS runtime

USER root

COPY --from=mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/bin/
RUN install-php-extensions bcmath

ENV PHP_OPCACHE_ENABLE=1 \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=0 \
    PHP_OPCACHE_MAX_ACCELERATED_FILES=20000 \
    PHP_OPCACHE_MEMORY_CONSUMPTION=128

WORKDIR /var/www/html

COPY --from=builder /app/vendor ./vendor
COPY . .
COPY --from=builder /app/public/build ./public/build

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache \
    && chmod +x docker/entrypoint.sh

USER www-data

EXPOSE 9000

ENTRYPOINT ["docker/entrypoint.sh"]
CMD ["php-fpm"]

##
## Stage 3: nginx. Serves the same public/ directory as a static webroot and
## proxies *.php to the app (php-fpm) service. Kept as its own image so the
## web server never has access to anything outside public/.
##
FROM nginx:1.27-alpine AS web

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=builder /app/public /var/www/html/public

