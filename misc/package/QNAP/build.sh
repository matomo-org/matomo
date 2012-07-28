#!/bin/sh

LATEST=`curl -s http://api.piwik.org/1.0/getLatestVersion/`
curl -s http://builds.piwik.org/piwik-$LATEST.tar.gz | gunzip | tar xf -

cp icons/qpkg_icon_80.gif piwik/.qpkg_icon_80.gif
cp icons/qpkg_icon.gif piwik/.qpkg_icon.gif
cp icons/qpkg_icon_gray.gif piwik/.qpkg_icon_gray.gif

tar cf - piwik | gzip >Piwik.tgz

sed "s/{{VERSION}}/$LATEST/" <qpkg.cfg.tpl >qpkg.cfg

cp header.qpkg Piwik_$LATEST.qpkg
tar zcf - qinstall.sh Piwik.tgz qpkg.cfg >>Piwik_$LATEST.qpkg

zip Piwik_$LATEST.zip Piwik_$LATEST.qpkg
