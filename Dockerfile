# Use official PHP + Apache image
FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Copy all project files into the container
COPY . .

# Set the Apache document root to /var/www/html/public
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Enable Apache rewrite module (important for routing)
RUN a2enmod rewrite

# Optional: set permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port 80
EXPOSE 80
