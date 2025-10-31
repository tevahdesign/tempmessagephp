FROM php:8.3-fpm

# Install dependencies including oniguruma for mbstring
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
    libcurl4-openssl-dev \
    libonig-dev \
 && docker-php-ext-configure intl \
 && docker-php-ext-install intl pdo pdo_mysql mbstring xml ctype bcmath zip fileinfo gd \
 && pecl install imap \
 && docker-php-ext-enable imap \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable common PHP options
RUN echo "allow_url_fopen=On" > /usr/local/etc/php/conf.d/docker-php-allow-url-fopen.ini

WORKDIR /var/www/html
COPY . .

EXPOSE 9000
CMD ["php-fpm"]
