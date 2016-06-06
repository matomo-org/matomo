<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataTable\Filter;

use Piwik\DataTable\BaseFilter;
use Piwik\DataTable;
use Piwik\DataTable\Row;

/**
 * Replaces one or more column values in each row of a DataTable with the results
 * of a callback.
 *
 * **Basic usage example**
 *
 *     $truncateString = function ($value, $truncateLength) {
 *         if (strlen($value) > $truncateLength) {
 *             return substr(0, $truncateLength);
 *         } else {
 *             return $value;
 *         }
 *     };
 *
 *     // label, url and truncate_length are columns in $dataTable
 *     $dataTable->filter('ColumnCallbackReplace', array('label', 'url'), $truncateString, null, array('truncate_length'));
 *
 * @api
 */
class ColumnCallbackReplace extends BaseFilter
{
    private $columnsToFilter;
    private $functionToApply;
    private $functionParameters;
    private $extraColumnParameters;

    /**
     * Constructor.
     *
     * @param DataTable $table The DataTable to filter.
     * @param array|string $columnsToFilter The columns whose values should be passed to the callback
     *                                      and then replaced with the callback's result.
     * @param callable $functionToApply The function to execute. Must take the column value as a parameter
     *                                  and return a value that will be used to replace the original.
     * @param array|null $functionParameters deprecated - use an [anonymous function](http://php.net/manual/en/functions.anonymous.php)
     *                                       instead.
     * @param array $extraColumnParameters Extra column values that should be passed to the callback, but
     *                                     shouldn't be replaced.
     */
    public function __construct($table, $columnsToFilter, $functionToApply, $functionParameters = null,
                                $extraColumnParameters = array())
    {
        parent::__construct($table);
        $this->functionToApply    = $functionToApply;
        $this->functionParameters = $functionParameters;

        if (!is_array($columnsToFilter)) {
            $columnsToFilter = array($columnsToFilter);
        }

        $this->columnsToFilter       = $columnsToFilter;
        $this->extraColumnParameters = $extraColumnParameters;
    }

    /**
     * See {@link ColumnCallbackReplace}.
     *
     * @param DataTable $table
     */
    public function filter($table)
    {
        foreach ($table->getRows() as $row) {
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

        if (in_array('label', $this->columnsToFilter)) {
            // we need to force rebuilding the index
            $table->setLabelsHaveChanged();
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
