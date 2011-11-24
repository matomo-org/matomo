<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * @package Piwik
 * @subpackage Piwik_Archive
 */
class Piwik_Archive_Array_IndexedBySite extends Piwik_Archive_Array 
{
	/**
	 * Used to cache the name of the table that holds the data this archive.
	 * 
	 * This will only be used if the archives held by this instance are instances of
	 * Piwik_Archive_Single.
	 */
	private $tableName = null;

	/**
	 * @param Piwik_Site $oSite 
	 * @param string $strPeriod eg. 'day' 'week' etc.
	 * @param string $strDate A date range, eg. 'last10', 'previous5' or 'YYYY-MM-DD,YYYY-MM-DD'
	 */
	function __construct($sites, $strPeriod, $strDate, Piwik_Segment $segment)
	{
		foreach($sites as $idSite)
		{
			$archive = Piwik_Archive::build($idSite, $strPeriod, $strDate, $segment->getString() );
			$archive->setSite(new Piwik_Site($idSite));
			$archive->setSegment($segment);
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
					$table->addRowsFromArray($values[$idSite]);
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
		$arrayValues = array();
		foreach($this->loadValuesFromDB($fields) as $value)
 		{
			$arrayValues[$value['idsite']][$value['name']] = $value['value'];
 		}
		return $arrayValues;
	}
	
	private function loadValuesFromDB($fields)
	{
		$requestedReport = is_string($fields) ? $fields : current($fields);
		$inNames = Piwik_Common::getSqlStringFieldsArray($fields);

		// get the archive ids
		if (!$this->getFirstArchive()->isArchivingDisabled())
		{
			$archiveIds = $this->getArchiveIdsAfterLaunching($requestedReport);
		}
		else
		{
			$archiveIds = $this->getArchiveIdsWithoutLaunching($requestedReport);
		}

		$archiveIds = implode(', ', array_filter($archiveIds));

		// if no archive ids are found, avoid executing any SQL queries
		if(empty($archiveIds))
		{
			return array();
		}

		// select archive data
 		$sql = "SELECT value, name, idarchive, idsite
								FROM {$this->getNumericTableName()}
								WHERE idarchive IN ( $archiveIds )
									AND name IN ( $inNames )";
		return Piwik_FetchAll($sql, $fields);
	}

	private function getFirstArchive()
	{
		return reset($this->archives);
	}

	/**
	 * Gets the archive id of every Single archive this archive holds. This method
	 * will launch the archiving process if appropriate.
	 * 
	 * @param string $requestedReport The requested archive report.
	 * @return array
	 */
	private function getArchiveIdsAfterLaunching( $requestedReport )
	{
		// prepare archives (this will launch archiving when appropriate)
		foreach($this->archives as $archive)
		{
			$archive->setRequestedReport( $requestedReport );
			$archive->prepareArchive();
		}

		// collect archive ids for archives that have visits
		$archiveIds = array();
		foreach($this->archives as $archive)
 		{
 			if( !$archive->isThereSomeVisits )
 			{
 				continue;
 			}
 			
			$archiveIds[] = $archive->getIdArchive();
		
 			if( $this->getNumericTableName() != $archive->archiveProcessing->getTableArchiveNumericName())
			{
				throw new Exception("Piwik_Archive_Array_IndexedBySite::getDataTableFromNumeric() algorithm won't work if data is stored in different tables");
			}
 		}
 		
 		return $archiveIds;
	}

	/**
	 * Gets the archive id of every Single archive this archive holds. This method
	 * will not launch the archiving process.
	 * 
	 * @param string $requestedReport The requested archive report.
	 * @return array
	 */
	private function getArchiveIdsWithoutLaunching( $requestedReport )
	{
		$firstArchive = $this->getFirstArchive();
		$segment = $firstArchive->getSegment();
		$period = $firstArchive->getPeriod();
		
		// the flags used to tell how the archiving process for a specific archive was completed,
		// if it was completed
		$done = Piwik_ArchiveProcessing::getDoneStringFlagFor($segment, $period, $requestedReport);
		$donePlugins = Piwik_ArchiveProcessing::getDoneStringFlagFor($segment, $period, $requestedReport, true);

		// create the SQL to query every archive ID
		$nameConditionSuffix = '';
		if ($done != $donePlugins)
		{
			$nameConditionSuffix = "OR name = '$donePlugins'";
		}

		$nameCondition = "(name = '$done' $nameConditionSuffix) AND
						  (value = '".Piwik_ArchiveProcessing::DONE_OK."' OR
						   value = '".Piwik_ArchiveProcessing::DONE_OK_TEMPORARY."')";

		$sql = "SELECT idsite,
		               MAX(idarchive) AS idarchive
		          FROM ".$this->getNumericTableName()."
		         WHERE date1 = ?
		           AND date2 = ?
		           AND period = ?
		           AND $nameCondition
		           AND idsite IN (".implode(',', array_keys($this->archives)).")
		      GROUP BY idsite";
		
		$bind = array($period->getDateStart()->toString('Y-m-d'),
					  $period->getDateEnd()->toString('Y-m-d'),
					  $period->getId());

		// execute the query and process the results.
		$archiveIds = array();
		foreach (Piwik_FetchAll($sql, $bind) as $row)
		{
			$archiveIds[] = $row['idarchive'];
		}
		
		return $archiveIds;
	}
	
	/**
	 * Gets the name of the database table that holds the numeric archive data for
	 * this archive.
	 *
	 * @return string
	 */
	private function getNumericTableName()
	{
		if (is_null($this->tableName))
		{
			$table = Piwik_ArchiveProcessing::makeNumericArchiveTable($this->getFirstArchive()->getPeriod());
			$this->tableName = $table->getTableName();
		}

		return $this->tableName;
	}
}
