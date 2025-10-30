FROM php:8.3-apache

# Set working directory
WORKDIR /var/www/html

# Install dependencies (Bookworm-safe)
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng16-dev \
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
 && docker-php-ext-configure intl \
 && docker-php-ext-install intl pdo pdo_mysql mbstring xml ctype bcmath zip fileinfo \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

# ✅ Build IMAP directly from PHP source (no missing system packages)
RUN docker-php-source extract \
 && cd /usr/src/php/ext/imap \
 && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
 && docker-php-ext-install imap \
 && docker-php-source delete

# Enable Apache rewrite
RUN a2enmod rewrite

# Copy app source code
COPY . .

# Set document root to /public (Laravel-style)
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Enable allow_url_fopen
RUN echo "allow_url_fopen=On" > /usr/local/etc/php/conf.d/custom.ini

# Install Composer globally
RUN wget https://getcomposer.org/installer -O composer-setup.php \
 && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
 && rm composer-setup.php

EXPOSE 80
CMD ["apache2-foreground"]
