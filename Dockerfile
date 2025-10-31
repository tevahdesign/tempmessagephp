FROM php:8.3-apache

# ---------- 1. System dependencies ----------
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libxml2-dev \
    libicu-dev \
    libssl-dev \
    zlib1g-dev \
    unzip \
    wget \
    pkg-config \
    build-essential \
    libcurl4-openssl-dev \
    libonig-dev \
    libkrb5-dev \
    libpam0g-dev

# ---------- 2. PHP core extensions ----------
RUN docker-php-ext-configure intl \
 && docker-php-ext-install intl pdo pdo_mysql mbstring xml ctype bcmath zip fileinfo gd

# ---------- 3. Build and enable IMAP (patched) ----------
WORKDIR /usr/src
RUN wget -q https://github.com/php/imap/archive/refs/heads/master.zip -O imap.zip \
 && unzip -q imap.zip \
 && cd imap-master \
 && phpize \
 && ./configure --with-kerberos --with-imap-ssl \
 && make -j$(nproc) && make install \
 && echo "extension=imap.so" > /usr/local/etc/php/conf.d/imap.ini \
 && cd /usr/src && rm -rf imap-master imap.zip

# ---------- 4. Apache rewrite ----------
RUN a2enmod rewrite

# ---------- 5. App setup ----------
WORKDIR /var/www/html
COPY . /var/www/html
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]
