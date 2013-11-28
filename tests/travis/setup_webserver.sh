#!/bin/bash
set -e

DIR=$(dirname "$0")

echo "Installing nginx"
sudo apt-get update -qq
sudo apt-get install -qq nginx realpath

sudo service nginx stop

# Setup PHP-FPM
echo "Configuring php-fpm"
PHP_FPM_BIN="$HOME/.phpenv/versions/$TRAVIS_PHP_VERSION/sbin/php-fpm"
PHP_FPM_CONF="$DIR/php-fpm.conf"
PHP_FPM_SOCK=$(realpath "$DIR")/php-fpm.sock

if [ -d "$TRAVIS_BUILD_DIR/../piwik/tmp/" ]; then
    PHP_FPM_LOG="$TRAVIS_BUILD_DIR/../piwik/tmp/php-fpm.log"
elif [ -d "$TRAVIS_BUILD_DIR/piwik/tmp/" ]; then
    PHP_FPM_LOG="$TRAVIS_BUILD_DIR/piwik/tmp/php-fpm.log"
else
    PHP_FPM_LOG="$TRAVIS_BUILD_DIR/php-fpm.log"
fi

USER=$(whoami)

touch "$PHP_FPM_LOG"

# Adjust php-fpm.ini
sed -i "s/@USER@/$USER/g" "$DIR/php-fpm.ini"
sed -i "s|@PHP_FPM_SOCK@|$PHP_FPM_SOCK|g" "$DIR/php-fpm.ini"
sed -i "s|@PHP_FPM_LOG@|$PHP_FPM_LOG|g" "$DIR/php-fpm.ini"

# Setup nginx
echo "Configuring nginx"
PIWIK_ROOT=$(realpath "$DIR/../..")
NGINX_CONF="/etc/nginx/sites-enabled/default"

sed -i "s|@PIWIK_ROOT@|$PIWIK_ROOT|g" "$DIR/piwik_nginx.conf"
sed -i "s|@PHP_FPM_SOCK@|$PHP_FPM_SOCK|g" "$DIR/piwik_nginx.conf"
sudo cp "$DIR/piwik_nginx.conf" $NGINX_CONF

# Start daemons
echo "Starting php-fpm"
sudo $PHP_FPM_BIN --fpm-config "$DIR/php-fpm.ini"
echo "Starting nginx"
sudo service nginx start
