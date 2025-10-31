FROM php:8.3-apache

# ---------- 1. Install Dependencies ----------
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libxml2-dev \
    libicu-dev \
    libssl-dev \
    zlib1g-dev \
    git \
    unzip \
    wget \
    pkg-config \
    build-essential \
    libcurl4-openssl-dev \
    libonig-dev \
    libkrb5-dev \
    libpam0g-dev \
    libssl-dev \
 && docker-php-ext-configure intl \
 && docker-php-ext-install intl pdo pdo_mysql mbstring xml ctype bcmath zip fileinfo gd

# ---------- 2. Patch + Build IMAP ----------
WORKDIR /usr/src
RUN git clone https://github.com/php/imap.git php-imap-fixed && \
    cd php-imap-fixed && \
    phpize && \
    ./configure --with-kerberos --with-imap-ssl && \
    make && make install && \
    echo "extension=imap.so" > /usr/local/etc/php/conf.d/imap.ini

# ---------- 3. Enable Apache Rewrite ----------
RUN a2enmod rewrite

# ---------- 4. Copy Project Files ----------
WORKDIR /var/www/html
COPY . /var/www/html
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]
