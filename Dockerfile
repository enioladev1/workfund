# syntax=docker/dockerfile:1

##
## Stage 1: build. Needs both PHP (for composer + the wayfinder artisan
## introspection that the Vite build depends on) and Node (for the frontend
## bundle). Nothing from this stage ships in the final image except the
## installed vendor/ and the compiled public/build assets.
##
FROM php:8.4-fpm-alpine AS builder

RUN apk add --no-cache \
        nodejs \
        npm \
        postgresql-dev \
        sqlite-dev \
        autoconf \
        g++ \
        make \
        linux-headers \
    && docker-php-ext-install pdo_pgsql pdo_sqlite bcmath \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del autoconf g++ make linux-headers

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

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
FROM php:8.4-fpm-alpine AS runtime

RUN apk add --no-cache \
        postgresql-dev \
        autoconf \
        g++ \
        make \
        linux-headers \
    && docker-php-ext-install pdo_pgsql bcmath pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del autoconf g++ make linux-headers \
    && docker-php-ext-enable opcache

RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.validate_timestamps=0'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.memory_consumption=128'; \
    } > /usr/local/etc/php/conf.d/opcache-recommended.ini

WORKDIR /var/www/html

COPY --from=builder /app/vendor ./vendor
COPY . .
COPY --from=builder /app/public/build ./public/build

RUN addgroup -g 1000 www && adduser -G www -u 1000 -D www \
    && chown -R www:www /var/www/html \
    && chmod -R 775 storage bootstrap/cache \
    && chmod +x docker/entrypoint.sh

USER www

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

