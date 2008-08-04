<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: ColumnCallbackReplace.php 444 2008-04-11 13:38:22Z johmathe $
 * 
 * @package Piwik_DataTable
 */

/**
 * Replace a column value with a new value resulting 
 * from the function called with the column's value
 * 
 * @package Piwik_DataTable
 * @subpackage Piwik_DataTable_Filter 
 */
class Piwik_DataTable_Filter_ColumnCallbackReplace extends Piwik_DataTable_Filter
{
	private $columnToFilter;
	private $functionToApply;
	
	public function __construct( $table, $columnToFilter, $functionToApply )
	{
		parent::__construct($table);
		$this->functionToApply = $functionToApply;
		$this->columnToFilter = $columnToFilter;
		$this->filter();
	}
	
	protected function filter()
	{
		foreach($this->table->getRows() as $key => $row)
		{
			$oldValue = $row->getColumn($this->columnToFilter);
			$newValue = call_user_func( $this->functionToApply, $oldValue);
			$row->setColumn($this->columnToFilter, $newValue);
		}
	}
}

