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
 * This filter replaces column names using a mapping table that maps from the old name to the new name.
 * 
 * Why this filter?
 * For saving bytes in the database, you can change all the columns labels by an integer value.
 * Exemple instead of saving 10000 rows with the column name 'nb_uniq_visitors' which would cost a lot of memory,
 * we map it to the integer 1 before saving in the DB.
 * After selecting the DataTable from the DB though, you need to restore back the real names so that
 * it shows nicely in the report (XML for example).
 * 
 * You can specify the mapping array to apply in the constructor.
 * 
 * @package Piwik_DataTable
 * @subpackage Piwik_DataTable_Filter 
 */
class Piwik_DataTable_Filter_ReplaceColumnNames extends Piwik_DataTable_Filter
{
	protected $mappingToApply;
	
	/**
	 * @param DataTable Table
	 * @param array Mapping to apply. Must have the format 	
	 * 				array( 	OLD_COLUMN_NAME => NEW_COLUMN NAME,
	 * 						OLD_COLUMN_NAME2 => NEW_COLUMN NAME2,
	 * 					)
	 */
	public function __construct( $table, $recursive = false, $mappingToApply = null )
	{
		parent::__construct($table);
		$this->mappingToApply = Piwik_Archive::$mappingFromIdToName;
		$this->applyFilterRecursively = $recursive;
		if(!is_null($mappingToApply))
		{
			$this->mappingToApply = $mappingToApply;
		}
		$this->filter();
	}
	
	protected function filter()
	{
		$this->filterTable($this->table);
	}
	
	protected function filterTable($table)
	{
		foreach($table->getRows() as $key => $row)
		{
			$oldColumns = $row->getColumns();
			$newColumns = $this->getRenamedColumns($oldColumns);
			$row->setColumns( $newColumns );
			if($this->applyFilterRecursively)
			{
				try {
					$subTable = Piwik_DataTable_Manager::getInstance()->getTable( $row->getIdSubDataTable() );
					$this->filterTable($subTable);
				} catch(Exception $e){
					// case idSubTable == null, or if the table is not loaded in memory
				}
			}
		}
	}
	
	protected function getRenamedColumns($columns) 
	{
		$newColumns = array();
		foreach($columns as $columnName => $columnValue)
		{
			if(isset(Piwik_Archive::$mappingFromIdToName[$columnName]))
			{
				$columnName = Piwik_Archive::$mappingFromIdToName[$columnName];
				if($columnName == 'goals')
				{
					$newSubColumns = array();
					foreach($columnValue as $idGoal => $goalValues)
					{
						foreach($goalValues as $id => $goalValue)
						{
							$subColumnName = Piwik_Archive::$mappingFromIdToNameGoal[$id];
							$newSubColumns['idgoal='.$idGoal][$subColumnName] = $goalValue;
						}
					}
					$columnValue = $newSubColumns;
				}
			}
			$newColumns[$columnName] = $columnValue;
		}
		return $newColumns;
	}
}

