# ---------- 1. Base Image ----------
FROM php:8.2-fpm

# ---------- 2. Install Dependencies ----------
RUN apt-get update && apt-get install -y \
    libc-client-dev \
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
 && apt-get clean && rm -rf /var/lib/apt/lists/*

# ---------- 3. Build IMAP (fixed version) ----------
RUN cd /usr/src && \
    wget https://github.com/uw-imap/imap/archive/refs/heads/master.zip -O imap.zip && \
    unzip imap.zip && mv imap-master imap && cd imap && \
    make slx EXTRAAUTHENTICATORS=gss && \
    cp c-client/*.h /usr/include/ && \
    cp c-client/*.c /usr/include/ && \
    cp c-client/*.a /usr/lib/

# ---------- 4. Compile PHP Extensions ----------
RUN docker-php-ext-configure intl \
 && docker-php-ext-install intl pdo pdo_mysql mbstring xml ctype bcmath zip fileinfo gd \
 && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
 && docker-php-ext-install imap

# ---------- 5. Working Directory ----------
WORKDIR /var/www/html
COPY . .

# ---------- 6. Permissions ----------
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html
