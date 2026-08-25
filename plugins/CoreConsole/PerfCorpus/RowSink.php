<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\PerfCorpus;

/**
 * Where generated rows go.
 *
 * Row synthesis and row delivery are separated on purpose. The corpus is loaded into MySQL, and
 * anything else that needs the same rows - another storage engine, a file on disk - is a sink
 * behind this interface rather than a second generator, so the corpus stays reproducible from the
 * same seed whatever it is written to.
 *
 * Binary columns (idvisitor, config_id, location_ip) are passed as lowercase hex strings and
 * converted by the sink, so the row factories never have to care how a given backend wants
 * binary data.
 */
interface RowSink
{
    /**
     * @param string   $table   unprefixed log table name
     * @param string[] $columns column names, matching the order of each row
     * @param array[]  $rows    positional values; null means SQL NULL
     */
    public function write(string $table, array $columns, array $rows): void;

    /**
     * Pushes anything buffered. Must be called before a chunk is marked done.
     */
    public function flush(): void;

    public function getRowsWritten(): int;

    /**
     * A checksum over everything written since the last resetChecksum(), so two runs with the
     * same seed can be compared without reading the tables back - this is what proves --workers
     * does not change the data.
     */
    public function getChecksum(): string;

    /**
     * Starts a fresh checksum. Called at the start of every chunk, so the recorded value covers
     * that chunk alone: a cumulative one would depend on how many chunks this particular process
     * happened to handle, which is exactly the thing that varies with --workers.
     */
    public function resetChecksum(): void;
}
