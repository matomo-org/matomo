<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updater\Migration\Db;

use Piwik\Db\Schema;

/**
 * @see Factory::dropIndex()
 * @ignore
 */
class DropIndex extends Statements
{
    /**
     * @param string $table Prefixed table name
     * @param string $indexName name of the index
     */
    public function __construct($table, $indexName)
    {
        $specification = sprintf('DROP INDEX `%s`', $indexName);

        parent::__construct(Schema::getInstance()->getAlterTableSql($table, [$specification]), array(static::ERROR_CODE_COLUMN_NOT_EXISTS));
    }
}
