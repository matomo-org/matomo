<?php


class Piwik_ArchiveProcessing_Month extends Piwik_ArchiveProcessing
{
	function __construct()
	{
	}
	
	protected function getRecordNumericSum( $aNames )
	{
		if(!is_array($aNames))
		{
			$aNames = array($aNames);
		}
		
		// fetch the numeric values and sum them
		$results = array();
		foreach($this->archives as $archive)
		{
			$archive->preFetch($aNames);
			
			foreach($aNames as $name)
			{
				if(!isset($results[$name]))
				{
					$results[$name] = 0;
				}
				$valueToSum = $archive->getNumeric($name);
				
				if($valueToSum !== false)
				{
					$results[$name] += $valueToSum;					
				}
			}
		}
		
		// build the Record Numeric objects
		$records = array();
		foreach($results as $name => $value)
		{
			$records[$name] = new Piwik_Archive_Processing_Record_Numeric(
													$name, 
													$value
												);
		}
		
		// if asked for only one field to sum
		if(count($records) == 1)
		{
			return $records[$name];
		}
		
		// returns the array of records once summed
		return $records;
	}
	
	protected function getRecordDataTableSum( $name )
	{
		$table = new Piwik_DataTable;
		foreach($this->archives as $archive)
		{
			$datatableToSum = $archive->getDataTable($name);
			$table->addDataTable($datatableToSum);
		}
		return $table;
	}
	
	protected function compute()
	{		
		$this->archives = $this->archivesSubperiods;
		
		echo $this->getRecordNumericSum('nb_visits');
		$records = $this->getRecordNumericSum(array('nb_uniq_visitors', 'nb_actions'));
		foreach($records as $rec) echo $rec."<br>";
		
		echo $this->getRecordDataTableSum('UserSettings_browserType');
//		Piwik_PostEvent('ArchiveProcessing_Month.compute', $this);

//		$nbVisits = new Piwik_ArchiveProcessing_Record('nb_visits',
//						new Piwik_ArchiveProcessing_Record_NumericSum)
	}
	
}
?>
