<?php

/**
 * Writes .github/scripts/bucket-timings.json from the JUnit files of a CI run, for list-bucket-tests.php.
 *
 * Download the run's "junit-<suite>-<bucket>" artifacts into one directory (gh run download <run-id>
 * --pattern 'junit-*' --dir <dir>), then run: php .github/scripts/update-bucket-timings.php <dir>
 */

declare(strict_types=1);

if ($argc < 2 || !is_dir($argv[1])) {
    fwrite(STDERR, "usage: php update-bucket-timings.php <directory with junit-<suite>-<bucket> folders>\n");
    exit(2);
}

$timings = [];
foreach (glob(rtrim($argv[1], '/') . '/junit-*-*/junit-bucket.xml') as $file) {
    if (!preg_match('~junit-(\w+)-\d+/junit-bucket\.xml$~', $file, $match)) {
        continue;
    }
    $suite = $match[1];
    $xml = simplexml_load_file($file);
    if ($xml === false) {
        fwrite(STDERR, "skipping unreadable $file\n");
        continue;
    }
    foreach ($xml->xpath('//testcase[@file]') as $testcase) {
        $path = (string) $testcase['file'];
        if (!preg_match('~/((?:plugins|tests|core)/.+)$~', $path, $relative)) {
            continue;
        }
        $timings[$suite][$relative[1]] = ($timings[$suite][$relative[1]] ?? 0) + (float) $testcase['time'];
    }
}

foreach ($timings as $suite => $files) {
    ksort($files);
    $timings[$suite] = array_map(function ($seconds) {
        return round($seconds, 1);
    }, $files);
}
ksort($timings);

$out = __DIR__ . '/bucket-timings.json';
file_put_contents($out, json_encode($timings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
foreach ($timings as $suite => $files) {
    printf("%s: %d files, %.0f s\n", $suite, count($files), array_sum($files));
}
