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

/**
 * Delete all rows for which the given $columnToFilter do not contain the $patternToSearch
 * This filter is to be used on columns containing strings.
 * Example: from the keyword report, keep only the rows for which the label contains "piwik"
 *
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Filter_Pattern extends Piwik_DataTable_Filter
{
    private $columnToFilter;
    private $patternToSearch;
    private $patternToSearchQuoted;
    private $invertedMatch;

    /**
     * @param Piwik_DataTable $table
     * @param string $columnToFilter
     * @param string $patternToSearch
     * @param bool $invertedMatch
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
     */
    static public function getPatternQuoted($pattern)
    {
        return '/' . str_replace('/', '\/', $pattern) . '/';
    }

    /**
     * Performs case insensitive match
     *
     * @param string $pattern
     * @param string $patternQuoted
     * @param string $string
     * @param bool $invertedMatch
     * @return int
     */
    static public function match($pattern, $patternQuoted, $string, $invertedMatch)
    {
        return @preg_match($patternQuoted . "i", $string) == 1 ^ $invertedMatch;
    }

    /**
     * @param Piwik_DataTable $table
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
            if (!self::match($this->patternToSearch, $this->patternToSearchQuoted, $value, $this->invertedMatch)) {
                $table->deleteRow($key);
            }
        }
    }
}
