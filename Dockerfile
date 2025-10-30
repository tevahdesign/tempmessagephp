FROM php:8.3-apache

# Set working directory
WORKDIR /var/www/html

# Install base system dependencies
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
    build-essential \
 && docker-php-ext-configure intl \
 && docker-php-ext-install intl pdo pdo_mysql mbstring xml ctype bcmath zip fileinfo \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

# Build IMAP from PHP source (avoids missing libc-client-dev)
RUN docker-php-source extract \
 && cd /usr/src/php/ext/imap \
 && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
 && docker-php-ext-install imap \
 && docker-php-source delete

# Enable Apache rewrite
RUN a2enmod rewrite

# Copy application code
COPY . .

# Set Apache DocumentRoot to /public (for Laravel or similar frameworks)
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# PHP custom configuration
RUN echo "allow_url_fopen=On" > /usr/local/etc/php/conf.d/custom.ini

# Install Composer
RUN wget https://getcomposer.org/installer -O composer-setup.php \
 && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
 && rm composer-setup.php

EXPOSE 80
CMD ["apache2-foreground"]
