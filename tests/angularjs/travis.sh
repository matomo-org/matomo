sudo apt-get install npm
DIR=`dirname $0`
cd $DIR
sudo npm install .
./node_modules/karma/bin/karma start --browsers PhantomJS --single-run karma.conf.js