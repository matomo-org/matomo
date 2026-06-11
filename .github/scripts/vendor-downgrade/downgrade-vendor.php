<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Downgrades installed composer dependencies to PHP 7.2 syntax using Rector.
 *
 * Matomo supports PHP >= 7.2.5, but composer.json resolves dependencies against
 * a newer PHP platform version (config.platform.php). This script makes the
 * installed vendor code compatible with PHP 7.2 again. It is wired into the
 * composer post-install-cmd/post-update-cmd hooks and can be run manually via
 * `composer downgrade-vendor`.
 *
 * Rector requires PHP >= 7.4, so this script locates a suitable PHP binary and
 * only uses it for running Rector; the script itself must stay compatible with
 * PHP 7.2 syntax, as it runs under whatever PHP binary invoked composer.
 *
 * A stamp file (vendor/.rector-downgraded) makes repeated runs no-ops as long
 * as composer.lock, the rector config and the pinned rector version stay
 * unchanged. Set MATOMO_SKIP_VENDOR_DOWNGRADE=1 to bypass the downgrade
 * entirely (the installed vendor code may then not run on PHP < 8).
 */

$rootDir = dirname(dirname(dirname(__DIR__)));

require_once $rootDir . '/vendor/autoload.php';
require_once __DIR__ . '/compute-downgrade-paths.php';

const MATOMO_DOWNGRADE_STAMP_FILE = '/vendor/.rector-downgraded';
const MATOMO_DOWNGRADE_MIN_PHP_ID = 70400;

function matomo_downgrade_log(string $message): void
{
    fwrite(STDERR, '[vendor-downgrade] ' . $message . "\n");
}

function matomo_downgrade_fail(string $message): void
{
    matomo_downgrade_log('ERROR: ' . $message);
    exit(1);
}

/**
 * @param array<string, array{path: string, version: string, php: string}> $packages
 */
function matomo_downgrade_stamp_hash(string $rootDir, array $packages): string
{
    $hashInputs = [json_encode($packages)];

    foreach (['/composer.lock', '/rector-downgrade.php', '/tools/rector/composer.lock'] as $file) {
        $hashInputs[] = file_exists($rootDir . $file) ? (string) file_get_contents($rootDir . $file) : '';
    }

    // a package directory's mtime changes when composer (re-)extracts the package, but not
    // when rector edits files within it. This detects e.g. `composer reinstall <package>`
    // restoring original package code without any composer.lock change.
    foreach ($packages as $package) {
        $hashInputs[] = $package['path'] . ':' . filemtime($package['path']);
    }

    return hash('sha256', implode("\0", $hashInputs));
}

/**
 * Detects enum declarations in the packages to be downgraded.
 *
 * Rector converts enums to constant-list classes, but several related downgrades
 * (type declarations, ->value fetches) cannot rely on rector's reflection, since it
 * breaks once the enum declaration was converted on disk within the same rector run.
 * The custom rules in tools/rector/rules are therefore configured with this statically
 * detected list (passed via the MATOMO_DOWNGRADE_ENUMS environment variable).
 *
 * @param array<string, array{path: string, version: string, php: string}> $packages
 * @return string[] fully qualified enum class names
 */
function matomo_downgrade_find_enums(array $packages): array
{
    $enums = [];

    foreach ($packages as $package) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($package['path'], FilesystemIterator::SKIP_DOTS)
        );

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $code = (string) file_get_contents((string) $file);

            if (
                strpos($code, 'enum ') === false
                || !preg_match_all('/^\s*(?:final\s+|abstract\s+)?enum\s+([A-Za-z_]\w*)/m', $code, $matches)
            ) {
                continue;
            }

            $namespace = preg_match('/^namespace\s+([^;{\s]+)/m', $code, $namespaceMatch)
                ? $namespaceMatch[1] . '\\'
                : '';

            foreach ($matches[1] as $enumName) {
                $enums[] = $namespace . $enumName;
            }
        }
    }

    sort($enums);

    return $enums;
}

/**
 * Returns the path to a PHP CLI binary that can run Rector (PHP >= 7.4).
 */
function matomo_downgrade_find_php(): ?string
{
    if (PHP_VERSION_ID >= MATOMO_DOWNGRADE_MIN_PHP_ID && PHP_SAPI === 'cli') {
        return PHP_BINARY;
    }

    $candidates = ['php8.3', 'php8.2', 'php8.4', 'php8.5', 'php8.1', 'php8.0', 'php7.4', 'php'];

    foreach ($candidates as $candidate) {
        $output = [];
        $exitCode = 1;
        exec($candidate . ' -r "echo PHP_VERSION_ID;" 2>/dev/null', $output, $exitCode);

        if ($exitCode === 0 && isset($output[0]) && (int) $output[0] >= MATOMO_DOWNGRADE_MIN_PHP_ID) {
            return $candidate;
        }
    }

    return null;
}

/**
 * Returns a shell command (including the PHP binary if needed) to run composer.
 */
function matomo_downgrade_find_composer(string $rootDir, string $phpBinary): ?string
{
    $composerBinary = getenv('COMPOSER_BINARY');

    foreach ([$composerBinary, $rootDir . '/composer.phar'] as $candidate) {
        if (is_string($candidate) && $candidate !== '' && file_exists($candidate)) {
            return escapeshellarg($phpBinary) . ' ' . escapeshellarg($candidate);
        }
    }

    $exitCode = 1;
    exec('composer --version 2>/dev/null', $output, $exitCode);

    return $exitCode === 0 ? 'composer' : null;
}

