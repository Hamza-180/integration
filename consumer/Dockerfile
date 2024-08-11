# Utilisez une image PHP de base
FROM php:8.0-cli

# Installez les extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    curl \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install sockets zip \
    && rm -rf /var/lib/apt/lists/*

# Installez Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Définir le répertoire de travail
WORKDIR /app

# Copiez les fichiers de l'application dans le conteneur
COPY ./composer.json /app/
COPY ./consumer.php /app/
COPY ./ClientService.php /app/
COPY ./php.ini /app/

# Installez les dépendances PHP
RUN composer install

# Copiez et définissez les permissions pour le script d'entrée
COPY ./entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Exposez le port si nécessaire
EXPOSE 80

# Définir le point d'entrée du conteneur
ENTRYPOINT ["/entrypoint.sh"]
