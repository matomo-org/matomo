<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Archive.php 585 2008-07-28 00:56:50Z matt $
 * 
 * @package Piwik
 */

require_once 'Period.php';
require_once 'Date.php';
require_once 'ArchiveProcessing.php';
require_once 'Archive/Single.php';

/**
 * The archive object is used to query specific data for a day or a period of statistics for a given website.
 * 
 * Example:
 * <pre>
 * 		$archive = Piwik_Archive::build($idSite = 1, $period = 'week', '2008-03-08' );
 * 		$dataTable = $archive->getDataTable('Provider_hostnameExt');
 * 		$dataTable->queueFilter('Piwik_DataTable_Filter_ReplaceColumnNames');
 * 		return $dataTable;
 * </pre>
 * 
 * Example bis:
 * <pre>
 * 		$archive = Piwik_Archive::build($idSite = 3, $period = 'day', $date = 'today' );
 * 		$nbVisits = $archive->getNumeric('nb_visits');
 * 		return $nbVisits;		
 * </pre>
 * 
 * If the requested statistics are not yet processed, Archive uses ArchiveProcessing to archive the statistics.
 * 
 * @package Piwik
 * @subpackage Piwik_Archive
 */
abstract class Piwik_Archive
{
	/**
	 * When saving DataTables in the DB, we sometimes replace the columns name by these IDs so we save up lots of bytes
	 * Eg. INDEX_NB_UNIQ_VISITORS is an integer: 4 bytes, but 'nb_uniq_visitors' is 16 bytes at least
	 * (in php it's actually even much more) 
	 *
	 */
	const INDEX_NB_UNIQ_VISITORS = 1;
	const INDEX_NB_VISITS = 2;
	const INDEX_NB_ACTIONS = 3;
	const INDEX_MAX_ACTIONS = 4;
	const INDEX_SUM_VISIT_LENGTH = 5;
	const INDEX_BOUNCE_COUNT = 6;
	const INDEX_NB_VISITS_CONVERTED = 7;
	const INDEX_NB_CONVERSIONS = 8;
	const INDEX_REVENUE = 9;
	const INDEX_GOALS = 10;
	
	const INDEX_GOAL_NB_CONVERSIONS = 1;
	const INDEX_GOAL_REVENUE = 2;

	public static $mappingFromIdToName = array(
				Piwik_Archive::INDEX_NB_UNIQ_VISITORS 		=> 'nb_uniq_visitors',
				Piwik_Archive::INDEX_NB_VISITS				=> 'nb_visits',
				Piwik_Archive::INDEX_NB_ACTIONS				=> 'nb_actions',
				Piwik_Archive::INDEX_MAX_ACTIONS			=> 'max_actions',
				Piwik_Archive::INDEX_SUM_VISIT_LENGTH		=> 'sum_visit_length',
				Piwik_Archive::INDEX_BOUNCE_COUNT			=> 'bounce_count',
				Piwik_Archive::INDEX_NB_VISITS_CONVERTED 	=> 'nb_visits_converted',
				Piwik_Archive::INDEX_NB_CONVERSIONS 		=> 'nb_conversions',
				Piwik_Archive::INDEX_REVENUE				=> 'revenue',
				Piwik_Archive::INDEX_GOALS					=> 'goals',
			);

	public static $mappingFromIdToNameGoal = array(
				Piwik_Archive::INDEX_GOAL_NB_CONVERSIONS 	=> 'nb_conversions',
				Piwik_Archive::INDEX_GOAL_REVENUE 			=> 'revenue',
	);

	/*
	 * string indexed column name => Integer indexed column name 
	 */
	public static $mappingFromNameToId = array(
				'nb_uniq_visitors'			=> Piwik_Archive::INDEX_NB_UNIQ_VISITORS,
				'nb_visits'					=> Piwik_Archive::INDEX_NB_VISITS,
				'nb_actions'				=> Piwik_Archive::INDEX_NB_ACTIONS,
				'max_actions'				=> Piwik_Archive::INDEX_MAX_ACTIONS,
				'sum_visit_length'			=> Piwik_Archive::INDEX_SUM_VISIT_LENGTH,
				'bounce_count'				=> Piwik_Archive::INDEX_BOUNCE_COUNT,
				'nb_visits_converted'		=> Piwik_Archive::INDEX_NB_VISITS_CONVERTED,
				'nb_conversions' 			=> Piwik_Archive::INDEX_NB_CONVERSIONS,
				'revenue' 					=> Piwik_Archive::INDEX_REVENUE,
				'goals'						=> Piwik_Archive::INDEX_GOALS,
	);
	
	/**
	 * Website Piwik_Site
	 *
	 * @var Piwik_Site
	 */
	protected $site = null;
	
