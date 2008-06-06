<?php
require_once "Archive/Array.php";

class Piwik_Archive_Array_IndexedBySite extends Piwik_Archive_Array {
	
	/**
	 *
	 * @param Piwik_Site $oSite 
	 * @param string $strPeriod eg. 'day' 'week' etc.
	 * @param string $strDate A date range, eg. 'last10', 'previous5' or 'YYYY-MM-DD,YYYY-MM-DD'
	 */
	function __construct($sites, $strPeriod, $strDate)
	{
		foreach($sites as $idSite)
		{
			$archive = Piwik_Archive::build($idSite, $strPeriod, $strDate );
			$archive->setSite(new Piwik_Site($idSite));
			$archive->prepareArchive();
			$this->archives[$idSite] = $archive;
		}
		ksort( $this->archives );
	}
	
	protected function getIndexName()
	{
		return 'idSite';
	}
	
	protected function getDataTableLabelValue( $archive )
	{
		return $archive->getIdSite();
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
		
		$numericTable = null;
		$aIds = array();
		foreach($this->archives as $archive)
		{
			if(is_null($numericTable))
			{
				$numericTable = $archive->archiveProcessing->getTableArchiveNumericName();
			}
			else if( $numericTable != $archive->archiveProcessing->getTableArchiveNumericName())
			{
				throw new Exception("Piwik_Archive_Array_IndexedBySite::getDataTableFromNumeric() algorithm won't work if data is stored in different tables");
			}
			$aIds[] = $archive->getIdArchive();
		}
		
		$inIds = implode(', ', $aIds);
		$sql = "SELECT value, name, idarchive, idsite
								FROM $numericTable
								WHERE idarchive IN ( $inIds )
									AND name IN ( $inName )";
		$values = Zend_Registry::get('db')->fetchAll($sql);
			
		$arrayValues = array();
		foreach($values as $value)
		{
			$arrayValues[$value['idsite']][$value['name']] = $value['value'];
		}			
		
		// we add empty tables so that every requested date has an entry, even if there is nothing
		// example: <result idSite="159" />
		$contentArray = array();
		foreach($this->archives as $idSite => $archive)
		{
			$contentArray[$idSite]['table'] = new Piwik_DataTable_Simple();
		}
		
		foreach($arrayValues as $idSite => $aNameValues)
		{
			$contentArray[$idSite]['table']->loadFromArray($aNameValues);
		}
		ksort( $contentArray );
				
		$tableArray = $this->getNewDataTableArray();
		foreach($contentArray as $idSite => $aData)
		{
			$tableArray->addTable($aData['table'], $idSite);
		}
		return $tableArray;
	}
}