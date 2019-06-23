FROM php:7.1-fpm

LABEL description="PHP 7.1 image for JW app"
LABEL version="1.0"

RUN apt-get update && apt-get install -y \
    zip \
    unzip  \
    libpng-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libxpm-dev \
    libvpx-dev \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-configure gd \
        --with-freetype-dir=/usr/include/ \
        --with-jpeg-dir=/usr/include/ \
        --with-xpm-dir=/usr/include/ \
        --with-vpx-dir=/usr/include/ \
    && docker-php-ext-install gd \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN apt-get install -y \
        libzip-dev \
        zip \
  && docker-php-ext-configure zip --with-libzip \
  && docker-php-ext-install zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

WORKDIR /var/www/html
COPY . .

CMD ["php-fpm"]
