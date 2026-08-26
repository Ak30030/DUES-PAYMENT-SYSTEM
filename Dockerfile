FROM php:8.2-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo pdo_mysql curl mysqli \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite (useful if you add clean URLs later)
RUN a2enmod rewrite

# Set Apache document root to the project files
WORKDIR /var/www/html

# Copy project files into the container
COPY . /var/www/html/

# Give Apache correct permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Render provides the PORT env variable — tell Apache to listen on it
ENV APACHE_LOG_DIR=/var/log/apache2
ENV PORT=8080
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's/80/${PORT}/g' /etc/apache2/ports.conf

EXPOSE 8080

CMD ["apache2-foreground"]
