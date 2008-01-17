<?php

require_once "DataTable/Array.php";

class Piwik_Archive_Array extends Piwik_Archive
{	
	protected $archives = array();
	
	
	function __construct(Piwik_Site $oSite, $strPeriod, $strDate)
	{
		$rangePeriod = new Piwik_Period_Range($strPeriod, $strDate);
		
		foreach($rangePeriod->getSubperiods() as $subPeriod)
		{
			$archive = Piwik_Archive::build($oSite->getId(), $strPeriod, $subPeriod->getDateStart() );
			$archive->prepareArchive();
		
			$this->archives[$archive->getIdArchive()] = $archive;
			$this->idArchives[] = $archive->getIdArchive();
		}
		
		$this->inIdArchives = implode("",$this->idArchives);
	}
	
	
	/**
	 * Returns the value of the element $name from the current archive 
	 * The value to be returned is a numeric value and is stored in the archive_numeric_* tables
	 *
	 * @param string $name For example Referers_distinctKeywords 
	 * @return float|int|false False if no value with the given name
	 */
	public function getNumeric( $name )
	{
		$table = new Piwik_DataTable_Array;
		return $table;
	}
	
	/**
	 * Returns the value of the element $name from the current archive
	 * 
	 * The value to be returned is a blob value and is stored in the archive_numeric_* tables
	 * 
	 * It can return anything from strings, to serialized PHP arrays or PHP objects, etc.
	 *
	 * @param string $name For example Referers_distinctKeywords 
	 * @return mixed False if no value with the given name
	 */
	public function getBlob( $name )
	{
		$table = new Piwik_DataTable_Array;
		return $table;
	}
	
	/**
	 * Given a list of fields defining numeric values, it will return a Piwik_DataTable_Simple 
	 * containing one row per value.
	 * 
	 * For example $fields = array( 	'max_actions',
	 *						'nb_uniq_visitors', 
	 *						'nb_visits',
	 *						'nb_actions', 
	 *						'sum_visit_length',
	 *						'bounce_count',
	 *					); 
	 *
	 * @param array $fields array( fieldName1, fieldName2, ...)
	 * @return Piwik_DataTable_Simple
	 */
	public function getDataTableFromNumeric( $fields )
	{
		// Simple algorithm not efficient
//		$table = new Piwik_DataTable_Array;
//		foreach($this->archives as $archive)
//		{
//			$subTable =  $archive->getDataTableFromNumeric( $fields ) ;
//			$table->addTable($subTable, $archive->getPrettyDate());
//		}
//		return $table;

//		$fields = $fields[1];
		require_once "DataTable/Simple.php";
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
		
		$tableArray = new Piwik_DataTable_Array;
		
		foreach($queries as $table => $aIds)
		{
			$inIds = implode(', ', $aIds);
			$sql = "SELECT value, name, idarchive
									FROM $table
									WHERE idarchive IN ( $inIds )
										AND name IN ( $inName )";

			$values = $db->fetchAll($sql);
			
			$idarchiveToName = array();
			foreach($values as $value)
			{
				$idarchiveToName[$value['idarchive']][$value['name']] = $value['value'];
			}
//			var_dump($idarchiveToName);exit;
			
			foreach($idarchiveToName as $id => $aNameValues)
			{
				$strDate = $this->archives[$id]->getPrettyDate();
				
				$table = new Piwik_DataTable_Simple;
				$table->loadFromArray($aNameValues);
				
//				echo $table; echo $strDate;exit;
				$tableArray->addTable($table, $strDate);
			}
		}
				
		return $tableArray;
	}

	/**
	 * This method will build a dataTable from the blob value $name in the current archive.
	 * 
	 * For example $name = 'Referers_searchEngineByKeyword' will return a  Piwik_DataTable containing all the keywords
	 * If a idSubTable is given, the method will return the subTable of $name 
	 * 
	 * @param string $name
	 * @param int $idSubTable
	 * @return Piwik_DataTable
	 * @throws exception If the value cannot be found
	 */
	public function getDataTable( $name, $idSubTable = null )
	{		
		$table = new Piwik_DataTable_Array;
		return $table;
	}
	
	
	/**
	 * Same as getDataTable() except that it will also load in memory
	 * all the subtables for the DataTable $name. 
	 * You can then access the subtables by using the Piwik_DataTable_Manager getTable() 
	 *
	 * @param string $name
	 * @param int $idSubTable
	 * @return Piwik_DataTable
	 */
	public function getDataTableExpanded($name, $idSubTable = null)
	{
		$table = new Piwik_DataTable_Array;
		return $table;
	}
}