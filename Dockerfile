FROM php:8.3-apache

WORKDIR /var/www/html

# Install dependencies safely
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libxml2-dev \
    libicu-dev \
    libssl-dev \
    libkrb5-dev \
    libpam0g-dev \
    libcurl4-openssl-dev \
    zlib1g-dev \
    git \
    unzip \
    wget \
    build-essential \
 && docker-php-ext-configure intl \
 && docker-php-ext-install intl pdo pdo_mysql mbstring xml ctype bcmath zip fileinfo \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

# --- Install IMAP manually from PHP source (no Debian package needed) ---
RUN docker-php-source extract \
 && cd /usr/src/php/ext/imap \
 && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
 && docker-php-ext-install imap \
 && docker-php-source delete

# Enable Apache rewrite
RUN a2enmod rewrite

# Copy app code
COPY . .

# Set document root to /public (for Laravel or similar)
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# PHP custom config
RUN echo "allow_url_fopen=On" > /usr/local/etc/php/conf.d/custom.ini

EXPOSE 80
CMD ["apache2-foreground"]
