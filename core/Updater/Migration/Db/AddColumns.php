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
 * @see Factory::addColumns()
 * @ignore
 */
class AddColumns extends AlterTable
{
    /**
     * @var array<string, string>
     */
    private $columns;

    /**
     * @var string|null
     */
    private $placeColumnAfter;

    public function __construct($table, $columns, $placeColumnAfter)
    {
        $this->columns = $columns;
        $this->placeColumnAfter = $placeColumnAfter;

        parent::__construct($table, [], static::ERROR_CODE_DUPLICATE_COLUMN);
    }

    /**
     * Skips the columns the table has when the migration runs, on the connection in use.
     */
    protected function getSpecifications(): array
    {
        try {
            $existingColumns = array_keys(Schema::getInstance()->getConnectionSchema(true)->getTableColumns(str_replace('`', '', $this->getTableName())));
        } catch (\Exception $ex) {
            $existingColumns = [];
        }

        $placeColumnAfter = $this->placeColumnAfter;
        $changes = array();
        foreach ($this->columns as $columnName => $columnType) {
            if (in_array($columnName, $existingColumns)) {
                continue;
            }

            $part = sprintf("ADD COLUMN `%s` %s", $columnName, $columnType);

            if (!empty($placeColumnAfter)) {
                $part .= sprintf(' AFTER `%s`', $placeColumnAfter);
                $placeColumnAfter = $columnName;
            }

            $changes[] = $part;
        }

        return $changes;
    }
}
