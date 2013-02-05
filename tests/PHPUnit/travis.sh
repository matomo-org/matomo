#!/bin/bash

# Copy Piwik configuration
cp ./tests/PHPUnit/config.ini.travis.php ./config/config.ini.php

# Create tmp/ sub-directories
mkdir ./tmp/assets
mkdir ./tmp/cache
mkdir ./tmp/latest
mkdir ./tmp/sessions
mkdir ./tmp/templates_c
