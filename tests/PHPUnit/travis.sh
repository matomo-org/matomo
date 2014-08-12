#!/bin/bash

# this file is here for backwards compatibility. travis.sh was moved to the travis subfolder but .travis.yml files from
# older tags of Piwik plugins still try to use it. this file makes sure builds for old tags do not fail.

DIR=`dirname $0`
$DIR/../travis/travis.sh