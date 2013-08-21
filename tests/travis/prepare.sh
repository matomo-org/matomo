#!/bin/bash
set -e

# Install XMLStarlet
sudo apt-get install -qq xmlstarlet

# Install phantomjs 1.9.1 for UI tests
if [ "$TEST_DIR" = "UI" ];
then
    cd tmp
    wget "https://phantomjs.googlecode.com/files/phantomjs-1.9.1-linux-x86_64.tar.bz2" -O phantomjs.tar.bz2
    tar xvjf phantomjs.tar.bz2
    cd phantomjs*
    export PATH=$PATH:`pwd`/bin
    cd ../..

    echo "Using phantomjs version:"
    phantomjs --version
fi

# Copy Piwik configuration
echo "Install config.ini.php"
cp ./tests/PHPUnit/config.ini.travis.php ./config/config.ini.php

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
mkdir ./tmp/sessions
mkdir ./tmp/templates_c
chmod a+rw ./tests/lib/geoip-files
