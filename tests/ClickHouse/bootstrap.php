<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/*
 * Minimal bootstrap for the isolated ClickHouse test suite (phpunit-clickhouse.xml).
 *
 * Deliberately does NOT load tests/PHPUnit/bootstrap.php: ClickHouse only ever backs
 * the log_* tables, so these tests must not boot the full Matomo test environment or
 * require a MySQL connection. Tests that need ClickHouse opt in by living in this
 * directory; nothing here is picked up by the standard suites in
 * tests/PHPUnit/phpunit.xml.dist.
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/ClickHouseTestConnection.php';
