<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\ClickHouse;

use PHPUnit\Framework\TestCase;

/**
 * Smoke test pinning the "ClickHouse plumbing works in CI" claim (DEV-20678):
 * create a table, insert one row, assert the SELECT returns it, all through the
 * smi2/phpclickhouse HTTP client.
 *
 * @group ClickHouse
 */
class SmokeTest extends TestCase
{
    public function testCreateInsertSelectRoundTrip(): void
    {
        $client = ClickHouseTestConnection::create();

        self::assertTrue($client->ping(), 'ClickHouse server is not reachable');

        $client->write('DROP TABLE IF EXISTS ci_smoke');
        $client->write('CREATE TABLE ci_smoke (id UInt32, v String) ENGINE = MergeTree ORDER BY id');
        $client->write("INSERT INTO ci_smoke VALUES (1, 'ok')");

        self::assertSame('ok', $client->select('SELECT v FROM ci_smoke WHERE id = 1')->fetchOne('v'));
    }
}
