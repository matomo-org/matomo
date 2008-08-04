<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: DataTableSummary.php 519 2008-06-09 01:59:24Z matt $
 * 
 * @package Piwik_DataTable
 */

/**
 * This class creates a row from a given DataTable. 
 * The row contains 
 * - for each numeric column, the returned "summary" column is the sum of all the subRows
 * - for every other column, it is ignored and will not be in the "summary row"
 * 
 * @see Piwik_DataTable_Row::sumRow() for more information on the algorithm
 * 
 * @package Piwik_DataTable
 * @subpackage Piwik_DataTable_Row
 */
class Piwik_DataTable_Row_DataTableSummary extends Piwik_DataTable_Row
{
	function __construct($subTable)
	{
		parent::__construct();
		foreach($subTable->getRows() as $row)
		{
			$this->sumRow($row);
		}
	}
}
