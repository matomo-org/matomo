<?php

/**
 * Delete all rows of when a given function returns false for a given column 
 */
class Piwik_DataTable_Filter_ReplaceColumnNames extends Piwik_DataTable_Filter
{
	/*
	 * old column name => new column name
	 */
	protected $mappingToApply = array(
				Piwik_Archive::INDEX_NB_UNIQ_VISITORS 	=> 'nb_unique_visitors',
				Piwik_Archive::INDEX_NB_VISITS			=> 'nb_visits',
				Piwik_Archive::INDEX_NB_ACTIONS			=> 'nb_actions',
				Piwik_Archive::INDEX_MAX_ACTIONS		=> 'max_actions',
				Piwik_Archive::INDEX_SUM_VISIT_LENGTH	=> 'sum_visit_length',
				Piwik_Archive::INDEX_BOUNCE_COUNT		=> 'bounce_count',
			);
				
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
		foreach($this->table->getRows() as $key => $row)
		{
			$columns = $row->getColumns();
			
			foreach($this->mappingToApply as $oldName => $newName)
			{
				// if the old column is there
				if(isset($columns[$oldName]))
				{
					$columns[$newName] = $columns[$oldName];
					unset($columns[$oldName]);
				}
			}
			
			$row->setColumns($columns);
		}
	}
}

