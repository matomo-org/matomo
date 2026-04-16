<?php

/**
 * Matomo - free/libre analytics platform
 *
 * Memory benchmark: columnar packed storage vs row-object storage.
 *
 * Run from the Matomo root:
 *   php benchmarks/datatable_memory.php
 *
 * The script measures peak RSS memory for a DataTable populated with N rows
 * of 10 columns each, both for this branch (columnar) and for the baseline
 * row-object approach using plain Row objects stored in $rows[] directly.
 */

define('PIWIK_DOCUMENT_ROOT', dirname(__DIR__));
define('PIWIK_INCLUDE_PATH', PIWIK_DOCUMENT_ROOT);
define('PIWIK_USER_PATH', PIWIK_DOCUMENT_ROOT);

require_once PIWIK_DOCUMENT_ROOT . '/vendor/autoload.php';

use Piwik\DataTable;
use Piwik\DataTable\Row;

$rowCounts = [10000, 50000, 100000];

$columns10 = [
    'label'              => 'example/page',
    'nb_visits'          => 1234,
    'nb_uniq_visitors'   => 987,
    'nb_actions'         => 4321,
    'nb_users'           => 500,
    'bounce_rate'        => 42.5,
    'avg_time_on_page'   => 67,
    'exit_rate'          => 18.3,
    'entry_nb_visits'    => 300,
    'sum_visit_length'   => 82000,
];

echo str_pad('Rows', 10) . str_pad('Columnar (MB)', 18) . str_pad('Row-object (MB)', 18) . "Savings\n";
echo str_repeat('-', 55) . "\n";

foreach ($rowCounts as $n) {
    // ── Columnar branch ──────────────────────────────────────────────────────
    gc_collect_cycles();
    $baselineMemory = memory_get_usage(true);

    $columnar = new DataTable();
    for ($i = 0; $i < $n; $i++) {
        $cols = $columns10;
        $cols['label'] = 'page/' . $i;
        $columnar->addRow(new Row([Row::COLUMNS => $cols]));
    }
    $columnarPeak = memory_get_usage(true) - $baselineMemory;
    unset($columnar);
    gc_collect_cycles();

    // ── Baseline: plain associative arrays inside Row (simulating old storage)
    // We allocate Row objects and store them the old way in a plain array so
    // the measurement is independent of the DataTable implementation.
    gc_collect_cycles();
    $baselineMemory = memory_get_usage(true);

    $oldRows = [];
    for ($i = 0; $i < $n; $i++) {
        $cols = $columns10;
        $cols['label'] = 'page/' . $i;
        // Each Row is an ArrayObject — matches the old per-row key storage.
        $oldRows[] = new Row([Row::COLUMNS => $cols]);
    }
    $rowObjectPeak = memory_get_usage(true) - $baselineMemory;
    unset($oldRows);
    gc_collect_cycles();

    $columnarMB   = round($columnarPeak   / 1024 / 1024, 2);
    $rowObjectMB  = round($rowObjectPeak  / 1024 / 1024, 2);
    $savings      = $rowObjectPeak > 0
        ? round((1 - $columnarPeak / $rowObjectPeak) * 100, 1) . '%'
        : 'n/a';

    echo str_pad(number_format($n), 10)
        . str_pad($columnarMB,  18)
        . str_pad($rowObjectMB, 18)
        . $savings . "\n";
}

echo "\nNote: 'Columnar' measures DataTable packed storage.\n";
echo "      'Row-object' measures an equivalent number of plain Row objects\n";
echo "      (ArrayObject with string-keyed column data) in a PHP array.\n";
echo "      Results vary by PHP version and GC state.\n";
