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
use Piwik\Db;

/**
 * ClickHouse POC plumbing (DEV-20678): runs Matomo-built log SQL against the ClickHouse
 * copy of the raw log tables, kept in sync from MySQL by the Altinity sink connector
 * (.ddev/clickhouse-sink/config.yml). Configured via the [ClickHouse] ini section.
 *
 * Not production code — it exists so the POC branch can route individual reports
 * (currently the Live visits log, see Piwik\Plugins\Live\Model) to ClickHouse behind
 * config, with automatic fallback to MySQL.
 */
class ClickHouse
{
    /**
     * Columns MySQL stores as BINARY/VARBINARY. The CDC pipeline lands them in ClickHouse
     * as lowercase hex strings, while Matomo expects raw bytes (it bin2hex()es them itself),
     * so rows read from ClickHouse convert them back.
     */
    private const HEX_ENCODED_BINARY_COLUMNS = ['idvisitor', 'config_id', 'location_ip'];

    /**
     * The five raw log tables the POC syncs into ClickHouse, with the ClickHouse sorting
     * key each copy uses (mirrors the MySQL primary keys, and what the Altinity sink
     * connector auto-creates).
     */
    private const LOG_TABLES = [
        'log_visit' => 'idvisit',
        'log_link_visit_action' => 'idlink_va',
        'log_action' => 'idaction',
        'log_conversion' => '(idvisit, idgoal, buster)',
        'log_conversion_item' => '(idvisit, idorder, idaction_sku)',
    ];

    /**
     * BINARY/VARBINARY columns per log table; stored as lowercase hex strings in
     * ClickHouse (both the CDC pipeline and syncLogTablesFromMysql() use that encoding,
     * because raw bytes do not survive the client's JSON transport).
     */
    private const BINARY_COLUMNS = [
        'log_visit' => ['idvisitor', 'config_id', 'location_ip'],
        'log_link_visit_action' => ['idvisitor'],
        'log_action' => [],
        'log_conversion' => ['idvisitor'],
        'log_conversion_item' => ['idvisitor'],
    ];

    /**
     * Whether the visits log should be served from ClickHouse ([ClickHouse] ini section).
     */
    public static function isLiveReportsEnabled(): bool
    {
        $config = self::getConfig();
        return !empty($config['live_reports_enabled']);
    }

    /**
     * Whether a failed ClickHouse query falls back to MySQL (default) or propagates.
     * Tests disable the fallback so CI fails loudly instead of silently serving MySQL.
     */
    public static function isFallbackToMysqlEnabled(): bool
    {
        $config = self::getConfig();
        return !array_key_exists('live_reports_fallback', $config) || !empty($config['live_reports_fallback']);
    }

    /**
     * Runs a log_visit SELECT built for MySQL against ClickHouse and returns rows shaped
     * like Db::fetchAll() would return them.
     *
     * @param string $sql MySQL SELECT with positional `?` placeholders
     * @param array $bind
     * @return array
     */
    public static function fetchLogVisits(string $sql, array $bind): array
    {
        if (defined('PIWIK_TEST_MODE') && PIWIK_TEST_MODE) {
            self::resyncIfLogTablesChangedForTests();
        }

        [$chSql, $params] = self::convertQuery($sql, $bind);

        $startTime = microtime(true);
        $rows = self::getClient()->select($chSql, $params)->rows();

        // error_log, not the Matomo logger (logger messages become on-screen notifications
        // in the UI test environment): proves in CI job output that ClickHouse served the
        // query, including who asked for it.
        error_log(sprintf(
            'ClickHouse query OK (%d rows, %.1f ms) via %s: %s params=%s',
            count($rows),
            (microtime(true) - $startTime) * 1000,
            self::describeCallers(),
            substr(preg_replace('/\s+/', ' ', $chSql), 0, 600),
            substr(json_encode($params), 0, 300)
        ));

        foreach ($rows as &$row) {
            // On JOIN queries ClickHouse returns columns whose name exists on both sides
            // under their qualified name ("log_visit.idvisit"); Matomo expects the short
            // MySQL-style names.
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

            // MySQL TIME lands as "HH:MM:SS.000000" via the CDC pipeline; MySQL returns "HH:MM:SS"
            if (!empty($row['visitor_localtime']) && is_string($row['visitor_localtime'])) {
                $row['visitor_localtime'] = preg_replace('~^(\d{2}:\d{2}:\d{2})\.\d+$~', '$1', $row['visitor_localtime']);
            }
        }

        return $rows;
    }

