#!/bin/bash

# If there is no FTPPASS available
# we don't build the documentation
if [ -z "$FTPPASS" ]
then
	exit 0
fi

# Install phpdoc
install_phpdoc(){
	echo "Installing phpdoc"
	pyrus channel-discover pear.phpdoc.org
	pyrus install phpdoc/phpDocumentor-alpha
}

command -v phpdoc > /dev/null 2>&1 || { install_phpdoc; }

# Rehash phpenv so phpdoc binary is picked up
phpenv rehash

# Generate phpdoc for PiwikTracker
echo "Generate documentation for PiwikTracker"
phpdoc -f libs/PiwikTracker/PiwikTracker.php --title="PiwikTracker" -t docs/PiwikTracker/ --template new-black

# Install lftp
echo "Installing lftp"
sudo apt-get install lftp

# Upload generated docs via FTP
echo "Upload generated docs"
lftp -u piwik-docs,$FTPPASS ftp.piwik.org -e "set ftp:ssl-allow no; set net:max-retries 1; mirror -R docs/PiwikTracker www/PiwikTracker; quit"
