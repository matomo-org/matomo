#!/bin/bash
set -e

# Install XMLStarlet
sudo apt-get install -qq xmlstarlet

# Install fonts for UI tests
if [ "$TEST_DIR" = "UI" ];
then
    sudo sh -c "echo ttf-mscorefonts-installer msttcorefonts/accepted-mscorefonts-eula select true | debconf-set-selections"
    sudo apt-get install -qq ttf-mscorefonts-installer
fi

# Copy Piwik configuration
echo "Install config.ini.php"

TRAVIS_CONFIG_FILE=config.ini.travis.php
if [ "$TEST_DIR" = "UI" ];
then
    TRAVIS_CONFIG_FILE=config.ini.travis.ui.php
fi

sed "s/PDO_MYSQL/${MYSQL_ADAPTER}/g" ./tests/PHPUnit/$TRAVIS_CONFIG_FILE > ./config/config.ini.php

# Prepare phpunit.xml
echo "Adjusting phpunit.xml"
cp ./tests/PHPUnit/phpunit.xml.dist ./tests/PHPUnit/phpunit.xml
sed -i 's/@REQUEST_URI@/\//g' ./tests/PHPUnit/phpunit.xml

# If we have a test suite remove code coverage report
if [ -n "$TEST_SUITE" ]
then
	xmlstarlet ed -L -d "//phpunit/logging/log[@type='coverage-html']" ./tests/PHPUnit/phpunit.xml
fi

# Create tmp/ sub-directories
mkdir ./tmp/assets
mkdir ./tmp/cache
mkdir ./tmp/latest
mkdir ./tmp/logs
mkdir ./tmp/sessions
mkdir ./tmp/templates_c
mkdir ./tmp/tcpdf
mkdir ./tmp/climulti
chmod a+rw ./tests/lib/geoip-files
chmod a+rw ./plugins/ExamplePlugin/tests/processed
