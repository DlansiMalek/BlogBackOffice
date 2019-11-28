cp ../config-eventizer/.env-dev .env

composer install
composer update
php artisan jwt:secret

docker-compose -f docker-compose-dev.yml kill

docker-compose -f docker-compose-dev.yml up -d --build


docker exec  eventizer-api-app chown -R www-data.www-data .
docker exec  eventizer-api-app chmod -R 755 .
docker exec  eventizer-api-app chmod -R 777 ./app
docker exec  eventizer-api-app chmod -R 777 ./vendor
docker exec  eventizer-api-app php artisan key:generate
