<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * Delete all rows for which a given function returns false for a given column.
 * 
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Filter_ColumnCallbackDeleteRow extends Piwik_DataTable_Filter
{
	private $columnToFilter;
	private $function;

	/**
	 * @param Piwik_DataTable  $table
	 * @param string           $columnToFilter
	 * @param callback         $function
	 */
	public function __construct( $table, $columnToFilter, $function )
	{
		parent::__construct($table);
		$this->function = $function;
		$this->columnToFilter = $columnToFilter;
	}

	/**
	 * Filters the given data table
	 *
	 * @param Piwik_DataTable  $table
	 */
	public function filter($table)
	{
		foreach($table->getRows() as $key => $row)
		{
			$columnValue = $row->getColumn($this->columnToFilter);
			if( !call_user_func( $this->function, $columnValue))
			{
				$table->deleteRow($key);
			}
			$this->filterSubTable($row);
		}
	}
}
