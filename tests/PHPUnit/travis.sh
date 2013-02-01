#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# Install XDebug
pecl install xdebug
echo "extension=xdebug.so" >> `php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`

# Copy Piwik configuration
cp $DIR/config.ini.travis.php ../../config/config.ini.php
