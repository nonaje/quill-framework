FROM php:8.3-fpm-alpine

# Essentials
RUN echo "UTC" > /etc/timezone

RUN apk add --no-cache \
    freetype-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    libxml2-dev \
    libwebp-dev \
    autoconf \
    g++ \
    imagemagick-dev \
    libtool \
    make \
    curl \
    sqlite \
    postgresql-dev \
    libzip-dev && \
    docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp

# Installing bash
RUN apk add bash
RUN sed -i 's/bin\/ash/bin\/bash/g' /etc/passwd

# Installing PECL
RUN pecl install redis

# Installing PHP
RUN apk add --no-cache php83 \
    php83-fpm
#    php83-fpm \
#    php83-common \
#    php83-pdo \
#    php83-opcache \
#    php83-zip \
#    php83-phar \
#    php83-iconv \
#    php83-cli \
#    php83-curl \
#    php83-openssl \
#    php83-mbstring \
#    php83-tokenizer \
#    php83-fileinfo \
#    php83-json \
#    php83-xml \
#    php83-xmlwriter \
#    php83-simplexml \
#    php83-dom \
#    php83-pdo_sqlite \
#    php83-pdo_pgsql \
#    php83-tokenizer \
#    php83-pgsql \
#    php83-bcmath \
#    php83-redis

# Configure PHP
RUN mkdir -p /run/php/
RUN touch /run/php/php8.3-fpm.pid

# Configure PHP extensions
#RUN docker-php-ext-install sockets pdo pdo_pgsql pgsql zip exif gd bcmath
#RUN docker-php-ext-enable pdo pdo_pgsql redis exif bcmath

# Clear APK cache
RUN rm -rf /var/cache/apk/*

# Composer install
COPY --from=composer:2.5 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Setup www-data user
RUN apk add shadow && usermod -u 1000 www-data && groupmod -g 1000 www-data
RUN chown -R www-data:www-data ./

USER www-data
