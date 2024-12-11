# shellcheck disable=SC1046
# shellcheck disable=SC1073
# shellcheck disable=SC1035
# shellcheck disable=SC1019
# shellcheck disable=SC1072
# shellcheck disable=SC1020
# shellcheck disable=SC1009

if [ ! -f .env ]; then
  composer install
  composer create-project --prefer-dist laravel/laravel ./
  cp ../.env ./
  php artisan key:generate
  chown -R www-data:www-data storage
  chown -R www-data:www-data bootstrap/cache
  chmod -R 775 storage
  chmod -R 755 bootstrap/cache
  php artisan migrate
fi

php artisan optimize
php-fpm

cd ../backend/