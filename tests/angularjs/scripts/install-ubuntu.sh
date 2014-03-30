DIR=`dirname $0`
cd $DIR
sudo apt-get install python-software-properties
sudo apt-add-repository -y ppa:chris-lea/node.js
sudo apt-get update
sudo apt-get install nodejs
sudo apt-get install npm
cd ..
sudo npm install .