<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */
namespace Piwik\DataTable\Filter;

use Piwik\DataTable\Filter;
use Piwik\DataTable;
use Piwik\DataTable\Row;

/**
 * Replace a column value with a new value resulting
 * from the function called with the column's value
 *
 * @package Piwik
 * @subpackage DataTable
 */
class ColumnCallbackReplace extends Filter
{
    private $columnsToFilter;
    private $functionToApply;
    private $functionParameters;
    private $extraColumnParameters;

    /**
     * @param DataTable $table
     * @param array|string $columnsToFilter
     * @param callback $functionToApply
     * @param array|null $functionParameters
     * @param array $extraColumnParameters
     */
    public function __construct($table, $columnsToFilter, $functionToApply, $functionParameters = null,
                                $extraColumnParameters = array())
    {
        parent::__construct($table);
        $this->functionToApply = $functionToApply;
        $this->functionParameters = $functionParameters;

        if (!is_array($columnsToFilter)) {
            $columnsToFilter = array($columnsToFilter);
        }

        $this->columnsToFilter = $columnsToFilter;
        $this->extraColumnParameters = $extraColumnParameters;
    }

    /**
     * Filters the given data table
     *
     * @param DataTable $table
     */
    public function filter($table)
    {
        foreach ($table->getRows() as $key => $row) {
            $extraColumnParameters = array();
            foreach ($this->extraColumnParameters as $columnName) {
                $extraColumnParameters[] = $row->getColumn($columnName);
            }

            foreach ($this->columnsToFilter as $column) {
                // when a value is not defined, we set it to zero by default (rather than displaying '-')
                $value = $this->getElementToReplace($row, $column);
                if ($value === false) {
                    $value = 0;
                }

                $parameters = array_merge(array($value), $extraColumnParameters);
                if (!is_null($this->functionParameters)) {
                    $parameters = array_merge($parameters, $this->functionParameters);
                }
                $newValue = call_user_func_array($this->functionToApply, $parameters);
                $this->setElementToReplace($row, $column, $newValue);
                $this->filterSubTable($row);
            }
        }
    }

    /**
     * Replaces the given column within given row with the given value
     *
     * @param Row $row
     * @param string $columnToFilter
     * @param mixed $newValue
     */
    protected function setElementToReplace($row, $columnToFilter, $newValue)
    {
        $row->setColumn($columnToFilter, $newValue);
    }

    /**
     * Returns the element that should be replaced
     *
     * @param Row $row
     * @param string $columnToFilter
     * @return mixed
     */
    protected function getElementToReplace($row, $columnToFilter)
    {
        return $row->getColumn($columnToFilter);
    }
}
