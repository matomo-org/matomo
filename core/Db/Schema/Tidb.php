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

    public function getDefaultPort(): int
    {
        return 4000;
    }
}
