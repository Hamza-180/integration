#!/bin/bash

# Fonction pour vérifier la disponibilité de RabbitMQ
function wait_for_rabbitmq() {
  while ! curl -s http://rabbitmq:15672 &> /dev/null; do
    echo "Waiting for RabbitMQ..."
    sleep 5
  done
}

# Attendre que RabbitMQ soit prêt
wait_for_rabbitmq

# Exécuter le script PHP
php /app/consumer.php

# Garder le conteneur en exécution
tail -f /dev/null
