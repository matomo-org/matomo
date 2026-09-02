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

    /**
     * ClickHouse query settings this adapter will forward when [database_analytics]
     * configures them, and omit entirely when it does not. Empty means "not configured",
     * the same convention every other key in that section uses; omitting the setting
     * leaves the server default in place rather than sending an empty value.
     */
    private const OPTIONAL_QUERY_SETTINGS = [
        'max_threads',
        'max_bytes_before_external_sort',
        'max_bytes_before_external_group_by',
        'join_algorithm',
    ];

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

        $translated = $this->applyAnalyticsTablePrefix($sql);
        $translated = ClickhouseDialectTranslator::translate($translated);
        [$chSql, $params] = self::convertPositionalBinds($translated, $bind);
        // Segment LIKEs were pointed at the lowercased, indexed copy of log_action.name
        // during translation; their needles can only be lowered here, once the binds have
        // names to match them by.
        $params = ClickhouseDialectTranslator::lowercaseNeedlesForIndexedColumns($chSql, $params);
        // Restricting the log_action joins repeats the driving query's WHERE, so it has to run
        // AFTER the binds have names: repeating `?` would desynchronise the positional list,
        // while a named parameter can appear as many times as it likes.
        $chSql = ClickhouseDialectTranslator::restrictLogActionJoins($chSql);

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
        error_log(sprintf(
            'ClickHouse query OK (%d rows, %.1f ms) via %s: %s params=%s',
            count($rows),
            (microtime(true) - $startTime) * 1000,
            $this->describeCallers(),
            substr(preg_replace('/\s+/', ' ', $chSql), 0, 600),
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

    /**
     * Rewrites log table references to the analytics database's own table prefix.
     *
     * Matomo has ONE [database] tables_prefix, but the two engines need not agree: a
     * ClickPipes destination commonly prefixes the copies with the source instance name
     * (mc_anonsite_log_visit) while the MySQL side has no prefix at all. Without this, one
     * engine or the other is always addressing tables that do not exist.
     *
     * Only the table reference after FROM/JOIN is rewritten, never the alias. Matomo always
     * aliases a log table to its own unprefixed name - JoinGenerator emits
     * `prefixTable($t) . " AS $t"` - so turning `FROM log_visit AS log_visit` into
     * `FROM mc_anonsite_log_visit AS log_visit` leaves every log_visit.column reference in
     * the rest of the statement valid, and nothing else has to know. Where a reference
     * carries no alias, one is added for the same reason.
     *
     * Set [database_analytics] tables_prefix to use it; empty means "same as [database]",
     * which is the normal case and costs nothing.
     */
    private function applyAnalyticsTablePrefix(string $sql): string
    {
        // 'analytics_tables_prefix', not 'tables_prefix' - Adapter::factory() unsets the
        // latter before the adapter is constructed. See Db::getAnalyticsDatabaseConfig().
        $target = (string) ($this->config['analytics_tables_prefix'] ?? '');
        $source = (string) ($this->config['source_tables_prefix'] ?? '');

        if ($target === $source) {
            return $sql;
        }

        // Longest first: log_conversion_item must not be matched as log_conversion.
        $tables = 'log_link_visit_action|log_conversion_item|log_conversion|log_bot_request'
                . '|log_visit|log_action';

        // The optional trailing group is the alias Matomo normally supplies. A clause keyword
        // is not an alias, so those are listed out - matching WHERE as an alias would swallow
        // the rest of the statement's meaning.
        $notAnAlias = 'AS|ON|WHERE|GROUP|ORDER|LIMIT|HAVING|UNION|SET|USING|FINAL|USE'
                    . '|INNER|LEFT|RIGHT|FULL|CROSS|STRAIGHT_JOIN|JOIN';

        $pattern = '~\b(FROM|JOIN)(\s+)`?' . preg_quote($source, '~') . '(' . $tables . ')`?(?![\w`])'
                 . '(\s+(?:AS\s+)?`?(?!(?:' . $notAnAlias . ')\b)\w+`?)?~i';

        return preg_replace_callback($pattern, static function (array $m) use ($source, $target): string {
            $rewritten = $m[1] . $m[2] . $target . $m[3];

            // Already aliased (the usual case): keep the alias exactly as written, so every
            // qualified column reference elsewhere in the statement still resolves.
            if (!empty($m[4])) {
                return $rewritten . $m[4];
            }

            // Unaliased: pin the name the rest of the statement expects.
            return $rewritten . ' AS ' . $source . $m[3];
        }, $sql) ?? $sql;
    }

    private function getClient(): Client
    {
        if ($this->client !== null) {
            return $this->client;
        }

        // ClickHouse Cloud is TLS-only on 8443, so the transport has to be selectable.
        // The scheme may be written into the host ("https://svc.clickhouse.cloud", which is
        // how the service hands it to you) or given explicitly as [database_analytics] https;
        // an explicit value wins. Without this the client hands the scheme to curl as a
        // hostname and fails with "Could not resolve host: https".
        $host = trim((string) ($this->config['host'] ?? '127.0.0.1'));
        $https = null;
        if (preg_match('~^(https?)://~i', $host, $scheme)) {
            $https = 0 === strcasecmp($scheme[1], 'https');
            $host = (string) preg_replace('~^https?://~i', '', $host);
        }
        if ('' !== (string) ($this->config['https'] ?? '')) {
            $https = filter_var((string) $this->config['https'], FILTER_VALIDATE_BOOLEAN);
        }

        $client = new Client([
            'host' => rtrim($host, '/'),
            'port' => (string) ($this->config['port'] ?? self::getDefaultPort()),
            'username' => (string) ($this->config['username'] ?? 'default'),
            'password' => (string) ($this->config['password'] ?? ''),
        ] + (null === $https ? [] : ['https' => $https]));
        $client->database((string) ($this->config['dbname'] ?? 'default'));
        $client->setConnectTimeOut(2);
        $client->setTimeout(300);

        // The synced tables are ReplacingMergeTree: FINAL collapses row versions so reads
        // see current-row state (and drops rows whose latest version is a delete).
        $client->settings()->set('final', 1);

        // Matomo stores UTC datetimes; pin the session so a ClickHouse server running in
        // another timezone cannot shift toDate()/toHour() results.
        $client->settings()->set('session_timezone', 'UTC');

        // Sizing and planner settings, every one of them optional and unset by default.
        // These were previously pinned here to values chosen for a 2.55 GiB ddev
        // container - fewer parallel streams and early spilling to disk - which is
        // exactly wrong on production hardware: max_threads = 2 throws away most of the
        // parallel scan that is ClickHouse's advantage, and spilling a sort or an
        // aggregation while memory is still available is pure loss. An empty config
        // value means the setting is not sent at all and the server's own default
        // applies. The constrained environments opt back in explicitly - see
        // [database_analytics] in global.ini.php and the CLICKHOUSE_* variables the ddev
        // and CI jobs export.
        foreach (self::OPTIONAL_QUERY_SETTINGS as $setting) {
            $value = (string) ($this->config[$setting] ?? '');
            if ('' === $value) {
                continue;
            }
            $client->settings()->set($setting, $value);
        }

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
