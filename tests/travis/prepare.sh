#!/bin/bash
if [ "$SKIP_PIWIK_TEST_PREPARE" == "1" ]; then
    echo "Skipping Piwik specific test peparation."
    exit 0;
fi

set -e

sudo apt-get update > /dev/null

# Install XMLStarlet
sudo apt-get install -qq xmlstarlet > /dev/null

# Install fonts for UI tests
if [ "$TEST_SUITE" = "UITests" ];
then
    sudo sh -c "echo ttf-mscorefonts-installer msttcorefonts/accepted-mscorefonts-eula select true | debconf-set-selections"
    sudo apt-get install -qq ttf-mscorefonts-installer > /dev/null
fi

# Copy Piwik configuration
echo "Install config.ini.php"
sed "s/PDO\\\MYSQL/${MYSQL_ADAPTER}/g" ./tests/PHPUnit/config.ini.travis.php > ./config/config.ini.php

# Prepare phpunit.xml
echo "Adjusting phpunit.xml"
cp ./tests/PHPUnit/phpunit.xml.dist ./tests/PHPUnit/phpunit.xml

if grep "@REQUEST_URI@" ./tests/PHPUnit/phpunit.xml > /dev/null; then
    sed -i 's/@REQUEST_URI@/\//g' ./tests/PHPUnit/phpunit.xml
fi

if [ -n "$PLUGIN_NAME" ];
then
      sed -n '/<filter>/{p;:a;N;/<\/filter>/!ba;s/.*\n/<whitelist addUncoveredFilesFromWhitelist=\"true\">\n<directory suffix=\".php\">..\/..\/plugins\/'$PLUGIN_NAME'<\/directory>\n<exclude>\n<directory suffix=\".php\">..\/..\/plugins\/'$PLUGIN_NAME'\/tests<\/directory>\n<directory suffix=\".php\">..\/..\/plugins\/'$PLUGIN_NAME'\/Test<\/directory>\n<directory suffix=\".php\">..\/..\/plugins\/'$PLUGIN_NAME'\/Updates<\/directory>\n<\/exclude>\n<\/whitelist>\n/};p' ./tests/PHPUnit/phpunit.xml > ./tests/PHPUnit/phpunit.xml.new && mv ./tests/PHPUnit/phpunit.xml.new ./tests/PHPUnit/phpunit.xml
fi;

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
chmod a+rw ./tests/lib/geoip-files || true
chmod a+rw ./plugins/*/tests/System/processed || true
chmod a+rw ./plugins/*/tests/Integration/processed || true

# install phpredis
echo 'extension="redis.so"' > ./tmp/redis.ini
phpenv config-add ./tmp/redis.ini
