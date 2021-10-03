#!/usr/bin/env bash
DIR=`dirname $0`
cd $DIR
cd ..

echo ""
echo "Running angularjs tests"
echo ""

./node_modules/karma/bin/karma start karma.conf.js --browsers ChromeHeadless --single-run

echo ""
echo "Running vue tests"
echo ""

cd ../..
ls node_modules
ls node_modules/.bin
ls node_modules/@vue/cli-service
npm test
