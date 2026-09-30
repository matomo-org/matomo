#!/bin/bash
# Runs the Playwright UI tests in the container: waits for MySQL, starts PHP's server like the CI job does,
# then passes all arguments to "playwright test". --update writes changed screenshots.
set -eu
# the CI checkout path: some pages and responses contain it
cd /home/runner/work/matomo/matomo

args=()
for arg in "$@"; do
  case "$arg" in
    --update) export PLAYWRIGHT_UPDATE_SNAPSHOTS=changed ;;
    *) args+=("$arg") ;;
  esac
done

[ -f vendor/autoload.php ] || composer install --no-interaction --no-progress
(cd tests/playwright && [ -d node_modules/@playwright ] || npm ci --no-audit --no-fund)

until mysqladmin ping -h127.0.0.1 -uroot -proot --silent; do sleep 1; done
mysql -h127.0.0.1 -uroot -proot -e "CREATE DATABASE IF NOT EXISTS matomo_tests; SET GLOBAL local_infile = 1"

mkdir -p tmp/assets tmp/cache/tracker tmp/latest tmp/logs tmp/sessions tmp/templates_c tmp/tcpdf tmp/climulti
PHP_CLI_SERVER_WORKERS=8 php -S 0.0.0.0:80 -t /home/runner/work/matomo/matomo > tmp/logs/php-server.log 2>&1 &
until curl -sf -o /dev/null http://localhost/index.php; do sleep 1; done

cd tests/playwright
exec npx playwright test "${args[@]}"
