<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Determines which installed composer packages need to be downgraded to PHP 7.2.
 *
 * A package needs downgrading when its declared PHP requirement cannot be
 * satisfied by Matomo's minimum supported PHP version. The list is derived from
 * vendor/composer/installed.json (and not composer.lock) so it also works in
 * --no-dev release builds.
 *
 * Must stay compatible with PHP 7.2 syntax, as it runs under whatever PHP
 * binary invoked composer.
 *
 * Can be run standalone to print the list:
 *   php .github/scripts/vendor-downgrade/compute-downgrade-paths.php
 */

use Composer\Semver\Semver;

/**
 * Packages with a missing or incorrect "php" requirement declaration can be
 * forced into or excluded from the downgrade here (composer package names).
 */
const MATOMO_DOWNGRADE_OVERRIDES = [
    'force' => [],
    'exclude' => [],
];

/**
 * The PHP version every downgraded package must be able to run on.
 */
const MATOMO_DOWNGRADE_TARGET_PHP = '7.2.9';

/**
 * @return array<string, array{path: string, version: string, php: string}>
 *         indexed by package name, paths are absolute
 */
function matomo_compute_downgrade_packages(string $rootDir): array
{
    $installedJsonPath = $rootDir . '/vendor/composer/installed.json';

    if (!file_exists($installedJsonPath)) {
        throw new RuntimeException('Unable to find ' . $installedJsonPath . '. Did composer install run?');
    }

    $installed = json_decode((string) file_get_contents($installedJsonPath), true);

    if (!is_array($installed) || !isset($installed['packages']) || !is_array($installed['packages'])) {
        throw new RuntimeException('Unexpected format of ' . $installedJsonPath);
    }

    $packages = [];

    foreach ($installed['packages'] as $package) {
        $name = isset($package['name']) ? $package['name'] : null;

        if ($name === null || in_array($name, MATOMO_DOWNGRADE_OVERRIDES['exclude'], true)) {
            continue;
        }

        $phpRequirement = isset($package['require']['php']) ? $package['require']['php'] : null;

        $needsDowngrade = in_array($name, MATOMO_DOWNGRADE_OVERRIDES['force'], true)
            || ($phpRequirement !== null && !Semver::satisfies(MATOMO_DOWNGRADE_TARGET_PHP, $phpRequirement));

        if (!$needsDowngrade) {
            continue;
        }

        // install-path is relative to vendor/composer
        $installPath = isset($package['install-path']) ? $package['install-path'] : null;

        if ($installPath === null) {
            continue; // metapackages have no code to downgrade
        }

        $absolutePath = realpath($rootDir . '/vendor/composer/' . $installPath);

        if ($absolutePath === false) {
            throw new RuntimeException(sprintf(
                'Package %s needs a PHP 7.2 downgrade, but its install path %s does not exist.',
                $name,
                $rootDir . '/vendor/composer/' . $installPath
            ));
        }

        $packages[$name] = [
            'path' => $absolutePath,
            'version' => isset($package['version']) ? $package['version'] : 'unknown',
            'php' => $phpRequirement !== null ? $phpRequirement : '(forced)',
        ];
    }

    ksort($packages);

    return $packages;
}

if (isset($argv[0]) && realpath($argv[0]) === __FILE__) {
    $rootDir = dirname(dirname(dirname(__DIR__)));

    require_once $rootDir . '/vendor/autoload.php';

    foreach (matomo_compute_downgrade_packages($rootDir) as $name => $package) {
        echo $name . ' ' . $package['version'] . ' (php: ' . $package['php'] . ') ' . $package['path'] . "\n";
    }
}
