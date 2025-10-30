FROM php:8.3-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies for extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libxml2-dev \
    libicu-dev \
    libc-client-dev \
    libkrb5-dev \
    zlib1g-dev \
    unzip \
    git \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl pdo pdo_mysql mbstring xml ctype bcmath zip fileinfo \
    && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install imap \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache rewrite module
RUN a2enmod rewrite

# Copy project files
COPY . .

# Set Apache document root to /public
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# PHP configuration tweaks
RUN echo "allow_url_fopen=On" >> /usr/local/etc/php/conf.d/custom.ini

# Fix permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]
