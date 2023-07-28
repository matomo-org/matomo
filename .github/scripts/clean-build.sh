#!/bin/bash
# This script removes all files that shouldn't be included in a release
# It should be called from within the root directory of Matomo

# ------------
# WARNING:
# if you add files below, also update the Integration test in ReleaseCheckListTest.php
# in isFileDeletedFromPackage()
# ------------

echo -e "Deleting un-needed files..."

# Delete all `tests/` and `Tests/` folders
find ./ -iname 'tests' -type d -prune -exec rm -rf {} \;

# Delete all di config files for test environments
rm -rf config/environment/test.php
rm -rf config/environment/ui-test.php
rm -rf plugins/*/config/test.php
rm -rf plugins/*/config/ui-test.php

# Delete un-used files from the matomo-icons repository
rm -rf plugins/Morpheus/icons/src*
rm -rf plugins/Morpheus/icons/tools*
rm -rf plugins/Morpheus/icons/flag-icon-css*
rm -rf plugins/Morpheus/icons/submodules*
rm -rf plugins/Morpheus/icons/.git*
rm -rf plugins/Morpheus/icons/*.py
rm -rf plugins/Morpheus/icons/*.sh
rm -rf plugins/Morpheus/icons/*.json
rm -rf plugins/Morpheus/icons/*.lock
rm -rf plugins/Morpheus/icons/*.svg
rm -rf plugins/Morpheus/icons/*.txt
rm -rf plugins/Morpheus/icons/*.php
rm -rf plugins/Morpheus/icons/*.yml

# Delete all Example plugins
rm -rf plugins/Example*

rm -rf composer.phar
rm -rf vendor/bin/
rm -rf vendor/container-interop/container-interop/docs
rm -rf vendor/davaxi/sparkline/composer-8.json
rm -rf vendor/davaxi/sparkline/docker-compose.yml
rm -rf vendor/davaxi/sparkline/Dockerfile
rm -rf vendor/geoip2/geoip2/examples/
rm -rf vendor/lox/xhprof/bin
rm -rf vendor/lox/xhprof/examples
rm -rf vendor/lox/xhprof/scripts
rm -rf vendor/lox/xhprof/extension
rm -rf vendor/lox/xhprof/xhprof_html
rm -rf vendor/maxmind-db/reader/ext/
rm -rf vendor/maxmind-db/reader/autoload.php
rm -rf vendor/maxmind-db/reader/CHANGELOG.md
rm -rf vendor/maxmind/web-service-common/dev-bin/
rm -rf vendor/maxmind/web-service-common/CHANGELOG.md
rm -rf vendor/pear/archive_tar/docs
rm -rf vendor/php-di/invoker/doc/
rm -rf vendor/php-di/php-di/benchmarks/
rm -rf vendor/symfony/console/Symfony/Component/Console/Resources/bin
rm -rf vendor/szymach/c-pchart/resources/doc
rm -rf vendor/szymach/c-pchart/coverage.sh
rm -rf vendor/szymach/c-pchart/codeception.yml
rm -rf vendor/tecnickcom/tcpdf/examples
rm -rf vendor/tecnickcom/tcpdf/tools
rm -rf vendor/tecnickcom/tcpdf/CHANGELOG.TXT
rm -rf vendor/twig/twig/test/
rm -rf vendor/twig/twig/doc/
rm -rf vendor/twig/twig/.php-cs-fixer.dist.php

# Delete un-used fonts
rm -rf vendor/tecnickcom/tcpdf/fonts/ae_fonts_2.0
rm -rf vendor/tecnickcom/tcpdf/fonts/dejavu-fonts-ttf-2.33
rm -rf vendor/tecnickcom/tcpdf/fonts/dejavu-fonts-ttf-2.34
rm -rf vendor/tecnickcom/tcpdf/fonts/freefont-20100919
rm -rf vendor/tecnickcom/tcpdf/fonts/freefont-20120503
rm -rf vendor/tecnickcom/tcpdf/fonts/freemon*
rm -rf vendor/tecnickcom/tcpdf/fonts/cid*
rm -rf vendor/tecnickcom/tcpdf/fonts/courier*
rm -rf vendor/tecnickcom/tcpdf/fonts/aefurat*
rm -rf vendor/tecnickcom/tcpdf/fonts/dejavusansb*
rm -rf vendor/tecnickcom/tcpdf/fonts/dejavusansi*
rm -rf vendor/tecnickcom/tcpdf/fonts/dejavusansmono*
rm -rf vendor/tecnickcom/tcpdf/fonts/dejavusanscondensed*
rm -rf vendor/tecnickcom/tcpdf/fonts/dejavusansextralight*
rm -rf vendor/tecnickcom/tcpdf/fonts/dejavuserif*
rm -rf vendor/tecnickcom/tcpdf/fonts/freesansi*
rm -rf vendor/tecnickcom/tcpdf/fonts/freesansb*
rm -rf vendor/tecnickcom/tcpdf/fonts/freeserifb*
rm -rf vendor/tecnickcom/tcpdf/fonts/freeserifi*
rm -rf vendor/tecnickcom/tcpdf/fonts/pdf*
rm -rf vendor/tecnickcom/tcpdf/fonts/times*
rm -rf vendor/tecnickcom/tcpdf/fonts/uni2cid*

rm -rf vendor/szymach/c-pchart/resources/fonts/advent_light*
rm -rf vendor/szymach/c-pchart/resources/fonts/Bedizen*
rm -rf vendor/szymach/c-pchart/resources/fonts/calibri*
rm -rf vendor/szymach/c-pchart/resources/fonts/Forgotte*
rm -rf vendor/szymach/c-pchart/resources/fonts/MankSans*
rm -rf vendor/szymach/c-pchart/resources/fonts/pf_arma_five*
rm -rf vendor/szymach/c-pchart/resources/fonts/Silkscreen*
rm -rf vendor/szymach/c-pchart/resources/fonts/verdana*

# not needed js files
rm -rf node_modules/angular/angular.min.js.gzip
rm -rf node_modules/angular/angular.js

rm -rf node_modules/angular-animate/angular-animate.min.js.gzip
rm -rf node_modules/angular-animate/angular-animate.js

rm -rf node_modules/angular-sanitize/angular-sanitize.min.js.gzip
rm -rf node_modules/angular-sanitize/angular-sanitize.js

rm -rf node_modules/angular-cookies/angular-cookies.min.js.gzip
rm -rf node_modules/angular-cookies/angular-cookies.js

rm -rf node_modules/chroma-js/Makefile
rm -rf node_modules/chroma-js/chroma.js
rm -rf node_modules/chroma-js/doc
rm -rf node_modules/chroma-js/readme.md
rm -rf node_modules/chroma-js/src
rm -rf node_modules/chroma-js/test

rm -rf node_modules/iframe-resizer/js/iframeResizer.contentWindow.js
rm -rf node_modules/iframe-resizer/js/iframeResizer.js
rm -rf node_modules/iframe-resizer/src/ie8.polyfils.js
rm -rf node_modules/iframe-resizer/src/iframeResizer.contentWindow.js
rm -rf node_modules/iframe-resizer/src/iframeResizer.js
rm -rf node_modules/iframe-resizer/test-main.js

rm -rf node_modules/jquery/dist/jquery.js
rm -rf node_modules/jquery/src
rm -rf node_modules/jquery/external

rm -rf node_modules/jquery-ui-dist/component.json
rm -rf node_modules/jquery-ui-dist/external
rm -rf node_modules/jquery-ui-dist/images
rm -rf node_modules/jquery-ui-dist/index.html
rm -rf node_modules/jquery-ui-dist/jquery-ui.css
rm -rf node_modules/jquery-ui-dist/jquery-ui.js
rm -rf node_modules/jquery-ui-dist/jquery-ui.structure.css
rm -rf node_modules/jquery-ui-dist/jquery-ui.theme.css

rm -rf node_modules/jquery.dotdotdot/gulpfile.js
rm -rf node_modules/jquery.dotdotdot/index.html
rm -rf node_modules/jquery.dotdotdot/dotdotdot.jquery.json
rm -rf node_modules/jquery.dotdotdot/src

rm -rf node_modules/jquery.scrollto/jquery.scrollTo.js
rm -rf node_modules/jquery.scrollto/scrollTo.jquery.json
rm -rf node_modules/jquery.scrollto/changes.txt
rm -rf node_modules/jquery.scrollto/demo

rm -rf node_modules/@materializecss/materialize/extras
rm -rf node_modules/@materializecss/materialize/js
rm -rf node_modules/@materializecss/materialize/sass
rm -rf node_modules/@materializecss/materialize/dist/js/materialize.js
rm -rf node_modules/@materializecss/materialize/dist/css/materialize.css

rm -rf node_modules/mousetrap/mousetrap.js
rm -rf node_modules/mousetrap/plugins
rm -rf node_modules/mousetrap/mousetrap.sublime-project

rm -rf node_modules/ng-dialog/CONTRIBUTING.md
rm -rf node_modules/ng-dialog/css
rm -rf node_modules/ng-dialog/example
rm -rf node_modules/ng-dialog/protractor.conf.js
rm -rf node_modules/ng-dialog/server.js

rm -rf node_modules/qrcodejs2/index-svg.html
rm -rf node_modules/qrcodejs2/index.html
rm -rf node_modules/qrcodejs2/index.svg
rm -rf node_modules/qrcodejs2/jquery.min.js
rm -rf node_modules/qrcodejs2/qrcode.js

rm -rf node_modules/sprintf-js/CONTRIBUTORS.MD
rm -rf node_modules/sprintf-js/README.md
rm -rf node_modules/sprintf-js/src

rm -rf node_modules/visibilityjs/ChangeLog.md
rm -rf node_modules/visibilityjs/component.json
rm -rf node_modules/visibilityjs/index.d.ts
rm -rf node_modules/visibilityjs/index.js
rm -rf node_modules/visibilityjs/README.md

rm -rf node_modules/vue/dist/vue.cjs.js
rm -rf node_modules/vue/dist/vue.cjs.prod.js
rm -rf node_modules/vue/dist/vue.d.ts
rm -rf node_modules/vue/dist/vue.esm-browser.js
rm -rf node_modules/vue/dist/vue.esm-browser.prod.js
rm -rf node_modules/vue/dist/vue.esm-bundler.js
rm -rf node_modules/vue/dist/vue.runtime.esm-browser.js
rm -rf node_modules/vue/dist/vue.runtime.esm-browser.prod.js
rm -rf node_modules/vue/dist/vue.runtime.esm-bundler.js
rm -rf node_modules/vue/dist/vue.runtime.global.js
rm -rf node_modules/vue/dist/vue.runtime.global.prod.js

rm -f libs/jqplot/jqplot.core.js
rm -f libs/jqplot/jqplot.lineRenderer.js
rm -f libs/jqplot/jqplot.linearAxisRenderer.js
rm -f libs/jqplot/jqplot.themeEngine.js
rm -f libs/jqplot/plugins/jqplot.barRenderer.js
rm -f libs/jqplot/plugins/jqplot.pieRenderer.js

rm -f $(find config -type f -name '*.ini.php' -not -name global.ini.php)
rm -f config/config.php

rm -rf tmp/*
rm -f HIRING.md

# delete unwanted git folders, recursively
for x in .git .github ; do
    find . -name "$x" -exec rm -rf {} \; 2>/dev/null
done

# delete unwanted common files, recursively
for x in .gitignore .gitmodules .gitattributes .git-blame-ignore-revs .bowerrc .bower.json bower.json \
    .coveralls.yml .editorconfig .gitkeep .jshintrc .php_cs .php_cs.dist \
    phpunit.xml.dist phpunit.xml .phpcs.xml.dist phpcs.xml Gruntfile.js gruntfile.js \
    *.map .travis.yml installed.json package.json package-lock.json yarn.lock\
    .scrutinizer.yml .gitstats.yml composer.json composer.lock *.spec.js \
    .phpstorm.meta.php .lfsconfig .travis.sh tsconfig.json tsconfig.spec.json \
    .eslintrc.js .eslintignore .eslintrc .browserslistrc babel.config.js jest.config.js \
    karma.conf.js karma-conf.js vue.config.js .npmignore .ncurc.json .prettierrc .jscsrc \
    phpstan.neon phpstan.neon.dist package.xml .stylelintrc.json; do
    find . -name "$x" -exec rm -f {} \;
done
