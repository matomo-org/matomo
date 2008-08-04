<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Period.php 536 2008-06-27 01:32:25Z matt $
 * 
 * @package Piwik_ArchiveProcessing
 */

/**
 * Handles the archiving process for a period
 * 
 * This class provides generic methods to archive data for a period (week / month / year).
 * 
 * These methods are called by the plugins that do the logic of archiving their own data. \
 * They hook on the event 'ArchiveProcessing_Period.compute'
 * 
 * @package Piwik_ArchiveProcessing
 */
class Piwik_ArchiveProcessing_Period extends Piwik_ArchiveProcessing
{
	/**
	 * Sums all values for the given field names $aNames over the period
	 * See @archiveNumericValuesGeneral for more information
	 * 
	 * @param string|array 
	 * @return Piwik_ArchiveProcessing_Record_Numeric
	 * 
	 */
	public function archiveNumericValuesSum( $aNames )
	{
		return $this->archiveNumericValuesGeneral($aNames, 'sum');
	}
	
	/**
	 * Get the maximum value for all values for the given field names $aNames over the period
	 * See @archiveNumericValuesGeneral for more information
	 * 
	 * @param string|array 
	 * @return Piwik_ArchiveProcessing_Record_Numeric
	 * 
	 */
	public function archiveNumericValuesMax( $aNames )
	{
		return $this->archiveNumericValuesGeneral($aNames, 'max');
	}
	
