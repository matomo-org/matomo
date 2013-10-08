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

use Exception;
use Piwik\DataTable\Filter;
use Piwik\DataTable;
use Piwik\DataTable\Manager;

/**
 * Delete all rows for which
 * - the given $columnToFilter do not contain the $patternToSearch
 * - AND all the subTables associated to this row do not contain the $patternToSearch
 *
 * This filter is to be used on columns containing strings.
 * Example: from the pages viewed report, keep only the rows that contain "piwik" or for which a subpage contains "piwik".
 *
 * @package Piwik
 * @subpackage DataTable
 */
class PatternRecursive extends Filter
{
    private $columnToFilter;
    private $patternToSearch;
    private $patternToSearchQuoted;

    /**
     * @param DataTable $table
     * @param string $columnToFilter
     * @param string $patternToSearch
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
     * @param DataTable $table
     * @return int
     */
    public function filter($table)
    {
        $rows = $table->getRows();

        foreach ($rows as $key => $row) {
            // A row is deleted if
            // 1 - its label doesnt contain the pattern
            // AND 2 - the label is not found in the children
            $patternNotFoundInChildren = false;

            try {
                $idSubTable = $row->getIdSubDataTable();
                $subTable = Manager::getInstance()->getTable($idSubTable);

                // we delete the row if we couldn't find the pattern in any row in the
                // children hierarchy
                if ($this->filter($subTable) == 0) {
                    $patternNotFoundInChildren = true;
                }
            } catch (Exception $e) {
                // there is no subtable loaded for example
                $patternNotFoundInChildren = true;
            }

            if ($patternNotFoundInChildren
                && !Pattern::match($this->patternToSearch, $this->patternToSearchQuoted, $row->getColumn($this->columnToFilter), $invertedMatch = false)
            ) {
                $table->deleteRow($key);
            }
        }

        return $table->getRowsCount();
    }
}
