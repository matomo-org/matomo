#!/bin/bash
set -e

# Copy Piwik configuration
echo "Install config.ini.php"
cp ./tests/PHPUnit/config.ini.travis.php ./config/config.ini.php

# Prepare phpunit.xml
echo "Adjusting phpunit.xml"
cp ./tests/PHPUnit/phpunit.xml.dist ./tests/PHPUnit/phpunit.xml
sed -i 's/@REQUEST_URI@/\//g' ./tests/PHPUnit/phpunit.xml

# Create tmp/ sub-directories
mkdir ./tmp/assets
mkdir ./tmp/cache
mkdir ./tmp/latest
mkdir ./tmp/sessions
mkdir ./tmp/templates_c
chmod a+rw ./tests/lib/geoip-files
