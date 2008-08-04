<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: AddConstantMetadata.php 519 2008-06-09 01:59:24Z matt $
 * 
 * @package Piwik_DataTable
 */

/**
 * Add a new metadata column to the table.
 * 
 * This is used to add a column containing the logo width and height of the countries flag icons.
 * This value is fixed for all icons so we simply add the same value for all rows.
 *  
 * @package Piwik_DataTable
 * @subpackage Piwik_DataTable_Filter 
 */
class Piwik_DataTable_Filter_AddConstantMetadata extends Piwik_DataTable_Filter
{
	private $metadataToRead;
	private $functionToApply;
	private $metadataToAdd;
	
	public function __construct( $table, $metadataName, $metadataValue )
	{
		parent::__construct($table);
		$this->name = $metadataName;
		$this->value = $metadataValue;
		$this->filter();
	}
	
	protected function filter()
	{
		foreach($this->table->getRows() as $row)
		{
			$row->addMetadata($this->name, $this->value);
		}
	}
}

