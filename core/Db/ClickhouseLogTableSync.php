<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Db;

use ClickHouseDB\Client;
use Piwik\Config;
use Piwik\Db;

/**
 * Copies the raw log tables of the current MySQL database into ClickHouse, using
 * ClickHouse's mysql() table function (the ClickHouse server pulls straight from
 * MySQL, so column types and nullability are mirrored automatically — including
 * columns added by plugins).
 *
 * This is how the test environment gets tracked data into ClickHouse: CI has no CDC
 * connector (ddev uses the Altinity sink instead, see .ddev/clickhouse-sink/). The
 * created tables match the sink connector's shape: ReplacingMergeTree(_version,
 * is_deleted) ordered by the MySQL primary key, binary columns hex-encoded.
 */
class ClickhouseLogTableSync
{
    /**
     * The raw log tables synced into ClickHouse, with the sorting key each copy uses
     * (mirrors the MySQL primary keys, and what the Altinity sink connector
     * auto-creates). log_bot_request only exists when the BotTracking plugin created
     * it; tables missing from MySQL are skipped (and dropped from ClickHouse).
     */
    public const LOG_TABLES = [
        'log_visit' => 'idvisit',
        'log_link_visit_action' => 'idlink_va',
        'log_action' => 'idaction',
        'log_conversion' => '(idvisit, idgoal, buster)',
        'log_conversion_item' => '(idvisit, idorder, idaction_sku)',
        'log_bot_request' => 'idrequest',
    ];

    /**
     * BINARY/VARBINARY columns per log table; stored as lowercase hex strings in
     * ClickHouse (both the CDC pipeline and this sync use that encoding, because raw
     * bytes do not survive the client's JSON transport).
     */
    private const BINARY_COLUMNS = [
        'log_visit' => ['idvisitor', 'config_id', 'location_ip'],
        'log_link_visit_action' => ['idvisitor'],
        'log_action' => [],
        'log_conversion' => ['idvisitor'],
        'log_conversion_item' => ['idvisitor'],
        'log_bot_request' => [],
    ];

    /**
     * Fingerprint of the MySQL log tables at the time this process last knew ClickHouse
     * to be in sync. Avoids one ClickHouse round-trip per query in test mode.
     *
     * @var string|null
     */
    private static $syncedFingerprint = null;

    /**
     * ClickHouse database holding the log table copies. Empty config value means
     * "mirror the MySQL database name" — right for both ddev (db → db, matching the
     * sink connector) and the test environment (matomo_tests → matomo_tests).
     */
    public static function getDatabaseName(): string
    {
        $analyticsConfig = Db::getAnalyticsDatabaseConfig();
        if (!empty($analyticsConfig['dbname'])) {
            return $analyticsConfig['dbname'];
        }

        $dbConfig = Config::getInstance()->database;
        return $dbConfig['dbname'] ?? 'db';
    }

    /**
     * Copies all existing log tables from MySQL into ClickHouse (full drop-and-recreate)
     * and records the source fingerprint for {@see resyncIfLogTablesChangedForTests()}.
     */
    public static function syncLogTablesFromMysql(): void
    {
        $dbConfig = Config::getInstance()->database;
        $analyticsConfig = Db::getAnalyticsDatabaseConfig();

        // Hostname of the MySQL server AS SEEN FROM the ClickHouse server. Defaults to
        // what PHP uses; CI overrides (PHP reaches MySQL on 127.0.0.1, the ClickHouse
        // service container reaches the runner host via host.docker.internal).
        $mysqlHost = getenv('CLICKHOUSE_SYNC_MYSQL_HOST')
            ?: ($analyticsConfig['sync_mysql_host'] ?? '')
            ?: ($dbConfig['host'] ?? '127.0.0.1');
        $mysqlPort = !empty($dbConfig['port']) ? (int) $dbConfig['port'] : 3306;
        $mysqlDb = $dbConfig['dbname'];
        $mysqlUser = $dbConfig['username'];
        $mysqlPassword = (string) ($dbConfig['password'] ?? '');
        $prefix = (string) ($dbConfig['tables_prefix'] ?? '');

        $chDatabase = self::getDatabaseName();

        // Fingerprint of the MySQL source, taken before copying: if anything writes to
        // the log tables mid-sync, the stored fingerprint no longer matches and the next
        // test read re-syncs again — always erring towards freshness.
        [$fingerprint, $existingTables] = self::computeMysqlLogTablesFingerprint();

        $client = self::getClient();
        // The target database may not exist yet; run DDL against the default database.
        $client->database('default');
        $client->write(sprintf('CREATE DATABASE IF NOT EXISTS `%s`', $chDatabase));

        foreach (array_keys(self::LOG_TABLES) as $table) {
            $mysqlTable = $prefix . $table;
            $orderBy = self::LOG_TABLES[$table];

            $client->write(sprintf('DROP TABLE IF EXISTS `%s`.`%s`', $chDatabase, $mysqlTable));
            if (!in_array($mysqlTable, $existingTables, true)) {
                continue;
            }

            $selectColumns = '*';
            if (!empty(self::BINARY_COLUMNS[$table])) {
                $replacements = [];
                foreach (self::BINARY_COLUMNS[$table] as $binaryColumn) {
                    $replacements[] = sprintf('lower(hex(`%1$s`)) AS `%1$s`', $binaryColumn);
                }
                $selectColumns = '* REPLACE (' . implode(', ', $replacements) . ')';
            }

            $client->write(sprintf(
                "CREATE TABLE `%s`.`%s`
                 ENGINE = ReplacingMergeTree(_version, is_deleted)
                 ORDER BY %s
                 AS SELECT %s, toUInt64(0) AS _version, toUInt8(0) AS is_deleted
                 FROM mysql('%s:%d', '%s', '%s', '%s', '%s')",
                $chDatabase,
                $mysqlTable,
                $orderBy,
                $selectColumns,
                addslashes($mysqlHost),
                $mysqlPort,
                addslashes($mysqlDb),
                addslashes($mysqlTable),
                addslashes($mysqlUser),
                addslashes($mysqlPassword)
            ));
        }

        $client->write(sprintf('DROP TABLE IF EXISTS `%s`.`sync_state`', $chDatabase));
        $client->write(sprintf(
            "CREATE TABLE `%s`.`sync_state` ENGINE = TinyLog AS SELECT '%s' AS fingerprint",
            $chDatabase,
            addslashes($fingerprint)
        ));

        self::$syncedFingerprint = $fingerprint;
    }

