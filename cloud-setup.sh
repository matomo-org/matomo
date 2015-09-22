#!/bin/bash
set -o xtrace
source cloud-config.sh

rm config/common.config.ini.php
rm config/test*.piwik.pro.config.ini.php 

cat plugins/EnterpriseAdmin/ConfigTemplate/common.config.ini.php | sed "s/force_ssl = 1/force_ssl = 0/" > config/common.config.ini.php
cat plugins/{LoginAdmin,WhitelistAdmin}/ConfigTemplate/common.config.ini.php >> config/common.config.ini.php


for I in $(seq 1 $END); do
    mysql -uroot -e "DROP DATABASE dbname$I;"

    sudo chmod 777 -R tmp/test$I.piwik.pro/

    # test no db prefix
    ./console enterprise:install \
        --db-host="localhost" --db-login="root" --db-password="" --db-name="dbname$I" --db-adapter="PDO\\MYSQL" \
        --website-name="Main Shop $I" --website-url="http://shop$I.enterprise.com" --website-timezone="Africa/Accra" --website-ecommerce \
        --superuser-login="admin" --superuser-password="secure" --superuser-email="admin@enterpriseanalytics.com" \
        --piwik-domain=http://test$I.piwik.pro \
        --skip-writable-check --skip-geoip-check --skip-https-check --skip-http-check #--db-dropexistingtables

    echo "Generating websites, users and goals for cloud instance = $I..."
    ./console enterprise:plugin activate VisitorGenerator --piwik-domain=http://test$I.piwik.pro

    echo "Generating websites for cloud instance ID = $I..."
    ./console visitorgenerator:generate-websites --piwik-domain=test$I.piwik.pro --limit=$(( ( RANDOM % 5 )  + 10 ))

    echo "Generating users for cloud instance ID = $I..."
    ./console visitorgenerator:generate-users --piwik-domain=test$I.piwik.pro --limit=5

    echo "Generating goals for cloud instance ID = $I..."
    for ((n=1;n<5;n++)); do (./console visitorgenerator:generate-goals --idsite=$n  --piwik-domain=test$I.piwik.pro); done

done

chmod 755 config/*.config.ini.php

echo "Setting master instance to test1.piwik.pro..."
./console enterprise:setmasterinstance --piwik-domain=test1.piwik.pro -vvv

