cp ../config-eventizer/.env .

composer install
composer update
php artisan jwt:secret

docker-compose kill

docker-compose up -d --build


docker exec  eventizer-api-app chown -R www-data.www-data .
docker exec  eventizer-api-app chmod -R 755 .
docker exec  eventizer-api-app chmod -R 777 ./app
docker exec  eventizer-api-app chmod -R 777 ./vendor
docker exec  eventizer-api-app php artisan key:generate
