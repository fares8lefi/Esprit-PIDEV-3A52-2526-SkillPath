FROM php:8.2-apache

# Extensions nécessaires pour Symfony + MySQL
RUN apt-get update && apt-get install -y \
    libicu-dev libzip-dev unzip git \
    && docker-php-ext-install pdo pdo_mysql intl zip opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Activer mod_rewrite Apache (nécessaire pour le routing Symfony)
RUN a2enmod rewrite

# Config Apache : DocumentRoot = public/ + AllowOverride All pour .htaccess
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
    Options -Indexes +FollowSymLinks\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
    </VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html


COPY . .


RUN composer install --no-interaction --no-scripts

# Permissions sur var/
RUN mkdir -p var/ && chown -R www-data:www-data var/ && chmod -R 777 var/

EXPOSE 80
