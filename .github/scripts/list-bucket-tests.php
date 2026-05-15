<?php
/**
 * Naive round-robin test bucketing for Matomo's PHPUnit suites.
 *
 * Reads tests/PHPUnit/phpunit.xml.dist, expands the requested testsuite's
 * <directory> globs against the live filesystem (so newly added test files
 * are picked up automatically), removes anything matched by the suite's
 * <exclude> rules, sorts alphabetically for a stable bucketing, then keeps
 * files where index % bucket-count == bucket-index.
 *
 * Two output modes:
 *   --list                 print the bucket's file paths to stdout
 *   --write-xml=<path>     write a self-contained phpunit XML mirroring
 *                          phpunit.xml.dist with a single
 *                          <testsuite name="Bucket"> listing this bucket's
 *                          files as <file> entries
 *
 * Supported suites: IntegrationTestsCore, IntegrationTestsPlugins,
 * SystemTestsPlugins. The script rejects others so a typo in the workflow
 * fails loudly.
 */

declare(strict_types=1);

const SUPPORTED_SUITES = [
    'IntegrationTestsCore',
    'IntegrationTestsPlugins',
    'SystemTestsPlugins',
];

function fail(string $msg, int $code = 2): void
{
    fwrite(STDERR, "list-bucket-tests: $msg\n");
    exit($code);
}

if ($argc < 5) {
    fail(
        "usage: php list-bucket-tests.php <suite> <bucket-index> <bucket-count> "
        . "--list | --write-xml=<path>"
    );
}

[$suite, $bucketIndex, $bucketCount, $mode] = [$argv[1], (int) $argv[2], (int) $argv[3], $argv[4]];

if (!in_array($suite, SUPPORTED_SUITES, true)) {
    fail("suite '$suite' not supported. Allowed: " . implode(', ', SUPPORTED_SUITES));
}
if ($bucketCount < 1) {
    fail("bucket-count must be >= 1");
}
if ($bucketIndex < 0 || $bucketIndex >= $bucketCount) {
    fail("bucket-index must be in [0, bucket-count-1]");
}

$repoRoot      = realpath(__DIR__ . '/../..');
$phpunitDir    = $repoRoot . '/tests/PHPUnit';
$phpunitConfig = $phpunitDir . '/phpunit.xml.dist';

if (!is_file($phpunitConfig)) {
    fail("cannot find $phpunitConfig");
}

$xml = simplexml_load_file($phpunitConfig);
if ($xml === false) {
    fail("failed to parse $phpunitConfig");
}

$target = null;
foreach ($xml->testsuites->testsuite as $ts) {
    if ((string) $ts['name'] === $suite) {
        $target = $ts;
        break;
    }
}
if ($target === null) {
    fail("suite '$suite' not declared in phpunit.xml.dist");
}

$files = collect_files($phpunitDir, $target);
sort($files, SORT_STRING);

$bucket = [];
foreach ($files as $i => $f) {
    if ($i % $bucketCount === $bucketIndex) {
        $bucket[] = $f;
    }
}

if ($mode === '--list') {
    foreach ($bucket as $f) {
        echo $f . "\n";
    }
    exit(0);
}

if (strncmp($mode, '--write-xml=', 12) === 0) {
    $outPath = substr($mode, 12);
    if ($outPath === '') {
        fail("--write-xml requires a path");
    }
    write_bucket_xml($phpunitConfig, $outPath, $bucket);
    fwrite(
        STDERR,
        sprintf(
            "list-bucket-tests: wrote %d files (suite=%s, bucket=%d/%d) to %s\n",
            count($bucket),
            $suite,
            $bucketIndex,
            $bucketCount,
            $outPath
        )
    );
    exit(0);
}

fail("unknown mode '$mode'");

/**
 * Walks the suite's <directory> entries and gathers every *Test.php file
 * underneath, minus anything covered by an <exclude>.
 */
