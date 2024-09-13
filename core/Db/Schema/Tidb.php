<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Db\Schema;

/**
 * Mariadb schema
 */
class Tidb extends Mysql
{
    /**
     * TiDB performs a sanity check before performing e.g. ALTER TABLE statements. If any of the used columns does not
     * exist before the query fails. This also happens if the column would be added in the same query.
     *
     * @return bool
     */
    public function supportsComplexColumnUpdates(): bool
    {
        return false;
    }

    public function getDefaultCollationForCharset(string $charset): string
    {
        $collation = parent::getDefaultCollationForCharset($charset);

        if ('utf8mb4' === $charset && 'utf8mb4_bin' === $collation) {
            // replace the TiDB default "utf8mb4_bin" with a better default
            return 'utf8mb4_0900_ai_ci';
        }

        return $collation;
    }

    public function getDefaultPort(): int
    {
        return 4000;
    }

    public function getTableCreateOptions(): string
    {
        $engine = $this->getTableEngine();
        $charset = $this->getUsedCharset();
        $collation = $this->getUsedCollation();
        $rowFormat = $this->getTableRowFormat();

        if ('utf8mb4' === $charset && '' === $collation) {
            $collation = 'utf8mb4_0900_ai_ci';
        }

        $options = "ENGINE=$engine DEFAULT CHARSET=$charset";

        if ('' !== $collation) {
            $options .= " COLLATE=$collation";
        }

        if ('' !== $rowFormat) {
            $options .= " $rowFormat";
        }

        return $options;
    }

    public function isOptimizeInnoDBSupported(): bool
    {
        return false;
    }

    public function optimizeTables(array $tables, bool $force = false): bool
    {
        // OPTIMIZE TABLE not supported for TiDb
        return false;
    }

    public function supportsSortingInSubquery(): bool
    {
        // TiDb optimizer removes all sorting from subqueries
        return false;
    }

    public function getSupportedReadIsolationTransactionLevel(): string
    {
        // TiDB doesn't support READ UNCOMMITTED
        return 'READ COMMITTED';
    }
}