	/**
	 * Stores the already built archives.
	 * Act as a big caching array
	 *
	 * @var array of Piwik_Archive
	 */
	static protected $alreadyBuilt = array();
	
	/**
	 * Builds an Archive object or returns the same archive if previously built.
	 *
	 * @param string|int idSite integer, or comma separated list of integer
	 * @param string|Piwik_Date $date 'YYYY-MM-DD' or magic keywords 'today' @see Piwik_Date::factory()
	 * @param string $period 'week' 'day' etc.
	 * 
	 * @return Piwik_Archive
	 */
	static public function build($idSite, $period, $strDate )
	{
		if($idSite === 'all')
		{
			$sites = Piwik_SitesManager_API::getSitesIdWithAtLeastViewAccess();
		}
		else
		{
			$sites = Piwik_Site::getIdSitesFromIdSitesString($idSite);
		}
		
		// idSite=1,3 or idSite=all
		if( count($sites) > 1 
			|| $idSite === 'all' )
		{
			require_once 'Archive/Array/IndexedBySite.php';
			$archive = new Piwik_Archive_Array_IndexedBySite($sites, $period, $strDate);
		}
		// if a period date string is detected: either 'last30', 'previous10' or 'YYYY-MM-DD,YYYY-MM-DD'
		elseif(is_string($strDate) 
			&& (
				ereg('^(last|previous){1}([0-9]*)$', $strDate, $regs)
				|| ereg('^([0-9]{4}-[0-9]{1,2}-[0-9]{1,2}),([0-9]{4}-[0-9]{1,2}-[0-9]{1,2})$', $strDate, $regs)
				)
			)
		{
			$oSite = new Piwik_Site($idSite);
			require_once 'Archive/Array/IndexedByDate.php';
			$archive = new Piwik_Archive_Array_IndexedByDate($oSite, $period, $strDate);
		}
		// case we request a single archive
		else
		{
			if(is_string($strDate))
			{
				$oDate = Piwik_Date::factory($strDate);
			}
			else
			{
				$oDate = $strDate;
			}
			$date = $oDate->toString();
			
			if(isset(self::$alreadyBuilt[$idSite][$date][$period]))
			{
				return self::$alreadyBuilt[$idSite][$date][$period];
			}
			
			$oPeriod = Piwik_Period::factory($period, $oDate);
			
			$archive = new Piwik_Archive_Single();
			$archive->setPeriod($oPeriod);
			$archive->setSite(new Piwik_Site($idSite));
			$archiveJustProcessed = $archive->prepareArchive();
			
			//we don't cache the archives just processed, the datatable were freed from memory 
			if(!$archiveJustProcessed)
			{
				self::$alreadyBuilt[$idSite][$date][$period] = $archive;
			}
		}
		
		return $archive;
	}
	
	abstract public function prepareArchive();
	
	/**
	 * Returns the value of the element $name from the current archive 
	 * The value to be returned is a numeric value and is stored in the archive_numeric_* tables
	 *
	 * @param string $name For example Referers_distinctKeywords 
	 * @return float|int|false False if no value with the given name
	 */
	abstract public function getNumeric( $name );
	
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
	abstract public function getBlob( $name );
	
	/**
	 * 
	 * @return Piwik_DataTable
	 */
	abstract public function getDataTableFromNumeric( $fields );

	/**
	 * This method will build a dataTable from the blob value $name in the current archive.
	 * 
	 * For example $name = 'Referers_searchEngineByKeyword' will return a  Piwik_DataTable containing all the keywords
	 * If a idSubTable is given, the method will return the subTable of $name 
	 * 
	 * @param string $name
	 * @param int $idSubTable or null if requesting the parent table
	 * @return Piwik_DataTable
	 * @throws exception If the value cannot be found
	 */
	abstract public function getDataTable( $name, $idSubTable = null );

	/**
	 * Same as getDataTable() except that it will also load in memory
	 * all the subtables for the DataTable $name. 
	 * You can then access the subtables by using the Piwik_DataTable_Manager getTable() 
	 *
	 * @param string $name
	 * @param int $idSubTable or null if requesting the parent table
	 * @return Piwik_DataTable
	 */
	abstract public function getDataTableExpanded($name, $idSubTable = null);

	/**
	 * Sets the site
	 *
	 * @param Piwik_Site $site
	 */
	public function setSite( Piwik_Site $site )
	{
		$this->site = $site;
	}
	
	/**
	 * Gets the site
	 *
	 * @param Piwik_Site $site
	 */
	public function getSite()
	{
		return $this->site;
	}
	
	/**
	 * Returns the Id site associated with this archive
	 *
	 * @return int
	 */
	public function getIdSite()
	{
		return $this->site->getId();
	}
	
}





