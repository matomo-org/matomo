<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
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
    private $discriminatorColumn;
    private $discriminatorValue;

    /**
     * Join constructor.
     * @param $table
     * @param $column
     * @param $targetColumn
     * @param null $discriminatorColumn
     * @param null|int $discriminatorValue should be only hard coded, safe values.
     * @throws Exception
     */
    public function __construct($table, $column, $targetColumn, $discriminatorColumn = null, $discriminatorValue = null)
    {
        $this->table = $table;
        $this->column = $column;
        $this->targetColumn = $targetColumn;

        if (!empty($discriminatorColumn) xor isset($discriminatorValue)) {
            throw new Exception('Both discriminatorColumn and discriminatorValue need to be defined');
        }
        if (!empty($discriminatorColumn) && !is_numeric($discriminatorValue)) {
            throw new Exception('$discriminatorValue needs to be null or numeric');
        }

        $this->discriminatorColumn = $discriminatorColumn;
        $this->discriminatorValue = $discriminatorValue;
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
     * @return string
     */
    public function getDiscriminatorColumn()
    {
        return $this->discriminatorColumn;
    }

    /**
     * @return int|null
     */
    public function getDiscriminatorValue()
    {
        return $this->discriminatorValue;
    }
}
