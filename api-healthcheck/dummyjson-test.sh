while true; do
  docker-compose exec -T app php artisan api:check-health > /dev/null 2>&1
  echo "Health check completed at $(date '+%Y-%m-%d %H:%M:%S')"
  sleep 3
done