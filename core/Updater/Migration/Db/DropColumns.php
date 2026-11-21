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
 * @see Factory::dropColumn()
 * @ignore
 */
class DropColumns extends AlterTable
{
    /**
     * @var string[]
     */
    private $columnNames;

    public function __construct($tableName, $columnNames)
    {
        $this->columnNames = $columnNames;

        parent::__construct($tableName, [], static::ERROR_CODE_COLUMN_NOT_EXISTS);
    }

    /**
     * Drops only the columns the table has when the migration runs, on the connection in use: if one of them
     * didn't exist, none would be removed otherwise.
     */
    protected function getSpecifications(): array
    {
        $table = Schema::getInstance()->getConnectionSchema()->getTableColumns($this->getTableName());

        $columnNames = array_unique(array_filter($this->columnNames, function ($columnName) use ($table) {
            return isset($table[$columnName]);
        }));

        return array_values(array_map(function ($columnName) {
            return sprintf('DROP COLUMN `%s`', $columnName);
        }, $columnNames));
    }

    protected function getStatements(): array
    {
        if ($this->getSpecifications() === []) {
            return [];
        }

        return parent::getStatements();
    }
}
