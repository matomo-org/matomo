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
 * The DataTable_Simple is used to provide a very simple way to create simple DataGrid.
 * 
 * A DataTable Simple basically is an array of name => value 
 * 
 * Returning a DataTable_Simple from a plugin API Call has huge advantages:
 * - the generic filters can be applied automatically (offset, limit, pattern search, sort, etc.)
 * - the renderer can be applied (XML, PHP, HTML, etc.)
 * 
 * So you don't have to write specific renderer for your data, it is already available in all the formats supported natively by Piwik.
 * 
 * NB: A DataTable_Simple actually is a DataTable with 2 columns: 'label' and 'value'.
 * 
 * @package Piwik_DataTable
 */
class Piwik_DataTable_Simple extends Piwik_DataTable
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Loads in the DataTable the array information
	 * @param array Array containing the rows information
	 * 		array(
	 * 			'Label row 1' => Value row 1,
	 * 			'Label row 2' => Value row 2,
	 * 	)
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
	
	function getColumn( $label )
	{
		return $this->getRowFromLabel($label)->getColumn('value');
	}
}


