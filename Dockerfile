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
    libc-client2007e-dev \
    libkrb5-dev \
 && docker-php-ext-configure intl \
 && docker-php-ext-install intl pdo pdo_mysql mbstring xml ctype bcmath zip fileinfo gd \
 && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
 && docker-php-ext-install imap \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

# ---------- 3. Enable Apache Rewrite Module ----------
RUN a2enmod rewrite

# ---------- 4. Set Working Directory ----------
WORKDIR /var/www/html

# ---------- 5. Copy Project Files ----------
COPY . /var/www/html

# ---------- 6. Set Permissions ----------
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# ---------- 7. Expose Web Port ----------
EXPOSE 80

# ---------- 8. Start Apache ----------
CMD ["apache2-foreground"]
