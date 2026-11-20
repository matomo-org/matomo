<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Db;

/**
 * Index hints, for a schema implementing {@link SchemaInterface} whose engine takes MySQL's hints
 * differently or not at all. Schemas that don't implement it are sent MySQL's `FORCE INDEX` and
 * `USE INDEX` by {@link Schema}.
 */
interface IndexHintInterface
{
    /**
     * Returns the hint to append directly after a table name (or its alias) in a `FROM` or `JOIN`, asking the
     * engine to use the given index, e.g. ` FORCE INDEX (index_name)`. It is either empty, on engines whose
     * planner takes no such hint, or starts with a space.
     *
     * @param string $indexName The index name as declared in the MySQL table definition.
     * @param bool   $force     `FORCE INDEX` (a table scan is the only alternative) rather than `USE INDEX`.
     */
    public function getIndexHintSql(string $indexName, bool $force = true): string;
}
