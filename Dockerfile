FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
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
 && docker-php-ext-install intl pdo pdo_mysql mbstring xml ctype bcmath zip fileinfo gd \
 && apt-get clean && rm -rf /var/lib/apt/lists/*