    public static function getClient(): Client
    {
        $config = self::getConfig();

        // Env beats config so CLI contexts (test fixtures on CI runners) can point at the
        // service container without a config override.
        $client = new Client([
            'host' => getenv('CLICKHOUSE_HOST') ?: ($config['host'] ?? 'clickhouse'),
            'port' => (string) (getenv('CLICKHOUSE_PORT') ?: ($config['port'] ?? 8123)),
            'username' => $config['user'] ?? 'matomo',
            'password' => $config['password'] ?? 'matomo',
        ]);
        $client->database(self::getDatabaseName());
        $client->setConnectTimeOut(2);
        $client->setTimeout(60);

        // The synced tables are ReplacingMergeTree: FINAL collapses row versions so reads
        // see current-row state (and drops rows whose latest version is a delete).
        $client->settings()->set('final', 1);

        // Keep wide SELECT-* queries with FINAL inside the small dev container's memory
        // budget: fewer parallel streams, spill sorts to disk instead of failing, and use
        // a disk-friendly join algorithm for the segment joins onto log_link_visit_action.
        $client->settings()->set('max_threads', 2);
        $client->settings()->set('max_bytes_before_external_sort', 256 * 1024 * 1024);
        $client->settings()->set('join_algorithm', 'grace_hash');

        return $client;
    }

    /**
     * Converts a Matomo-built MySQL SELECT into something ClickHouse accepts: strips the
     * MySQL/MariaDB max-execution-time hints and rewrites positional `?` placeholders into
     * the smi2 client's named bindings. The scan is quote-aware so `?` inside string
     * literals is left alone. Bind names are fixed-width so no name is a prefix of another
     * during the client's string substitution.
     */
    private static function convertQuery(string $sql, array $bind): array
    {
        $sql = preg_replace('~/\*\+\s*MAX_EXECUTION_TIME\(\d+\)\s*\*/~i', '', $sql);
        $sql = preg_replace('~^\s*SET\s+STATEMENT\s+max_statement_time=\S+\s+FOR\s+~i', '', $sql);
        $sql = self::rewriteVisitDedupForClickHouse($sql);

        // Matomo's "is (not) empty" segment idiom compares any column against '' and '0'
        // literals, relying on MySQL's loose typing ('' casts to 0). ClickHouse refuses to
        // compare numeric columns with ''; comparing toString(col) instead reproduces the
        // MySQL results exactly — the idiom's own "<> '0'" leg keeps numeric zero excluded.
        $sql = preg_replace(
            "~((?:`?\\w+`?\\.)?`?\\w+`?)\\s*(<>|!=|=)\\s*('(?:0)?')~",
            'toString($1) $2 $3',
            $sql
        );

        // MySQL allows LIKE on numeric columns by casting (the GDPR data subject search
        // does idvisit LIKE '10%'); ClickHouse requires a String argument. toString() is
        // the identity for String columns and MySQL's cast semantics for everything else.
        $sql = preg_replace(
            '~((?:`?\w+`?\.)?`?\w+`?)(\s+(?:NOT\s+)?LIKE\s)~i',
            'toString($1)$2',
            $sql
        );

        $bind = array_values($bind);
        foreach ($bind as $i => $value) {
            // Matomo binds BINARY column values (idvisitor/config_id/location_ip) as raw
            // bytes; the ClickHouse copies store them hex-encoded (see BINARY_COLUMNS).
            // Raw binary of those widths virtually always contains non-printable bytes.
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
                    throw new \Exception('ClickHouse query conversion failed: more `?` placeholders than bind values');
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
            throw new \Exception(sprintf(
                'ClickHouse query conversion failed: %d `?` placeholders for %d bind values',
                $paramIndex,
                count($bind)
            ));
        }

        return [$converted, $params];
    }

    /**
     * Segmented visits-log queries dedup joined rows with MySQL's loose
     * `SELECT log_visit.* … GROUP BY log_visit.idvisit`, which ClickHouse rejects
     * (NOT_AN_AGGREGATE). ClickHouse's native equivalent is `LIMIT 1 BY idvisit`:
     * keep the first row per visit after ORDER BY. Rewrites only a top-level
     * `GROUP BY <x>.idvisit` (the last occurrence, and only when nothing after it
     * reopens parentheses — inner subqueries like the intersect-segment filter
     * group validly and are left alone).
     */
    private static function rewriteVisitDedupForClickHouse(string $sql): string
    {
        $pattern = '~\bGROUP\s+BY\s+(`?\w+`?\.`?idvisit`?|`?idvisit`?)\s*~i';
        if (!preg_match_all($pattern, $sql, $matches, PREG_OFFSET_CAPTURE)) {
            return $sql;
        }

        $last = count($matches[0]) - 1;
        [$groupByText, $groupByOffset] = $matches[0][$last];
        $dedupColumn = $matches[1][$last][0];

        $remainder = substr($sql, $groupByOffset + strlen($groupByText));
        if (strpos($remainder, ')') !== false) {
            // Not provably top-level; leave the query alone (caller falls back to MySQL).
            return $sql;
        }

        $sql = substr($sql, 0, $groupByOffset) . ' ' . $remainder;

        if (preg_match('~\bLIMIT\s+\d+(?:\s*,\s*\d+)?\s*$~i', $sql, $limitMatch, PREG_OFFSET_CAPTURE)) {
            $limitOffset = $limitMatch[0][1];
            return substr($sql, 0, $limitOffset) . 'LIMIT 1 BY ' . $dedupColumn . ' ' . substr($sql, $limitOffset);
        }

        return $sql . ' LIMIT 1 BY ' . $dedupColumn;
    }

