DIR=`dirname $0`
source $DIR/../../travis/travis-helper.sh

cd $DIR
travis_retry sudo apt-get -qq install python-software-properties
travis_retry sudo apt-add-repository -y ppa:chris-lea/node.js > /dev/null
travis_retry sudo apt-get -qq update

cd ..
npm config set loglevel error
travis_retry npm install .