# Base PHP + Apache
FROM php:8.2-apache

# Activer mod_rewrite
RUN a2enmod rewrite

#  Installer dépendances système et extensions PHP
RUN apt-get update \
 && apt-get install -y \
      libpq-dev \
      libzip-dev \
      unzip \
      git \
 && docker-php-ext-install pdo_pgsql zip \
 && rm -rf /var/lib/apt/lists/*

# Composer global
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# php.ini
COPY php.ini /usr/local/etc/php/

# Copier la config Apache
COPY /000-default.conf /etc/apache2/sites-available/000-default.conf

# copie Composer
WORKDIR /var/www/html
COPY composer.json composer.lock* /var/www/html/

# Install Composer
RUN composer install --no-dev --optimize-autoloader

# Install APCu
RUN apt-get update && apt-get install -y libpcre3-dev

RUN pecl install apcu && docker-php-ext-enable apcu


# Copie le code
COPY public/ /var/www/html/public/
COPY src/ /var/www/html/src/
COPY views/ /var/www/html/views/
COPY public/.htaccess /var/www/html/public/.htaccess

# Permissions
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html

# Expose
EXPOSE 80
CMD ["apache2-foreground"]
