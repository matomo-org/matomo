<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\ClickHouse;

use ClickHouseDB\Client;
use Piwik\Config;

/**
 * ClickHouse POC plumbing (DEV-20678): connection factory for the ClickHouse copy of the
 * raw log tables, kept in sync from MySQL by the Altinity sink connector
 * (.ddev/clickhouse-sink/config.yml). Configured via the [ClickHouse] ini section.
 *
 * Not production code — it exists so POC code paths (and the ClickHouseStatus plugin's
 * UI connection test) can talk to ClickHouse through one place.
 */
class ClickHouse
{
    public static function getClient(): Client
    {
        $config = self::getConfig();

        $client = new Client([
            'host' => $config['host'] ?? 'clickhouse',
            'port' => (string) ($config['port'] ?? 8123),
            'username' => $config['user'] ?? 'matomo',
            'password' => $config['password'] ?? 'matomo',
        ]);
        $client->database($config['database'] ?? 'db');
        $client->setConnectTimeOut(2);
        $client->setTimeout(60);

        // The synced tables are ReplacingMergeTree: FINAL collapses row versions so reads
        // see current-row state (and drops rows whose latest version is a delete).
        $client->settings()->set('final', 1);

        // Keep wide queries inside a small dev container's memory budget: fewer parallel
        // streams, spill sorts to disk, disk-friendly joins.
        $client->settings()->set('max_threads', 2);
        $client->settings()->set('max_bytes_before_external_sort', 256 * 1024 * 1024);
        $client->settings()->set('join_algorithm', 'grace_hash');

        return $client;
    }

    private static function getConfig(): array
    {
        $section = Config::getInstance()->ClickHouse;
        return is_array($section) ? $section : [];
    }
}
