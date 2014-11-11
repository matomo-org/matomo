#!/bin/bash
mkdir /tmp/ramdisk
mount -t tmpfs -o size=1536M tmpfs /tmp/ramdisk/
mv /var/lib/mysql /tmp/ramdisk/mysql
ln -s /tmp/ramdisk/mysql/ /var/lib/mysql
chmod -R 770 /var/lib/mysql
chown -R ubuntu:ubuntu /var/lib/mysql
service mysql restart
composer-phar self-update