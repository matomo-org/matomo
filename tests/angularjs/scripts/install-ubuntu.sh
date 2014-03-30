DIR=`dirname $0`
cd $DIR
sudo apt-get install python-software-properties
sudo apt-add-repository -y ppa:chris-lea/node.js
sudo apt-get -qq update
sudo apt-get -qq install nodejs
sudo apt-get -qq install npm
cd ..
sudo npm config set loglevel warn
sudo npm install .