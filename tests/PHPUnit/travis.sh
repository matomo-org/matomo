#!/bin/bash

set -e

# Sourced from https://github.com/travis-ci/travis-build/blob/master/lib/travis/build/script/templates/header.sh
# + Tweaked to display output and not show the status line
travis_wait() {
  local timeout=40
  local cmd="$@"
  local log_file=travis_wait_$$.log

  $cmd &
  local cmd_pid=$!

  travis_jigger $! $timeout $cmd &
  local jigger_pid=$!
  local result

  {
    wait $cmd_pid 2>/dev/null
    result=$?
    ps -p$jigger_pid &>/dev/null && kill $jigger_pid
  } || return 1

  if [ $result -eq 0 ]; then
echo -e "\n${GREEN}The command \"$TRAVIS_CMD\" exited with $result.${RESET}"
  else
echo -e "\n${RED}The command \"$TRAVIS_CMD\" exited with $result.${RESET}"
  fi

echo -e "\n${GREEN}Log:${RESET}\n"
  cat $log_file

  return $result
}

travis_jigger() {
  # helper method for travis_wait()
  local cmd_pid=$1
  shift
local timeout=40
  shift
local count=0


  # clear the line
  echo -e "\n"

  while [ $count -lt $timeout ]; do
count=$(($count + 1))
    #echo -ne "Still running ($count of $timeout): $@\r"
    sleep 60
  done

echo -e "\n${RED}Timeout (${timeout} minutes) reached. Terminating \"$@\"${RESET}\n"
  kill -9 $cmd_pid
}



##--------------------------------------
## Starting Piwik tests
##--------------------------------------

if [ "$TEST_SUITE" != "UITests" ] && [ "$TEST_SUITE" != "AngularJSTests" ]
then
    if [ `phpunit --group __nogroup__ | grep "No tests executed" | wc -l` -ne 1 ]
    then
        echo "=====> There are some tests functions which do not have a @group set. "
        echo "       Please add the @group phpdoc comment to the following tests: <====="
        phpunit --group __nogroup__ --testdox | grep "[x]"
        exit 1
    fi
fi

if [ -n "$TEST_SUITE" ]
then
    echo "Executing tests in test suite $TEST_SUITE..."

    if [ -n "$PLUGIN_NAME" ]
    then
        echo "    [ plugin name = $PLUGIN_NAME ]"
    fi

    if [ "$TEST_SUITE" = "AngularJSTests" ]
    then
        sh ./../angularjs/scripts/travis.sh
    elif [ "$TEST_SUITE" = "JavascriptTests" ]
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
            phantomjs ../lib/screenshot-testing/run-tests.js --assume-artifacts --persist-fixture-data --screenshot-repo=$TRAVIS_REPO_SLUG --plugin=$PLUGIN_NAME
        else
            phantomjs ../lib/screenshot-testing/run-tests.js --store-in-ui-tests-repo --persist-fixture-data --assume-artifacts
        fi
    else
        if [ -n "$PLUGIN_NAME" ]
        then
            travis_wait phpunit --configuration phpunit.xml --colors --testsuite $TEST_SUITE --group $PLUGIN_NAME
        else
            travis_wait phpunit --configuration phpunit.xml --testsuite $TEST_SUITE --colors
        fi
    fi
else
    travis_wait phpunit --configuration phpunit.xml --coverage-text --colors
fi

