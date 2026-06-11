<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Rector configuration used to downgrade composer dependencies to PHP 7.2 syntax.
 *
 * This config is not meant to be run directly. It is executed by
 * .github/scripts/vendor-downgrade/downgrade-vendor.php (wired into the composer
 * post-install/post-update hooks), which passes the vendor package paths to
 * process on the command line.
 *
 * Note that the downgrade only covers PHP *syntax*. Calls to functions or classes
 * introduced after PHP 7.2 (e.g. str_contains, Stringable) are not rewritten and
 * must be provided through symfony/polyfill-php7x/php8x packages in composer.json.
 */

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\DowngradeLevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([DowngradeLevelSetList::DOWN_TO_PHP_72]);

    $rectorConfig->parallel(240);

    $rectorConfig->skip([
        // test and doc files of vendor packages are neither autoloaded nor shipped,
        // and may intentionally contain syntax rector cannot parse or downgrade
        '*/tests/*',
        '*/Tests/*',
        '*/test/*',
        '*/Test/*',
        '*/doc/*',
        '*/docs/*',
        '*/examples/*',
    ]);
};