function matomo_downgrade_run(string $command, string $errorMessage): void
{
    matomo_downgrade_log('Running: ' . $command);

    passthru($command, $exitCode);

    if ($exitCode !== 0) {
        matomo_downgrade_fail($errorMessage . ' (exit code ' . $exitCode . ')');
    }
}

chdir($rootDir);

try {
    $packages = matomo_compute_downgrade_packages($rootDir);
} catch (Exception $e) {
    matomo_downgrade_fail($e->getMessage());
    return;
}

$stampFile = $rootDir . MATOMO_DOWNGRADE_STAMP_FILE;
$hash = matomo_downgrade_stamp_hash($rootDir, $packages);
$stamp = file_exists($stampFile) ? json_decode((string) file_get_contents($stampFile), true) : null;

if (is_array($stamp) && isset($stamp['hash']) && $stamp['hash'] === $hash) {
    matomo_downgrade_log('Vendor code is already downgraded to PHP 7.2, nothing to do.');
    exit(0);
}

$packageList = [];

foreach ($packages as $name => $package) {
    $packageList[$name] = $package['version'] . ' (php: ' . $package['php'] . ')';
}

$writeStamp = function () use ($stampFile, $hash, $packageList): void {
    file_put_contents($stampFile, json_encode(
        ['hash' => $hash, 'downgraded-packages' => $packageList],
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    ) . "\n");
};

if (empty($packages)) {
    matomo_downgrade_log('All installed packages support PHP 7.2, no downgrade needed.');
    $writeStamp();
    exit(0);
}

matomo_downgrade_log('Packages requiring a PHP 7.2 downgrade:');

foreach ($packageList as $name => $details) {
    matomo_downgrade_log('  - ' . $name . ' ' . $details);
}

if (getenv('MATOMO_SKIP_VENDOR_DOWNGRADE')) {
    matomo_downgrade_log(
        'WARNING: MATOMO_SKIP_VENDOR_DOWNGRADE is set, skipping the downgrade. '
        . 'The packages listed above may not run on PHP 7.2!'
    );
    exit(0);
}

$phpBinary = matomo_downgrade_find_php();

if ($phpBinary === null) {
    matomo_downgrade_fail(
        'The packages listed above require a PHP 7.2 downgrade, but Rector needs a PHP >= 7.4 CLI binary '
        . 'and none was found on this system. Install PHP >= 7.4 (it can coexist with PHP 7.2), '
        . 'or set MATOMO_SKIP_VENDOR_DOWNGRADE=1 to skip the downgrade at your own risk.'
    );
}

$rectorBinary = $rootDir . '/tools/rector/vendor/bin/rector';

if (!file_exists($rectorBinary)) {
    $composer = matomo_downgrade_find_composer($rootDir, $phpBinary);

    if ($composer === null) {
        matomo_downgrade_fail('Could not find a composer binary to install tools/rector.');
    }

    matomo_downgrade_run(
        $composer . ' install -d ' . escapeshellarg($rootDir . '/tools/rector')
            . ' --no-interaction --no-progress --ignore-platform-reqs 2>&1',
        'Failed to install Rector (tools/rector)'
    );
}

$pathArgs = [];

foreach ($packages as $package) {
    $pathArgs[] = escapeshellarg($package['path']);
}

$enums = matomo_downgrade_find_enums($packages);

if (!empty($enums)) {
    matomo_downgrade_log('Detected enums to downgrade: ' . implode(', ', $enums));
}

// top-level test and doc directories of each package are skipped by rector
// (precise paths instead of glob patterns, which would also match real code
// directories like twig's src/Node/Expression/Test)
$skipPaths = [];

foreach ($packages as $package) {
    foreach (['tests', 'Tests', 'test', 'Test', 'doc', 'docs', 'examples'] as $directory) {
        if (is_dir($package['path'] . '/' . $directory)) {
            $skipPaths[] = $package['path'] . '/' . $directory;
        }
    }
}

// the downgrade runs in multiple passes (see rector-downgrade.php): enums and promoted
// constructor properties must already be downgraded on disk before the PHP 8.0 named
// argument downgrade can resolve parameter default values across files
foreach (['php80', 'php72-prep', 'php72', 'variance'] as $downgradeSet) {
    matomo_downgrade_run(
        'MATOMO_DOWNGRADE_SET=' . $downgradeSet . ' '
            . 'MATOMO_DOWNGRADE_ENUMS=' . escapeshellarg(implode(',', $enums)) . ' '
            . 'MATOMO_DOWNGRADE_SKIP=' . escapeshellarg(implode(',', $skipPaths)) . ' '
            . escapeshellarg($phpBinary) . ' ' . escapeshellarg($rectorBinary)
            . ' process ' . implode(' ', $pathArgs)
            . ' --config=' . escapeshellarg($rootDir . '/rector-downgrade.php')
            . ' --no-diffs 2>&1',
        'Rector failed to downgrade the vendor code (set: ' . $downgradeSet . ')'
    );
}

$writeStamp();

matomo_downgrade_log('Successfully downgraded ' . count($packages) . ' package(s) to PHP 7.2.');
