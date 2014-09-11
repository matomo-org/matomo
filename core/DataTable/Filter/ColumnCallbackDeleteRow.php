<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataTable\Filter;

use Piwik\DataTable;
use Piwik\DataTable\BaseFilter;

/**
 * Deletes all rows for which a callback returns true.
 *
 * **Basic usage example**
 *
 *     $labelsToRemove = array('label1', 'label2', 'label2');
 *     $dataTable->filter('ColumnCallbackDeleteRow', array('label', function ($label) use ($labelsToRemove) {
 *         return in_array($label, $labelsToRemove);
 *     }));
 *
 * @api
 */
class ColumnCallbackDeleteRow extends BaseFilter
{
    private $columnToFilter;
    private $function;
    private $functionParams;

    /**
     * Constructor.
     *
     * @param DataTable $table The DataTable that will be filtered eventually.
     * @param array|string $columnsToFilter The column or array of columns that should be
     *                                      passed to the callback.
     * @param callback $function The callback that determines whether a row should be deleted
     *                           or not. Should return `true` if the row should be deleted.
     * @param array $functionParams deprecated - use an [anonymous function](http://php.net/manual/en/functions.anonymous.php)
     *                              instead.
     */
    public function __construct($table, $columnsToFilter, $function, $functionParams = array())
    {
        parent::__construct($table);

        if (!is_array($functionParams)) {
            $functionParams = array($functionParams);
        }

        if (!is_array($columnsToFilter)) {
            $columnsToFilter = array($columnsToFilter);
        }

        $this->function = $function;
        $this->columnsToFilter = $columnsToFilter;
        $this->functionParams = $functionParams;
    }

    /**
     * Filters the given data table
     *
     * @param DataTable $table
     */
    public function filter($table)
    {
        foreach ($table->getRows() as $key => $row) {
            $params = array();
            foreach ($this->columnsToFilter as $column) {
                $params[] = $row->getColumn($column);
            }

            $params = array_merge($params, $this->functionParams);
            if (call_user_func_array($this->function, $params) === true) {
                $table->deleteRow($key);
            }

            $this->filterSubTable($row);
        }
    }
}
