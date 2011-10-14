<?php

/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_API
 */

/**
 * @package Piwik_API
 */
class Piwik_API extends Piwik_Plugin {

	public function getInformation() 
	{
		return array(
			'description' => Piwik_Translate('API_PluginDescription'),
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => Piwik_Version::VERSION,
		);
	}
	
	public function getListHooksRegistered() 
	{
		return array(
			'AssetManager.getCssFiles' => 'getCssFiles',
			'TopMenu.add' => 'addTopMenu',
		);
	}
	
	public function addTopMenu() 
	{
		Piwik_AddTopMenu('General_API', array('module' => 'API', 'action' => 'listAllAPI'), true, 7);
		
		if(empty($_SERVER['HTTP_USER_AGENT']))
		{
			return;
		}
		require_once PIWIK_INCLUDE_PATH . '/libs/UserAgentParser/UserAgentParser.php';
		$os = UserAgentParser::getOperatingSystem($_SERVER['HTTP_USER_AGENT']);
		if($os && in_array($os['id'], array('AND', 'IPD', 'IPA', 'IPH')))
		{
			Piwik_AddTopMenu('Piwik Mobile App', array('module' => 'Proxy', 'action' => 'redirect', 'url' => 'http://piwik.org/mobile/'), true, 4);
		}
	}

	public function getCssFiles($notification) {
		$cssFiles = &$notification->getNotificationObject();
		
		$cssFiles[] = "plugins/API/css/styles.css";
	}
}


/**
 * This API is the <a href='http://piwik.org/docs/analytics-api/metadata/' target='_blank'>Metadata API</a>: it gives information about all other available APIs methods, as well as providing
 * human readable and more complete outputs than normal API methods.
 *
 * Some of the information that is returned by the Metadata API:
 * <ul>
 * <li>the dynamically generated list of all API methods via "getReportMetadata"</li>
 * <li>the list of metrics that will be returned by each method, along with their human readable name, via "getDefaultMetrics" and "getDefaultProcessedMetrics"</li>
 * <li>the list of segments metadata supported by all functions that have a 'segment' parameter</li>
 * <li>the (truly magic) method "getProcessedReport" will return a human readable version of any other report, and include the processed metrics such as
 * conversion rate, time on site, etc. which are not directly available in other methods.
 * </ul>
 * The Metadata API is for example used by the Piwik Mobile App to automatically display all Piwik reports, with translated report & columns names and nicely formatted values.
 * More information on the <a href='http://piwik.org/docs/analytics-api/metadata/' target='_blank'>Metadata API documentation page</a>
 *
 * @package Piwik_API
 */
class Piwik_API_API
{
	static private $instance = null;

