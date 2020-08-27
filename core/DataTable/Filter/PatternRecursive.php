<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataTable\Filter;

use Piwik\DataTable\BaseFilter;
use Piwik\DataTable;

/**
 * Deletes rows that do not contain a column that matches a regex pattern and do not contain a
 * subtable that contains a column that matches a regex pattern.
 *
 * **Example**
 *
 *     // only display index pageviews in Actions.getPageUrls
 *     $dataTable->filter('PatternRecursive', array('label', 'index'));
 *
 * @api
 */
class PatternRecursive extends BaseFilter
{
    private $columnToFilter;
    private $patternToSearch;
    private $patternToSearchQuoted;

    /**
     * Constructor.
     *
     * @param DataTable $table The table to eventually filter.
     * @param string $columnToFilter The column to match with the `$patternToSearch` pattern.
     * @param string $patternToSearch The regex pattern to use.
     */
    public function __construct($table, $columnToFilter, $patternToSearch)
    {
        parent::__construct($table);
        $this->patternToSearch = $patternToSearch;
        $this->patternToSearchQuoted = Pattern::getPatternQuoted($patternToSearch);
        $this->patternToSearch = $patternToSearch; //preg_quote($patternToSearch);
        $this->columnToFilter = $columnToFilter;
    }

    /**
     * See {@link PatternRecursive}.
     *
     * @param DataTable $table
     * @return int The number of deleted rows.
     */
    public function filter($table)
    {
        $rows = $table->getRows();

        foreach ($rows as $key => $row) {
            // A row is deleted if
            // 1 - its label doesn't contain the pattern
            // AND 2 - the label is not found in the children
            $patternNotFoundInChildren = false;

            $subTable = $row->getSubtable();
            if (!$subTable) {
                $patternNotFoundInChildren = true;
            } else {
                // we delete the row if we couldn't find the pattern in any row in the
                // children hierarchy
                if ($this->filter($subTable) == 0) {
                    $patternNotFoundInChildren = true;
                }
            }

            if ($patternNotFoundInChildren
                && !Pattern::match($this->patternToSearchQuoted, $row->getColumn($this->columnToFilter), $invertedMatch = false)
            ) {
                $table->deleteRow($key);
            }
        }

        return $table->getRowsCount();
    }
}
