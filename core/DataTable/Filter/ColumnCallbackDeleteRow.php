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
 * Delete all rows for which a given function returns false for a given column.
 * 
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Filter_ColumnCallbackDeleteRow extends Piwik_DataTable_Filter
{
	private $columnToFilter;
	private $function;
	
	public function __construct( $table, $columnToFilter, $function )
	{
		parent::__construct($table);
		$this->function = $function;
		$this->columnToFilter = $columnToFilter;
		$this->filter();
	}
	
	protected function filter()
	{
		foreach($this->table->getRows() as $key => $row)
		{
			$columnValue = $row->getColumn($this->columnToFilter);
			if( $columnValue !== false 
				&& !call_user_func( $this->function, $columnValue))
			{
				$this->table->deleteRow($key);
			}
		}
	}
}
