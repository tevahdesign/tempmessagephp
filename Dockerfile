# Use official PHP FPM image as base
FROM php:8.3-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libxml2-dev \
    libicu-dev \
    libssl-dev \
    libkrb5-dev \
    zlib1g-dev \
    git \
    unzip \
    wget \
    pkg-config \
    build-essential \
    # lightweight uw-imap build deps
    libcurl4-openssl-dev \
 && docker-php-ext-configure intl \
 && docker-php-ext-install intl pdo pdo_mysql mbstring xml ctype bcmath zip fileinfo gd \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

# Optional: install imap extension via PECL (since libc-client removed in Trixie)
RUN pecl install imap && docker-php-ext-enable imap

# Enable common settings
RUN echo "allow_url_fopen=On" > /usr/local/etc/php/conf.d/docker-php-allow-url-fopen.ini

WORKDIR /var/www/html
COPY . .

EXPOSE 9000
CMD ["php-fpm"]
