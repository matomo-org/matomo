#!/bin/bash

sudo mkdir /mnt/ramdisk
sudo mount -t tmpfs -o size=1024m tmpfs /mnt/ramdisk
sudo mv /var/lib/mysql /mnt/ramdisk
sudo ln -s /mnt/ramdisk/mysql /var/lib/mysql

## print out mysql information
#mysql --version
#mysql -e 'CREATE DATABASE ${{ env.DB_DATABASE }};' -u${{ env.DB_USER }} -p${{ env.DB_PASSWORD }}
## configure mysql
#mysql -e "SET GLOBAL sql_mode = 'NO_ENGINE_SUBSTITUTION,STRICT_TRANS_TABLES'" -u${{ env.DB_USER }} -p${{ env.DB_PASSWORD }}  # Travis default
## try to avoid 'mysql has gone away' errors
#mysql -e "SET GLOBAL wait_timeout = 36000;" -u${{ env.DB_USER }} -p${{ env.DB_PASSWORD }}
#mysql -e "SET GLOBAL max_allowed_packet = 134209536;"  -u${{ env.DB_USER }} -p${{ env.DB_PASSWORD }}
#mysql -e "SHOW VARIABLES LIKE 'max_allowed_packet';" -u${{ env.DB_USER }} -p${{ env.DB_PASSWORD }}
#mysql -e "SHOW VARIABLES LIKE 'wait_timeout';" -u${{ env.DB_USER }} -p${{ env.DB_PASSWORD }}
#
#mysql -e "SELECT @@sql_mode;" -u${{ env.DB_USER }} -p${{ env.DB_PASSWORD }}
## - mysql -e "SHOW GLOBAL VARIABLES;"

