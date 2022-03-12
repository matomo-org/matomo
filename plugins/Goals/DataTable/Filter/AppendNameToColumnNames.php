<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Goals\DataTable\Filter;

use Piwik\DataTable\BaseFilter;
use Piwik\DataTable;

/**
 * Appends a string to each column name in each row of a table. Please note this filter even appends the name to a
 * 'label' column. If you do not need this behaviour feel free to add a check to ignore label columns.
 */
class AppendNameToColumnNames extends BaseFilter
{
    protected $nameToAppend;

    /**
     * Constructor.
     *
     * @param DataTable $table     The table that will be eventually filtered.
     * @param string $nameToAppend The name that will be appended to each column
     */
    public function __construct($table, $nameToAppend)
    {
        parent::__construct($table);
        $this->nameToAppend = $nameToAppend;
    }

    /**
     * See {@link ReplaceColumnNames}.
     *
     * @param DataTable $table
     */
    public function filter($table)
    {
        if (!isset($this->nameToAppend) || '' === $this->nameToAppend || false === $this->nameToAppend) {
            return;
        }

        foreach ($table->getRows() as $row) {
            $columns = $row->getColumns();

            foreach ($columns as $column => $value) {
                $row->deleteColumn($column);
                $row->setColumn($column . $this->nameToAppend, $value);
            }

            $this->filterSubTable($row);
        }
    }
}
