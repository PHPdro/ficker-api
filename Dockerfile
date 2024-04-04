FROM webdevops/php:8.2-alpine

ENV WEB_DOCUMENT_ROOT=/app \
    WEB_DOCUMENT_INDEX=index.php \
    WEB_ALIAS_DOMAIN=*.vm \
    WEB_PHP_TIMEOUT=600 \
    WEB_PHP_SOCKET=""
ENV WEB_PHP_SOCKET=127.0.0.1:9000
# ENV APACHE_RUN_USER='#1000' \
#     APACHE_RUN_GROUP='#1000'

COPY conf/ /opt/docker/

RUN set -x \
    # Install apache
    && apk-install \
        apache2 \
        apache2-ctl \
        apache2-utils \
        apache2-proxy \
        apache2-ssl \
    # Fix issue with module loading order of lbmethod_* (see https://serverfault.com/questions/922573/apache2-fails-to-start-after-recent-update-to-2-4-34-no-clue-why)
    && sed -i '2,5{H;d}; ${p;x;s/^\n//}' /etc/apache2/conf.d/proxy.conf \
    && sed -ri ' \
        s!^(\s*CustomLog)\s+\S+!\1 /proc/self/fd/1!g; \
        s!^(\s*ErrorLog)\s+\S+!\1 /proc/self/fd/2!g; \
        ' /etc/apache2/httpd.conf \
    # $$ chown -R 1000:1000 apache2 \
    && docker-run-bootstrap \
    && docker-image-cleanup

EXPOSE 80 443

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

WORKDIR /app

COPY . .

RUN composer install --no-interaction --optimize-autoloader
RUN cp .env.example .env
RUN php artisan key:generate
RUN php artisan optimize

EXPOSE 8080
