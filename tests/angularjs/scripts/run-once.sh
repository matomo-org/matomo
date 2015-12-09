#!/usr/bin/env bash
DIR=`dirname $0`
cd $DIR
cd ..
./node_modules/karma/bin/karma start karma.conf.js --browsers PhantomJS --single-run