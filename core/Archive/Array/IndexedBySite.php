<?php
require_once "Archive/Array.php";

class Piwik_Archive_Array_IndexedBySite extends Piwik_Archive_Array 
{
	/**
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
	 * ordered by idsite
	 *
	 * @param array|string $fields array( fieldName1, fieldName2, ...)  Names of the mysql table fields to load
	 * @return Piwik_DataTable_Array
	 */
	public function getDataTableFromNumeric( $fields )
	{
		$tableArray = $this->getNewDataTableArray();
		if ($this->getFirstArchive() instanceof Piwik_Archive_Single)
		{
			$values = $this->getValues($fields);
			foreach($this->archives as $idSite => $archive)
			{
				$table = new Piwik_DataTable_Simple();
				if (array_key_exists($idSite, $values))
				{
					$table->loadFromArray($values[$idSite]);
				}
				$tableArray->addTable($table, $idSite);
			}
		}
		elseif ($this->getFirstArchive() instanceof Piwik_Archive_Array)
		{
			foreach($this->archives as $idSite => $archive)
			{
				$tableArray->addTable($archive->getDataTableFromNumeric($fields), $idSite);
			}
		}
		
		return $tableArray;
	}

	private function getValues($fields)
	{
		foreach($this->loadValuesFromDB($fields) as $value)
 		{
			$arrayValues[$value['idsite']][$value['name']] = $value['value'];
 		}
		return $arrayValues;
	}
	
	private function loadValuesFromDB($fields)
	{
 		$sql = "SELECT value, name, idarchive, idsite
								FROM {$this->getNumericTableName()}
								WHERE idarchive IN ( {$this->getArchiveIds()} )
									AND name IN ( {$this->getSqlStringFieldsArray($fields)} )";
		return Zend_Registry::get('db')->fetchAll($sql);
	}

	private function getFirstArchive()
	{
		reset($this->archives);
		return current($this->archives);
	}

	private function getArchiveIds()
	{
		foreach($this->archives as $archive)
 		{
 			if( $this->getNumericTableName() != $archive->archiveProcessing->getTableArchiveNumericName())
			{
				throw new Exception("Piwik_Archive_Array_IndexedBySite::getDataTableFromNumeric() algorithm won't work if data is stored in different tables");
			}
			$archiveIds[] = $archive->getIdArchive();
 		}
		return implode(', ', $archiveIds);
	}
	
	private function getNumericTableName()
	{
		return $this->getFirstArchive()->archiveProcessing->getTableArchiveNumericName();
	}
}