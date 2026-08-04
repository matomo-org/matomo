<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Columns;

use Exception;

/**
 * @api
 * @since 3.1.0
 */
class Join
{
    private $table;
    private $column;
    private $targetColumn;

    /**
     * Join constructor.
     * @param $table
     * @param $column
     * @param $targetColumn
     * @throws Exception
     */
    public function __construct($table, $column, $targetColumn)
    {
        $this->table = $table;
        $this->column = $column;
        $this->targetColumn = $targetColumn;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return string
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @return string
     */
    public function getTargetColumn()
    {
        return $this->targetColumn;
    }

    /**
     * Columns that must additionally match between the joined-from table and the joined table
     * to identify a row, given as column names present on both tables. Use this when the primary
     * join column is not unique on its own and needs a composite key (for example the site id).
     *
     * @return string[]
     * @since 5.13.0
     */
    public function getAdditionalKeyColumns()
    {
        return [];
    }
}
