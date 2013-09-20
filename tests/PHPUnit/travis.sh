#!/bin/bash

if [ -n "$TEST_SUITE" ]
then
	phpunit --configuration phpunit.xml --testsuite $TEST_SUITE --colors
else
  if [ -n "$TEST_DIR" ]
  then
    if [ "$TEST_DIR" = "UI" ]
    then
        echo ""
        echo "View UI failures (if any) here http://builds-artifacts.piwik.org/ui-tests.master/$TRAVIS_JOB_NUMBER/screenshot-diffs/diffviewer.html."
        echo "If the new screenshots are valid, then you can copy them over to tests/PHPUnit/UI/expected-ui-screenshots/."
        echo ""
    fi

    phpunit --colors $TEST_DIR
  else
	phpunit --configuration phpunit.xml --coverage-text --colors
  fi
fi
