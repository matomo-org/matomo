<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Null.php 482 2008-05-18 17:22:35Z matt $
 * 
 * @package Piwik_DataTable
 */

/**
 * Filter template.
 * You can use it if you want to create a new filter.
 * 
 * @package Piwik_DataTable
 * @subpackage Piwik_DataTable_Filter 
 */
class Piwik_DataTable_Filter_Null extends Piwik_DataTable_Filter
{
	
	public function __construct( $table )
	{
		parent::__construct($table);
		$this->filter();
	}
	
	protected function filter()
	{
		foreach($this->table->getRows() as $key => $row)
		{
		}
	}
}