	/**
	 * @return Piwik_API_API
	 */
	static public function getInstance()
	{
		if (self::$instance == null)
		{
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function getDefaultMetrics()
	{
		$translations = array(
			// Standard metrics
    		'nb_visits' => 'General_ColumnNbVisits',
    		'nb_uniq_visitors' => 'General_ColumnNbUniqVisitors',
    		'nb_actions' => 'General_ColumnNbActions',
// Do not display these in reports, as they are not so relevant
// They are used to process metrics below
//			'nb_visits_converted' => 'General_ColumnVisitsWithConversions',
//    		'max_actions' => 'General_ColumnMaxActions',
//    		'sum_visit_length' => 'General_ColumnSumVisitLength',
//			'bounce_count'
		);
		$translations = array_map('Piwik_Translate', $translations);
		return $translations;
	}

	public function getDefaultProcessedMetrics()
	{
		$translations = array(
			// Processed in AddColumnsProcessedMetrics
			'nb_actions_per_visit' => 'General_ColumnActionsPerVisit',
    		'avg_time_on_site' => 'General_ColumnAvgTimeOnSite',
    		'bounce_rate' => 'General_ColumnBounceRate',
    		'conversion_rate' => 'General_ColumnConversionRate',
		);
		return array_map('Piwik_Translate', $translations);
	}
	
	public function getDefaultMetricsDocumentation()
	{
		$documentation = array(
			'nb_visits' => 'General_ColumnNbVisitsDocumentation',
    		'nb_uniq_visitors' => 'General_ColumnNbUniqVisitorsDocumentation',
    		'nb_actions' => 'General_ColumnNbActionsDocumentation',
			'nb_actions_per_visit' => 'General_ColumnActionsPerVisitDocumentation',
    		'avg_time_on_site' => 'General_ColumnAvgTimeOnSiteDocumentation',
    		'bounce_rate' => 'General_ColumnBounceRateDocumentation',
    		'conversion_rate' => 'General_ColumnConversionRateDocumentation',
			'avg_time_on_page' => 'General_ColumnAverageTimeOnPageDocumentation',
			'nb_hits' => 'General_ColumnPageviewsDocumentation',
			'exit_rate' => 'General_ColumnExitRateDocumentation'
		);
		return array_map('Piwik_Translate', $documentation);
	}
	
	public function getSegmentsMetadata($idSites = array(), $_hideImplementationData = true)
	{
		$segments = array();
		Piwik_PostEvent('API.getSegmentsMetadata', $segments, $idSites);
		
		$segments[] = array(
		        'type' => 'dimension',
		        'category' => 'Visit',
		        'name' => 'General_VisitorIP',
		        'segment' => 'visitIp',
				'acceptedValues' => '13.54.122.1, etc.',
		        'sqlSegment' => 'log_visit.location_ip',
		        'sqlFilter' => array('Piwik_IP', 'P2N'),
		        'permission' => Piwik::isUserHasAdminAccess($idSites),
	    );
		$segments[] = array(
		        'type' => 'dimension',
		        'category' => 'Visit',
		        'name' => 'General_VisitorID',
		        'segment' => 'visitorId',
				'acceptedValues' => '34c31e04394bdc63 - any 16 chars ID requested via the Tracking API function getVisitorId()',
		        'sqlSegment' => 'log_visit.idvisitor',
		        'sqlFilter' => array('Piwik_Common', 'convertVisitorIdToBin'),
	    );
		$segments[] = array(
		        'type' => 'metric',
		        'category' => 'Visit',
		        'name' => 'General_NbActions',
		        'segment' => 'actions',
		        'sqlSegment' => 'log_visit.visit_total_actions',
	    );
		$segments[] = array(
		        'type' => 'metric',
		        'category' => 'Visit',
		        'name' => 'General_ColumnVisitDuration',
		        'segment' => 'visitDuration',
		        'sqlSegment' => 'log_visit.visit_total_time',
	    );
		$segments[] = array(
		        'type' => 'dimension',
		        'category' => 'Visit',
		        'name' => Piwik_Translate('General_VisitType') . ". ".Piwik_Translate('General_VisitTypeExample', '"&segment=visitorType==returning,visitorType==returningCustomer"'),
		        'segment' => 'visitorType',
		        'acceptedValues' => 'new, returning, returningCustomer',
		        'sqlSegment' => 'log_visit.visitor_returning',
		        'sqlFilter' => create_function('$type', 'return $type == "new" ? 0 : ($type == "returning" ? 1 : 2);'),
	    );
		$segments[] = array(
		        'type' => 'metric',
		        'category' => 'Visit',
		        'name' => 'General_DaysSinceLastVisit',
		        'segment' => 'daysSinceLastVisit',
		        'sqlSegment' => 'log_visit.visitor_days_since_last',
	    );
		$segments[] = array(
		        'type' => 'metric',
		        'category' => 'Visit',
		        'name' => 'General_DaysSinceFirstVisit',
		        'segment' => 'daysSinceFirstVisit',
		        'sqlSegment' => 'log_visit.visitor_days_since_first',
	    );
		$segments[] = array(
		        'type' => 'metric',
		        'category' => 'Visit',
		        'name' => 'General_NumberOfVisits',
		        'segment' => 'visitCount',
		        'sqlSegment' => 'log_visit.visitor_count_visits',
	    );
	    
		$segments[] = array(
		        'type' => 'dimension',
		        'category' => 'Visit',
		        'name' => 'General_VisitConvertedGoal',
		        'segment' => 'visitConverted',
				'acceptedValues' => '0, 1',
		        'sqlSegment' => 'log_visit.visit_goal_converted',
	    );
	    
		$segments[] = array(
		        'type' => 'dimension',
		        'category' => 'Visit',
		        'name' => Piwik_Translate('General_EcommerceVisitStatus', '"&segment=visitEcommerceStatus==ordered,visitEcommerceStatus==orderedThenAbandonedCart"'),
		        'segment' => 'visitEcommerceStatus',
				'acceptedValues' => implode(", ", self::$visitEcommerceStatus),
		        'sqlSegment' => 'log_visit.visit_goal_buyer',
		        'sqlFilter' => array('Piwik_API_API', 'getVisitEcommerceStatus'),
	    );
	    
	    $segments[] = array(
		        'type' => 'metric',
		        'category' => 'Visit',
		        'name' => 'General_DaysSinceLastEcommerceOrder',
		        'segment' => 'daysSinceLastEcommerceOrder',
		        'sqlSegment' => 'log_visit.visitor_days_since_order',
	    );
	    
		foreach ($segments as &$segment)
		{
		    $segment['name'] = Piwik_Translate($segment['name']);
		    $segment['category'] = Piwik_Translate($segment['category']);
		    
		    if($_hideImplementationData)
		    {
		    	unset($segment['sqlFilter']);
		    	unset($segment['sqlSegment']);
		    }
		}
		
		usort($segments, array($this, 'sortSegments'));
		return $segments;
	}
	
	static protected $visitEcommerceStatus = array(
		Piwik_Tracker_GoalManager::TYPE_BUYER_NONE => 'none',
		Piwik_Tracker_GoalManager::TYPE_BUYER_ORDERED => 'ordered',
		Piwik_Tracker_GoalManager::TYPE_BUYER_OPEN_CART => 'abandonedCart',
		Piwik_Tracker_GoalManager::TYPE_BUYER_ORDERED_AND_OPEN_CART => 'orderedThenAbandonedCart',
	);
	
	static public function getVisitEcommerceStatusFromId($id)
	{
		if(!isset(self::$visitEcommerceStatus[$id]))
		{
			throw new Exception("Unexpected ECommerce status value ");
		}
		return self::$visitEcommerceStatus[$id];
	}
	
	static public function getVisitEcommerceStatus($status)
	{
		$id = array_search($status, self::$visitEcommerceStatus);
		if($id === false)
		{
			throw new Exception("Invalid 'visitEcommerceStatus' segment value");
		}
		return $id;
	}
	
	private function sortSegments($row1, $row2)
	{
		$columns = array('type', 'category', 'name', 'segment');
		foreach($columns as $column)
		{
			// Keep segments ordered alphabetically inside categories..
			$type = -1;
			if($column == 'name') $type = 1;
			$compare = $type * strcmp($row1[$column], $row2[$column]);
			
			// hack so that custom variables "page" are grouped together in the doc
			if($row1['category'] == Piwik_Translate('CustomVariables_CustomVariables')
				&& $row1['category'] == $row2['category']) {
				$compare = strcmp($row1['segment'], $row2['segment']);
				return $compare;
			}
			if($compare != 0){
				return $compare;
			}
		}
		return $compare;
	}
	
	/**
	 * Returns the url to application logo (~280x110px)
	 *
	 * @param bool $pathOnly If true, returns path relative to doc root. Otherwise, returns a URL.
	 * @return string
	 */
	public function getLogoUrl($pathOnly=false)
	{
        $logo = 'themes/default/images/logo.png';
	    if(Zend_Registry::get('config')->branding->use_custom_logo == 1 
	    	&& file_exists(Piwik_Common::getPathToPiwikRoot() .'/themes/logo.png')) 
	    {
	        $logo = 'themes/logo.png';
	    } 
	    if(!$pathOnly) {
	        return Piwik::getPiwikUrl() . $logo;
	    } 
	    return Piwik_Common::getPathToPiwikRoot() .'/'. $logo;
	}
	
	/**
	 * Returns the url to header logo (~127x50px)
	 *
	 * @param bool $pathOnly If true, returns path relative to doc root. Otherwise, returns a URL.
	 * @return string
	 */
	public function getHeaderLogoUrl($pathOnly=false)
	{
        $logo = 'themes/default/images/logo-header.png';
	    if(Zend_Registry::get('config')->branding->use_custom_logo == 1 
	    	&& file_exists(Piwik_Common::getPathToPiwikRoot() .'/themes/logo-header.png')) 
	    {
	        $logo = 'themes/logo-header.png';
	    } 
	    if(!$pathOnly) {
	        return Piwik::getPiwikUrl() . $logo;
	    }
	    return Piwik_Common::getPathToPiwikRoot() .'/'. $logo;
	}
	
    /**
     * Loads reports metadata, then return the requested one,
     * matching optional API parameters.
     */
	public function getMetadata($idSite, $apiModule, $apiAction, $apiParameters = array(), $language = false, $period = false, $date = false)
    {
    	Piwik_Translate::getInstance()->reloadLanguage($language);
    	static $reportsMetadata = array();
    	$cacheKey = $idSite.$language;
    	if(!isset($reportsMetadata[$cacheKey]))
    	{
    		$reportsMetadata[$cacheKey] = $this->getReportMetadata($idSite, $period, $date);
    	}
    	
    	foreach($reportsMetadata[$cacheKey] as $report)
    	{
    		// See ArchiveProcessing/Period.php - unique visitors are not processed for period != day
	    	if($period != 'day'
	    		&& !($apiModule == 'VisitsSummary'
	    			&& $apiAction == 'get'))
	    	{
	    		unset($report['metrics']['nb_uniq_visitors']);
	    	}
    		if($report['module'] == $apiModule
    			&& $report['action'] == $apiAction)
			{
				// No custom parameters 
				if(empty($apiParameters)
					&& empty($report['parameters']))
				{
        			return array($report);
				}
				if(empty($report['parameters']))
				{
					continue;
				}
				$diff = array_diff($report['parameters'], $apiParameters);
				if(empty($diff))
				{
					return array($report);
				}
			}
    	}
    	return false;
    }
    
	/**
	 * Triggers a hook to ask plugins for available Reports.
	 * Returns metadata information about each report (category, name, dimension, metrics, etc.)
	 *
	 * @param string $idSites Comma separated list of website Ids
	 * @return array
	 */
	public function getReportMetadata($idSites = '', $period = false, $date = false)
	{
		$idSites = Piwik_Site::getIdSitesFromIdSitesString($idSites);
		if(!empty($idSites))
		{
			Piwik::checkUserHasViewAccess($idSites);
		}
		
		$parameters = array( 'idSites' => $idSites, 'period' => $period, 'date' => $date);
		
		$availableReports = array();
		Piwik_PostEvent('API.getReportMetadata', $availableReports, $parameters);
		foreach ($availableReports as &$availableReport) {
			if (!isset($availableReport['metrics'])) {
				$availableReport['metrics'] = $this->getDefaultMetrics();
			}
			if (!isset($availableReport['processedMetrics'])) {
				$availableReport['processedMetrics'] = $this->getDefaultProcessedMetrics();
			}
			if (!isset($availableReport['metricsDocumentation'])) {
				$availableReport['metricsDocumentation'] = $this->getDefaultMetricsDocumentation();
			}
		}
		
		// Some plugins need to add custom metrics after all plugins hooked in
		Piwik_PostEvent('API.getReportMetadata.end', $availableReports, $parameters);
		
		// Sort results to ensure consistent order
		usort($availableReports, array($this, 'sort'));

		$knownMetrics = array_merge( $this->getDefaultMetrics(), $this->getDefaultProcessedMetrics() );
		foreach($availableReports as &$availableReport)
		{
			// Ensure all metrics have a translation
			$metrics = $availableReport['metrics'];
			$cleanedMetrics = array();
			foreach($metrics as $metricId => $metricTranslation)
			{
				// When simply the column name was given, ie 'metric' => array( 'nb_visits' )
				// $metricTranslation is in this case nb_visits. We look for a known translation.
				if(is_numeric($metricId)
					&& isset($knownMetrics[$metricTranslation]))
				{
					$metricId = $metricTranslation;
					$metricTranslation = $knownMetrics[$metricTranslation];
				}
				$cleanedMetrics[$metricId] = $metricTranslation;
			}
			$availableReport['metrics'] = $cleanedMetrics;
			
			// Remove array elements that are false (to clean up API output)
			foreach($availableReport as $attributeName => $attributeValue)
			{
				if(empty($attributeValue))
				{
					unset($availableReport[$attributeName]);
				}
			}
        	// when there are per goal metrics, don't display conversion_rate since it can differ from per goal sum
	        if(isset($availableReport['metricsGoal']))
	        {
	        	unset($availableReport['processedMetrics']['conversion_rate']);
	        	unset($availableReport['metricsGoal']['conversion_rate']);
	        }
			
			// Processing a uniqueId for each report,
			// can be used by UIs as a key to match a given report
			$uniqueId = $availableReport['module'] . '_' . $availableReport['action'];
			if(!empty($availableReport['parameters']))
			{
				foreach($availableReport['parameters'] as $key => $value)
				{
					$uniqueId .= '_' . $key . '--' . $value;
				}
			}
			$availableReport['uniqueId'] = $uniqueId;
			
			// Order is used to order reports internally, but not meant to be used outside
			unset($availableReport['order']);
		}
		
		return $availableReports;
	}

	public function getProcessedReport($idSite, $period, $date, $apiModule, $apiAction, $segment = false, $apiParameters = false, $idGoal = false, $language = false, $showTimer = true)
    {
    	$timer = new Piwik_Timer();
    	if($apiParameters === false)
    	{
    		$apiParameters = array();
    	}
		if(!empty($idGoal)
			&& empty($apiParameters['idGoal']))
		{
			$apiParameters['idGoal'] = $idGoal;
		}
        // Is this report found in the Metadata available reports?
        $reportMetadata = $this->getMetadata($idSite, $apiModule, $apiAction, $apiParameters, $language, $period, $date);
        if(empty($reportMetadata))
        {
        	throw new Exception("Requested report $apiModule.$apiAction for Website id=$idSite not found in the list of available reports. \n");
        }
        $reportMetadata = reset($reportMetadata);
        
		// Generate Api call URL passing custom parameters
		$parameters = array_merge( $apiParameters, array(
			'method' => $apiModule.'.'.$apiAction,
			'idSite' => $idSite,
			'period' => $period,
			'date' => $date,
			'format' => 'original',
			'serialize' => '0',
			'language' => $language,
		));
		if(!empty($segment)) $parameters['segment'] = $segment;
		
		$url = Piwik_Url::getQueryStringFromParameters($parameters);
        $request = new Piwik_API_Request($url);
        try {
        	/** @var Piwik_DataTable */
        	$dataTable = $request->process();
        } catch(Exception $e) {
        	throw new Exception("API returned an error: ".$e->getMessage()."\n");
        }

    	list($newReport, $columns, $rowsMetadata) = $this->handleTableReport($idSite, $dataTable, $reportMetadata, isset($reportMetadata['dimension']));
    	foreach($columns as $columnId => &$name)
    	{
    		$name = ucfirst($name);
    	}
    	$website = new Piwik_Site($idSite);
//    	$segment = new Piwik_Segment($segment, $idSite);

		if(Piwik_Archive::isMultiplePeriod($date, $period))
		{
			$period =  new Piwik_Period_Range($period, $date);
		}
		else
		{
			if($period == 'range')
			{
				$period = new Piwik_Period_Range($period, $date);
			}
			else
			{
				$period = Piwik_Period::factory($period, Piwik_Date::factory($date));
			}
		}

		$period = $period->getLocalizedLongString();
    	
    	$return = array(
				'website' => $website->getName(),
				'prettyDate' => $period,
//    			'prettySegment' => $segment->getPrettyString(),
				'metadata' => $reportMetadata,
				'columns' => $columns,
				'reportData' =>	$newReport,
				'reportMetadata' => $rowsMetadata,
		);
		if($showTimer)
		{
			$return['timerMillis'] = $timer->getTimeMs(0);
		}
		return $return;
    }

	/**
	 * Enhance a $dataTable using metadata :
	 *
	 * - remove metrics based on $reportMetadata['metrics']
	 * - add 0 valued metrics if $dataTable doesn't provide all $reportMetadata['metrics']
	 * - format metric values to a 'human readable' format
	 * - extract row metadata to a separate Piwik_DataTable_Simple|Piwik_DataTable_Array : $rowsMetadata
	 * - translate metric names to a separate array : $columns
	 *
	 * @param int $idSite enables monetary value formatting based on site currency
	 * @param Piwik_DataTable|Piwik_DataTable_Array $dataTable
	 * @param array $reportMetadata
	 * @param boolean $hasDimension
	 * @return array Piwik_DataTable_Simple|Piwik_DataTable_Array $newReport with human readable format & array $columns list of translated column names & Piwik_DataTable_Simple|Piwik_DataTable_Array $rowsMetadata
	**/
    private function handleTableReport($idSite, $dataTable, &$reportMetadata, $hasDimension)
    {
    	$columns = $reportMetadata['metrics'];

		if($hasDimension)
		{
			$columns = array_merge(
				array('label' => $reportMetadata['dimension'] ),
				$columns
			);

			if(isset($reportMetadata['processedMetrics']))
			{
				$processedMetricsAdded = $this->getDefaultProcessedMetrics();
				foreach($processedMetricsAdded as $processedMetricId => $processedMetricTranslation)
				{
					// this processed metric can be displayed for this report
					if(isset($reportMetadata['processedMetrics'][$processedMetricId]))
					{
						$columns[$processedMetricId] = $processedMetricTranslation;
					}
				}
			}

			// Display the global Goal metrics
			if(isset($reportMetadata['metricsGoal']))
			{
				$metricsGoalDisplay = array('revenue');
				// Add processed metrics to be displayed for this report
				foreach($metricsGoalDisplay as $goalMetricId)
				{
					if(isset($reportMetadata['metricsGoal'][$goalMetricId]))
					{
						$columns[$goalMetricId] = $reportMetadata['metricsGoal'][$goalMetricId];
					}
				}
			}

			if(isset($reportMetadata['processedMetrics']))
			{
				// Add processed metrics
				$dataTable->filter('AddColumnsProcessedMetrics', array($deleteRowsWithNoVisit = false));
			}
		}

		// $dataTable is an instance of Piwik_DataTable_Array when multiple periods requested
		if ($dataTable instanceof Piwik_DataTable_Array)
		{
			// Need a new Piwik_DataTable_Array to store the 'human readable' values
			$newReport = new Piwik_DataTable_Array();
			$newReport->setKeyName("prettyDate");
			$dataTableMetadata = $dataTable->metadata;
			$newReport->metadata = $dataTableMetadata;

			// Need a new Piwik_DataTable_Array to store report metadata
			$rowsMetadata = new Piwik_DataTable_Array();
			$rowsMetadata->setKeyName("prettyDate");

			// Process each Piwik_DataTable_Simple entry
			foreach($dataTable->getArray() as $label => $simpleDataTable)
			{
				list($enhancedSimpleDataTable, $rowMetadata) = $this->handleSimpleDataTable($idSite, $simpleDataTable, $columns, $hasDimension);

				$period = $dataTableMetadata[$label]['period']->getLocalizedLongString();
				$newReport->addTable($enhancedSimpleDataTable, $period);
				$rowsMetadata->addTable($rowMetadata, $period);
			}
		}
		else
		{
			list($newReport, $rowsMetadata) = $this->handleSimpleDataTable($idSite, $dataTable, $columns, $hasDimension);
		}

    	return array(
    		$newReport,
    		$columns,
    		$rowsMetadata
    	);
    }

	/**
	 * Enhance $simpleDataTable using metadata :
	 *
	 * - remove metrics based on $reportMetadata['metrics']
	 * - add 0 valued metrics if $simpleDataTable doesn't provide all $reportMetadata['metrics']
	 * - format metric values to a 'human readable' format
	 * - extract row metadata to a separate Piwik_DataTable_Simple $rowsMetadata
	 *
	 * @param int $idSite enables monetary value formatting based on site currency
	 * @param Piwik_DataTable_Simple $simpleDataTable
	 * @param array $metadataColumns
	 * @param boolean $hasDimension
	 * @return array Piwik_DataTable $enhancedDataTable filtered metrics with human readable format & Piwik_DataTable_Simple $rowsMetadata
	 */
	private function handleSimpleDataTable($idSite, $simpleDataTable, $metadataColumns, $hasDimension)
	{
		// new DataTable to store metadata
		$rowsMetadata = new Piwik_DataTable();
		
		// new DataTable to store 'human readable' values
		if($hasDimension)
		{
			$enhancedDataTable = new Piwik_DataTable();
		}
		else
		{
			$enhancedDataTable = new Piwik_DataTable_Simple();
		}

		// add missing metrics
		foreach($simpleDataTable->getRows() as $row)
		{
			$rowMetrics = $row->getColumns();
    		foreach($metadataColumns as $id => $name)
    		{
    			if(!isset($rowMetrics[$id]))
    			{
					$row->addColumn($id, 0);
    			}
    		}
		}

		foreach($simpleDataTable->getRows() as $row)
		{
			$enhancedRow = new Piwik_DataTable_Row();
			$enhancedDataTable->addRow($enhancedRow);

			$rowMetrics = $row->getColumns();
			foreach($rowMetrics as $columnName => $columnValue)
			{
				// filter metrics according to metadata definition
				if(isset($metadataColumns[$columnName]))
				{
					// generate 'human readable' metric values
					$prettyValue = Piwik::getPrettyValue($idSite, $columnName, $columnValue, false, false);
					$enhancedRow->addColumn($columnName, $prettyValue);
				}
			}

			// If report has a dimension, extract metadata into a distinct DataTable
			if($hasDimension)
			{
				$rowMetadata = $row->getMetadata();
				$idSubDataTable = $row->getIdSubDataTable();

				// Create a row metadata only if there are metadata to insert
				if(count($rowMetadata) > 0 || !is_null($idSubDataTable))
				{
					$metadataRow = new Piwik_DataTable_Row();
					$rowsMetadata->addRow($metadataRow);

					foreach($rowMetadata as $metadataKey => $metadataValue)
					{
						$metadataRow->addColumn($metadataKey, $metadataValue);
					}

					if(!is_null($idSubDataTable))
					{
						$metadataRow->addColumn('idsubdatatable', $idSubDataTable);
					}
				}
			}
		}

		return array(
    		$enhancedDataTable,
    		$rowsMetadata
    	);
	}

	/**
	 * API metadata are sorted by category/name,
	 * with a little tweak to replicate the standard Piwik category ordering
	 *
	 * @param string $a
	 * @param string $b
	 * @return int
	 */
	private function sort($a, $b)
	{
		static $order = null;
		if(is_null($order))
		{
			$order = array(
				Piwik_Translate('VisitsSummary_VisitsSummary'),
				Piwik_Translate('Actions_Actions'),
				Piwik_Translate('Referers_Referers'),
				Piwik_Translate('Goals_Goals'),
				Piwik_Translate('General_Visitors'),
				Piwik_Translate('UserSettings_VisitorSettings'),
			);
		}
		return ($category = strcmp(array_search($a['category'], $order), array_search($b['category'], $order))) == 0
				?  (@$a['order'] < @$b['order'] ? -1 : 1)
				: $category;
	}
}
