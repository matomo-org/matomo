#!/bin/bash
#
# Piwik - package untagged trunk files for release testing by CI server
#

function cleanup() {
	rm -rf piwik
	rm -f *.html
	rm -f *.xml
}

#
# Clean up build environment
#
cleanup
rm -f latest.zip

#
# Package into a release
#

svn export http://dev.piwik.org/svn/trunk piwik
rm -rf piwik/tmp/*
rm -f piwik/misc/db-schema*
rm -f piwik/misc/diagram_general_request*

cp piwik/tests/README.txt .
find piwik -name 'tests' -type d -prune -exec rm -rf {} \;
mkdir piwik/tests
mv README.txt piwik/tests/

cp piwik/misc/How\ to\ install\ Piwik.html .
cp piwik/misc/WebAppGallery/*.xml .

find piwik -type f -printf '%s ' -exec md5sum {} \; | fgrep -v 'manifest.inc.php' | sed '1,$ s/\([0-9]*\) \([a-z0-9]*\) *piwik\/\(.*\)/\t\t"\3" => array("\1", "\2"),/; 1 s/^/<?php\nclass Manifest {\n\tstatic $files=array(\n/; $ s/$/\n\t);\n}/' > piwik/config/manifest.inc.php

zip -q -r latest.zip piwik How\ to\ install\ Piwik.html *.xml > /dev/null 2> /dev/null

cleanup
