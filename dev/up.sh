#!/usr/bin/env bash
function print {
    # COLORED OUTPUT
    COLOR='\033[0;32m'
    RESET='\033[0m'
    length=${#1}
    border=""

    for (( i=-6; i < length; i++ ))
    do
        border="$border#"
    done

    echo -e ${COLOR}${border}
    echo -e "#  $1  #"
    echo -e ${border}${RESET}
}

print "COMPOSER SECTION"
docker-compose exec php /usr/bin/composer install --prefer-dist --no-interaction --optimize-autoloader
docker-compose run npm npm install --global gulp-cli
docker-compose run npm npm install

print "PERMISSION SECTION"
echo "Setting up permissions..."
docker-compose exec php find . -type d -exec chmod 775 {} \;
docker-compose exec php find . -type f -exec chmod 664 {} \;

print "STORAGE SECTION"
echo "Setting up storage permissions..."
docker-compose exec php find storage/ -type d -exec chmod 777 {} \;
docker-compose exec php find storage/ -type f -exec chmod 777 {} \;
echo "Setting up ownership..."
docker-compose exec php chgrp -R www-data storage bootstrap/cache
echo "Setting up cache permissions..."
docker-compose exec php chmod -R ug+rwx bootstrap/cache
echo "Setting up dev permissions..."
docker-compose exec php chmod -R +x dev
docker-compose exec php chmod +x ./exec
docker-compose exec php chmod +x ./gulp

print "KEY SECTION"
echo "Setting up key generating..."
docker-compose exec php php artisan key:generate

print "DATABASE SECTION"
echo "Running migrations..."
docker-compose exec php php artisan migrate:fresh --seed
