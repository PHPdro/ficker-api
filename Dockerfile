FROM webdevops/php-apache:8.1-alpine

# Install Laravel framework system requirements (https://laravel.com/docs/10.x/deployment#optimizing-configuration-loading)
RUN apk update && apk upgrade
RUN apk add --update --no-cache oniguruma-dev libxml2-dev wget
RUN docker-php-ext-install bcmath
RUN docker-php-ext-install ctype
RUN docker-php-ext-install fileinfo
RUN docker-php-ext-install mbstring
RUN docker-php-ext-install pdo
RUN docker-php-ext-install calendar

# Copy Composer binary from the Composer official Docker image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Clean up
RUN apk del gcc musl-dev wget
RUN rm -rf /tmp/.zip /var/cache/apk/ /tmp/pear/

ENV APP_ENV=local
ENV PHP_DATE_TIMEZONE America/Maceio

# Script to run after container starts
RUN { echo "cd /app && composer install --no-interaction --optimize-autoloader"; \
    # Generating app key
    echo "php artisan key:generate"; \
    # Optimizing Route loading
    echo "php artisan route:cache"; \
    # Optimizing View loading
    echo "php artisan view:cache"; \
    # Migrating database
    echo "php artisan migrate --force"; \
    } > /opt/docker/provision/entrypoint.d/start.sh

# Permission to execute the script
RUN chmod +x /opt/docker/provision/entrypoint.d/start.sh

WORKDIR /app

EXPOSE 80
