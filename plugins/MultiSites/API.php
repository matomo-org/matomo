<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id: API.php$
 * 
 * @category Piwik_Plugins
 * @package Piwik_Goals
 */

/**
 * @see plugins/MultiSites/CalculateEvolutionFilter.php
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/MultiSites/CalculateEvolutionFilter.php';

/**
 * The MultiSites API lets you request the key metrics (visits, page views, revenue) for all Websites in Piwik.
 */
class Piwik_MultiSites_API
{
	/**
	 * The singleton instance of this class.
	 */
	static private $instance = null;

	/**
	 * Returns the singleton instance of this class. The instance is created
	 * if it hasn't been already.
	 * 
	 * @return Piwik_Goals_API
	 */
	static public function getInstance()
	{
		if (self::$instance == null)
		{
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Mapping of metric names to the names of their 'evolution' metric
	 * counterparts. Used by getAll.
	 */
	private $evolutionColumnNames = null;
	
	/**
	 * Constructor.
	 */
	function __construct()
	{
		$this->evolutionColumnNames = array(
			'nb_visits' => 'visits_evolution',
			'nb_actions' => 'actions_evolution'
		);
		
		if (Piwik_Common::isGoalPluginEnabled())
		{
			$this->evolutionColumnNames[Piwik_Goals::getRecordName('revenue')] = 'revenue_evolution';
		}
	}

	/**
	 * Returns a report displaying the total visits, actions and revenue, as
	 * well as the evolution of these values, of all existing sites over a
	 * specified period of time.
	 * 
	 * If the specified period is not a 'range', this function will calculcate
	 * evolution metrics. Evolution metrics are metrics that display the
	 * percent increase/decrease of another metric since the last period.
	 * 
	 * This function will merge the result of the archive query so each
	 * row in the result DataTable will correspond to the metrics of a single
	 * site. If a date range is specified, the result will be a
	 * DataTable_Array, but it will still be merged.
	 * 
	 * @param string $period The period type to get data for.
	 * @param string $date The date(s) to get data for.
	 * @param string $segment The segments to get data for.
	 * @param string $_restrictSitesToLogin Hack used to enforce we restrict the returned data to the specified username
	 * 										Only used when a scheduled task is running 
	 */
	public function getAll($period, $date, $segment = false, $_restrictSitesToLogin = false)
	{
		Piwik::checkUserHasSomeViewAccess();
		$isGoalPluginEnabled = Piwik_Common::isGoalPluginEnabled();

		
		if (Piwik::isUserIsSuperUser()
				// Hack: when this API function is called as a Scheduled Task, Super User status is enforced.
				// This means this function would return ALL websites in all cases. 
				// Instead, we make sure that only the right set of data is returned
				&& !Piwik_TaskScheduler::isTaskBeingExecuted()) 
		{
			$sites = Piwik_SitesManager_API::getInstance()->getAllSites();
			Piwik_Site::setSites($sites);
		}
		else
		{ 
			$sites = Piwik_SitesManager_API::getInstance()->getSitesWithAtLeastViewAccess($limit = false, $_restrictSitesToLogin);
			Piwik_Site::setSitesFromArray($sites);
		}
		
		// build the archive type used to query archive data
		$archive = Piwik_Archive::build('all', $period, $date, $segment, $_restrictSitesToLogin);

		// determine what data will be displayed
		$fieldsToGet = array('nb_visits', 'nb_actions');
		if ($isGoalPluginEnabled)
		{
			$revenueMetric = Piwik_Goals::getRecordName('revenue');
			$fieldsToGet[] = $revenueMetric;
		}
		
		// get the data
		$dataTable = $archive->getDataTableFromNumeric($fieldsToGet);

		// get rid of the DataTable_Array that is created by the IndexedBySite archive type
		$dataTable = $dataTable->mergeChildren();

		// if the period isn't a range & a lastN/previousN date isn't used, we get the same
		// data for the last period to show the evolution of visits/actions/revenue
		if ($period != 'range' && !preg_match('/(last|previous)([0-9]*)/', $date, $regs))
		{
			if (strpos($date, ',')) // date in the form of 2011-01-01,2011-02-02
			{
				$rangePeriod = new Piwik_Period_Range($period, $date);
				
				$lastStartDate = Piwik_Period_Range::removePeriod($period, $rangePeriod->getDateStart(), $n = 1);
				$lastEndDate = Piwik_Period_Range::removePeriod($period, $rangePeriod->getDateEnd(), $n = 1);
				
				$strLastDate = "$lastStartDate,$lastEndDate";
			}
			else
			{
				$strLastDate = Piwik_Period_Range::removePeriod($period, Piwik_Date::factory($date), $n = 1)->toString();
			}

			$pastArchive = Piwik_Archive::build('all', $period, $strLastDate, $segment, $_restrictSitesToLogin);
			$pastData = $pastArchive->getDataTableFromNumeric($fieldsToGet);

			$pastData = $pastData->mergeChildren();

			// use past data to calculate evolution percentages
			$this->calculateEvolutionPercentages($dataTable, $pastData, $fieldsToGet);
		}

		// move the site id to a metadata column
		$dataTable->filter('ColumnCallbackAddMetadata', array('label', 'idsite'));

		// set the label of each row to the site name
		$getNameFor = array('Piwik_Site', 'getNameFor');
		$dataTable->filter('ColumnCallbackReplace', array('label', $getNameFor));

		// rename the revenue column from the metric name to 'revenue'
		if ($isGoalPluginEnabled)
		{
			$mapping = array($revenueMetric => 'revenue');
			$dataTable->filter('ReplaceColumnNames', array($mapping));
		}
		
		// Ensures data set sorted, for Metadata output
		$dataTable->filter('Sort', array('nb_visits', 'desc', $naturalSort = false));

		return $dataTable;
	}

	/**
	 * Utility function used by getAll. Performs a binary filter of two
	 * DataTables in order to correctly calculate evolution metrics.
	 * 
	 * @param Piwik_DataTable|Piwik_DataTable_Array $currentData
	 * @param Piwik_DataTable|Piwik_DataTable_Array $pastData
	 * @param array $fields The array of string fields to calculate evolution
	 *                      metrics for.
	 */
	private function calculateEvolutionPercentages($currentData, $pastData, $fields)
	{
		if ($currentData instanceof Piwik_DataTable_Array)
		{
			$pastArray = $pastData->getArray();
			foreach ($currentData->getArray() as $label => $subTable)
			{
				$this->calculateEvolutionPercentages($subTable, current($pastArray), $fields);
				next($pastArray);
			}
		}
		else
		{
			foreach ($fields as $field)
			{
				$currentData->filter('Piwik_MultiSites_CalculateEvolutionFilter',
					array($pastData, $this->evolutionColumnNames[$field], $field, $quotientPrecision = 2));
			}
		}
	}
}