	/**
	 * Given a list of fields names, the method will fetch all their values over the period, and archive them using the given operation.
	 * 
	 * For example if $operationToApply = 'sum' and $aNames = array('nb_visits', 'sum_time_visit')
	 *  it will sum all values of nb_visits for the period (for example give the number of visits for the month by summing the visits of every day)
	 * 
	 * @param array|string $aNames Array of strings or string containg the field names to select
	 * @param string $operationToApply Available operations = sum, max, min 
	 * @return Piwik_ArchiveProcessing_Record_Numeric Returns the record if $aNames is a string, 
	 *  an array of Piwik_ArchiveProcessing_Record_Numeric indexed by their field names if aNames is an array of strings
	 */
	private function archiveNumericValuesGeneral($aNames, $operationToApply)
	{
		if(!is_array($aNames))
		{
			$aNames = array($aNames);
		}
		
		// fetch the numeric values and apply the operation on them
		$results = array();
		foreach($this->archives as $archive)
		{
			foreach($aNames as $name)
			{
				if(!isset($results[$name]))
				{
					$results[$name] = 0;
				}
				$valueToSum = $archive->getNumeric($name);
				
				if($valueToSum !== false)
				{
					switch ($operationToApply) {
						case 'sum':
							$results[$name] += $valueToSum;	
							break;
						case 'max':
							$results[$name] = max($results[$name], $valueToSum);		
							break;
						case 'min':
							$results[$name] = min($results[$name], $valueToSum);		
							break;
						default:
							throw new Exception("Operation not applicable.");
							break;
					}								
				}
			}
		}
		
		// build the Record Numeric objects
		$records = array();
		foreach($results as $name => $value)
		{
			$records[$name] = new Piwik_ArchiveProcessing_Record_Numeric(
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
	
	
	/**
	 * This powerful method will compute the sum of DataTables over the period for the given fields $aRecordName.
	 * The resulting DataTable will be then added to queue of data to be recorded in the database.
	 * It will usually be called in a plugin that listens to the hook 'ArchiveProcessing_Period.compute'
	 * 
	 * For example if $aRecordName = 'UserCountry_country' the method will select all UserCountry_country DataTable for the period
	 * (eg. the 31 dataTable of the last month), sum them, and create the Piwik_ArchiveProcessing_Record_BlobArray so that
	 * the resulting dataTable is AUTOMATICALLY recorded in the database.
	 * 
	 * 
	 * This method works on recursive dataTable. For example for the 'Actions' it will select all subtables of all dataTable of all the sub periods
	 *  and get the sum.
	 * 
	 * It returns an array that gives information about the "final" DataTable. The array gives for every field name, the number of rows in the 
	 *  final DataTable (ie. the number of distinct LABEL over the period) (eg. the number of distinct keywords over the last month)
	 * 
	 * @param string|array Field name(s) of DataTable to select so we can get the sum 
	 * @return array  array (
	 * 					nameTable1 => number of rows, 
	 *  				nameTable2 => number of rows,
	 * 				)
	 */
	public function archiveDataTable( $aRecordName, $maximumRowsInDataTableLevelZero = null, $maximumRowsInSubDataTable = null )
	{
		if(!is_array($aRecordName))
		{
			$aRecordName = array($aRecordName);
		}
		
		$nameToCount = array();
		foreach($aRecordName as $recordName)
		{
			$table = $this->getRecordDataTableSum($recordName);
			
			$nameToCount[$recordName]['level0'] =  $table->getRowsCount();
			$nameToCount[$recordName]['recursive'] =  $table->getRowsCountRecursive();
			
			$record = new Piwik_ArchiveProcessing_Record_BlobArray($recordName, $table->getSerialized( $maximumRowsInDataTableLevelZero, $maximumRowsInSubDataTable ));
		}
		return $nameToCount;
	}
	
	/**
	 * This method selects all DataTables that have the name $name over the period.
	 * It calls the appropriate methods that sum all these tables together.
	 * The resulting DataTable is returned.
	 *
	 * @param string $name
	 * @return Piwik_DataTable
	 */
	protected function getRecordDataTableSum( $name )
	{
		$table = new Piwik_DataTable;
		foreach($this->archives as $archive)
		{
			$archive->preFetchBlob($name);
			$datatableToSum = $archive->getDataTable($name);
			$archive->loadSubDataTables($name, $datatableToSum);
			$table->addDataTable($datatableToSum);
			$archive->freeBlob($name);
		}
		return $table;
	}
	
	protected function initCompute()
	{
		parent::initCompute();
		$this->archives = $this->loadSubperiodsArchive();
	}

	/**
	 * Returns the ID of the archived subperiods.
	 * 
	 * @return array Array of the idArchive of the subperiods
	 */
	protected function loadSubperiodsArchive()
	{
		$periods = array();
		
		// we first compute every subperiod of the archive
		foreach($this->period->getSubperiods() as $period)
		{
			$archivePeriod = new Piwik_Archive_Single;
			$archivePeriod->setSite( $this->site );
			$archivePeriod->setPeriod( $period );
			$archivePeriod->prepareArchive();
			
			$periods[] = $archivePeriod;
		}
		return $periods;
	}
	
	/**
	 * Main method to process logs for a period. 
	 * The only logic done here is computing the number of visits, actions, etc.
	 * 
	 * All the other reports are computed inside plugins listening to the event 'ArchiveProcessing_Period.compute'.
	 * See some of the plugins for an example.
	 * 
	 * @return void
	 */
	protected function compute()
	{		
		$this->archiveNumericValuesMax( 'max_actions' ); 
		$toSum = array(
			'nb_uniq_visitors', 
			'nb_visits',
			'nb_actions', 
			'sum_visit_length',
			'bounce_count',
		);
		$record = $this->archiveNumericValuesSum($toSum);
		
		$this->isThereSomeVisits = ($record['nb_visits']->value != 0);
		if($this->isThereSomeVisits === false)
		{
			return;
		}
		
		Piwik_PostEvent('ArchiveProcessing_Period.compute', $this);		
	}
	
	/**
	 * Called at the end of the archiving process.
	 * Does some cleaning job in the database.
	 * 
	 * @return void
	 */
	protected function postCompute()
	{
		parent::postCompute();
		
		// we delete records that are now out of date
		// in the case of a period we delete archives that were archived before the end of the period
		// and only if they are at least 1 day old (so we don't delete archives computed today that may be stil valid) 
		$blobTable = $this->tableArchiveBlob->getTableName();
		$numericTable = $this->tableArchiveNumeric->getTableName();
		
		$query = "	DELETE 
					FROM %s
					WHERE period > ? 
						AND DATE(ts_archived) <= date2
						AND date(ts_archived) < date_sub(CURRENT_DATE(), INTERVAL 1 DAY)
					";
		
		Zend_Registry::get('db')->query(sprintf($query, $blobTable), Piwik::$idPeriods['day']);
		Zend_Registry::get('db')->query(sprintf($query, $numericTable), Piwik::$idPeriods['day']);
	}
	
}
