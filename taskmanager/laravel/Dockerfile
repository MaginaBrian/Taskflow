FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git curl zip unzip libonig-dev libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip mbstring

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN php artisan config:clear

EXPOSE 10000

CMD ["sh", "-c", "if [ -n \"$AIVEN_CA_PEM\" ]; then printf \"%s\" \"$AIVEN_CA_PEM\" > /tmp/aiven-ca.pem; export MYSQL_ATTR_SSL_CA=/tmp/aiven-ca.pem; fi; php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"]
