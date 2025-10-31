# ---------- 1. Base Image ----------
FROM php:8.3-apache

# ---------- 2. Install Dependencies ----------
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
    dovecot-imapd \
 && docker-php-ext-configure intl \
 && docker-php-ext-install intl pdo pdo_mysql mbstring xml ctype bcmath zip fileinfo gd \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

# ---------- 3. Build IMAP Extension Manually ----------
RUN docker-php-source extract \
 && cd /usr/src/php/ext/imap \
 && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
 && docker-php-ext-install imap \
 && docker-php-source delete

# ---------- 4. Enable Apache Rewrite Module ----------
RUN a2enmod rewrite

# ---------- 5. Set Working Directory ----------
WORKDIR /var/www/html

# ---------- 6. Copy Project Files ----------
COPY . /var/www/html

# ---------- 7. Set Permissions ----------
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# ---------- 8. Expose Web Port ----------
EXPOSE 80

# ---------- 9. Start Apache ----------
CMD ["apache2-foreground"]
