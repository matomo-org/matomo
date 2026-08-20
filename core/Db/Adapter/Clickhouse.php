<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Db\Adapter;

use ClickHouseDB\Client;
use Exception;
use Piwik\DataAccess\ClickhouseDialectTranslator;
use Piwik\Db\AdapterInterface;
use Piwik\Db\ClickhouseLogTableSync;
use Piwik\Db\TransactionalDatabaseDynamicTrait;

/**
 * ClickHouse adapter for the analytics database connection ({@see \Piwik\Db::getAnalytics()}).
 *
 * Serves READ queries against the ClickHouse copies of the raw log tables. The Tracker
 * write path and all config/archive tables stay on MySQL; ClickHouse is kept in sync
 * externally (Altinity CDC sink in ddev, {@see ClickhouseLogTableSync} in tests, the
 * clickhouse:migrate-log-data command for manual copies).
 *
 * Queries pass through {@see ClickhouseDialectTranslator} on the way in (Matomo builds
 * MySQL-flavoured SQL) and through row post-processing on the way out (hex-encoded
 * binary columns, MySQL-style column names). There is deliberately NO fallback to
 * MySQL anywhere in this class: when ClickHouse is configured, its errors propagate
 * so problems surface immediately.
 *
 * Transactions are not supported by ClickHouse; the corresponding methods are no-ops.
 */
class Clickhouse implements AdapterInterface
{
    use TransactionalDatabaseDynamicTrait;

    /**
     * Columns MySQL stores as BINARY/VARBINARY. The sync pipelines land them in
     * ClickHouse as lowercase hex strings (raw bytes do not survive the JSON
     * transport), while Matomo expects raw bytes, so result rows convert them back.
     */
    private const HEX_ENCODED_BINARY_COLUMNS = ['idvisitor', 'config_id', 'location_ip'];

    /** @var array<string, mixed> */
    private $config;

    /** @var Client|null */
    private $client = null;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    // -------------------------------------------------------------------------
    // AdapterInterface — connection lifecycle
    // -------------------------------------------------------------------------

    /**
     * Verifies connectivity. Called by Adapter::factory() after construction.
     *
     * Pings against the always-present 'default' database: the target analytics
     * database may not exist yet at connect time (in the test environment the log
     * table sync creates it on the first real query).
     */
    public function getConnection(): bool
    {
        $client = $this->getClient();
        $client->database('default');
        try {
            $client->select('SELECT 1')->rows();
        } finally {
            $client->database((string) ($this->config['dbname'] ?? 'default'));
        }
        return true;
    }

    public function resetConfig(): void
    {
        // ClickHouse authenticates on every HTTP request; credentials must be
        // retained for the lifetime of this object.
    }

    public function closeConnection(): void
    {
        $this->client = null;
    }

    // -------------------------------------------------------------------------
    // AdapterInterface — static metadata
    // -------------------------------------------------------------------------

    public static function isEnabled(): bool
    {
        return function_exists('curl_init') && class_exists('\ClickHouseDB\Client');
    }

    public static function getDefaultPort(): int
    {
        return 8123;
    }

    public function hasBlobDataType(): bool
    {
        return false;
    }

    public function hasBulkLoader(): bool
    {
        return false;
    }

    public function checkServerVersion(): void
    {
        $version = (string) $this->fetchOne('SELECT version()');
        // 23.3+: `final = 1` query setting and ReplacingMergeTree is_deleted support,
        // both of which the log table copies rely on.
        if (version_compare($version, '23.3', '<')) {
            throw new Exception(sprintf(
                'ClickHouse version %s is not supported. Minimum required version is 23.3.',
                $version
            ));
        }
    }

    /**
     * ClickHouse errors are descriptive strings; there are no MySQL-style numeric codes.
     */
    public function isErrNo($e, $errno): bool
    {
        return false;
    }

    // -------------------------------------------------------------------------
    // AdapterInterface — query methods
    // -------------------------------------------------------------------------

    /**
     * @param string $sql  MySQL-flavoured SELECT with positional `?` placeholders
     * @param array|string|int $bind A single scalar bind value is accepted like Zend adapters do
     * @return array<int, array<string, mixed>>
     */
    public function fetchAll($sql, $bind = [])
    {
        return $this->selectRows($sql, is_array($bind) ? $bind : [$bind]);
    }

    /**
     * @return array<string, mixed>
     */
    public function fetchRow($sql, $bind = [])
    {
        $rows = $this->fetchAll($sql, $bind);
        return $rows[0] ?? [];
    }

    /**
     * @return mixed The first column of the first row, or false when there is no row.
     */
    public function fetchOne($sql, $bind = [])
    {
        $row = $this->fetchRow($sql, $bind);
        if (empty($row)) {
            return false;
        }
        return reset($row);
    }

    /**
     * @return array<string, array<string, mixed>> Rows indexed by the first column's value.
     */
    public function fetchAssoc($sql, $bind = [])
    {
        $result = [];
        foreach ($this->fetchAll($sql, $bind) as $row) {
            $result[reset($row)] = $row;
        }
        return $result;
    }

