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
 * The DataTable_Simple is used to provide an easy way to create simple DataGrid.
 * A DataTable_Simple actually is a DataTable with 2 columns: 'label' and 'value'.
 * 
 * It is usually best to return a DataTable_Simple instead of 
 * a PHP array (or other custom data structure) in API methods:
 * - the generic filters can be applied automatically (offset, limit, pattern search, sort, etc.)
 * - the renderer can be applied (XML, PHP, HTML, etc.)
 * So you don't have to write specific renderer for your data, it is already available in all the formats supported natively by Piwik.
 * 
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Simple extends Piwik_DataTable
{
	/**
	 * Loads (append) in the DataTable the array information
	 *
	 * @param array Array containing the rows information
	 * 		array(
	 * 			'Label row 1' => Value row 1,
	 * 			'Label row 2' => Value row 2,
	 * 	)
	 */
	function addRowsFromArray($array)
	{
		$this->addRowsFromSimpleArray(array($array));
	}
	
	function setColumn($columnName, $value)
	{
		$this->getLastRow()->setColumn($columnName, $value);
	}
}
