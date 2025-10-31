# ---------- 1. Base Image ----------
FROM php:8.2-apache

# ---------- 2. Install Dependencies ----------
RUN apt-get update && apt-get install -y \
    libkrb5-dev \
    libssl-dev \
    libzip-dev \
    libpng-dev \
    libxml2-dev \
    libicu-dev \
    zlib1g-dev \
    git \
    unzip \
    wget \
    pkg-config \
    build-essential \
    libcurl4-openssl-dev \
    libonig-dev \
    ca-certificates \
    make \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

# ---------- 3. Build and Install UW IMAP Library ----------
RUN cd /usr/src && \
    wget https://github.com/uw-imap/imap/archive/refs/heads/master.zip -O imap.zip && \
    unzip imap.zip && mv imap-master imap && cd imap && \
    make slx EXTRAAUTHENTICATORS=gss SSLTYPE=unix && \
    mkdir -p /usr/local/imap && \
    cp -r c-client /usr/local/imap && \
    cp c-client/*.a /usr/lib/ && \
    cp c-client/*.h /usr/include/

# ---------- 4. Compile PHP Extensions ----------
RUN docker-php-ext-configure intl \
 && docker-php-ext-install intl pdo pdo_mysql mbstring xml ctype bcmath zip fileinfo gd \
 && docker-php-ext-configure imap --with-kerberos --with-imap-ssl=/usr/local/imap \
 && docker-php-ext-install imap

# ---------- 5. Enable Apache Rewrite ----------
RUN a2enmod rewrite

# ---------- 6. Set Working Directory ----------
WORKDIR /var/www/html
COPY . /var/www/html

# ---------- 7. Permissions ----------
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html

# ---------- 8. Expose Port ----------
EXPOSE 80

# ---------- 9. Start Apache ----------
CMD ["apache2-foreground"]
