FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libonig-dev \
    zip \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*


RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp && \
    docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    zip \
    intl \
    mbstring \
    exif \
    gd

RUN php -m | grep pgsql && \
    php -m | grep pdo_pgsql

RUN a2enmod rewrite

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --no-interaction --no-scripts

RUN composer dump-autoload --optimize

RUN chown -R www-data:www-data /var/www/html/writable && \
    chmod -R 777 /var/www/html/writable && \
    find /var/www/html/writable -type d -exec chmod 777 {} + && \
    find /var/www/html/writable -type f -exec chmod 666 {} +

CMD ["apache2-foreground"]
