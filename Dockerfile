FROM php:8.3-apache

# Set working directory
WORKDIR /var/www/html

# Update and install required dependencies
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
    libc-client-dev \
    libkrb5-dev \
 && docker-php-ext-configure intl \
 && docker-php-ext-install intl pdo pdo_mysql mbstring xml ctype bcmath zip fileinfo \
 && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
 && docker-php-ext-install imap \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache rewrite module
RUN a2enmod rewrite

# Copy project files
COPY . .

# Set document root to /public (important for Laravel or frameworks)
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# PHP settings
RUN echo "allow_url_fopen=On" > /usr/local/etc/php/conf.d/custom.ini

# Install Composer globally
RUN wget https://getcomposer.org/installer -O composer-setup.php \
 && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
 && rm composer-setup.php

# Expose Apache port
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
