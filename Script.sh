

# Stop the Docker Compose services + remove Volumes
docker-compose down -v

# Verwijder  Docker volumes
sudo docker volume rm wordpress_data || true
sudo docker volume rm db_data || true
sudo docker volume rm fossbilling_data || true
sudo docker volume rm foss_db_data || true 

# Clean up Docker system and volumes
docker system prune -af
docker volume prune -f

# Build docker compose zonder de cache
docker-compose build --no-cache

# Start het docker compose service
docker-compose up -d 

# Print als het werkt 
echo "Restart complete."
