#!/bin/bash
# Runs the PHP test suites on SQLite in CI: the setup-script of matomo-org/github-action-tests, with
# mysql-service false. Points [database] and [database_tests] of the generated config at SQLite files.
set -euo pipefail

php -m | grep -qx pdo_sqlite || { echo "pdo_sqlite is not installed" >&2; exit 1; }
mkdir -p tmp/sqlite-tests

php -r '
$file = "config/config.ini.php";
$lines = file($file);
$section = null;
$values = [
    "database" => ["dbname" => "tmp/sqlite-tests/matomo_main.db"],
    "database_tests" => ["dbname" => "matomo_tests"],
];
foreach ($lines as $i => $line) {
    if (preg_match("/^\[(.+)\]/", $line, $m)) {
        $section = $m[1];
        continue;
    }
    if (isset($values[$section]) && preg_match("/^(host|dbname|adapter|schema)\s*=/", $line, $m)) {
        $value = [
            "host" => "",
            "dbname" => $values[$section]["dbname"],
            "adapter" => "PDO\\SQLITE",
            "schema" => "Sqlite",
        ][$m[1]];
        $lines[$i] = $m[1] . " = \"" . $value . "\"\n";
    }
}
file_put_contents($file, implode("", $lines));

// the main database must exist in WAL mode before the test framework connects
$pdo = new PDO("sqlite:tmp/sqlite-tests/matomo_main.db");
if (strtolower($pdo->query("PRAGMA journal_mode = WAL")->fetchColumn()) !== "wal") {
    fwrite(STDERR, "SQLite: WAL mode is not available\n");
    exit(1);
}
echo "SQLite ", $pdo->query("SELECT sqlite_version()")->fetchColumn(), "\n";
'
grep -A8 '^\[database' config/config.ini.php | grep -E '^\[|^(host|dbname|adapter|schema)'
