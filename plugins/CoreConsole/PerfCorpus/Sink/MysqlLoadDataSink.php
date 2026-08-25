<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\PerfCorpus\Sink;

use Piwik\Common;
use Piwik\Db;
use Piwik\Plugins\CoreConsole\PerfCorpus\RowSink;

/**
 * Writes rows with LOAD DATA LOCAL INFILE, falling back to multi-row INSERT.
 *
 * Core already has BatchInsert, and it is nearly right, but two of its behaviours are wrong here:
 * its LOAD DATA path emits REPLACE INTO while its fallback emits INSERT IGNORE - different
 * semantics depending on which path you land on - and it skips LOAD DATA entirely when
 * multi_server_environment is set, which silently drops throughput by roughly an order of
 * magnitude on exactly the kind of installation this corpus is generated on. Its fallback is also
 * one INSERT per row.
 *
 * So: plain INSERT on both paths, a real multi-row INSERT as the fallback, and no config that can
 * quietly change which one is used.
 */
class MysqlLoadDataSink implements RowSink
{
    /** Buffer per table before a flush. Bigger batches help until the packet size gets awkward. */
    private const FLUSH_THRESHOLD_BYTES = 8388608;

    /** Rows per statement on the fallback path, kept well inside max_allowed_packet. */
    private const INSERT_BATCH_ROWS = 500;

    /**
     * Columns stored as raw bytes. They travel as hex and are converted on the way in, because
     * raw bytes in a delimited file are a source of corruption nobody needs.
     */
    private const BINARY_COLUMNS = [
        'log_visit' => ['idvisitor', 'config_id', 'location_ip'],
        'log_link_visit_action' => ['idvisitor'],
        'log_conversion' => ['idvisitor'],
        'log_conversion_item' => ['idvisitor'],
        'log_action' => [],
    ];

    private string $tmpDir;
    private bool $useLoadData;
    private int $rowsWritten = 0;
    private ?string $fallbackReason = null;

    /** @var array<string,array{columns: string[], data: string, rows: int}> */
    private array $buffers = [];

    /** @var \HashContext */
    private $checksum;

    public function __construct(string $tmpDir, ?bool $useLoadData = null)
    {
        $this->tmpDir = rtrim($tmpDir, '/');
        $this->useLoadData = $useLoadData ?? self::isLoadDataAvailable();
        $this->checksum = hash_init('crc32b');

        if (!is_dir($this->tmpDir) && !@mkdir($this->tmpDir, 0o770, true) && !is_dir($this->tmpDir)) {
            throw new \RuntimeException('Cannot create the CSV staging directory: ' . $this->tmpDir);
        }
    }

    /**
     * Where the staged TSV files go by default.
     *
     * Deliberately the system temp directory rather than anywhere under the Matomo tree. The
     * staging file is written and read once and then deleted, so it wants the fastest local disk
     * available - and the project directory is frequently the slowest thing on the machine: a
     * synced mount under ddev, an EBS volume with a network round trip on a cloud box.
     */
    public static function defaultStagingDir(int $idRun): string
    {
        return rtrim(sys_get_temp_dir(), '/') . '/perfcorpus-csv-' . $idRun;
    }

    public static function isLoadDataAvailable(): bool
    {
        try {
            return Db::get()->hasBulkLoader() && '1' === (string) Db::fetchOne('SELECT @@local_infile');
        } catch (\Exception $e) {
            return false;
        }
    }

    public function isUsingLoadData(): bool
    {
        return $this->useLoadData;
    }

    public function write(string $table, array $columns, array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        if (!isset($this->buffers[$table])) {
            $this->buffers[$table] = ['columns' => $columns, 'data' => '', 'rows' => 0];
        } elseif ($this->buffers[$table]['columns'] !== $columns) {
            // Two different column sets for one table would produce a file the LOAD DATA column
            // list no longer matches. Flush the old shape first.
            $this->flushTable($table);
            $this->buffers[$table] = ['columns' => $columns, 'data' => '', 'rows' => 0];
        }

        $lines = '';
        foreach ($rows as $row) {
            $fields = [];
            foreach ($row as $value) {
                $fields[] = self::escape($value);
            }
            $lines .= implode("\t", $fields) . "\n";
        }

        $this->buffers[$table]['data'] .= $lines;
        $this->buffers[$table]['rows'] += count($rows);
        $this->rowsWritten += count($rows);

        hash_update($this->checksum, $table);
        hash_update($this->checksum, $lines);

        if (strlen($this->buffers[$table]['data']) >= self::FLUSH_THRESHOLD_BYTES) {
            $this->flushTable($table);
        }
    }

    public function flush(): void
    {
        foreach (array_keys($this->buffers) as $table) {
            $this->flushTable($table);
        }
    }

