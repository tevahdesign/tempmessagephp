FROM php:8.3-fpm

# Install dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libxml2-dev \
    libicu-dev \
    libssl-dev \
    libkrb5-dev \
    libc-client-dev \
    libkrb5-dev \
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

# Enable recommended PHP settings
RUN echo "allow_url_fopen=On" >> /usr/local/etc/php/conf.d/docker-php-allow-url-fopen.ini

# Expose port
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
