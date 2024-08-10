FROM fossbilling/fossbilling:latest
 
RUN apt-get update && apt-get install -y \
    curl \
    git \
    unzip \
&& rm -rf /var/lib/apt/lists/*
 
# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
 
WORKDIR /var/www/html
 
# Install PHP extensions
RUN docker-php-ext-install bcmath sockets
 
COPY ./composer.json .
COPY ./Admin.php ./modules/Client/Api/
 
RUN composer require php-amqplib/php-amqplib
RUN composer install
 
 
#correct permissions
RUN chown -R www-data:www-data /var/www/html
 
EXPOSE 80
 
CMD ["apache2-foreground"]
