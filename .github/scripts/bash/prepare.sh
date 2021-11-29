#!/bin/bash

if [${{ $matrix.tests }} = "UITests" ] ]
then
  git clone --recursive https://github.com/google/woff2.git ../travis_woff2
  cd ../travis_woff2
  make clean all
  mkdir $HOME/.fonts
  cp /home/runner/work/matomo/matomo/.github/scripts/fonts/* $HOME/.fonts
  fc-cache -f -v
  echo "fonts:"
  ls $HOME/.fonts
  sudo sed -i -E 's/name="memory" value="[^"]+"/name="memory" value="2GiB"/g' /etc/ImageMagick-6/policy.xml
  sudo sed -i -E 's/name="width" value="[^"]+"/name="width" value="64KP"/g' /etc/ImageMagick-6/policy.xml
  sudo sed -i -E 's/name="height" value="[^"]+"/name="height" value="64KP"/g' /etc/ImageMagick-6/policy.xml
  sudo sed -i -E 's/name="area" value="[^"]+"/name="area" value="1GiB"/g' /etc/ImageMagick-6/policy.xml
  sudo sed -i -E 's/name="disk" value="[^"]+"/name="area" value="4GiB"/g' /etc/ImageMagick-6/policy.xml
fi

if [ ${{ $matrix.tests }} = "UITests" ] || [ ${{ $matrix.tests }} = "JavascriptTests" ];
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



if [ ${{ $matrix.tests }} = "UITests" ||  ];
  echo "installing node/puppeteer"
  cd ./tests/lib/screenshot-testing
  git lfs pull --exclude=
  npm install
  node --version


if [ ${{ $matrix.tests }} = "UITests" ];
then
  echo "setup php-fpm"
  sudo systemctl enable php7.2-fpm.service
  sudo systemctl start php7.2-fpm.service
  sudo cp -rf  ./.github/scripts/www.conf /etc/php/7.2/fpm/pool.d/
  sudo systemctl reload php7.2-fpm.service
  sudo systemctl restart php7.2-fpm.service
  sudo systemctl status php7.2-fpm.service
  sudo systemctl enable nginx
  sudo systemctl start nginx
  sudo cp ./.github/scripts/ui_nginx.conf /etc/nginx/conf.d/
  sudo unlink /etc/nginx/sites-enabled/default
  sudo nginx -t
  sudo systemctl reload nginx
  sudo systemctl restart nginx

  echo "set folder Permission"
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
  mkdir -p ./tests/UI/processed-ui-screenshots
  chmod a+rw ./tests/lib/geoip-files || true
  chmod a+rw ./plugins/*/tests/System/processed || true
  chmod a+rw ./plugins/*/tests/Integration/processed || true

fi

