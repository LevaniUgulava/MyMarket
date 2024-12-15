FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libzip-dev unzip git libexif-dev && \
    docker-php-ext-install pdo_mysql zip bcmath exif && \
    a2enmod rewrite

WORKDIR /var/www/backend

COPY ./000-default.conf /etc/apache2/sites-available/000-default.conf

COPY . .

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    composer install --no-dev --prefer-dist --optimize-autoloader

RUN chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

EXPOSE 80

CMD ["apache2-foreground"]
