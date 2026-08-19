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
use Piwik\Option;

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
     * Runtime demo toggle (see the Visits Log page): lets the backend be flipped between
     * ClickHouse and MySQL without editing config. Only consulted while the [ClickHouse]
     * live_reports_enabled config flag makes the feature available at all.
     */
    private const OPTION_LIVE_REPORTS_UI_TOGGLE = 'ClickHouse.live_reports_enabled_ui';

    /**
     * Whether ClickHouse live reports are configured at all ([ClickHouse] section).
     */
    public static function isLiveReportsAvailable(): bool
    {
        $config = self::getConfig();
        return !empty($config['live_reports_enabled']);
    }

    /**
     * Whether the visits log should be served from ClickHouse right now: configured as
     * available, and not switched off via the runtime demo toggle (defaults to on).
     */
    public static function isLiveReportsEnabled(): bool
    {
        if (!self::isLiveReportsAvailable()) {
            return false;
        }

        return Option::get(self::OPTION_LIVE_REPORTS_UI_TOGGLE) !== '0';
    }

    public static function setLiveReportsEnabled(bool $enabled): void
    {
        Option::set(self::OPTION_LIVE_REPORTS_UI_TOGGLE, $enabled ? '1' : '0');
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
        [$chSql, $params] = self::convertQuery($sql, $bind);

        $rows = self::getClient()->select($chSql, $params)->rows();

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

        $bind = array_values($bind);
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

    private static function getConfig(): array
    {
        $section = Config::getInstance()->ClickHouse;
        return is_array($section) ? $section : [];
    }
}
