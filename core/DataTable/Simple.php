<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Simple.php 519 2008-06-09 01:59:24Z matt $
 * 
 * @package Piwik_DataTable
 */

/**
 * The DataTable_Simple is used to provide an easy way to create simple DataGrid.
 * A DataTable_Simple actually is a DataTable with 2 columns: 'label' and 'value'.
 * 
 * It is usually best to return a DataTable_Simple instead of 
 * a PHP array (or other custom data structure) in API methods:
 * - the generic filters can be applied automatically (offset, limit, pattern search, sort, etc.)
 * - the renderer can be applied (XML, PHP, HTML, etc.)
 * So you don't have to write specific renderer for your data, it is already available in all the formats supported natively by Piwik.
 * 
 * @package Piwik_DataTable
 */
class Piwik_DataTable_Simple extends Piwik_DataTable
{
	/**
	 * Loads in the DataTable the array information
	 * @param array Array containing the rows information
	 * 		array(
	 * 			'Label row 1' => Value row 1,
	 * 			'Label row 2' => Value row 2,
	 * 	)
	 * @return void
	 */
	function loadFromArray($array)
	{
		foreach($array as $label => $value)
		{
			$row = new Piwik_DataTable_Row;
			$row->addColumn('label', $label);
			$row->addColumn('value', $value);
			$this->addRow($row);
		}
	}
	
	/**
	 * Returns the 'value' column of the row that has a label '$label'. 
	 *
	 * @param string Label of the row we want the value
	 * @return false|mixed The 'value' column of the row labelled $label
	 */
	function getColumn( $label )
	{
		$row = $this->getRowFromLabel($label);
		if($row === false)
		{
			return false;
		}
		return $row->getColumn('value');
	}
}
