#!/bin/bash

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
      else
        phpunit --configuration phpunit.xml --coverage-text --colors
      fi
    fi
fi