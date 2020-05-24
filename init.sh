# /bin/bash

mkdir -p bootstrap/cache
chmod 777 bootstrap/* -R
mkdir -p storage/app/public
mkdir -p storage/framework/{cache,sessions,views}
mkdir -p storage/logs
sudo chmod 777 -R storage
