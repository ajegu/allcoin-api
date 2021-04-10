FROM php:8-fpm
RUN apt-get update && apt-get install -y
RUN apt-get install nodejs -y
RUN apt-get install npm -y
RUN apt-get install -y \
        libzip-dev \
        zip \
  && docker-php-ext-install zip
WORKDIR /var/www/html
COPY --from=composer /usr/bin/composer /usr/bin/composer