<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: ColumnCallbackAddMetadata.php 515 2008-06-08 20:03:21Z matt $
 * 
 * @package Piwik_DataTable
 */


/**
 * Add a new 'metadata' column to the table based on the value resulting 
 * from a callback function with the parameter being another column's value
 * 
 * For example from the "label" column we can to create an "icon" 'metadata' column 
 * with the icon URI built from the label (LINUX => UserSettings/icons/linux.png)
 * 
 * @package Piwik_DataTable
 * @subpackage Piwik_DataTable_Filter 
 */

class Piwik_DataTable_Filter_ColumnCallbackAddMetadata extends Piwik_DataTable_Filter
{
	private $columnToRead;
	private $functionToApply;
	private $metadataToAdd;
	
	public function __construct( $table, $columnToRead, $metadataToAdd, $functionToApply )
	{
		parent::__construct($table);
		$this->functionToApply = $functionToApply;
		$this->columnToRead = $columnToRead;
		$this->metadataToAdd = $metadataToAdd;
		$this->filter();
	}
	
	protected function filter()
	{
		foreach($this->table->getRows() as $key => $row)
		{
			$oldValue = $row->getColumn($this->columnToRead);
			$newValue = call_user_func( $this->functionToApply, $oldValue);
			$row->addMetadata($this->metadataToAdd, $newValue);
		}
	}
}

