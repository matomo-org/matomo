 #!/bin/bash

# fonts + phantomjs 1.9.8, tests do not work with the PhantomJS version provided by Ubuntu
add-apt-repository "deb http://us-east-1.ec2.archive.ubuntu.com/ubuntu/ trusty multiverse"
add-apt-repository "deb http://us-east-1.ec2.archive.ubuntu.com/ubuntu/ trusty-updates multiverse"
echo ttf-mscorefonts-installer msttcorefonts/accepted-mscorefonts-eula select true | debconf-set-selections

# Blackfire
curl -s https://packagecloud.io/gpg.key | apt-key add -
echo "deb http://packages.blackfire.io/debian any main" > /etc/apt/sources.list.d/blackfire.list

apt-get update -qq

export DEBIAN_FRONTEND=noninteractive
apt-get install -y --quiet git zsh curl apache2 php5 php5-curl php5-gd php5-mcrypt php5-mysql php5-redis mysql-server-5.5 redis-server phantomjs ttf-mscorefonts-installer imagemagick imagemagick-doc blackfire-agent blackfire-php

# Apache
rm /etc/apache2/sites-enabled/000-default.conf
cp /vagrant/misc/vagrant/apache/sites/* /etc/apache2/sites-enabled/
cp /vagrant/misc/vagrant/apache/user.conf /etc/apache2/conf-enabled/
chmod -R 777 /var/log/apache2/

# PHP
cp /vagrant/misc/vagrant/php/custom.ini /etc/php5/apache2/conf.d/
cp /vagrant/misc/vagrant/php/custom.ini /etc/php5/cli/conf.d/

service apache2 restart

# MySQL
mysql -e "CREATE DATABASE piwik"

# phpMyAdmin
export DEBIAN_FRONTEND=noninteractive
echo 'phpmyadmin phpmyadmin/dbconfig-install boolean true' | debconf-set-selections
echo 'phpmyadmin phpmyadmin/app-password-confirm password ' | debconf-set-selections
echo 'phpmyadmin phpmyadmin/mysql/admin-pass password ' | debconf-set-selections
echo 'phpmyadmin phpmyadmin/mysql/app-pass password ' | debconf-set-selections
echo 'phpmyadmin phpmyadmin/reconfigure-webserver multiselect apache2' | debconf-set-selections
apt-get install -q -y phpmyadmin
cp /vagrant/misc/vagrant/phpmyadmin/phpmyadmin-config.inc.php /etc/phpmyadmin/config.inc.php
chmod 755 /etc/phpmyadmin/*

# Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Zsh
chsh -s /bin/zsh vagrant
git clone git://github.com/robbyrussell/oh-my-zsh.git /home/vagrant/.oh-my-zsh
cp /vagrant/misc/vagrant/.zshrc /home/vagrant/.zshrc

# This locale is used in tests
locale-gen de_DE
