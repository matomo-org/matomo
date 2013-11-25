#!/bin/bash

RESULT_JS=0
RESULT_PHP=0
touch ../javascript/enable_sqlite

if [ `phpunit --group __nogroup__ | grep "No tests executed" | wc -l` -ne 1 ]
then
    echo "=====> There are some tests functions which do not have a @group set. "
    echo "       Please add the @group phpdoc comment to the following tests: <====="
    phpunit --group __nogroup__ --testdox | grep "[x]"
    exit 1
else
    if [ -n "$TEST_SUITE" ]
    then
        phpunit --configuration phpunit.xml --testsuite $TEST_SUITE --colors
        RESULT_PHP=$?
        phantomjs ../javascript/testrunner.js
        RESULT_JS=$?
    else
      if [ -n "$TEST_DIR" ]
      then
        if [ "$TEST_DIR" = "UI" ]
        then
            echo ""
            echo "View UI failures (if any) here http://builds-artifacts.piwik.org/ui-tests.master/$TRAVIS_JOB_NUMBER/screenshot-diffs/diffviewer.html"
            echo "If the new screenshots are valid, then you can copy them over to tests/PHPUnit/UI/expected-ui-screenshots/."
            echo ""
        fi

        phpunit --colors $TEST_DIR
        RESULT_PHP=$?
      else
        phpunit --configuration phpunit.xml --coverage-text --colors
        RESULT_PHP=$?
      fi
    fi
fi

if [ 0 == $RESULT_PHP ] && [ 0 == $RESULT_JS ]; then
   exit 0;
else
   exit 1;
fi