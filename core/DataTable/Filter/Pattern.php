<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * Delete all rows for which the given $columnToFilter do not contain the $patternToSearch
 * This filter is to be used on columns containing strings. 
 * Exemple: fron the keyword report, keep only the rows for which the label contains "piwik"
 * 
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Filter_Pattern extends Piwik_DataTable_Filter
{
	private $columnToFilter;
	private $patternToSearch;
	private $patternToSearchQuoted;
	
	public function __construct( $table, $columnToFilter, $patternToSearch )
	{
		parent::__construct($table);
		$this->patternToSearch = $patternToSearch;
		$this->patternToSearchQuoted = self::getPatternQuoted($patternToSearch);
		$this->columnToFilter = $columnToFilter;
		$this->filter();
	}
	
	static public function getPatternQuoted( $pattern )
	{
		return '/'. str_replace('/', '\/', $pattern) .'/';
	}
	
	/*
	 * Performs case insensitive match
	 */
	static public function match($pattern, $patternQuoted, $string)
	{
		return @preg_match($patternQuoted . "i",  $string) == 1;
	}
	
	protected function filter()
	{
		foreach($this->table->getRows() as $key => $row)
		{
			//instead search must handle
			// - negative search with -piwik
			// - exact match with ""
			// see (?!pattern) 	A subexpression that performs a negative lookahead search, which matches the search string at any point where a string not matching pattern begins. 
			if( !self::match($this->patternToSearch, $this->patternToSearchQuoted, $row->getColumn($this->columnToFilter)))
			{
				$this->table->deleteRow($key);
			}
		}
	}
}
