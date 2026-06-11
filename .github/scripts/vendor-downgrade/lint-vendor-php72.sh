#!/bin/bash
# Verifies that all installed vendor code can be parsed by PHP 7.2.
#
# Acts as an early warning when a composer dependency was not (fully) downgraded
# by the vendor downgrade mechanism (see downgrade-vendor.php), e.g. because a
# package under-declares its PHP requirement or rector missed a construct.
#
# Must be run with a PHP 7.2 binary, either as the default `php` or passed
# explicitly: PHP_BIN=php7.2 ./lint-vendor-php72.sh

set -e

cd "$(dirname "$0")/../../.."

PHP_BIN="${PHP_BIN:-php}"

if [ ! -f tools/rector/vendor/bin/parallel-lint ]; then
    composer install -d tools/rector --no-interaction --no-progress --ignore-platform-reqs
fi

excludes=(--exclude vendor/bin)

# Files that intentionally contain newer PHP syntax but are never loaded on PHP 7.2:
# - symfony polyfill bootstrap8*.php files (required only when PHP_VERSION_ID >= 80000)
# - symfony PHP attribute classes (attributes are only used on PHP >= 8; on PHP 7.2
#   rector downgrades attribute usages to annotations)
# - package test/doc/example directories (not autoloaded, also skipped by the downgrade;
#   only matched at package root level, as deeper directories with these names can be
#   real code, e.g. twig's src/Node/Expression/Test classes)
while IFS= read -r path; do
    excludes+=(--exclude "$path")
done < <(
    find vendor -type f -name 'bootstrap8*.php' -path '*/polyfill-*'
    find vendor -mindepth 3 -maxdepth 3 -type d -name 'Attribute' -path 'vendor/symfony/*'
    find vendor -mindepth 3 -maxdepth 3 -type d \( -name 'tests' -o -name 'Tests' -o -name 'test' -o -name 'Test' -o -name 'doc' -o -name 'docs' -o -name 'examples' \)
)

"$PHP_BIN" -v | head -1

"$PHP_BIN" tools/rector/vendor/bin/parallel-lint --no-progress -e php "${excludes[@]}" vendor/
