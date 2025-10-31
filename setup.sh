#!/bin/bash

echo "======================================"
echo "Laravel Monitoring Docker Setup"
echo "======================================"
echo ""

# Update .env file
echo "Updating Laravel .env configuration..."
cd app

# Update database configuration
sed -i.bak 's/DB_HOST=127.0.0.1/DB_HOST=mysql/' .env
sed -i.bak 's/DB_DATABASE=laravel/DB_DATABASE=laravel/' .env
sed -i.bak 's/DB_USERNAME=root/DB_USERNAME=laravel/' .env
sed -i.bak 's/DB_PASSWORD=/DB_PASSWORD=password/' .env

echo "✓ Laravel .env configured"
echo ""

cd ..

# Build and start Docker containers
echo "Building and starting Docker containers..."
docker-compose up -d --build

echo ""
echo "Waiting for MySQL to be ready..."
sleep 15

# Run Laravel setup commands
echo "Running Laravel setup..."
docker-compose exec -T app composer install
docker-compose exec -T app php artisan key:generate
docker-compose exec -T app php artisan migrate --force

# Set permissions
echo "Setting permissions..."
docker-compose exec -T app chmod -R 775 storage bootstrap/cache

echo ""
echo "======================================"
echo "Setup Complete!"
echo "======================================"
echo ""
echo "Access your applications:"
echo "- Laravel:    http://localhost:8000"
echo "- Prometheus: http://localhost:9090"
echo "- Grafana:    http://localhost:3000"
echo "              (admin/admin)"
echo ""
echo "Useful commands:"
echo "  docker-compose logs -f        # View logs"
echo "  docker-compose exec app bash  # Access app container"
echo "  docker-compose down           # Stop containers"
echo ""
