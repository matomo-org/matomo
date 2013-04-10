#!/bin/bash

if [ -n "$TEST_SUITE" ]
then
	phpunit --configuration phpunit.xml --testsuite $TEST_SUITE --colors
else
	phpunit --configuration phpunit.xml --coverage-text --colors
fi
