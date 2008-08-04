<?php
require_once "Archive/Array.php";

class Piwik_Archive_Array_IndexedByDate extends Piwik_Archive_Array {
	
	/**
	 * Builds an array of Piwik_Archive of a given date range
	 *
	 * @param Piwik_Site $oSite 
	 * @param string $strPeriod eg. 'day' 'week' etc.
	 * @param string $strDate A date range, eg. 'last10', 'previous5' or 'YYYY-MM-DD,YYYY-MM-DD'
	 */
	function __construct(Piwik_Site $oSite, $strPeriod, $strDate)
	{
		$rangePeriod = new Piwik_Period_Range($strPeriod, $strDate);
		foreach($rangePeriod->getSubperiods() as $subPeriod)
		{
			$startDate = $subPeriod->getDateStart();
			$archive = Piwik_Archive::build($oSite->getId(), $strPeriod, $startDate );
			$archive->prepareArchive();
			$timestamp = $archive->getTimestampStartDate();
			$this->archives[$timestamp] = $archive;
		}
		ksort( $this->archives );
	}
	
	protected function getIndexName()
	{
		return 'date';
	}
	
	protected function loadMetadata(Piwik_DataTable_Array $table, $archive)
	{
		$table->metadata[$archive->getPrettyDate()] = array( 
				'timestamp' => $archive->getTimestampStartDate(),
				'site' => $archive->getSite(),
			);
	}
	protected function getDataTableLabelValue( $archive )
	{
		return $archive->getPrettyDate();
	}
	
	/**
	 * Given a list of fields defining numeric values, it will return a Piwik_DataTable_Array
	 * which is an array of Piwik_DataTable_Simple, ordered by chronological order
	 *
	 * @param array|string $fields array( fieldName1, fieldName2, ...)  Names of the mysql table fields to load
	 * @return Piwik_DataTable_Array
	 */
	public function getDataTableFromNumeric( $fields )
	{
		if(!is_array($fields))
		{
			$fields = array($fields);
		}
		
		$inName = "'" . implode("', '",$fields) . "'";
		
		// we select in different shots
		// one per distinct table (case we select last 300 days, maybe we will  select from 10 different tables)
		$queries = array();
		foreach($this->archives as $archive) 
		{
			if(!$archive->isThereSomeVisits)
			{
				continue;
			}
			
			$table = $archive->archiveProcessing->getTableArchiveNumericName();

			// for every query store IDs
			$queries[$table][] = $archive->getIdArchive();
		}
		// we select the requested value
		$db = Zend_Registry::get('db');
		
		// date => array( 'field1' =>X, 'field2'=>Y)
		// date2 => array( 'field1' =>X2, 'field2'=>Y2)		
		
		$arrayValues = array();
		foreach($queries as $table => $aIds)
		{
			$inIds = implode(', ', $aIds);
			$sql = "SELECT value, name, idarchive, UNIX_TIMESTAMP(date1) as timestamp
									FROM $table
									WHERE idarchive IN ( $inIds )
										AND name IN ( $inName )";

			$values = $db->fetchAll($sql);
			
			foreach($values as $value)
			{
				$arrayValues[$value['timestamp']][$value['name']] = $value['value'];
			}			
		}
		
		$contentArray = array();
		// we add empty tables so that every requested date has an entry, even if there is nothing
		// example: <result date="2007-01-01" />
		foreach($this->archives as $timestamp => $archive)
		{
			$strDate = $this->archives[$timestamp]->getPrettyDate();
			$contentArray[$timestamp]['table'] = new Piwik_DataTable_Simple();
			$contentArray[$timestamp]['prettyDate'] = $strDate;
		}

		foreach($arrayValues as $timestamp => $aNameValues)
		{
			$contentArray[$timestamp]['table']->loadFromArray($aNameValues);
		}
		ksort( $contentArray );
				
		$tableArray = $this->getNewDataTableArray();
		foreach($contentArray as $timestamp => $aData)
		{
			$tableArray->addTable($aData['table'], $aData['prettyDate']);
			$this->loadMetadata($tableArray, $this->archives[$timestamp]);
		}
		return $tableArray;
	}
}