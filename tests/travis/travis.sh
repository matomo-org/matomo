#!/bin/bash

SCRIPT_DIR=$( dirname "$0" )

# for travis_wait function
source $SCRIPT_DIR/travis-helper.sh

# go to tests directory
cd ../PHPUnit

if [ -n "$TEST_SUITE" ]
then
    echo "Executing tests in test suite $TEST_SUITE..."

    if [ -n "$PLUGIN_NAME" ]
    then
        echo "    [ plugin name = $PLUGIN_NAME ]"
    fi

    if [ "$TEST_SUITE" = "AngularJSTests" ]
    then
        ./../angularjs/scripts/travis.sh
    elif [ "$TEST_SUITE" = "JavascriptTests" ]
    then
        touch ../javascript/enable_sqlite
        phantomjs ../javascript/testrunner.js
    elif [ "$TEST_SUITE" = "UITests" ]
    then
        if [ -n "$PLUGIN_NAME" ]
        then
            artifacts_folder="ui-tests.$TRAVIS_BRANCH.$PLUGIN_NAME"

            if [ "$UNPROTECTED_ARTIFACTS" = "" ];
            then
                artifacts_folder="protected/$artifacts_folder"
            fi
        else
            artifacts_folder="ui-tests.$TRAVIS_BRANCH"
        fi

        echo ""
        echo "View UI failures (if any) here:"
        echo ""
        echo "http://builds-artifacts.piwik.org/$artifacts_folder/$TRAVIS_JOB_NUMBER/screenshot-diffs/diffviewer.html"
        echo ""
        echo "If the new screenshots are valid, then you can copy them over to the right directory with the command:"

        echo ""
        echo "./console development:sync-ui-test-screenshots $TRAVIS_JOB_NUMBER"
        if [ -n "$PLUGIN_NAME" ]
        then
            echo "  --plugin=$PLUGIN_NAME"

            if [ "$UNPROTECTED_ARTIFACTS" = "" ];
            then
                echo " --http-user=... --http-password=..."
            fi
        fi
        
        echo ""

        if [ -n "$PLUGIN_NAME" ]
        then
            phantomjs ../lib/screenshot-testing/run-tests.js --assume-artifacts --persist-fixture-data --screenshot-repo=$TRAVIS_REPO_SLUG --plugin=$PLUGIN_NAME
        else
            phantomjs ../lib/screenshot-testing/run-tests.js --store-in-ui-tests-repo --persist-fixture-data --assume-artifacts --core --print-logs
        fi
    elif [ "$TEST_SUITE" = "AllTests" ]
    then
        travis_wait ./../../console tests:run --options="--colors"
    else
        if [ -n "$PLUGIN_NAME" ]
        then
            travis_wait phpunit --configuration phpunit.xml --colors --testsuite $TEST_SUITE --group $PLUGIN_NAME --coverage-clover $PIWIK_ROOT_DIR/build/logs/clover-$PLUGIN_NAME.xml $PHPUNIT_EXTRA_OPTIONS
        else
            travis_wait phpunit --configuration phpunit.xml --testsuite $TEST_SUITE --colors $PHPUNIT_EXTRA_OPTIONS
        fi
    fi
else
    if [ "$COVERAGE" = "Unit" ]
    then
        echo "Executing tests in test suite UnitTests..."
        phpunit --configuration phpunit.xml --testsuite UnitTests --colors --coverage-clover $TRAVIS_BUILD_DIR/build/logs/clover-unit.xml $PHPUNIT_EXTRA_OPTIONS || true
    elif [ "$COVERAGE" = "Integration" ]
    then
        echo "Executing tests in test suite IntegrationTests..."
        phpunit --configuration phpunit.xml --testsuite IntegrationTests --colors --coverage-clover $TRAVIS_BUILD_DIR/build/logs/clover-integration.xml $PHPUNIT_EXTRA_OPTIONS || true
    fi;
fi
