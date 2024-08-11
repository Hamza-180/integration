#!/bin/bash

# Stop the Docker Compose services and remove volumes
docker-compose down -v

# Remove Docker volumes
sudo docker volume rm integration_wordpress_data || true
sudo docker volume rm integration_db_data || true
sudo docker volume rm integration_fossbilling_data || true
sudo docker volume rm integration_foss_db_data || true 

# Clean up Docker system and unused volumes
docker system prune -af
docker volume prune -f

# Build Docker Compose without cache
docker-compose build --no-cache

# Start the Docker Compose services
docker-compose up -d 

# Print confirmation message
echo "Restart complete."