function collect_files(string $phpunitDir, SimpleXMLElement $suite): array
{
    $found = [];
    foreach ($suite->directory as $dir) {
        $pattern = trim((string) $dir);
        foreach (glob_dirs($phpunitDir, $pattern) as $rootDir) {
            foreach (recursive_test_files($rootDir) as $file) {
                $found[$file] = true;
            }
        }
    }

    foreach ($suite->exclude as $excl) {
        $pattern = trim((string) $excl);
        foreach (glob_dirs($phpunitDir, $pattern) as $excludeDir) {
            $excludeReal = realpath($excludeDir);
            if ($excludeReal === false) {
                continue;
            }
            $prefix = rtrim($excludeReal, '/') . '/';
            foreach (array_keys($found) as $f) {
                if (strncmp($f, $prefix, strlen($prefix)) === 0) {
                    unset($found[$f]);
                }
            }
        }
    }

    return array_keys($found);
}

/**
 * Resolves a phpunit.xml-style directory pattern (relative to tests/PHPUnit/)
 * to absolute directory paths. PHP's glob() handles `*` in middle path
 * segments via libc, so plugins/* /tests/Integration expands directly.
 */
function glob_dirs(string $baseDir, string $relativePattern): array
{
    $absolute = $baseDir . '/' . $relativePattern;
    $matches  = glob($absolute, GLOB_ONLYDIR | GLOB_NOSORT);
    if ($matches === false) {
        return [];
    }
    $out = [];
    foreach ($matches as $m) {
        $real = realpath($m);
        if ($real !== false) {
            $out[] = $real;
        }
    }
    return $out;
}

function recursive_test_files(string $rootDir): array
{
    if (!is_dir($rootDir)) {
        return [];
    }
    $out = [];
    $it  = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($rootDir, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($it as $info) {
        /** @var SplFileInfo $info */
        if ($info->isFile() && substr($info->getFilename(), -8) === 'Test.php') {
            $out[] = $info->getPathname();
        }
    }
    return $out;
}

/**
 * Emits a phpunit config cloned from phpunit.xml.dist with the entire
 * <testsuites> block replaced by a single <testsuite name="Bucket"> whose
 * <file> children are this bucket's files (relative to the new XML's dir,
 * so PHPUnit resolves them the same way it would resolve <directory>).
 *
 * Keeping the rest of the config verbatim (bootstrap, attributes, <filter>)
 * means the bucket run behaves identically to the unsplit run for everything
 * other than test selection.
 */
function write_bucket_xml(string $sourceConfig, string $outPath, array $files): void
{
    $dom                     = new DOMDocument('1.0', 'UTF-8');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput       = true;
    if (!$dom->load($sourceConfig)) {
        fail("failed to load $sourceConfig for cloning");
    }

    $testsuites = $dom->getElementsByTagName('testsuites')->item(0);
    if ($testsuites === null) {
        fail("source phpunit config has no <testsuites>");
    }
    while ($testsuites->firstChild) {
        $testsuites->removeChild($testsuites->firstChild);
    }

    $bucketSuite = $dom->createElement('testsuite');
    $bucketSuite->setAttribute('name', 'Bucket');
    $testsuites->appendChild($bucketSuite);

    $outDirReal = resolve_dir_for_output($outPath);
    foreach ($files as $absoluteFile) {
        $rel = make_relative($outDirReal, $absoluteFile);
        $bucketSuite->appendChild($dom->createElement('file', $rel));
    }

    if ($dom->save($outPath) === false) {
        fail("failed to write $outPath");
    }
}

function resolve_dir_for_output(string $outPath): string
{
    $dir     = dirname($outPath);
    $dirReal = realpath($dir);
    if ($dirReal !== false) {
        return $dirReal;
    }
    if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
        fail("cannot create output directory $dir");
    }
    $dirReal = realpath($dir);
    if ($dirReal === false) {
        fail("cannot resolve output directory $dir");
    }
    return $dirReal;
}

function make_relative(string $fromDir, string $toPath): string
{
    $fromParts = explode('/', trim($fromDir, '/'));
    $toParts   = explode('/', trim($toPath, '/'));

    $shared = 0;
    foreach ($fromParts as $i => $segment) {
        if (($toParts[$i] ?? null) !== $segment) {
            break;
        }
        $shared++;
    }

    return str_repeat('../', count($fromParts) - $shared)
        . implode('/', array_slice($toParts, $shared));
}
