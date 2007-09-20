piwik:/home/www# ls -al
lrwxrwxrwx  1 root     root       17 2007-09-20 18:20 last.zip -> latest/latest.zip
lrwxrwxrwx  1 root     root       17 2007-09-20 18:20 latest.zip -> latest/latest.zip

piwik:/home/www# cat latest/build.sh
echo "building the nightly build"
rm piwik/* -Rf
rm piwik/.* -Rf  2> /dev/null
rmdir piwik
svn export https://piwik.svn.sourceforge.net/svnroot/piwik/trunk piwik
rm piwik/libs/PhpDocumentor-1.3.2/* -fR
rm piwik/tmp/* -Rf
rm latest.zip 2> /dev/null
zip -r latest.zip piwik
echo "build finished! http://piwik.org/last.zip"
