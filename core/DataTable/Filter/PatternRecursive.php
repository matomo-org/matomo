<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: PatternRecursive.php 519 2008-06-09 01:59:24Z matt $
 * 
 * @package Piwik_DataTable
 */

/**
 * Delete all rows for which 
 * - the given $columnToFilter do not contain the $patternToSearch 
 * - AND all the subTables associated to this row do not contain the $patternToSearch
 * 
 * This filter is to be used on columns containing strings. 
 * Exemple: from the pages viewed report, keep only the rows that contain "piwik" or for which a subpage contains "piwik".
 * 
 * @package Piwik_DataTable
 * @subpackage Piwik_DataTable_Filter 
 */
class Piwik_DataTable_Filter_PatternRecursive extends Piwik_DataTable_Filter
{
	private $columnToFilter;
	private $patternToSearch;
	
	public function __construct( $table, $columnToFilter, $patternToSearch )
	{
		parent::__construct($table);
		$this->patternToSearch = $patternToSearch;//preg_quote($patternToSearch);
		$this->columnToFilter = $columnToFilter;
		$this->filter();
	}
	
	protected function filter( $table = null )
	{
		if(is_null($table))
		{
			$table = $this->table;
		}
		$rows = $table->getRows();
		
		foreach($rows as $key => $row)
		{
			// A row is deleted if
			// 1 - its label doesnt contain the pattern 
			// AND 2 - the label is not found in the children
			$patternNotFoundInChildren = false;
			
			try{
				$idSubTable = $row->getIdSubDataTable();
				$subTable = Piwik_DataTable_Manager::getInstance()->getTable($idSubTable);
				
				// we delete the row if we couldn't find the pattern in any row in the 
				// children hierarchy
				if( $this->filter($subTable) == 0 )
				{
					$patternNotFoundInChildren = true;
				}
			} catch(Exception $e) {
				// there is no subtable loaded for example
				$patternNotFoundInChildren = true;
			}

			if( $patternNotFoundInChildren
				&& (stripos($row->getColumn($this->columnToFilter), $this->patternToSearch) === false)	
			)
			{
				$table->deleteRow($key);
			}
		}
		
		return $table->getRowsCount();
	}
}