    private function flushTable(string $table): void
    {
        if (empty($this->buffers[$table]) || 0 === $this->buffers[$table]['rows']) {
            return;
        }

        $buffer = $this->buffers[$table];
        $this->buffers[$table]['data'] = '';
        $this->buffers[$table]['rows'] = 0;

        if ($this->useLoadData) {
            try {
                $this->loadData($table, $buffer['columns'], $buffer['data']);

                return;
            } catch (\Exception $e) {
                // One failure is enough to distrust the path for the rest of this process; falling
                // back per statement would hide a misconfiguration behind a 10x slowdown.
                $this->useLoadData = false;
                $this->fallbackReason = $e->getMessage();
            }
        }

        $this->insertRows($table, $buffer['columns'], $buffer['data']);
    }

    private function loadData(string $table, array $columns, string $data): void
    {
        $path = sprintf('%s/perfcorpus-%s-%s.tsv', $this->tmpDir, $table, bin2hex(random_bytes(8)));

        if (false === file_put_contents($path, $data)) {
            throw new \RuntimeException('Could not stage the CSV file: ' . $path);
        }

        try {
            $prefixed = Common::prefixTable($table);
            [$columnList, $setClause] = $this->buildColumnList($table, $columns);

            $sql = sprintf(
                "LOAD DATA LOCAL INFILE '%s' INTO TABLE `%s` "
                . "FIELDS TERMINATED BY '\\t' ESCAPED BY '\\\\' LINES TERMINATED BY '\\n' (%s)%s",
                addslashes($path),
                $prefixed,
                $columnList,
                $setClause
            );

            Db::exec($sql);
        } finally {
            @unlink($path);
        }
    }

    /**
     * Binary columns are read into a user variable and UNHEXed, because raw bytes in a delimited
     * file are a source of corruption nobody needs.
     *
     * @return array{0: string, 1: string} the column list and the SET clause
     */
    private function buildColumnList(string $table, array $columns): array
    {
        $binary = self::BINARY_COLUMNS[$table] ?? [];
        $list = [];
        $sets = [];

        foreach ($columns as $column) {
            if (in_array($column, $binary, true)) {
                $list[] = '@' . $column . '_hex';
                $sets[] = sprintf('`%s` = UNHEX(@%s_hex)', $column, $column);
            } else {
                $list[] = '`' . $column . '`';
            }
        }

        return [implode(', ', $list), empty($sets) ? '' : ' SET ' . implode(', ', $sets)];
    }

    /**
     * Fallback: real multi-row INSERTs built from the same staged text, so both paths write
     * byte-identical data.
     */
    private function insertRows(string $table, array $columns, string $data): void
    {
        $binary = self::BINARY_COLUMNS[$table] ?? [];
        $prefixed = Common::prefixTable($table);
        $quoted = '`' . implode('`, `', $columns) . '`';
        $placeholder = '(' . implode(',', array_fill(0, count($columns), '?')) . ')';

        $lines = explode("\n", rtrim($data, "\n"));

        foreach (array_chunk($lines, self::INSERT_BATCH_ROWS) as $batch) {
            $bind = [];

            foreach ($batch as $line) {
                foreach (explode("\t", $line) as $index => $field) {
                    $value = self::unescape($field);

                    if (null !== $value && in_array($columns[$index], $binary, true)) {
                        $value = hex2bin($value);
                    }

                    $bind[] = $value;
                }
            }

            Db::query(
                sprintf(
                    'INSERT INTO `%s` (%s) VALUES %s',
                    $prefixed,
                    $quoted,
                    implode(',', array_fill(0, count($batch), $placeholder))
                ),
                $bind
            );
        }
    }

    /**
     * MySQL's LOAD DATA escaping. \N is how it spells NULL, which is why a literal backslash has
     * to be escaped first.
     */
    private static function escape($value): string
    {
        if (null === $value) {
            return '\\N';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return str_replace(
            ["\\", "\t", "\n", "\r", "\0"],
            ["\\\\", "\\t", "\\n", "\\r", "\\0"],
            (string) $value
        );
    }

    private static function unescape(string $field): ?string
    {
        if ('\\N' === $field) {
            return null;
        }

        // One left-to-right pass. A sequential str_replace would decode the "t" of an escaped
        // backslash followed by a literal t as a tab.
        return preg_replace_callback(
            '/\\\\(.)/s',
            static function (array $match): string {
                switch ($match[1]) {
                    case 't':
                        return "\t";
                    case 'n':
                        return "\n";
                    case 'r':
                        return "\r";
                    case '0':
                        return "\0";
                    default:
                        return $match[1];
                }
            },
            $field
        );
    }

    /**
     * Why LOAD DATA stopped being used, if it did. A silent fall back to INSERTs is an order of
     * magnitude slower, so it has to be reported rather than absorbed.
     */
    public function getFallbackReason(): ?string
    {
        return $this->fallbackReason;
    }

    public function getRowsWritten(): int
    {
        return $this->rowsWritten;
    }

    public function getChecksum(): string
    {
        return hash_final(hash_copy($this->checksum));
    }

    public function resetChecksum(): void
    {
        $this->checksum = hash_init('crc32b');
    }
}