    /**
     * ClickHouse database holding the log table copies. Empty config value means "mirror
     * the MySQL database name" — right for both ddev (db -> db, matching the sink
     * connector) and the test environment (matomo_tests -> matomo_tests).
     */
    public static function getDatabaseName(): string
    {
        $config = self::getConfig();
        if (!empty($config['database'])) {
            return $config['database'];
        }

        $dbConfig = Config::getInstance()->database;
        return $dbConfig['dbname'] ?? 'db';
    }

    /**
     * Copies the five raw log tables of the current MySQL database into ClickHouse as
     * ReplacingMergeTree tables, using ClickHouse's mysql() table function (the server
     * pulls straight from MySQL). Test/POC plumbing: this is how UI test fixtures get
     * their tracked data into ClickHouse — CI has no CDC connector. Binary columns are
     * hex-encoded to match the sink connector's convention.
     */
    public static function syncLogTablesFromMysql(): void
    {
        $dbConfig = Config::getInstance()->database;
        $chConfig = self::getConfig();

        // Hostname of the MySQL server AS SEEN FROM the ClickHouse server. Defaults to
        // what PHP uses; CI overrides (PHP reaches MySQL on 127.0.0.1, the ClickHouse
        // service container reaches the runner host via host.docker.internal).
        $mysqlHost = getenv('CLICKHOUSE_SYNC_MYSQL_HOST')
            ?: ($chConfig['sync_mysql_host'] ?? '')
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
        $fingerprint = self::computeMysqlLogTablesFingerprint();

        $client = self::getClient();
        // The target database may not exist yet; run DDL against the default database.
        $client->database('default');
        $client->write(sprintf('CREATE DATABASE IF NOT EXISTS `%s`', $chDatabase));

        foreach (self::LOG_TABLES as $table => $orderBy) {
            $mysqlTable = $prefix . $table;

            $selectColumns = '*';
            if (!empty(self::BINARY_COLUMNS[$table])) {
                $replacements = [];
                foreach (self::BINARY_COLUMNS[$table] as $binaryColumn) {
                    $replacements[] = sprintf('lower(hex(`%1$s`)) AS `%1$s`', $binaryColumn);
                }
                $selectColumns = '* REPLACE (' . implode(', ', $replacements) . ')';
            }

            $client->write(sprintf('DROP TABLE IF EXISTS `%s`.`%s`', $chDatabase, $mysqlTable));
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
    }

    /**
     * Test-mode freshness: tests mutate the log tables mid-run (tracking new visits, GDPR
     * deletions, anonymisation), which a one-shot fixture sync cannot see. Before serving
     * a query, compare the MySQL log tables' checksum fingerprint with the one stored at
     * sync time and re-copy when they diverge — zero-lag CDC as far as tests can tell.
     */
    private static function resyncIfLogTablesChangedForTests(): void
    {
        $current = self::computeMysqlLogTablesFingerprint();

        try {
            $stored = self::getClient()->select(sprintf(
                'SELECT fingerprint FROM `%s`.`sync_state`',
                self::getDatabaseName()
            ))->fetchOne('fingerprint');
        } catch (\Exception $e) {
            $stored = null;
        }

        if ($stored === $current) {
            return;
        }

        error_log('ClickHouse test resync: log tables changed since last sync, re-copying from MySQL');
        self::syncLogTablesFromMysql();
    }

    private static function computeMysqlLogTablesFingerprint(): string
    {
        $prefix = (string) (Config::getInstance()->database['tables_prefix'] ?? '');

        $tables = [];
        foreach (array_keys(self::LOG_TABLES) as $table) {
            $tables[] = '`' . $prefix . $table . '`';
        }

        $parts = [];
        foreach (Db::fetchAll('CHECKSUM TABLE ' . implode(', ', $tables)) as $row) {
            $parts[] = ($row['Table'] ?? '') . ':' . ($row['Checksum'] ?? '');
        }

        return implode('|', $parts);
    }

    /**
     * Compact call tree (nearest callers first) for the query log line, e.g.
     * "Piwik\Plugins\Live\Model::executeLogVisitsQuery <- Piwik\Plugins\Live\Model::queryLogVisits <- …".
     * Frames inside this class are skipped.
     */
    private static function describeCallers(): string
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

    private static function getConfig(): array
    {
        $section = Config::getInstance()->ClickHouse;
        return is_array($section) ? $section : [];
    }
}
