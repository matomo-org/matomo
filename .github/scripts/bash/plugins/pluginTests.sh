#!/bin/bash
RED='\033[0;31m'
GREEN='\033[0;32m'
SET='\033[0m'

if [ -n "$TEST_SUITE" ]
then
    echo -e "${GREEN}Executing tests in test suite $TEST_SUITE...${SET}"
    if [ -n "$PLUGIN_NAME" ]
    then
        echo -e "${GREEN}[ plugin name = $PLUGIN_NAME ]${SET}"
    fi

    if [ "$TEST_SUITE" = "AngularJSTests" ]
    then
     echo -e "${GREEN}Running angularjs tests${SET}"
     ./node_modules/karma/bin/karma start karma.conf.js --browsers ChromeHeadless --single-run
     echo -e "${GREEN}Running vue tests${SET}"
     npm test
    elif [ "$TEST_SUITE" = "JavascriptTests" ]
    then
      ./console tests:run-js --matomo-url='http://localhost'
    elif [ "$TEST_SUITE" = "UI" ]
    then
        if [ -n "$PLUGIN_NAME" ]
        then
            ./console tests:run-ui --store-in-ui-tests-repo --persist-fixture-data --assume-artifacts --plugin=$PLUGIN_NAME --extra-options="$UITEST_EXTRA_OPTIONS"
        else
            ./console tests:run-ui --store-in-ui-tests-repo --persist-fixture-data --assume-artifacts --core --extra-options="$UITEST_EXTRA_OPTIONS"
        fi
    elif [ "$TEST_SUITE" = "AllTests" ]
    then
        exit_code=0

        if [ "$ALLTEST_EXTRA_OPTIONS" = "--run-first-half-only" ]
        then
            echo "Executing tests in test suite UnitTests"
            ./vendor/phpunit/phpunit/phpunit --configuration ./tests/PHPUnit/phpunit.xml --testsuite UnitTests --colors $PHPUNIT_EXTRA_OPTIONS || exit_code=$?
            echo "Executing tests in test suite SystemTests for Plugins"
            ./vendor/phpunit/phpunit/phpunit --configuration ./tests/PHPUnit/phpunit.xml --testsuite SystemTestsPlugins --colors $PHPUNIT_EXTRA_OPTIONS || exit_code=$?
            echo "Executing tests in test suite SystemTests for Core"
            ./vendor/phpunit/phpunit/phpunit --configuration ./tests/PHPUnit/phpunit.xml --testsuite SystemTestsCore --colors $PHPUNIT_EXTRA_OPTIONS || exit_code=$?
        elif [ "$ALLTEST_EXTRA_OPTIONS" = "--run-second-half-only" ]
        then
            echo "Executing tests in test suite IntegrationTests"
            ./vendor/phpunit/phpunit/phpunit --configuration ./tests/PHPUnit/phpunit.xml --testsuite IntegrationTests --colors $PHPUNIT_EXTRA_OPTIONS || exit_code=$?
        else
            ./console tests:run --options="--colors" || exit_code=$?
        fi

        exit $exit_code
    else
        if [ -n "$PLUGIN_NAME" ]
        then
            ./vendor/phpunit/phpunit/phpunit --configuration ./tests/PHPUnit/phpunit.xml --colors --testsuite $TEST_SUITE --group $PLUGIN_NAME --coverage-clover $PIWIK_ROOT_DIR/build/logs/clover-$PLUGIN_NAME.xml $PHPUNIT_EXTRA_OPTIONS | tee phpunit.out
        else
            ./vendor/phpunit/phpunit/phpunit --configuration ./tests/PHPUnit/phpunit.xml --testsuite $TEST_SUITE --colors $PHPUNIT_EXTRA_OPTIONS | tee phpunit.out
        fi

        exit_code="${PIPESTATUS[0]}"
        if [ "$exit_code" -ne "0" ]; then
            exit $exit_code
        elif grep "No tests executed" phpunit.out; then
            exit 1
        else
            exit 0
        fi
    fi
fi