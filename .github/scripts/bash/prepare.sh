#!/bin/bash
RED='\033[0;31m'
GREEN='\033[0;32m'
SET='\033[0m'

if [$PIWIK_TEST_TARGET = "UI" ] ]
then
  echo -e "${GREEN}Setup fonts${SET}"
  git clone --recursive https://github.com/google/woff2.git ../travis_woff2
  cd ../travis_woff2
  make clean all
  mkdir $HOME/.fonts
  cp /home/runner/work/matomo/matomo/.github/scripts/fonts/* $HOME/.fonts
  fc-cache -f -v
  ls $HOME/.fonts
  sudo sed -i -E 's/name="memory" value="[^"]+"/name="memory" value="2GiB"/g' /etc/ImageMagick-6/policy.xml
  sudo sed -i -E 's/name="width" value="[^"]+"/name="width" value="64KP"/g' /etc/ImageMagick-6/policy.xml
  sudo sed -i -E 's/name="height" value="[^"]+"/name="height" value="64KP"/g' /etc/ImageMagick-6/policy.xml
  sudo sed -i -E 's/name="area" value="[^"]+"/name="area" value="1GiB"/g' /etc/ImageMagick-6/policy.xml
  sudo sed -i -E 's/name="disk" value="[^"]+"/name="area" value="4GiB"/g' /etc/ImageMagick-6/policy.xml
fi

if [ $PIWIK_TEST_TARGET = "UI" ] || [ $PIWIK_TEST_TARGET = "Javascript" ];
then
  mkdir -p ./tmp/assets
  mkdir -p ./tmp/cache
  mkdir -p ./tmp/cache/tracker
  mkdir -p ./tmp/latest
  mkdir -p ./tmp/logs
  mkdir -p ./tmp/sessions
  mkdir -p ./tmp/templates_c
  mkdir -p ./tmp/templates_c/d2
  mkdir -p ./tmp/tcpdf
  mkdir -p ./tmp/climulti
  mkdir -p /tmp
  chmod a+rw ./tests/lib/geoip-files || true
  chmod a+rw ./plugins/*/tests/System/processed || true
  cp .github/scripts/config.ini.github.php  config/config.ini.php
  php ./tests/PHPUnit/formatXML.php
  ls ./tests/PHPUnit/
fi



if [ $PIWIK_TEST_TARGET = "UI" ];
then
  echo -e "${GREEN}installing node/puppeteer${SET}"
  cd ./tests/lib/screenshot-testing
  git lfs pull --exclude=
  npm install
  node --version
fi

  echo -e "${GREEN}setup php-fpm${SET}"
  sudo systemctl enable php$PHP_VERSION-fpm.service
  sudo systemctl start php$PHP_VERSION-fpm.service
  sudo cp -rf  ./.github/scripts/www.conf /etc/php/$PHP_VERSION/fpm/pool.d/
  sudo systemctl reload php$PHP_VERSION-fpm.service
  sudo systemctl restart php$PHP_VERSION-fpm.service
  sudo systemctl status php$PHP_VERSION-fpm.service
  sudo systemctl enable nginx
  sudo systemctl start nginx
  sudo cp ./.github/scripts/ui_nginx.conf /etc/nginx/conf.d/
  sudo unlink /etc/nginx/sites-enabled/default
  sudo nginx -t
  sudo systemctl reload nginx
  sudo systemctl restart nginx

  echo -e "${GREEN}set folder Permission${SET}"
  cp .github/scripts/config.ini.github.ui.php config/config.ini.php
  cp .github/scripts/config.dist.js ./tests/UI/config.js
  cp ./tests/PHPUnit/phpunit.xml.dist ./tests/PHPUnit/phpunit.xml
  mkdir -p ./tmp/assets
  mkdir -p ./tmp/cache
  mkdir -p ./tmp/cache/tracker
  mkdir -p ./tmp/latest
  mkdir -p ./tmp/logs
  mkdir -p ./tmp/sessions
  mkdir -p ./tmp/templates_c
  mkdir -p ./tmp/templates_c/d2
  mkdir -p ./tmp/templates_c/2f
  mkdir -p ./tmp/nonexistant
  mkdir -p ./tmp/tcpdf
  mkdir -p ./tmp/climulti
  mkdir -p /tmp
  
if [ $PIWIK_TEST_TARGET = "UI" ];
then
  mkdir -p ./tests/UI/processed-ui-screenshots
  chmod a+rw ./tests/lib/geoip-files || true
  chmod a+rw ./plugins/*/tests/System/processed || true
  chmod a+rw ./plugins/*/tests/Integration/processed || true

fi

echo -e "${GREEN}install composer${SET}"
composer install --ignore-platform-reqs
#if [ $PIWIK_TEST_TARGET !== "UI" ];
#  sudo setcap CAP_NET_BIND_SERVICE=+eip $(readlink -f $(which php))
#  tmux new-session -d -s "php-80" php -S localhost:80
#  tmux new-session -d -s "php-3000" php -S localhost:3000
#  tmux ls
#fi

echo -e "${GREEN}set tmp and screenshot folder permission${SET}"
sudo gpasswd -a "$USER" www-data
sudo chown -R "$USER":www-data /home/runner/work/matomo/matomo/
sudo chmod o+w /home/runner/work/matomo/matomo/
sudo chmod -R 777 /home/runner/work/matomo/matomo/tmp
sudo chmod -R 777 /tmp
sudo chmod -R 777 /home/runner/work/matomo/matomo/tmp/templates_c
sudo chmod -R 777 /home/runner/work/matomo/matomo/tests/UI
