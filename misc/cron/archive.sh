#!/bin/sh -e
# =======================================================================
# WARNING: this script archive.sh is DEPRECATED!
#
# => Replace your cron with `/usr/bin/php5 /path/to/piwik/console core:archive --url=http://example.org/piwik/`
#
# See documentation at http://piwik.org/setup-auto-archiving/
# =======================================================================

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
PIWIK_PATH="$PIWIK_CRON_FOLDER"/../../console

CONSOLE_CMD="$PHP_BIN -q $PIWIK_PATH core:archive --url=http://example.org"

MESSAGE="\n\n WARNING: this script archive.sh is DEPRECATED! \n\nPlease update your cron as explained in the documentation: http://piwik.org/docs/setup-auto-archiving/ \n\n"

echo $MESSAGE;

$CONSOLE_CMD

echo $MESSAGE;

exit 1