    /**
     * Executes a SELECT and returns a statement whose fetch() method returns rows one
     * by one — compatible with the `while ($row = $query->fetch())` loops archiving
     * code uses on Zend statements.
     */
    public function query($sql, $bind = []): ClickhouseStatement
    {
        return new ClickhouseStatement($this->fetchAll($sql, $bind));
    }

    /**
     * Executes a statement that returns no result rows (DDL, INSERT …). The SQL is
     * passed through verbatim — callers of exec() on this adapter write
     * ClickHouse-native SQL.
     */
    public function exec($sql)
    {
        try {
            $this->getClient()->write($sql);
        } catch (\Exception $e) {
            // Same reasoning as selectRows(): without the statement in the message a CI
            // failure shows only the ClickHouse error and no way to tell what ran.
            throw new Exception(sprintf(
                'ClickHouse statement failed: %s | SQL: %s',
                $e->getMessage(),
                substr(preg_replace('/\s+/', ' ', $sql), 0, 2000)
            ), 0, $e);
        }

        return 0;
    }

    // -------------------------------------------------------------------------
    // TransactionalDatabaseInterface — ClickHouse has no transactions
    // -------------------------------------------------------------------------

    public function getCurrentTransactionIsolationLevelForSession(): string
    {
        return 'READ COMMITTED';
    }

    public function setTransactionIsolationLevel(string $level): void
    {
        // no-op
    }

    // -------------------------------------------------------------------------
    // Bind conversion (public so it is unit-testable)
    // -------------------------------------------------------------------------

    /**
     * Rewrites positional `?` placeholders into the smi2 client's named bindings.
     * The scan is quote-aware so `?` inside string literals is left alone. Bind names
     * are fixed-width so no name is a prefix of another during the client's string
     * substitution. Values bound for BINARY columns (idvisitor/config_id/location_ip)
     * are hex-encoded to match how the sync pipelines store them.
     *
     * @param array $bind
     * @return array{0: string, 1: array<string, mixed>} [converted SQL, named params]
     */
    public static function convertPositionalBinds(string $sql, array $bind): array
    {
        $bind = array_values($bind);
        foreach ($bind as $i => $value) {
            // Matomo binds BINARY column values as raw bytes; the ClickHouse copies
            // store them hex-encoded. Raw binary of those widths virtually always
            // contains non-printable bytes.
            if (
                is_string($value)
                && in_array(strlen($value), [4, 8, 16], true)
                && preg_match('/[^\x20-\x7E]/', $value)
            ) {
                $bind[$i] = strtolower(bin2hex($value));
            }
        }

        $converted = '';
        $params = [];
        $paramIndex = 0;
        $inQuote = false;
        $quoteChar = '';
        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];

            if ($inQuote) {
                if ($char === '\\' && $i + 1 < $length) {
                    $converted .= $char . $sql[++$i];
                    continue;
                }
                if ($char === $quoteChar) {
                    $inQuote = false;
                }
                $converted .= $char;
                continue;
            }

            if ($char === "'" || $char === '"' || $char === '`') {
                $inQuote = true;
                $quoteChar = $char;
                $converted .= $char;
                continue;
            }

            if ($char === '?') {
                if (!array_key_exists($paramIndex, $bind)) {
                    throw new Exception('ClickHouse bind conversion failed: more `?` placeholders than bind values');
                }
                $name = sprintf('chBind%03d', $paramIndex);
                $params[$name] = $bind[$paramIndex];
                $converted .= ':' . $name;
                $paramIndex++;
                continue;
            }

