FROM php:7.4-apache
# RUN a2enmod rewrite 
WORKDIR /var/www/html 
#COPY composer.json composer.lock ./
# Install Composer
RUN apt-get update && apt-get install -y \
    unzip \
    && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"
RUN docker-php-ext-install pdo pdo_mysql
RUN apt-get update \
    && apt-get install -y libzip-dev \
    && apt-get install -y zlib1g-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install zip \
    && docker-php-ext-install mysqli
# Install Composer dependencies
#RUN composer install --no-dev --optimize-autoloader
# Copy the rest of the application code
COPY . .

# Set the correct document root in Apache to the public directory
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Ensure Apache mod_rewrite is enabled
RUN a2enmod rewrite
# Expose port 80    
EXPOSE 80
# Start Apache server
CMD ["apache2-foreground"]
# COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
# RUN composer self-update
# WORKDIR /var/www/html
# COPY . .
# RUN composer install
