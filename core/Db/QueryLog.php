<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Db;

/**
 * Appends every statement the database adapters send to a JSON lines file, with the bound
 * values interpolated.
 *
 * Off unless MATOMO_SQL_QUERY_LOG names a file. An environment variable rather than a config
 * setting, because it has to reach every process of a run - including the ones core:archive and
 * CliMulti spawn, which inherit the environment but not command line options - and because it is
 * a diagnostic that should not be left switched on in a deployed config.
 *
 * MATOMO_SQL_QUERY_LOG_CONTEXT may hold a JSON object, which is copied into every line as
 * "context" so a harness can tell its steps apart.
 *
 * Unlike [Debug] log_sql_queries this records the bound values, so the file can hold personal
 * data from the log tables. It is meant for benchmark and debugging runs.
 *
 * @internal
 */
final class QueryLog
{
    public const ENV_FILE = 'MATOMO_SQL_QUERY_LOG';
    public const ENV_CONTEXT = 'MATOMO_SQL_QUERY_LOG_CONTEXT';

    /** @var array<string, resource|false> open handles by path, false for a path that failed to open */
    private static $handles = [];

    /** @var int */
    private static $sequence = 0;

    public static function isEnabled(): bool
    {
        return (string) getenv(self::ENV_FILE) !== '';
    }

    /**
     * Runs a statement on a MySQL adapter and records it.
     *
     * @param \Zend_Db_Adapter_Abstract $adapter quotes the bound values the way the driver does
     * @param mixed $sql string or Zend_Db_Select
     * @param mixed $bind
     * @return mixed whatever $run returns
     */
    public static function runMysql($adapter, $sql, $bind, callable $run)
    {
        $startTime = microtime(true);
        try {
            $result = $run();
        } catch (\Throwable $e) {
            self::record('mysql', $adapter, self::interpolateMysql($adapter, $sql, $bind), $startTime, $e);
            throw $e;
        }

        self::record('mysql', $adapter, self::interpolateMysql($adapter, $sql, $bind), $startTime, null);

        return $result;
    }

    /**
     * @param object $adapter the connection, so lines from two connections of one process can be told apart
     */
    public static function record(string $engine, $adapter, string $sql, float $startTime, ?\Throwable $error): void
    {
        $durationMs = (microtime(true) - $startTime) * 1000;

        $handle = self::getHandle();
        if ($handle === null) {
            return;
        }

        $context = json_decode((string) getenv(self::ENV_CONTEXT), true);

        $line = [
            'ts' => round($startTime, 6),
            'pid' => getmypid(),
            'seq' => ++self::$sequence,
            'engine' => $engine,
            'connection' => spl_object_id($adapter),
            'ms' => round($durationMs, 3),
            'error' => $error === null ? null : $error->getMessage(),
            'caller' => self::describeCallers(),
            'context' => is_array($context) ? $context : null,
            'sql' => $sql,
        ];

        // A literal in the SQL text itself can still hold bytes that are not UTF-8.
        $json = json_encode($line, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

        // Several processes of one run append to the same file.
        flock($handle, LOCK_EX);
        fwrite($handle, $json . "\n");
        fflush($handle);
        flock($handle, LOCK_UN);
    }

    /**
     * The statement as PDO's emulated prepare sends it. Zend binds every value as a string, so
     * every non-null value arrives quoted, numbers included.
     *
     * Values that are not valid UTF-8 - the BINARY idvisitor, config_id and location_ip - are
     * written as X'…' hex literals instead of the raw bytes the driver sends. The server reads
     * both the same way, and only the hex form survives a JSON file.
     *
     * Only positional `?` placeholders are interpolated, which is all Matomo uses. A statement
     * whose placeholders do not match its values is logged uninterpolated with the values
     * appended, rather than guessed at.
     *
     * @param \Zend_Db_Adapter_Abstract $adapter
     * @param mixed $sql
     * @param mixed $bind
     */
    public static function interpolateMysql($adapter, $sql, $bind): string
    {
        if ($sql instanceof \Zend_Db_Select) {
            if (empty($bind)) {
                $bind = $sql->getBind();
            }
            $sql = $sql->assemble();
        }
        $sql = (string) $sql;

        if (!is_array($bind)) {
            $bind = [$bind];
        }
        $bind = array_values($bind);

        $placeholders = self::findPositionalPlaceholders($sql);
        if (count($placeholders) !== count($bind)) {
            return empty($bind)
                ? $sql
                : $sql . ' /* unbound values: ' . json_encode($bind, JSON_INVALID_UTF8_SUBSTITUTE) . ' */';
        }

        // Back to front, so the offsets still ahead stay valid.
        for ($i = count($placeholders) - 1; $i >= 0; $i--) {
            $sql = substr_replace($sql, self::quoteMysqlValue($adapter, $bind[$i]), $placeholders[$i], 1);
        }

        return $sql;
    }

    /**
     * Compact call chain, nearest caller first, skipping the database layer itself.
     */
    public static function describeCallers(): string
    {
        $frames = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 30);
        $parts = [];

        foreach ($frames as $frame) {
            $class = $frame['class'] ?? '';
            $function = $frame['function'];
            if (
                strpos($function, 'call_user_func') === 0
                || strpos($function, '{closure') !== false
                || $class === self::class
                || $class === \Piwik\Db::class
                || $class === \Piwik\DataAccess\ArchivingDbAdapter::class
                || strpos($class, 'Piwik\\Db\\Adapter\\') === 0
                || strpos($class, 'Zend_Db_') === 0
            ) {
                continue;
            }

            $parts[] = ($class !== '' ? $class . '::' : '') . $function;
            if (count($parts) >= 5) {
                break;
            }
        }

        return $parts ? implode(' <- ', $parts) : '(unknown caller)';
    }

    /**
     * @param \Zend_Db_Adapter_Abstract $adapter
     * @param mixed $value
     */
    private static function quoteMysqlValue($adapter, $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        $value = (string) $value;
        if (preg_match('//u', $value) !== 1) {
            return "X'" . bin2hex($value) . "'";
        }

        return (string) $adapter->quote($value);
    }

    /**
     * Offsets of the `?` placeholders outside quoted strings and identifiers.
     *
     * @return int[]
     */
    private static function findPositionalPlaceholders(string $sql): array
    {
        $offsets = [];
        $quoteChar = null;
        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];

            if ($quoteChar !== null) {
                if ($char === '\\' && $quoteChar !== '`') {
                    $i++;
                } elseif ($char === $quoteChar) {
                    $quoteChar = null;
                }
                continue;
            }

            if ($char === "'" || $char === '"' || $char === '`') {
                $quoteChar = $char;
            } elseif ($char === '?') {
                $offsets[] = $i;
            }
        }

        return $offsets;
    }

    /**
     * @return resource|null
     */
    private static function getHandle()
    {
        $path = (string) getenv(self::ENV_FILE);
        if ($path === '') {
            return null;
        }

        if (!array_key_exists($path, self::$handles)) {
            self::$handles[$path] = @fopen($path, 'ab');
            if (self::$handles[$path] === false) {
                // Said once per path, then quiet: a diagnostic must not take the run down with it.
                error_log(sprintf('%s=%s cannot be opened for writing, no queries are logged', self::ENV_FILE, $path));
            }
        }

        return self::$handles[$path] ?: null;
    }
}
