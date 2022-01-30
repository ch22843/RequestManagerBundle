FROM php:8.1.1-fpm

RUN apt-get update && apt-get install -y zlib1g-dev libpng-dev g++ git libicu-dev zip libzip-dev zip libpq-dev \
    && docker-php-ext-install zip intl opcache pdo pgsql pdo_pgsql gd sockets bcmath \
    && pecl install apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-enable apcu

WORKDIR /var/www/code

COPY . /var/www/code/

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony/bin/symfony /usr/local/bin/symfony

# # RUN composer self-update --1
# RUN composer install

# RUN chmod -R 777 var/