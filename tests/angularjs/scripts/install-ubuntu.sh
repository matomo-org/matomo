DIR=`dirname $0`
cd $DIR
travis_retry sudo apt-get -qq install python-software-properties
travis_retry sudo apt-add-repository -y ppa:chris-lea/node.js > /dev/null
travis_retry sudo apt-get -qq update
travis_retry sudo apt-get -qq install nodejs
travis_retry sudo apt-get -qq install npm
cd ..
sudo npm config set loglevel error
sudo npm install .