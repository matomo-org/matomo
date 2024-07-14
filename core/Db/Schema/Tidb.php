<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Db\Schema;

use Piwik\DbHelper;

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

    public function getDefaultPort(): int
    {
        return 4000;
    }

    public function getTableCreateOptions(): string
    {
        $engine = $this->getTableEngine();
        $charset = $this->getUsedCharset();
        $rowFormat = $this->getTableRowFormat();

        $options = "ENGINE=$engine DEFAULT CHARSET=$charset";

        if ('utf8mb4' === $charset) {
            $options .= ' COLLATE=utf8mb4_0900_ai_ci';
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

    protected function getDatabaseCreateOptions(): string
    {
        $charset = DbHelper::getDefaultCharset();
        $options = "DEFAULT CHARACTER SET $charset";

        if ('utf8mb4' === $charset) {
            $options .= ' COLLATE=utf8mb4_0900_ai_ci';
        }

        return $options;
    }
}
