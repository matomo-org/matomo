<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_DataTable
 */

/**
 * Delete all rows for which the given $columnToFilter do not equal $patternToSearch
 * This filter can be used on both integer and string columns. 
 * You can pass an array of integers in $patternToSearch parameter.
 * 
 * @package Piwik_DataTable
 * @subpackage Piwik_DataTable_Filter 
 */
class Piwik_DataTable_Filter_ExactMatch extends Piwik_DataTable_Filter
{
	private $columnToFilter;
	private $patternToSearch;
	
	public function __construct( $table, $columnToFilter, $patternToSearch )
	{
		parent::__construct($table);
		$this->patternToSearch = $patternToSearch;
		$this->columnToFilter = $columnToFilter;
		$this->filter();
	}
	
	protected function filter()
	{
		foreach($this->table->getRows() as $key => $row)
		{
			if( is_array($this->patternToSearch) )
			{
				if( in_array($row->getColumn($this->columnToFilter), $this->patternToSearch) == false )
				{
					$this->table->deleteRow($key);
				}
			}
			else if( $row->getColumn($this->columnToFilter) != $this->patternToSearch )
			{
				$k = $row->getColumn($this->columnToFilter);
				$this->table->deleteRow($key);
			}
		}
	}
}

