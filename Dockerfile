FROM php:8.3-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libxml2-dev \
    libicu-dev \
    libssl-dev \
    libkrb5-dev \
    libpam0g-dev \
    libcurl4-openssl-dev \
    libc-client2007e-dev \
    zlib1g-dev \
    git \
    unzip \
    wget \
    pkg-config \
    build-essential \
 && docker-php-ext-configure intl \
 && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
 && docker-php-ext-install intl pdo pdo_mysql mbstring xml ctype bcmath zip fileinfo gd imap \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable allow_url_fopen
RUN echo "allow_url_fopen=On" > /usr/local/etc/php/conf.d/docker-php-allow-url-fopen.ini

EXPOSE 9000
CMD ["php-fpm"]