    /**
     * Test-mode freshness: tests mutate the log tables mid-run (tracking new visits,
     * GDPR deletions, anonymisation) and alter their schema (plugins adding columns),
     * which a one-shot fixture sync cannot see. Before serving a query, compare the
     * MySQL log tables' fingerprint (row checksums + column schema) with the one stored
     * at sync time and re-copy when they diverge — zero-lag CDC as far as tests can
     * tell. The first ClickHouse query of a fresh process triggers the initial sync
     * the same way.
     */
    public static function resyncIfLogTablesChangedForTests(): void
    {
        [$current] = self::computeMysqlLogTablesFingerprint();

        if ($current === self::$syncedFingerprint) {
            return;
        }

        try {
            $stored = self::getClient()->select(sprintf(
                'SELECT fingerprint FROM `%s`.`sync_state`',
                self::getDatabaseName()
            ))->fetchOne('fingerprint');
        } catch (\Exception $e) {
            $stored = null;
        }

        if ($stored === $current) {
            self::$syncedFingerprint = $current;
            return;
        }

        error_log('ClickHouse test resync: log tables changed since last sync, re-copying from MySQL');
        self::syncLogTablesFromMysql();
    }

    /**
     * Fingerprint of the MySQL log tables: their column schema (name/type/nullability —
     * catches ALTER-only changes such as CustomDimensions adding columns, which row
     * checksums cannot see) plus a CHECKSUM TABLE over every existing log table.
     *
     * @return array{0: string, 1: string[]} [fingerprint, prefixed names of the log tables that exist]
     */
    private static function computeMysqlLogTablesFingerprint(): array
    {
        $prefix = (string) (Config::getInstance()->database['tables_prefix'] ?? '');

        $prefixedNames = [];
        foreach (array_keys(self::LOG_TABLES) as $table) {
            $prefixedNames[] = $prefix . $table;
        }

        $placeholders = implode(', ', array_fill(0, count($prefixedNames), '?'));
        $columns = Db::fetchAll(
            'SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN (' . $placeholders . ')
             ORDER BY TABLE_NAME, ORDINAL_POSITION',
            $prefixedNames
        );

        $existingTables = [];
        $schemaParts = [];
        foreach ($columns as $column) {
            $tableName = $column['TABLE_NAME'] ?? '';
            if (!in_array($tableName, $existingTables, true)) {
                $existingTables[] = $tableName;
            }
            $schemaParts[] = implode(':', [
                $tableName,
                $column['COLUMN_NAME'] ?? '',
                $column['COLUMN_TYPE'] ?? '',
                $column['IS_NULLABLE'] ?? '',
            ]);
        }

        $parts = ['schema:' . md5(implode('|', $schemaParts))];

        if (!empty($existingTables)) {
            $quoted = array_map(function ($table) {
                return '`' . $table . '`';
            }, $existingTables);
            foreach (Db::fetchAll('CHECKSUM TABLE ' . implode(', ', $quoted)) as $row) {
                $parts[] = ($row['Table'] ?? '') . ':' . ($row['Checksum'] ?? '');
            }
        }

        return [implode('|', $parts), $existingTables];
    }

    private static function getClient(): Client
    {
        $config = Db::getAnalyticsDatabaseConfig();
        if (empty($config['host'])) {
            throw new \Exception(
                'Cannot sync log tables to ClickHouse: no analytics database configured '
                . '([database_analytics] host / CLICKHOUSE_HOST)'
            );
        }

        $client = new Client([
            'host' => (string) $config['host'],
            'port' => (string) ($config['port'] ?? 8123),
            'username' => (string) ($config['username'] ?? 'default'),
            'password' => (string) ($config['password'] ?? ''),
        ]);
        $client->database(self::getDatabaseName());
        $client->setConnectTimeOut(2);
        // Full-table copies of large fixtures can take a while.
        $client->setTimeout(300);

        return $client;
    }
}
