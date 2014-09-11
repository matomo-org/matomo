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
 * Deletes every row for which a specific column does not match a supplied regex pattern.
 *
 * **Example**
 *
 *     // filter out all rows whose labels doesn't start with piwik
 *     $dataTable->filter('Pattern', array('label', '^piwik'));
 *
 * @api
 */
class Pattern extends BaseFilter
{
    private $columnToFilter;
    private $patternToSearch;
    private $patternToSearchQuoted;
    private $invertedMatch;

    /**
     * Constructor.
     *
     * @param DataTable $table The table to eventually filter.
     * @param string $columnToFilter The column to match with the `$patternToSearch` pattern.
     * @param string $patternToSearch The regex pattern to use.
     * @param bool $invertedMatch Whether to invert the pattern or not. If true, will remove
     *                            rows if they match the pattern.
     */
    public function __construct($table, $columnToFilter, $patternToSearch, $invertedMatch = false)
    {
        parent::__construct($table);
        $this->patternToSearch = $patternToSearch;
        $this->patternToSearchQuoted = self::getPatternQuoted($patternToSearch);
        $this->columnToFilter = $columnToFilter;
        $this->invertedMatch = $invertedMatch;
    }

    /**
     * Helper method to return the given pattern quoted
     *
     * @param string $pattern
     * @return string
     * @ignore
     */
    public static function getPatternQuoted($pattern)
    {
        return '/' . str_replace('/', '\/', $pattern) . '/';
    }

    /**
     * Performs case insensitive match
     *
     * @param string $patternQuoted
     * @param string $string
     * @param bool $invertedMatch
     * @return int
     * @ignore
     */
    public static function match($patternQuoted, $string, $invertedMatch = false)
    {
        return preg_match($patternQuoted . "i", $string) == 1 ^ $invertedMatch;
    }

    /**
     * See {@link Pattern}.
     *
     * @param DataTable $table
     */
    public function filter($table)
    {
        foreach ($table->getRows() as $key => $row) {
            //instead search must handle
            // - negative search with -piwik
            // - exact match with ""
            // see (?!pattern) 	A subexpression that performs a negative lookahead search, which matches the search string at any point where a string not matching pattern begins.
            $value = $row->getColumn($this->columnToFilter);
            if ($value === false) {
                $value = $row->getMetadata($this->columnToFilter);
            }
            if (!self::match($this->patternToSearchQuoted, $value, $this->invertedMatch)) {
                $table->deleteRow($key);
            }
        }
    }
}
