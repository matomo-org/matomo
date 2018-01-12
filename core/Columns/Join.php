<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
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

}
