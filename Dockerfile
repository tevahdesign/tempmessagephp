# Use official PHP 8.3 + Apache image
FROM php:8.3-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies for extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    libmcrypt-dev \
    libssl-dev \
    libbz2-dev \
    libcurl4-openssl-dev \
    libkrb5-dev \
    libpq-dev \
    libimap-dev \
    zlib1g-dev \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql mbstring tokenizer xml ctype json bcmath zip fileinfo iconv \
    && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install imap \
    && docker-php-ext-enable imap

# Enable Apache rewrite module
RUN a2enmod rewrite

# Copy all files to container
COPY . .

# Set Apache document root to /public
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Set recommended PHP settings
RUN echo "allow_url_fopen=On" >> /usr/local/etc/php/conf.d/custom.ini

# Enable symlink function
RUN ln -sf /var/www/html/storage /var/www/html/public/storage

# Permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
