#!/bin/bash

if [ -n "$TEST_SUITE" ]
then
	phpunit --configuration phpunit.xml --testsuite $TEST_SUITE --colors
else
  if [ -n "$TEST_DIR" ]
  then
    phpunit --colors $TEST_DIR
  else
	  phpunit --configuration phpunit.xml --coverage-text --colors
  fi
fi
