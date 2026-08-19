<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\ClickHouse;

use ClickHouseDB\Client;

/**
 * Connection helper for the isolated ClickHouse test suite.
 *
 * Defaults match the ddev service (.ddev/docker-compose.clickhouse.yaml); CI overrides
 * the host via env (.github/workflows/clickhouse-smoke.yml).
 */
class ClickHouseTestConnection
{
    public static function create(): Client
    {
        $client = new Client([
            'host' => getenv('CLICKHOUSE_HOST') ?: 'clickhouse',
            'port' => getenv('CLICKHOUSE_PORT') ?: '8123',
            'username' => getenv('CLICKHOUSE_USER') ?: 'matomo',
            'password' => getenv('CLICKHOUSE_PASSWORD') ?: 'matomo',
        ]);
        $client->database(getenv('CLICKHOUSE_DATABASE') ?: 'default');
        $client->setConnectTimeOut(5);
        $client->setTimeout(30);

        return $client;
    }
}
