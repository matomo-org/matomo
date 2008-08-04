<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: ReplaceColumnNames.php 519 2008-06-09 01:59:24Z matt $
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
	/*
	 * Old column name => new column name
	 */
	protected $mappingToApply = array(
				Piwik_Archive::INDEX_NB_UNIQ_VISITORS 	=> 'nb_uniq_visitors',
				Piwik_Archive::INDEX_NB_VISITS			=> 'nb_visits',
				Piwik_Archive::INDEX_NB_ACTIONS			=> 'nb_actions',
				Piwik_Archive::INDEX_MAX_ACTIONS		=> 'max_actions',
				Piwik_Archive::INDEX_SUM_VISIT_LENGTH	=> 'sum_visit_length',
				Piwik_Archive::INDEX_BOUNCE_COUNT		=> 'bounce_count',
			);
	
	/**
	 * @param DataTable Table
	 * @param array Mapping to apply. Must have the format 	
	 * 				array( 	OLD_COLUMN_NAME => NEW_COLUMN NAME,
	 * 						OLD_COLUMN_NAME2 => NEW_COLUMN NAME2,
	 * 					)
	 */
	public function __construct( $table, $mappingToApply = null )
	{
		parent::__construct($table);
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
			$this->renameColumns($row);
			
			try {
				$subTable = Piwik_DataTable_Manager::getInstance()->getTable( $row->getIdSubDataTable() );
				$this->filterTable($subTable);
			} catch(Exception $e){
				// case idSubTable == null, or if the table is not loaded in memory
			}
		}
	}
	
	protected function renameColumns($row) 
	{
		$columns = $row->getColumns();
		foreach($this->mappingToApply as $oldName => $newName)
		{
			if(isset($columns[$oldName]))
			{
				$columns[$newName] = $columns[$oldName];
				unset($columns[$oldName]);
			}
		}
		$row->setColumns($columns);
	}
}

