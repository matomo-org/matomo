DIR=`dirname $0`
cd $DIR
sudo apt-get -qq install python-software-properties
sudo apt-add-repository -y ppa:chris-lea/node.js > /dev/null
sudo apt-get -qq update
sudo apt-get -qq install nodejs
sudo apt-get -qq install npm
cd ..
sudo npm config set loglevel error
sudo npm install .