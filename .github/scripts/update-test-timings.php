<?php

/**
 * Regenerates .github/test-timings.json, which list-bucket-tests.php uses to balance the CI buckets,
 * from the BUCKET-TIMING lines of a finished "Matomo Tests" run. Each line must be prefixed with its
 * job's name and a tab. A full run's log is too large for `gh run view --log`, so fetch each job's:
 *
 *   gh run view <run-id> --repo matomo-org/matomo --json jobs \
 *       --jq '.jobs[] | select(.name | test("bucket")) | "\(.databaseId)\t\(.name)"' |
 *     while IFS=$'\t' read -r id name; do
 *       gh api --allow-escape-sequences "repos/matomo-org/matomo/actions/jobs/$id/logs" | grep -F BUCKET-TIMING | sed "s#^#$name\t#"
 *     done | php .github/scripts/update-test-timings.php
 *
 * Each file's time is averaged across the matrix jobs that ran it. A file absent from the log, eg
 * because its job was cancelled, keeps its current timing.
 */

declare(strict_types=1);

$path = dirname(__DIR__) . '/test-timings.json';
$timings = is_file($path) ? json_decode((string) file_get_contents($path), true) : [];
if (!is_array($timings)) {
    fwrite(STDERR, "update-test-timings: $path is not valid JSON\n");
    exit(1);
}

$samples = [];
while (($line = fgets(STDIN)) !== false) {
    if (preg_match('/^((?:[^\t]* \/ )?PHP \((\w+), bucket [^\t]*)\t.*BUCKET-TIMING\t([\d.]+)\t\d+\t(\S+)/', $line, $m)) {
        // summed per job, as a file holding several test classes prints one line for each
        $samples[$m[2]][$m[4]][$m[1]] = ($samples[$m[2]][$m[4]][$m[1]] ?? 0.0) + (float) $m[3];
    }
}
if (!$samples) {
    fwrite(STDERR, "update-test-timings: no BUCKET-TIMING lines on stdin\n");
    exit(1);
}

foreach ($samples as $suite => $files) {
    $timings[$suite] = $timings[$suite] ?? [];
    foreach ($files as $file => $secs) {
        $timings[$suite][$file] = round(array_sum($secs) / count($secs), 1);
    }
    ksort($timings[$suite], SORT_STRING);
    fwrite(STDERR, sprintf("update-test-timings: %s, %d files\n", $suite, count($files)));
}
ksort($timings, SORT_STRING);

if (file_put_contents($path, json_encode($timings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n") === false) {
    fwrite(STDERR, "update-test-timings: cannot write $path\n");
    exit(1);
}
