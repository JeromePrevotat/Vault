FROM php:8.4-apache
RUN apt-get update && apt-get install -y \
  git zip unzip libpng-dev \
  libzip-dev default-mysql-client
RUN docker-php-ext-install pdo pdo_mysql zip gd
RUN a2enmod rewrite
RUN echo '<Directory /var/www/html/public>\n\
  AllowOverride All\n\
  Require all granted\n\
  </Directory>' >> /etc/apache2/apache2.conf
WORKDIR /var/www
COPY . /var/www/
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-scripts --no-autoloader
EXPOSE 80
RUN sed -i 's!/var/www/html!/var/www/html/public!g' \
  /etc/apache2/sites-available/000-default.conf

# Copy and set entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]