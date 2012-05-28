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
 * Deletes a column from a data table
 * 
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Filter_ColumnDelete extends Piwik_DataTable_Filter
{
	/**
	 * Column that should be removed
	 *
	 * @var string
	 */
	private $columnToDelete;

	/**
	 * Constructor - sets the column to be deleted
	 *
	 * @param Piwik_DataTable  $table           data table
	 * @param string           $columnToDelete  column to delete
	 */
	public function __construct( $table, $columnToDelete )
	{
		parent::__construct($table);
		$this->columnToDelete = $columnToDelete;
	}

	/**
	 * Executes the filter and removes the specified column in the given data table
	 *
	 * @param Piwik_DataTable  $table
	 */
	public function filter($table)
	{
		$table->deleteColumn($this->columnToDelete);
	}
	
}
