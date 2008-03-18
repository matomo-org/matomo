<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik
 */

 
require_once 'Period.php';
require_once 'Date.php';
require_once 'ArchiveProcessing.php';
require_once 'Archive/Single.php';

/**
 * Architecture
 * - *ArchiveProcessing* entity : handle all the computation on an archive / create & delete archive
 * - *Archive* entity: 
 * 		contains the information on an archive, 
 * 		uses the ArchiveProcessing if necessary
 * 		small overhead so we can instanciate many objects of this class for example one for each day 
 * 			of the month
 * - *Website* entity: getId, getUrls, getFirstDay, etc.
 * + *Period* entity: composed of *Date* objects
 * + *Table* entity: serialize, unserialize, sort elements, limit number of elements
 * 		contains all the logic, data structure, etc.
 * 		receives data directly from the sql query via a known API
 * - The *ArchiveProcessing* saves in the DB *numbers* or *Table* objects
 *  
 * @package Piwik
 */

abstract class Piwik_Archive
{
	const INDEX_NB_UNIQ_VISITORS = 1;
	const INDEX_NB_VISITS = 2;
	const INDEX_NB_ACTIONS = 3;
	const INDEX_MAX_ACTIONS = 4;
	const INDEX_SUM_VISIT_LENGTH = 5;
	const INDEX_BOUNCE_COUNT = 6;

	protected $alreadyChecked = false;
	protected $site = null;
		
	static protected $alreadyBuilt = array();
	
	/**
	 * Builds an Archive object or returns the same archive if previously built.
	 *
	 * @param int $idSite
	 * @param string|Piwik_Date $date 'YYYY-MM-DD' or magic keywords 'today' See Piwik_Date::factory
	 * @param string $period 'week' 'day' etc.
	 * @return Piwik_Archive
	 */
	static public function build($idSite, $period, $strDate )
	{
		$oSite = new Piwik_Site($idSite);
			
		if(is_string($strDate) 
			&& (
				ereg('^(last|previous){1}([0-9]*)$', $strDate, $regs)
				|| ereg('^([0-9]{4}-[0-9]{1,2}-[0-9]{1,2}),([0-9]{4}-[0-9]{1,2}-[0-9]{1,2})$', $strDate, $regs)
				)
			)
		{
			require_once 'Archive/Array.php';
			$archive = new Piwik_Archive_Array($oSite, $period, $strDate);
		}
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
			
			$archive = new Piwik_Archive_Single;
			$archive->setPeriod($oPeriod);
			$archive->setSite($oSite);
			
			self::$alreadyBuilt[$idSite][$date][$period] = $archive;
		}
		
		return $archive;
	}
	
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
	abstract public function getDataTableFromNumeric( $fields );

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
	abstract public function getDataTable( $name, $idSubTable = null );

	/**
	 * Same as getDataTable() except that it will also load in memory
	 * all the subtables for the DataTable $name. 
	 * You can then access the subtables by using the Piwik_DataTable_Manager getTable() 
	 *
	 * @param string $name
	 * @param int $idSubTable
	 * @return Piwik_DataTable
	 */
	abstract public function getDataTableExpanded($name, $idSubTable = null);

	/**
	 * Set the site
	 *
	 * @param Piwik_Site $site
	 */
	public function setSite( Piwik_Site $site )
	{
		$this->site = $site;
	}
	
	/**
	 * Get the site
	 *
	 * @param Piwik_Site $site
	 */
	public function getSite( )
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





