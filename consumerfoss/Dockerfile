FROM php:7.4-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev \
    && docker-php-ext-install bcmath sockets

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

COPY . /app

# Install PHP dependencies
RUN composer install

CMD ["php", "/app/consumer_foss.php"]
