<?php

// Prints one "BUCKET-TIMING <seconds> <tests> <file>" line per test class in a phpunit JUnit log.

$path = $argv[1] ?? '';
if (!is_file($path)) {
    fwrite(STDERR, "no JUnit log at $path\n");
    exit(0);
}
$root = rtrim($argv[2] ?? '', '/') . '/';
$xml = simplexml_load_file($path);
foreach ($xml->xpath('//testsuite[@file and not(contains(@name, "::"))]') as $suite) {
    $file = (string) $suite['file'];
    if (str_starts_with($file, $root)) {
        $file = substr($file, strlen($root));
    }
    printf("BUCKET-TIMING\t%.2f\t%d\t%s\n", (float) $suite['time'], (int) $suite['tests'], $file);
}
