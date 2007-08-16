<?php
class Piwik_ArchiveProcessing_Day extends Piwik_ArchiveProcessing
{
	
	function __construct()
	{
		$this->db = Zend_Registry::get('db');
	}
	
	/**
	 * Reads the log and compute the essential reports.
	 * All the non essential reports are computed inside plugins.
	 * 
	 * One record is either a numeric value or a BLOB which is 
	 * usually a compressed serialized DataTable.
	 *  
	 * At the end of the process we add a fake record called 'done' that flags
	 * the archive as being valid.
	 * 
	 * 
	 * 
	 */
	protected function compute()
	{
		$query = "SELECT 	count(distinct visitor_idcookie) as nb_uniq_visitors, 
							count(*) as nb_visits,
							sum(visit_total_actions) as nb_actions, 
							max(visit_total_actions) as max_actions, 
							sum(visit_total_time) as sum_visit_length,
							sum(case visit_total_actions when 1 then 1 else 0 end) as bounce_count 
					FROM ".$this->logTable."
					WHERE visit_server_date = ?
						AND idsite = ?
					GROUP BY visit_server_date
				 ";
		$row = $this->db->fetchRow($query, array($this->strDateStart,$this->idsite ) );
		
		if($row === false)
		{
			return;
		}
	
		foreach($row as $name => $value)
		{
			$record = new Piwik_Archive_Processing_Record_Numeric($name, $value);
		}
		Piwik::log($row);
		
				
		Piwik_PostEvent('ArchiveProcessing_Day.compute', $this);
	}
	
	public function getSimpleDataTableFromSelect($select, $labelCount)
	{
		$query = "SELECT $select 
			 	FROM ".$this->logTable." 
			 	WHERE visit_server_date = ?
			 		AND idsite = ?";
		$data = $this->db->fetchRow($query, array( $this->strDateStart, $this->idsite ));
		
		foreach($data as $label => &$count)
		{
			$count = array($labelCount => $count);
		}
		$table = new Piwik_DataTable;
		$table->loadFromArrayLabelIsKey($data);
		return $table;
	}
	
	
	public function getDataTableInterestForLabel( $label )
	{
		$query = "SELECT 	$label as label,
							count(distinct visitor_idcookie) as nb_uniq_visitors, 
							count(*) as nb_visits,
							sum(visit_total_actions) as nb_actions, 
							max(visit_total_actions) as max_actions, 
							sum(visit_total_time) as sum_visit_length,
							sum(case visit_total_actions when 1 then 1 else 0 end) as bounce_count
				 FROM ".$this->logTable."
					WHERE visit_server_date = ?
						AND idsite = ?
					GROUP BY label";
		$query = $this->db->query($query, array( $this->strDateStart, $this->idsite ) );
		
		$interest = array();
		while($rowBefore = $query->fetch())
		{
			$row = array(
				Piwik_Archive::INDEX_NB_UNIQ_VISITORS 	=> $rowBefore['nb_uniq_visitors'], 
				Piwik_Archive::INDEX_NB_VISITS 			=> $rowBefore['nb_visits'], 
				Piwik_Archive::INDEX_NB_ACTIONS 		=> $rowBefore['nb_actions'], 
				Piwik_Archive::INDEX_MAX_ACTIONS 		=> $rowBefore['max_actions'], 
				Piwik_Archive::INDEX_SUM_VISIT_LENGTH 	=> $rowBefore['sum_visit_length'], 
				Piwik_Archive::INDEX_BOUNCE_COUNT 		=> $rowBefore['bounce_count'],
				'label'									=> $rowBefore['label']
				);
				
			if(!isset($interest[$row['label']])) $interest[$row['label']]= $this->getNewInterestRow();
			$this->updateInterestStats( $row, $interest[$row['label']]);
		}
		
		$table = new Piwik_DataTable;
		$table->loadFromArrayLabelIsKey($interest);
		return $table;
	}
	
	static public function generateDataTable( $table )
	{
		$dataTableToReturn = new Piwik_DataTable;
		
		foreach($table as $label => $maybeDatatableRow)
		{
			// case the aInfo is a subtable-like array
			// it means that we have to go recursively and process it
			// then we build the row that is an aggregate of all the children
			// and we associate this row to the subtable
			if( !($maybeDatatableRow instanceof Piwik_DataTable_Row) )
			{
				$subTable = self::generateDataTable($maybeDatatableRow);
				$row = new Piwik_DataTable_Row_ActionTableSummary( $subTable );
				$row->addColumn('label', $label);
			}
			// if aInfo is a simple Row we build it
			else
			{
				$row = $maybeDatatableRow;
			}
			
			$dataTableToReturn->addRow($row);
		}
		
		return $dataTableToReturn;
	}
	
	public function getDataTableSerialized( $arrayLevel0 )
	{
		$table = new Piwik_DataTable;
		$table->loadFromArrayLabelIsKey($arrayLevel0);
		$toReturn = $table->getSerialized();
		return $toReturn;
	}
	
	
	public function getDataTablesSerialized( $arrayLevel0, $subArrayLevel1ByKey)
	{
		$tablesByLabel = array();

		foreach($arrayLevel0 as $label => $aAllRowsForThisLabel)
		{
			$table = new Piwik_DataTable;
			$table->loadFromArrayLabelIsKey($aAllRowsForThisLabel);
			$tablesByLabel[$label] = $table;
		}
		$parentTableLevel0 = new Piwik_DataTable;
		$parentTableLevel0->loadFromArrayLabelIsKey($subArrayLevel1ByKey, $tablesByLabel);

//		echo $parentTableLevel0;
		$toReturn = $parentTableLevel0->getSerialized();

		return $toReturn;
	}
	
	public function getNewInterestRow()
	{
		return array(	Piwik_Archive::INDEX_NB_UNIQ_VISITORS 	=> 0, 
						Piwik_Archive::INDEX_NB_VISITS 			=> 0, 
						Piwik_Archive::INDEX_NB_ACTIONS 		=> 0, 
						Piwik_Archive::INDEX_MAX_ACTIONS 		=> 0, 
						Piwik_Archive::INDEX_SUM_VISIT_LENGTH 	=> 0, 
						Piwik_Archive::INDEX_BOUNCE_COUNT 		=> 0
						);
	}
	
	public function updateInterestStats( $newRowToAdd, &$oldRowToUpdate)
	{		
		$oldRowToUpdate[Piwik_Archive::INDEX_NB_UNIQ_VISITORS]	+= $newRowToAdd[Piwik_Archive::INDEX_NB_UNIQ_VISITORS];
		$oldRowToUpdate[Piwik_Archive::INDEX_NB_VISITS] 		+= $newRowToAdd[Piwik_Archive::INDEX_NB_VISITS];
		$oldRowToUpdate[Piwik_Archive::INDEX_NB_ACTIONS] 		+= $newRowToAdd[Piwik_Archive::INDEX_NB_ACTIONS];
		$oldRowToUpdate[Piwik_Archive::INDEX_MAX_ACTIONS] 		 = max($newRowToAdd[Piwik_Archive::INDEX_MAX_ACTIONS], $oldRowToUpdate[Piwik_Archive::INDEX_MAX_ACTIONS]);
		$oldRowToUpdate[Piwik_Archive::INDEX_SUM_VISIT_LENGTH]	+= $newRowToAdd[Piwik_Archive::INDEX_SUM_VISIT_LENGTH];
		$oldRowToUpdate[Piwik_Archive::INDEX_BOUNCE_COUNT] 		+= $newRowToAdd[Piwik_Archive::INDEX_BOUNCE_COUNT];
	}
}
?>
