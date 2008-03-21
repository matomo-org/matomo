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
 * Add a new detail column to the table.
 * 
 * This is used to add a column containing the logo width and height of the countries flag icons.
 * This value is fixed for all icons so we simply add the same value for all rows.
 *  
 * @package Piwik_DataTable
 * @subpackage Piwik_DataTable_Filter 
 */
class Piwik_DataTable_Filter_AddConstantDetail extends Piwik_DataTable_Filter
{
	private $detailToRead;
	private $functionToApply;
	private $detailToAdd;
	
	
	public function __construct( $table, $detailName, $detailValue )
	{
		parent::__construct($table);
		$this->name = $detailName;
		$this->value = $detailValue;
		$this->filter();
	}
	
	protected function filter()
	{
		foreach($this->table->getRows() as $row)
		{
			$row->addDetail($this->name, $this->value);
		}
	}
}

