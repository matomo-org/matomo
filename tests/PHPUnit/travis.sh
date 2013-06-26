#!/bin/bash

if [ -n "$TEST_SUITE" ]
then
  if [ "$TEST_SUITE" -eq "Integration" ]
  then
	  phpunit --configuration phpunit.xml --filter Test_Piwik_Integration_NoVisit --colors Integration
	else
	  phpunit --configuration phpunit.xml --testsuite $TEST_SUITE --colors
	fi
else
	phpunit --configuration phpunit.xml --coverage-text --colors
fi
