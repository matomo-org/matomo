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
 * Deletes a column from a datatable
 * 
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Filter_ColumnDelete extends Piwik_DataTable_Filter
{
	private $columnToFilter;
	private $functionToApply;
	
	public function __construct( $table, $columnToDelete )
	{
		parent::__construct($table);
		$this->columnToDelete = $columnToDelete;
		$this->filter();
	}
	
	protected function filter()
	{
		$this->table->deleteColumn($this->columnToDelete);
	}
	
}
