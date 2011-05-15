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

	public function getInformation() {
		return array(
			'description' => Piwik_Translate('API_PluginDescription'),
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => Piwik_Version::VERSION,
		);
	}
	
	public function getListHooksRegistered() {
		return array(
			'AssetManager.getCssFiles' => 'getCssFiles',
			'TopMenu.add' => 'addTopMenu',
		);
	}
	
	public function addTopMenu() {
		Piwik_AddTopMenu('General_API', array('module' => 'API', 'action' => 'listAllAPI'), true, 7);
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
		        'sqlSegment' => 'location_ip',
		        'sqlFilter' => array('Piwik_IP', 'P2N'),
		        'permission' => Piwik::isUserHasAdminAccess($idSites),
	    );
		$segments[] = array(
		        'type' => 'dimension',
		        'category' => 'Visit',
		        'name' => 'General_VisitorID',
		        'segment' => 'visitorId',
				'acceptedValues' => '34c31e04394bdc63 - any 16 chars ID requested via the Tracking API function getVisitorId()',
		        'sqlSegment' => 'idvisitor',
		        'sqlFilter' => array('Piwik_Common', 'convertVisitorIdToBin'),
	    );
		$segments[] = array(
		        'type' => 'metric',
		        'category' => 'Visit',
		        'name' => 'General_NbActions',
		        'segment' => 'actions',
		        'sqlSegment' => 'visit_total_actions',
	    );
		$segments[] = array(
		        'type' => 'metric',
		        'category' => 'Visit',
		        'name' => 'General_ColumnVisitDuration',
		        'segment' => 'visitDuration',
		        'sqlSegment' => 'visit_total_time',
	    );
		$segments[] = array(
		        'type' => 'dimension',
		        'category' => 'Visit',
		        'name' => 'General_VisitType',
		        'segment' => 'visitorType',
		        'acceptedValues' => 'new, returning',
		        'sqlSegment' => 'visitor_returning',
		        'sqlFilter' => create_function('$type', 'return $type == "new" ? 0 : 1;'),
	    );
		$segments[] = array(
		        'type' => 'metric',
		        'category' => 'Visit',
		        'name' => 'General_DaysSinceLastVisit',
		        'segment' => 'daysSinceLastVisit',
		        'sqlSegment' => 'visitor_days_since_last',
	    );
		$segments[] = array(
		        'type' => 'metric',
		        'category' => 'Visit',
		        'name' => 'General_DaysSinceFirstVisit',
		        'segment' => 'daysSinceFirstVisit',
		        'sqlSegment' => 'visitor_days_since_first',
	    );
		$segments[] = array(
		        'type' => 'metric',
		        'category' => 'Visit',
		        'name' => 'General_NumberOfVisits',
		        'segment' => 'visitCount',
		        'sqlSegment' => 'visitor_count_visits',
	    );
	    
		$segments[] = array(
		        'type' => 'metric',
		        'category' => 'Visit',
		        'name' => 'General_VisitConvertedGoal',
		        'segment' => 'visitConverted',
				'acceptedValues' => '0, 1',
		        'sqlSegment' => 'visit_goal_converted',
	    );
	    
		$segments[] = array(
		        'type' => 'metric',
		        'category' => 'Visit',
		        'name' => Piwik_Translate('General_EcommerceVisitStatus', '"&segment=visitEcommerceStatus==ordered,visitEcommerceStatus==orderedThenAbandonedCart"'),
		        'segment' => 'visitEcommerceStatus',
				'acceptedValues' => implode(", ", self::$visitEcommerceStatus),
		        'sqlSegment' => 'visit_goal_buyer',
		        'sqlFilter' => array('Piwik_API_API', 'getVisitEcommerceStatus'),
	    );
	    
	    $segments[] = array(
		        'type' => 'metric',
		        'category' => 'Visit',
		        'name' => 'General_DaysSinceLastEcommerceOrder',
		        'segment' => 'daysSinceLastEcommerceOrder',
		        'sqlSegment' => 'visitor_days_since_order',
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
			$compare = -1 * strcmp($row1[$column], $row2[$column]);
			if($compare != 0){
				return $compare;
			}
		}
		return $compare;
	}
    /**
     * Loads reports metadata, then return the requested one,
     * matching optional API parameters.
     */
	public function getMetadata($idSite, $apiModule, $apiAction, $apiParameters = array(), $language = false, $period = false)
    {
    	Piwik_Translate::getInstance()->reloadLanguage($language);
    	static $reportsMetadata = array();
    	$cacheKey = $idSite.$language;
    	if(!isset($reportsMetadata[$cacheKey]))
    	{
    		$reportsMetadata[$cacheKey] = $this->getReportMetadata($idSite);
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
				if(empty($apiParameters))
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
	public function getReportMetadata($idSites = '')
	{
		$idSites = Piwik_Site::getIdSitesFromIdSitesString($idSites);
		
		$availableReports = array();
		Piwik_PostEvent('API.getReportMetadata', $availableReports, $idSites);
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
		Piwik_PostEvent('API.getReportMetadata.end', $availableReports, $idSites);
		
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

	public function getProcessedReport($idSite, $period, $date, $apiModule, $apiAction, $segment = false, $apiParameters = false, $language = false, $showTimer = true)
    {
    	$timer = new Piwik_Timer();
    	if($apiParameters === false)
    	{
    		$apiParameters = array();
    	}
        // Is this report found in the Metadata available reports?
        $reportMetadata = $this->getMetadata($idSite, $apiModule, $apiAction, $apiParameters, $language, $period);
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
        // Table with a Dimension (Keywords, Pages, Browsers, etc.)
        if(isset($reportMetadata['dimension']))
        {
        	$callback = 'handleTableReport';
        }
        // Table without a dimension, simple list of general metrics (eg. VisitsSummary.get)
        else
        {
        	$callback = 'handleTableSimple';
        }
    	list($newReport, $columns, $rowsMetadata) = $this->$callback($idSite, $period, $dataTable, $reportMetadata);
    	foreach($columns as $columnId => &$name)
    	{
    		$name = ucfirst($name);
    	}
    	$website = new Piwik_Site($idSite);
//    	$segment = new Piwik_Segment($segment, $idSite);
    	if($period == 'range')
    	{
	    	$period = new Piwik_Period_Range($period, $date);
    	}
    	else
    	{
	    	$period = Piwik_Period::factory($period, Piwik_Date::factory($date));
    	}
    	
    	$return = array(
				'website' => $website->getName(),
				'prettyDate' => $period->getLocalizedLongString(),
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
    
    private function handleTableSimple($idSite, $period, $dataTable, $reportMetadata)
    {
        $renderer = new Piwik_DataTable_Renderer_Php();
        $renderer->setTable($dataTable);
        $renderer->setSerialize(false);
        $reportTable = $renderer->render();

        $newReport = array();
        foreach($reportTable as $metric => $value)
        {
        	// Use translated metric from metadata
        	// If translation not found, do not display the returned data
        	if(isset($reportMetadata['metrics'][$metric]))
        	{
        		$value = Piwik::getPrettyValue($idSite, $metric, $value, $htmlAllowed = false, $timeAsSentence = false);
    		
        		$metric = $reportMetadata['metrics'][$metric];
            	$newReport[] = array(
            		'label' => $metric,
            		'value' => $value
            	);
        	}
        }
        
        $columns = array(
        	'label' => Piwik_Translate('General_Name'),
        	'value' => Piwik_Translate('General_Value'),
        );
    	return array(
    		$newReport,
    		$columns,
    		$rowsMetadata = array()
    	);
    }
    
    private function handleTableReport($idSite, $period, $dataTable, &$reportMetadata)
    {
    	// displayed columns
    	$columns = array_merge(
    		array('label' => $reportMetadata['dimension'] ),
    		$reportMetadata['metrics']
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
        	$dataTable->filter('AddColumnsProcessedMetrics');
        }
        $renderer = new Piwik_DataTable_Renderer_Php();
        $renderer->setTable($dataTable);
        $renderer->setSerialize(false);
        $reportTable = $renderer->render();
    	$rowsMetadata = array();
    	
    	$newReport = array();
    	foreach($reportTable as $rowId => $row)
    	{
    		// ensure all displayed columns have 0 values
    		foreach($columns as $id => $name)
    		{
    			if(!isset($row[$id]))
    			{
    				$row[$id] = 0;
    			}
    		}
    		$newRow = array();
    		foreach($row as $columnId => $value)
    		{
    			// Keep displayed columns
    			if(isset($columns[$columnId]))
    			{
        			$newRow[$columnId] = Piwik::getPrettyValue($idSite, $columnId, $value, $htmlAllowed = false, $timeAsSentence = false);
    			}
        		// We try and only keep metadata
        		// - if the column value is not an array (eg. metrics per goal)
        		// - if the column name doesn't contain _ (which is by standard, a metric column)
    			elseif(!is_array($value)
    				&& strpos($columnId, '_') === false
    				)
    			{
    				$rowsMetadata[$rowId][$columnId] = $value;
    			}
    		}
    		$newReport[] = $newRow;
    	}
    	return array(
    		$newReport,
    		$columns,
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
