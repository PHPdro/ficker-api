FROM webdevops/php-apache:8.2-alpine

RUN apk update && apk upgrade
RUN apk add --update --no-cache oniguruma-dev libcurl libxml2-dev wget nano

# Install Laravel framework system requirements (https://laravel.com/docs/11.x/deployment)
RUN docker-php-ext-install ctype curl dom filter hash fileinfo mbstring pdo openssl pcre session tokenizer xml

# Copy Composer binary from the Composer official Docker image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Clean up
RUN apk del gcc musl-dev wget
RUN rm -rf /tmp/.zip /var/cache/apk/ /tmp/pear/

ENV APP_ENV=local
ENV PHP_DATE_TIMEZONE America/Maceio
ENV WEB_DOCUMENT_ROOT /app/public

WORKDIR /app

COPY . .

RUN composer install --no-interaction --optimize-autoloader
RUN cp .env.example .env
RUN php artisan key:generate
RUN php artisan optimize
RUN chown -R application /app/storage

EXPOSE 80
