#!/bin/bash



if [ "$MATOMO_TEST_TARGET" == "AngularJSTests" ] || [ "$MATOMO_TEST_TARGET" == "UnitTests" ] || [ "$MATOMO_TEST_TARGET" == "JavascriptTests" ]; then
    echo "Skipping mysql setup."
    exit 0;
fi
sudo /etc/init.d/mysql start
sudo mkdir /mnt/ramdisk
sudo mount -t tmpfs -o size=1024m tmpfs /mnt/ramdisk
sudo mv /var/lib/mysql /mnt/ramdisk
sudo ln -s /mnt/ramdisk/mysql /var/lib/mysql

# print out mysql information
mysql --version
mysql -e 'CREATE DATABASE matomo_tests;' -u root -p root

# configure mysql
mysql -e "SET GLOBAL sql_mode = 'NO_ENGINE_SUBSTITUTION,STRICT_TRANS_TABLES'" -u root -p root # Travis default
# try to avoid 'mysql has gone away' errors
mysql -e "SET GLOBAL wait_timeout = 36000;" -u root -p root
mysql -e "SET GLOBAL max_allowed_packet = 134209536;" -u root -p root
mysql -e "SHOW VARIABLES LIKE 'max_allowed_packet';" -u root -p root
mysql -e "SHOW VARIABLES LIKE 'wait_timeout';" -u root -p root

mysql -e "SELECT @@sql_mode;" -u root -p root
# - mysql -e "SHOW GLOBAL VARIABLES;"

