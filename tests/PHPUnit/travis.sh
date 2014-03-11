#!/bin/bash

if [ `phpunit --group __nogroup__ | grep "No tests executed" | wc -l` -ne 1 ]
then
    echo "=====> There are some tests functions which do not have a @group set. "
    echo "       Please add the @group phpdoc comment to the following tests: <====="
    phpunit --group __nogroup__ --testdox | grep "[x]"
    exit 1
fi

if [ -n "$TEST_SUITE" ]
then
    if [ "$TEST_SUITE" = "JavascriptTests" ]
    then
        touch ../javascript/enable_sqlite
        phantomjs ../javascript/testrunner.js
    elif [ "$TEST_SUITE" = "UITests" ]
    then
        if [ -n "$PLUGIN_NAME" ]
        then
            artifacts_folder="protected/ui-tests.master.$PLUGIN_NAME"
        else
            artifacts_folder="ui-tests.master"
        fi

        echo ""
        echo "View UI failures (if any) here http://builds-artifacts.piwik.org/$artifacts_folder/$TRAVIS_JOB_NUMBER/screenshot-diffs/diffviewer.html"
        echo "If the new screenshots are valid, then you can copy them over to tests/PHPUnit/UI/expected-ui-screenshots/."
        echo ""

        if [ -n "$PLUGIN_NAME" ]
        then
            phantomjs ../lib/screenshot-testing/run-tests.js --assume-artifacts --persist-fixture-data --screenshot-repo=$TRAVIS_REPO_SLUG $PLUGIN_NAME
        else
            phantomjs ../lib/screenshot-testing/run-tests.js --store-in-ui-tests-repo --persist-fixture-data --assume-artifacts
        fi
    else
        if [ -n "$PLUGIN_NAME" ]
        then
            phpunit --configuration phpunit.xml --colors --testsuite $TEST_SUITE $PLUGIN_NAME
        else
            phpunit --configuration phpunit.xml --testsuite $TEST_SUITE --colors
        fi
    fi
else
    phpunit --configuration phpunit.xml --coverage-text --colors
fi