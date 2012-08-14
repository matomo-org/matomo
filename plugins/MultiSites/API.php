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
	const METRIC_TRANSLATION_KEY = 'translation';
	const METRIC_EVOLUTION_COL_NAME_KEY = 'evolution_column_name';
	const METRIC_RECORD_NAME_KEY = 'record_name';
	const METRIC_IS_ECOMMERCE_KEY = 'is_ecommerce';

	const NB_VISITS_METRIC = 'nb_visits';
	const NB_ACTIONS_METRIC = 'nb_actions';
	const GOAL_REVENUE_METRIC = 'revenue';
	const GOAL_CONVERSION_METRIC = 'nb_conversions';
	const ECOMMERCE_ORDERS_METRIC = 'orders';
	const ECOMMERCE_REVENUE_METRIC = 'ecommerce_revenue';

	static private $baseMetrics = array(
		self::NB_VISITS_METRIC => array (
			self::METRIC_TRANSLATION_KEY => 'General_ColumnNbVisits',
			self::METRIC_EVOLUTION_COL_NAME_KEY => 'visits_evolution',
			self::METRIC_RECORD_NAME_KEY => self::NB_VISITS_METRIC,
			self::METRIC_IS_ECOMMERCE_KEY => false,
		),
		self::NB_ACTIONS_METRIC => array (
			self::METRIC_TRANSLATION_KEY => 'General_ColumnNbActions',
			self::METRIC_EVOLUTION_COL_NAME_KEY => 'actions_evolution',
			self::METRIC_RECORD_NAME_KEY => self::NB_ACTIONS_METRIC,
			self::METRIC_IS_ECOMMERCE_KEY => false,
		),
	);

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
	 * @param bool|string $segment The segments to get data for.
	 * @param bool|string $_restrictSitesToLogin Hack used to enforce we restrict the returned data to the specified username
	 * 										Only used when a scheduled task is running
	 * @param bool|string $enhanced When true, return additional goal & ecommerce metrics
	 * @return Piwik_DataTable
	 */
	public function getAll($period, $date, $segment = false, $_restrictSitesToLogin = false, $enhanced = false)
	{
		Piwik::checkUserHasSomeViewAccess();

		return $this->buildDataTable(
			'all',
			$period,
			$date,
			$segment,
			$_restrictSitesToLogin,
			$enhanced
		);
	}

	/**
	 * Same as getAll but for a unique Piwik site
	 * @see Piwik_MultiSites_API::getAll()
	 *
	 * @param int $idSite Id of the Piwik site
	 * @param string $period The period type to get data for.
	 * @param string $date The date(s) to get data for.
	 * @param bool|string $segment The segments to get data for.
	 * @param bool|string $_restrictSitesToLogin Hack used to enforce we restrict the returned data to the specified username
	 * 										Only used when a scheduled task is running
	 * @param bool|string $enhanced When true, return additional goal & ecommerce metrics
	 * @return Piwik_DataTable
	 */
	public function getOne($idSite, $period, $date, $segment = false, $_restrictSitesToLogin = false, $enhanced = false)
	{
		Piwik::checkUserHasSomeViewAccess();

		return $this->buildDataTable(
			$idSite,
			$period,
			$date,
			$segment,
			$_restrictSitesToLogin,
			$enhanced
		);
	}

	private function buildDataTable($sites, $period, $date, $segment, $_restrictSitesToLogin, $enhanced)
	{
		$allWebsitesRequested = $sites == 'all';
		if($allWebsitesRequested)
		{
			if (Piwik::isUserIsSuperUser()
					// Hack: when this API function is called as a Scheduled Task, Super User status is enforced.
					// This means this function would return ALL websites in all cases.
					// Instead, we make sure that only the right set of data is returned
					&& !Piwik_TaskScheduler::isTaskBeingExecuted())
			{
				Piwik_Site::setSites(
					Piwik_SitesManager_API::getInstance()->getAllSites()
				);
			}
			else
			{
				Piwik_Site::setSitesFromArray(
					Piwik_SitesManager_API::getInstance()->getSitesWithAtLeastViewAccess($limit = false, $_restrictSitesToLogin)
				);
			}
		}

		// build the archive type used to query archive data
		$archive = Piwik_Archive::build(
			$sites,
			$period,
			$date,
			$segment,
			$_restrictSitesToLogin
		);

		// determine what data will be displayed
		$fieldsToGet = array();
		$columnNameRewrites = array();
		$apiECommerceMetrics = array();
		$apiMetrics = Piwik_MultiSites_API::getApiMetrics($enhanced);
		foreach($apiMetrics as $metricName => $metricSettings)
		{
			$fieldsToGet[] = $metricSettings[self::METRIC_RECORD_NAME_KEY];
			$columnNameRewrites[$metricSettings[self::METRIC_RECORD_NAME_KEY]] = $metricName;

			if($metricSettings[self::METRIC_IS_ECOMMERCE_KEY])
			{
				$apiECommerceMetrics[$metricName] = $metricSettings;
			}
		}

		// get the data
		// $dataTable instanceOf Piwik_DataTable_Array
		$dataTable = $archive->getDataTableFromNumeric($fieldsToGet);

		// get rid of the DataTable_Array that is created by the IndexedBySite archive type
		if($dataTable instanceof Piwik_DataTable_Array && $allWebsitesRequested)
		{
			$dataTable = $dataTable->mergeChildren();
		}
		else
		{
			if(!$dataTable instanceof Piwik_DataTable_Array)
			{
				$firstDataTableRow = $dataTable->getFirstRow();
				$firstDataTableRow->setColumn('label', $sites);
			}
		}

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
			$this->calculateEvolutionPercentages($dataTable, $pastData, $apiMetrics);
		}

		// remove eCommerce related metrics on non eCommerce Piwik sites
		// note: this is not optimal in terms of performance: those metrics should not be retrieved in the first place
		if($enhanced)
		{
			// $dataTableRows instanceOf Piwik_DataTable_Row[]
			$dataTableRows = $dataTable->getRows();

			foreach($dataTableRows as $dataTableRow)
			{
				$siteId = $dataTableRow->getColumn('label');
				if(!Piwik_Site::isEcommerceEnabledFor($siteId))
				{
					foreach($apiECommerceMetrics as $metricSettings)
					{
						$dataTableRow->deleteColumn($metricSettings[self::METRIC_RECORD_NAME_KEY]);
						$dataTableRow->deleteColumn($metricSettings[self::METRIC_EVOLUTION_COL_NAME_KEY]);
					}
				}
			}
		}

		// move the site id to a metadata column
		$dataTable->filter('ColumnCallbackAddMetadata', array('label', 'idsite'));

		// set the label of each row to the site name
		if($allWebsitesRequested)
		{
			$getNameFor = array('Piwik_Site', 'getNameFor');
			$dataTable->filter('ColumnCallbackReplace', array('label', $getNameFor));
		}
		else
		{
			$dataTable->filter('ColumnDelete', array('label'));
		}

		// replace record names with user friendly metric names
		$dataTable->filter('ReplaceColumnNames', array($columnNameRewrites));

		// Ensures data set sorted, for Metadata output
		$dataTable->filter('Sort', array(self::NB_VISITS_METRIC, 'desc', $naturalSort = false));

		// filter rows without visits
		// note: if only one website is queried and there are no visits, we can not remove the row otherwise Piwik_API_ResponseBuilder throws 'Call to a member function getColumns() on a non-object'
		if($allWebsitesRequested)
		{
			$dataTable->filter(
				'ColumnCallbackDeleteRow',
				array(
					self::NB_VISITS_METRIC,
					create_function ( '$value', 'return $value != 0;')
				)
			);
		}

		return $dataTable;
	}

	/**
	 * Performs a binary filter of two
	 * DataTables in order to correctly calculate evolution metrics.
	 * 
	 * @param Piwik_DataTable|Piwik_DataTable_Array $currentData
	 * @param Piwik_DataTable|Piwik_DataTable_Array $pastData
	 * @param array $fields The array of string fields to calculate evolution
	 *                      metrics for.
	 */
	private function calculateEvolutionPercentages($currentData, $pastData, $apiMetrics)
	{
		if ($currentData instanceof Piwik_DataTable_Array)
		{
			$pastArray = $pastData->getArray();
			foreach ($currentData->getArray() as $subTable)
			{
				$this->calculateEvolutionPercentages($subTable, current($pastArray), $apiMetrics);
				next($pastArray);
			}
		}
		else
		{
			foreach ($apiMetrics as $metricSettings)
			{
				$currentData->filter(
					'Piwik_MultiSites_CalculateEvolutionFilter',
					array(
						$pastData,
						$metricSettings[self::METRIC_EVOLUTION_COL_NAME_KEY],
						$metricSettings[self::METRIC_RECORD_NAME_KEY],
						$quotientPrecision = 2)
				);
			}
		}
	}

	/**
	 * @ignore
	 */
	public static function getApiMetrics($enhanced)
	{
		$metrics = self::$baseMetrics;
		if (Piwik_Common::isGoalPluginEnabled())
		{
			// goal revenue metric
			$metrics[self::GOAL_REVENUE_METRIC] = array(
				self::METRIC_TRANSLATION_KEY => 'Goals_ColumnRevenue',
				self::METRIC_EVOLUTION_COL_NAME_KEY => self::GOAL_REVENUE_METRIC . '_evolution',
				self::METRIC_RECORD_NAME_KEY => Piwik_Goals::getRecordName(self::GOAL_REVENUE_METRIC),
				self::METRIC_IS_ECOMMERCE_KEY => false,
			);

			if($enhanced)
			{
				// number of goal conversions metric
				$metrics[self::GOAL_CONVERSION_METRIC] = array(
					self::METRIC_TRANSLATION_KEY => 'Goals_ColumnConversions',
					self::METRIC_EVOLUTION_COL_NAME_KEY => self::GOAL_CONVERSION_METRIC . '_evolution',
					self::METRIC_RECORD_NAME_KEY => Piwik_Goals::getRecordName(self::GOAL_CONVERSION_METRIC),
					self::METRIC_IS_ECOMMERCE_KEY => false,
				);

				// number of orders
				$metrics[self::ECOMMERCE_ORDERS_METRIC] = array(
					self::METRIC_TRANSLATION_KEY => 'General_EcommerceOrders',
					self::METRIC_EVOLUTION_COL_NAME_KEY => self::ECOMMERCE_ORDERS_METRIC . '_evolution',
					self::METRIC_RECORD_NAME_KEY => Piwik_Goals::getRecordName(self::GOAL_CONVERSION_METRIC ,0),
					self::METRIC_IS_ECOMMERCE_KEY => true,
				);

				// eCommerce revenue
				$metrics[self::ECOMMERCE_REVENUE_METRIC] = array(
					self::METRIC_TRANSLATION_KEY => 'General_ProductRevenue',
					self::METRIC_EVOLUTION_COL_NAME_KEY => self::ECOMMERCE_REVENUE_METRIC . '_evolution',
					self::METRIC_RECORD_NAME_KEY => Piwik_Goals::getRecordName(self::GOAL_REVENUE_METRIC ,0),
					self::METRIC_IS_ECOMMERCE_KEY => true,
				);
			}
		}

		return $metrics;
	}
}
