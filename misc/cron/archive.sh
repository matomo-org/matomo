#!/bin/bash -e

# Description
# This script automatically fetches the Super User token_auth 
# and triggers the archiving for all websites for all periods.
# This ensures that all reports are pre-computed and Piwik renders very fast. 

# Documentation
# Please check the documentation on http://piwik.org/docs/setup-auto-archiving/

# How to setup the crontab job?
# Add the following lines in your crontab file, eg. /etc/cron.d/piwik-archive
#MAILTO="youremail@example.com"
#5 0 * * * www-data /path/to/piwik/misc/cron/archive.sh > /dev/null

# Other optimization for high traffic websites
# You may want to override the following settings in config/config.ini.php (see documentation in config/config.ini.php)
# [General]
# time_before_archive_considered_outdated = 3600
# enable_browser_archiving_triggering = false

for TEST_PHP_BIN in php5 php; do
  if which $TEST_PHP_BIN >/dev/null 2>/dev/null; then
    PHP_BIN=`which $TEST_PHP_BIN`
    break
  fi
done
if test -z $PHP_BIN; then
  echo "php binary not found. Make sure php5 or php exists in PATH."
  exit 1
fi

act_path() {
  local pathname="$1"
  readlink -f "$pathname" 2>/dev/null || \
  realpath "$pathname" 2>/dev/null || \
  type -P "$pathname" 2>/dev/null
}

ARCHIVE=`act_path ${0}`
PIWIK_CRON_FOLDER=`dirname ${ARCHIVE}`
PIWIK_PATH="$PIWIK_CRON_FOLDER"/../../index.php
PIWIK_CONFIG="$PIWIK_CRON_FOLDER"/../../config/config.ini.php

PIWIK_SUPERUSER=`sed '/^\[superuser\]/,$!d;/^login[ \t]*=[ \t]*"*/!d;s///;s/"*[ \t]*$//;q' $PIWIK_CONFIG`
PIWIK_SUPERUSER_MD5_PASSWORD=`sed '/^\[superuser\]/,$!d;/^password[ \t]*=[ \t]*"*/!d;s///;s/"*[ \t]*$//;q' $PIWIK_CONFIG`

CMD_TOKEN_AUTH="$PHP_BIN $PIWIK_PATH -- module=API&method=UsersManager.getTokenAuth&userLogin=$PIWIK_SUPERUSER&md5Password=$PIWIK_SUPERUSER_MD5_PASSWORD&format=php&serialize=0"
TOKEN_AUTH=`$CMD_TOKEN_AUTH`

for period in day week year; do
  CMD="$PHP_BIN $PIWIK_PATH -- module=API&method=VisitsSummary.getVisits&idSite=all&period=$period&date=last52&format=xml&token_auth=$TOKEN_AUTH";
  $CMD
  echo ""
done