            $converted .= $char;
        }

        if ($paramIndex !== count($bind)) {
            throw new Exception(sprintf(
                'ClickHouse bind conversion failed: %d `?` placeholders for %d bind values',
                $paramIndex,
                count($bind)
            ));
        }

        return [$converted, $params];
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * The one SELECT funnel: test-mode freshness check, dialect translation, bind
     * conversion, execution, query logging, row post-processing.
     *
     * @return array<int, array<string, mixed>>
     */
    private function selectRows(string $sql, array $bind, bool $logTableFreshnessCheck = true): array
    {
        if ($logTableFreshnessCheck && defined('PIWIK_TEST_MODE') && PIWIK_TEST_MODE) {
            // Tests mutate the MySQL log tables mid-run; re-copy them before serving
            // stale results. The first ClickHouse query of a test process also runs
            // the initial sync this way.
            ClickhouseLogTableSync::resyncIfLogTablesChangedForTests();
        }

        $translated = ClickhouseDialectTranslator::translate($sql);
        [$chSql, $params] = self::convertPositionalBinds($translated, $bind);

        $startTime = microtime(true);
        try {
            $rows = $this->getClient()->select($chSql, $params)->rows();
        } catch (\Exception $e) {
            // No fallback by design — but make the failed (translated) SQL part of the
            // error so CI failures identify the offending query without reproduction.
            throw new Exception(sprintf(
                "ClickHouse query failed: %s | translated SQL: %s | params: %s",
                $e->getMessage(),
                substr(preg_replace('/\s+/', ' ', $chSql), 0, 2000),
                substr((string) json_encode($params), 0, 500)
            ), 0, $e);
        }

        // error_log, not the Matomo logger (logger messages become on-screen notifications
        // in the UI test environment): proves in CI job output that ClickHouse served the
        // query, including who asked for it.
        // TEMPORARY (DEV-20678 debugging, revert before merge): the 600 character cap hides
        // the WHERE clause of the long Live queries, which is exactly where a query that
        // returns nothing has to be diagnosed. Log the whole statement for the specific
        // shape being chased - an empty ecommerce result - and nothing else.
        $sqlCap = (count($rows) === 0 && stripos($chSql, 'visit_goal_buyer') !== false) ? 20000 : 600;

        error_log(sprintf(
            'ClickHouse query OK (%d rows, %.1f ms) via %s: %s params=%s',
            count($rows),
            (microtime(true) - $startTime) * 1000,
            $this->describeCallers(),
            substr(preg_replace('/\s+/', ' ', $chSql), 0, $sqlCap),
            substr((string) json_encode($params), 0, 300)
        ));

        return $this->postProcessRows($rows);
    }

    /**
     * Reverses the transport encodings so rows look like Db::fetchAll() on MySQL:
     * strips the table qualification ClickHouse adds to ambiguous JOIN columns,
     * decodes hex-encoded binary columns, trims TIME microseconds.
     *
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    private function postProcessRows(array $rows): array
    {
        foreach ($rows as &$row) {
            foreach ($row as $key => $value) {
                $dotPos = strrpos($key, '.');
                if ($dotPos !== false) {
                    $shortKey = substr($key, $dotPos + 1);
                    if (!array_key_exists($shortKey, $row)) {
                        $row[$shortKey] = $value;
                    }
                    unset($row[$key]);
                }
            }

            foreach (self::HEX_ENCODED_BINARY_COLUMNS as $column) {
                if (
                    !empty($row[$column])
                    && is_string($row[$column])
                    && strlen($row[$column]) % 2 === 0
                    && ctype_xdigit($row[$column])
                ) {
                    $row[$column] = hex2bin($row[$column]);
                }
            }

            // MySQL TIME lands as "HH:MM:SS.000000" via the sync pipelines; MySQL returns "HH:MM:SS"
            if (!empty($row['visitor_localtime']) && is_string($row['visitor_localtime'])) {
                $row['visitor_localtime'] = preg_replace('~^(\d{2}:\d{2}:\d{2})\.\d+$~', '$1', $row['visitor_localtime']);
            }
        }

        return $rows;
    }

    private function getClient(): Client
    {
        if ($this->client !== null) {
            return $this->client;
        }

        $client = new Client([
            'host' => (string) ($this->config['host'] ?? '127.0.0.1'),
            'port' => (string) ($this->config['port'] ?? self::getDefaultPort()),
            'username' => (string) ($this->config['username'] ?? 'default'),
            'password' => (string) ($this->config['password'] ?? ''),
        ]);
        $client->database((string) ($this->config['dbname'] ?? 'default'));
        $client->setConnectTimeOut(2);
        $client->setTimeout(300);

        // The synced tables are ReplacingMergeTree: FINAL collapses row versions so reads
        // see current-row state (and drops rows whose latest version is a delete).
        $client->settings()->set('final', 1);

        // Matomo stores UTC datetimes; pin the session so a ClickHouse server running in
        // another timezone cannot shift toDate()/toHour() results.
        $client->settings()->set('session_timezone', 'UTC');

        // Keep wide SELECT-* queries with FINAL inside a small container's memory budget:
        // fewer parallel streams, spill sorts and aggregations to disk instead of failing,
        // and use a disk-friendly join algorithm for the segment joins onto
        // log_link_visit_action. Archiving is aggregation-heavy, so the GROUP BY spill
        // threshold matters as much as the sort one: without it a wide aggregation (the
        // PagePerformance totals over log_link_visit_action, for one) dies with
        // MEMORY_LIMIT_EXCEEDED in AggregatingTransform rather than spilling.
        $client->settings()->set('max_threads', 2);
        $client->settings()->set('max_bytes_before_external_sort', 256 * 1024 * 1024);
        $client->settings()->set('max_bytes_before_external_group_by', 256 * 1024 * 1024);
        $client->settings()->set('join_algorithm', 'grace_hash');

        return $this->client = $client;
    }

    /**
     * Compact call tree (nearest callers first) for the query log line. Frames inside
     * this class are skipped.
     */
    private function describeCallers(): string
    {
        $frames = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 15);
        $parts = [];

        foreach ($frames as $frame) {
            if (
                empty($frame['function'])
                || ($frame['class'] ?? '') === self::class
                || strpos($frame['function'], 'call_user_func') === 0
            ) {
                continue;
            }
            $parts[] = (!empty($frame['class']) ? $frame['class'] . '::' : '') . $frame['function'];
            if (count($parts) >= 5) {
                break;
            }
        }

        return $parts ? implode(' <- ', $parts) : '(unknown caller)';
    }
}
