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
     * The raw log tables synced into ClickHouse, with the MySQL primary key of each.
     * The primary key is what makes a row unique, so it is also what ReplacingMergeTree
     * must deduplicate on: every column here appears in the table's {@see SORTING_KEYS}
     * entry. log_bot_request only exists when the BotTracking plugin created it; tables
     * missing from MySQL are skipped (and dropped from ClickHouse).
     */
    public const LOG_TABLES = [
        'log_visit' => ['idvisit'],
        'log_link_visit_action' => ['idlink_va'],
        'log_action' => ['idaction'],
        'log_conversion' => ['idvisit', 'idgoal', 'buster'],
        'log_conversion_item' => ['idvisit', 'idorder', 'idaction_sku'],
        'log_bot_request' => ['idrequest'],
    ];

    /**
     * Sorting key of each copy, as [required column => the term it contributes].
     *
     * Reads filter on idsite and a datetime range - that is what
     * LogAggregator::getWhereStatement() emits for every archiving query, and what
     * Live\Model uses for the visits log - so those lead, and the primary key follows
     * to keep deduplication exact. Sorting by the MySQL primary key alone (which is
     * what the CDC connectors create by default) leaves no read path filtering on the
     * sorting key at all, so the sparse index cannot skip any granule.
     *
     * Every column named here must be immutable for the lifetime of its row.
     * ReplacingMergeTree deduplicates on the whole sorting key, so a row whose sorting
     * key changes between versions sorts to a different key and FINAL returns both
     * copies - double counting, not merely a slowdown. That rules out
     * log_visit.visit_last_action_time (rewritten on every action) and
     * log_conversion.server_time (rewritten on abandoned-cart updates, see
     * Tracker\GoalManager::recordEcommerceGoal()); both get a skipping index on the
     * datetime instead, see {@see SKIP_INDICES}.
     *
     * Keyed by the column each term needs, so old-schema fixtures can drop the terms
     * whose columns do not exist yet.
     */
    private const SORTING_KEYS = [
        'log_visit' => [
            'idsite' => '`idsite`',
            'visit_first_action_time' => 'toDate(`visit_first_action_time`)',
            'idvisit' => '`idvisit`',
        ],
        'log_link_visit_action' => [
            'idsite' => '`idsite`',
            'server_time' => 'toDate(`server_time`)',
            // Ahead of the primary key because the visits log and Transitions read
            // actions by visit, not by idlink_va.
            'idvisit' => '`idvisit`',
            'idlink_va' => '`idlink_va`',
        ],
        // Read by idaction (the name lookup every Actions report joins on), which is
        // already the primary key.
        'log_action' => [
            'idaction' => '`idaction`',
        ],
        'log_conversion' => [
            'idsite' => '`idsite`',
            'idvisit' => '`idvisit`',
            'idgoal' => '`idgoal`',
            'buster' => '`buster`',
        ],
        'log_conversion_item' => [
            'idsite' => '`idsite`',
            'idvisit' => '`idvisit`',
            'idorder' => '`idorder`',
            'idaction_sku' => '`idaction_sku`',
        ],
        'log_bot_request' => [
            'idsite' => '`idsite`',
            'server_time' => 'toDate(`server_time`)',
            'idrequest' => '`idrequest`',
        ],
    ];

    /**
     * Partition key of each copy: the month of the table's immutable datetime.
     * Partitioning is what turns raw-log retention into a DROP PARTITION rather than a
     * row-wise delete, and two years of retention is 24 partitions - well inside
     * ClickHouse's guidance of a few hundred.
     *
     * Tables absent here have no immutable datetime to partition by. Merges only
     * collapse row versions within a partition, so a mutable partition key would strand
     * versions in different partitions where no merge ever reaches them:
     * log_conversion and log_conversion_item rewrite server_time on cart updates, and
     * log_action has no datetime at all.
     */
    private const PARTITION_DATE_COLUMNS = [
        'log_visit' => 'visit_first_action_time',
        'log_link_visit_action' => 'server_time',
        'log_bot_request' => 'server_time',
    ];

    /**
     * Data skipping indices per copy, as [index name => [column, type]].
     *
     * These cover the filters the sorting key cannot. minmax on the mutable datetimes
     * works because rows arrive in time order, so the column still correlates with
     * physical position even though it is not sorted on; the bloom filter serves the
     * visitor profile, which looks a visitor up by id across the whole table.
     */
    private const SKIP_INDICES = [
        'log_visit' => [
            'idx_visit_last_action' => ['visit_last_action_time', 'minmax'],
            'idx_idvisitor' => ['idvisitor', 'bloom_filter'],
        ],
        'log_conversion' => [
            'idx_server_time' => ['server_time', 'minmax'],
        ],
        'log_conversion_item' => [
            'idx_server_time' => ['server_time', 'minmax'],
        ],
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
     * Seconds to reuse the last MySQL fingerprint within one process.
     * {@see resyncIfLogTablesChangedForTests()} explains why this is safe.
     */
    private const FINGERPRINT_CHECK_TTL = 0.25;

    /**
     * When the MySQL fingerprint was last computed in this process, or null if never.
     *
     * @var float|null
     */
    private static $lastFingerprintCheck = null;

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
        try {
            self::copyLogTablesFromMysql();
        } catch (\Exception $e) {
            // The client throws its own exception type here, and these copies run from
            // web requests as well as the fixture, where the bare message ("Connections
            // to mysql failed") gives no hint that it was the log table copy talking.
            throw new \Exception(
                'ClickHouse log table sync failed: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    private static function copyLogTablesFromMysql(): void
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
        [$fingerprint, $tableColumns] = self::computeMysqlLogTablesFingerprint();

        $client = self::getClient();
        // The target database may not exist yet; run DDL against the default database.
        $client->database('default');
        $client->write(sprintf('CREATE DATABASE IF NOT EXISTS `%s`', $chDatabase));

        foreach (array_keys(self::LOG_TABLES) as $table) {
            $mysqlTable = $prefix . $table;

            $client->write(sprintf('DROP TABLE IF EXISTS `%s`.`%s`', $chDatabase, $mysqlTable));
            if (empty($tableColumns[$mysqlTable])) {
                continue;
            }

            // Old-schema fixtures (the CoreUpdater update tests load ancient dumps) may
            // predate some sort-key, partition or binary columns; only reference what
            // exists. Dropping a sorting-key term is safe for deduplication as long as
            // the primary key survives, which it does: those columns are the oldest.
            $availableColumns = $tableColumns[$mysqlTable];
            $sortingKey = array_intersect_key(self::SORTING_KEYS[$table], array_flip($availableColumns));
            $orderBy = empty($sortingKey) ? 'tuple()' : '(' . implode(', ', $sortingKey) . ')';

            $partitionColumn = self::PARTITION_DATE_COLUMNS[$table] ?? null;
            $partitionBy = (null !== $partitionColumn && in_array($partitionColumn, $availableColumns, true))
                ? sprintf('PARTITION BY toYYYYMM(`%s`)', $partitionColumn)
                : '';

            $selectColumns = '*';
            $binaryColumns = array_intersect(self::BINARY_COLUMNS[$table], $availableColumns);
            if (!empty($binaryColumns)) {
                $replacements = [];
                foreach ($binaryColumns as $binaryColumn) {
                    $replacements[] = sprintf('lower(hex(`%1$s`)) AS `%1$s`', $binaryColumn);
                }
                $selectColumns = '* REPLACE (' . implode(', ', $replacements) . ')';
            }

            $client->write(sprintf(
                "CREATE TABLE `%s`.`%s`
                 ENGINE = ReplacingMergeTree(_version, is_deleted)
                 %s
                 ORDER BY %s
                 AS SELECT %s, toUInt64(0) AS _version, toUInt8(0) AS is_deleted
                 FROM mysql('%s:%d', '%s', '%s', '%s', '%s')",
                $chDatabase,
                $mysqlTable,
                $partitionBy,
                $orderBy,
                $selectColumns,
                addslashes($mysqlHost),
                $mysqlPort,
                addslashes($mysqlDb),
                addslashes($mysqlTable),
                addslashes($mysqlUser),
                addslashes($mysqlPassword)
            ));

            self::addSkipIndices($client, $chDatabase, $mysqlTable, $table, $availableColumns);
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
     * Adds the table's data skipping indices. They cannot be declared inline because
     * CREATE TABLE ... AS SELECT takes its column list from the SELECT, so the index has
     * to be added afterwards - and materialised, because the same statement already
     * wrote the data.
     *
     * @param string[] $availableColumns
     */
    private static function addSkipIndices(
        Client $client,
        string $chDatabase,
        string $mysqlTable,
        string $table,
        array $availableColumns
    ): void {
        foreach (self::SKIP_INDICES[$table] ?? [] as $indexName => [$indexColumn, $indexType]) {
            if (!in_array($indexColumn, $availableColumns, true)) {
                continue;
            }

            $client->write(sprintf(
                'ALTER TABLE `%s`.`%s` ADD INDEX `%s` `%s` TYPE %s GRANULARITY 4',
                $chDatabase,
                $mysqlTable,
                $indexName,
                $indexColumn,
                $indexType
            ));
            $client->write(sprintf(
                'ALTER TABLE `%s`.`%s` MATERIALIZE INDEX `%s`',
                $chDatabase,
                $mysqlTable,
                $indexName
            ));
        }
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
        // Fingerprinting MySQL is not free - the information_schema lookup alone costs
        // ~2ms and CHECKSUM TABLE scans every log table - and archiving a single report
        // page issues thousands of queries, so doing it per query dominated the cost of
        // the queries themselves. Only re-fingerprint once per interval within a process.
        //
        // This does not weaken cross-process freshness, which is the case the fixtures
        // rely on: the tests track through separate HTTP requests, and a new process
        // starts with an empty cache and so always fingerprints on its first query. The
        // window only applies to repeated checks inside one process, where the adapter
        // itself never writes to a log table.
        $now = microtime(true);
        if (null !== self::$lastFingerprintCheck && ($now - self::$lastFingerprintCheck) < self::FINGERPRINT_CHECK_TTL) {
            return;
        }
        self::$lastFingerprintCheck = $now;

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
     * @return array{0: string, 1: array<string, string[]>} [fingerprint, column names per existing prefixed log table]
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

        $tableColumns = [];
        $schemaParts = [];
        foreach ($columns as $column) {
            $tableName = $column['TABLE_NAME'] ?? '';
            $tableColumns[$tableName][] = $column['COLUMN_NAME'] ?? '';
            $schemaParts[] = implode(':', [
                $tableName,
                $column['COLUMN_NAME'] ?? '',
                $column['COLUMN_TYPE'] ?? '',
                $column['IS_NULLABLE'] ?? '',
            ]);
        }

        $parts = ['schema:' . md5(implode('|', $schemaParts))];

        if (!empty($tableColumns)) {
            $quoted = array_map(function ($table) {
                return '`' . $table . '`';
            }, array_keys($tableColumns));
            foreach (Db::fetchAll('CHECKSUM TABLE ' . implode(', ', $quoted)) as $row) {
                $parts[] = ($row['Table'] ?? '') . ':' . ($row['Checksum'] ?? '');
            }
        }

        return [implode('|', $parts), $tableColumns];
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
