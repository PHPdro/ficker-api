FROM webdevops/php-apache:8.2-alpine

# Install Laravel framework system requirements (https://laravel.com/docs/10.x/deployment#optimizing-configuration-loading)
RUN apk update && apk upgrade
RUN apk add --update --no-cache oniguruma-dev libxml2-dev wget nano
RUN docker-php-ext-install ctype curl dom fileinfo filter hash mbstring openssl pcre pdo session tokenizer xml

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

RUN composer install --optimize-autoloader --no-dev
RUN cp .env.example .env
RUN php artisan key:generate
RUN php artisan optimize
RUN chown -R application /app/storage

EXPOSE 80
