#!/bin/sh -e

# =======================================================================
# BEFORE YOU USE THIS SCRIPT:
# PLEASE DON'T.
# =======================================================================
#
#
#                ==> Use archive.php instead. <==
#
# See documentation at http://piwik.org/setup-auto-archiving/
# =======================================================================

# Description
# This cron script will automatically run Piwik archiving every hour.
# The script will also run scheduled tasks configured within piwik using
# the event hook 'TaskScheduler.getScheduledTasks'

# It automatically fetches the Super User token_auth
# and triggers the archiving for all websites for all periods.
# This ensures that all reports are pre-computed and Piwik renders very fast.

# Documentation
# Please check the documentation on http://piwik.org/docs/setup-auto-archiving/

# How to setup the crontab job?
# Add the following lines in your crontab file, eg. /etc/cron.d/piwik-archive
#---------------START CRON TAB--
#MAILTO="youremail@example.com"
#5 * * * * www-data /path/to/piwik/misc/cron/archive.sh > /dev/null
#-----------------END CRON TAB--
# When an error occurs (eg. php memory error, timeout) the error messages
# will be sent to youremail@example.com.
#
# Optimization for high traffic websites
# You may want to override the following settings in config/config.ini.php:
# See documentation of the fields in your piwik/config/config.ini.php
#
# [General]
# time_before_archive_considered_outdated = 3600
# enable_browser_archiving_triggering = false
#===========================================================================

for TEST_PHP_BIN in php5 php php-cli php-cgi; do
  if which $TEST_PHP_BIN >/dev/null 2>/dev/null; then
    PHP_BIN=`which $TEST_PHP_BIN`
    break
  fi
done
if test -z $PHP_BIN; then
  echo "php binary not found. Make sure php5 or php exists in PATH." >&2
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
PIWIK_TOKEN_GENERATOR="$PIWIK_CRON_FOLDER"/../../misc/cron/updatetoken.php

FILENAME_TOKEN_CONTENT=`php $PIWIK_TOKEN_GENERATOR`
TOKEN_AUTH=`cat $FILENAME_TOKEN_CONTENT | cut -f2`

CMD_GET_ID_SITES="$PHP_BIN -q $PIWIK_PATH -- module=API&method=SitesManager.getAllSitesId&token_auth=$TOKEN_AUTH&format=csv&convertToUnicode=0"
ID_SITES=`$CMD_GET_ID_SITES`

CMD_GET_SEGMENTS_TO_ARCHIVE="$PHP_BIN -q $PIWIK_PATH -- module=API&method=CoreAdminHome.getKnownSegmentsToArchive&token_auth=$TOKEN_AUTH&format=csv&convertToUnicode=0"
SEGMENTS_TO_ARCHIVE=`$CMD_GET_SEGMENTS_TO_ARCHIVE`

echo "Starting Piwik reports archiving..."
echo ""
for idsite in $ID_SITES; do
  TEST_IS_NUMERIC=`echo $idsite | egrep '^[0-9]+$'`
  if test -n "$TEST_IS_NUMERIC"; then
    for period in day week month year; do
      echo ""
      echo "Archiving period = $period for idsite = $idsite..."
      CMD="$PHP_BIN -q $PIWIK_PATH -- module=API&method=VisitsSummary.getVisits&idSite=$idsite&period=$period&date=last52&format=xml&token_auth=$TOKEN_AUTH"
      $CMD
      
      for segment in $SEGMENTS_TO_ARCHIVE; do
	    if test $segment != "value"; then
	        # Ignore "No data available" response when there are no segment to pre-process
            if test $segment != "No"; then
            if test $segment != "data"; then
            if test $segment != "available"; then
              echo ""
              echo " - Archiving for visitor segment $segment ..."
              CMD_ARCHIVE_SEGMENT="${CMD}&segment=$segment"
              $CMD_ARCHIVE_SEGMENT
            fi
            fi
            fi
        fi
      done
    done

    echo ""
    echo "Archiving for idsite = $idsite done!"
  fi
done

echo "Reports archiving finished."
echo "---------------------------"
echo "Starting Scheduled tasks..."
echo ""
CMD="$PHP_BIN -q $PIWIK_PATH -- module=API&method=CoreAdminHome.runScheduledTasks&format=csv&convertToUnicode=0&token_auth=$TOKEN_AUTH"
$CMD
echo ""
echo "Finished Scheduled tasks."
echo ""
