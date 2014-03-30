DIR=`dirname $0`
cd $DIR
./install-ubuntu.sh
./node_modules/karma/bin/karma start --browsers PhantomJS --single-run karma.conf.js