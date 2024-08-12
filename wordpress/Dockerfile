# Gebruik het officiële WordPress image als basis
FROM wordpress:latest

# Installeer benodigde pakketten
RUN apt-get update && apt-get install -y \
    curl \
    git \
    unzip \
    supervisor \
    && rm -rf /var/lib/apt/lists/*

# Installeer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer



# Kopieer de rest-Api plugin directory naar de WordPress plugins directory
COPY ./rest-Api /var/www/html/wp-content/plugins/rest-Api

# Installeer Composer dependencies voor de rest-Api plugin
RUN composer install --working-dir=/var/www/html/wp-content/plugins/rest-Api/
RUN composer require php-amqplib/php-amqplib

# Zorg voor de juiste permissies
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# Exposeer poort 80
EXPOSE 80