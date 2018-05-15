#!/usr/bin/env bash
DIR=`dirname $0`
source $DIR/../../travis/travis-helper.sh

cd ..
npm config set loglevel error
travis_retry npm install .