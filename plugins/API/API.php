<?php

/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_API
 */

/**
 * @package Piwik_API
 */
class Piwik_API extends Piwik_Plugin
{

    public function getInformation()
    {
        return array(
            'description'     => Piwik_Translate('API_PluginDescription'),
            'author'          => 'Piwik',
            'author_homepage' => 'http://piwik.org/',
            'version'         => Piwik_Version::VERSION,
        );
    }

    public function getListHooksRegistered()
    {
        return array(
            'AssetManager.getCssFiles' => 'getCssFiles',
            'TopMenu.add'              => 'addTopMenu',
        );
    }

    public function addTopMenu()
    {
        $apiUrlParams = array('module' => 'API', 'action' => 'listAllAPI', 'segment' => false);
        $tooltip = Piwik_Translate('API_TopLinkTooltip');

        Piwik_AddTopMenu('General_API', $apiUrlParams, true, 7, $isHTML = false, $tooltip);

        $this->addTopMenuMobileApp();
    }

    protected function addTopMenuMobileApp()
    {
        if (empty($_SERVER['HTTP_USER_AGENT'])) {
            return;
        }
        require_once PIWIK_INCLUDE_PATH . '/libs/UserAgentParser/UserAgentParser.php';
        $os = UserAgentParser::getOperatingSystem($_SERVER['HTTP_USER_AGENT']);
        if ($os && in_array($os['id'], array('AND', 'IPD', 'IPA', 'IPH'))) {
            Piwik_AddTopMenu('Piwik Mobile App', array('module' => 'Proxy', 'action' => 'redirect', 'url' => 'http://piwik.org/mobile/'), true, 4);
        }
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     */
    public function getCssFiles($notification)
    {
        $cssFiles = & $notification->getNotificationObject();

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
 * conversion rate, time on site, etc. which are not directly available in other methods.</li>
 * <li>the method "getSuggestedValuesForSegment" returns top suggested values for a particular segment. It uses the Live.getLastVisitsDetails API to fetch the most recently used values, and will return the most often used values first.</li>
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
        if (self::$instance == null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Get Piwik version
     * @return string
     */
    public function getPiwikVersion()
    {
        Piwik::checkUserHasSomeViewAccess();
        return Piwik_Version::VERSION;
    }

    /**
     * Returns the section [APISettings] if defined in config.ini.php
     * @return array
     */
    public function getSettings()
    {
        return Piwik_Config::getInstance()->APISettings;
    }

    /**
     * Derive the unit name from a column name
     * @param $column
     * @param $idSite
     * @return string
     * @ignore
     */
    static public function getUnit($column, $idSite)
    {
        $nameToUnit = array(
            '_rate'   => '%',
            'revenue' => Piwik::getCurrency($idSite),
            '_time_'  => 's'
        );

        foreach ($nameToUnit as $pattern => $type) {
            if (strpos($column, $pattern) !== false) {
                return $type;
            }
        }

        return '';
    }

    /**
     * Is a lower value for a given column better?
     * @param $column
     * @return bool
     *
     * @ignore
     */
    static public function isLowerValueBetter($column)
    {
        $lowerIsBetterPatterns = array(
            'bounce', 'exit'
        );

        foreach ($lowerIsBetterPatterns as $pattern) {
            if (strpos($column, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Default translations for many core metrics.
     * This is used for exports with translated labels. The exports contain columns that
     * are not visible in the UI and not present in the API meta data. These columns are
     * translated here.
     * @return array
     */
    static public function getDefaultMetricTranslations()
    {
        $trans = array(
            'label'                         => 'General_ColumnLabel',
            'date'                          => 'General_Date',
            'avg_time_on_page'              => 'General_ColumnAverageTimeOnPage',
            'sum_time_spent'                => 'General_ColumnSumVisitLength',
            'sum_visit_length'              => 'General_ColumnSumVisitLength',
            'bounce_count'                  => 'General_ColumnBounces',
            'bounce_count_returning'        => 'VisitFrequency_ColumnBounceCountForReturningVisits',
            'max_actions'                   => 'General_ColumnMaxActions',
            'max_actions_returning'         => 'VisitFrequency_ColumnMaxActionsInReturningVisit',
            'nb_visits_converted_returning' => 'VisitFrequency_ColumnNbReturningVisitsConverted',
            'sum_visit_length_returning'    => 'VisitFrequency_ColumnSumVisitLengthReturning',
            'nb_visits_converted'           => 'General_ColumnVisitsWithConversions',
            'nb_conversions'                => 'Goals_ColumnConversions',
            'revenue'                       => 'Goals_ColumnRevenue',
            'nb_hits'                       => 'General_ColumnPageviews',
            'entry_nb_visits'               => 'General_ColumnEntrances',
            'entry_nb_uniq_visitors'        => 'General_ColumnUniqueEntrances',
            'exit_nb_visits'                => 'General_ColumnExits',
            'exit_nb_uniq_visitors'         => 'General_ColumnUniqueExits',
            'entry_bounce_count'            => 'General_ColumnBounces',
            'exit_bounce_count'             => 'General_ColumnBounces',
            'exit_rate'                     => 'General_ColumnExitRate'
        );

        $trans = array_map('Piwik_Translate', $trans);

        $dailySum = ' (' . Piwik_Translate('General_DailySum') . ')';
        $afterEntry = ' ' . Piwik_Translate('General_AfterEntry');

        $trans['sum_daily_nb_uniq_visitors'] = Piwik_Translate('General_ColumnNbUniqVisitors') . $dailySum;
        $trans['sum_daily_entry_nb_uniq_visitors'] = Piwik_Translate('General_ColumnUniqueEntrances') . $dailySum;
        $trans['sum_daily_exit_nb_uniq_visitors'] = Piwik_Translate('General_ColumnUniqueExits') . $dailySum;
        $trans['entry_nb_actions'] = Piwik_Translate('General_ColumnNbActions') . $afterEntry;
        $trans['entry_sum_visit_length'] = Piwik_Translate('General_ColumnSumVisitLength') . $afterEntry;

        $api = self::getInstance();
        $trans = array_merge($api->getDefaultMetrics(), $api->getDefaultProcessedMetrics(), $trans);

        return $trans;
    }

    public function getDefaultMetrics()
    {
        $translations = array(
            // Standard metrics
            'nb_visits'        => 'General_ColumnNbVisits',
            'nb_uniq_visitors' => 'General_ColumnNbUniqVisitors',
            'nb_actions'       => 'General_ColumnNbActions',
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
            'avg_time_on_site'     => 'General_ColumnAvgTimeOnSite',
            'bounce_rate'          => 'General_ColumnBounceRate',
            'conversion_rate'      => 'General_ColumnConversionRate',
        );
        return array_map('Piwik_Translate', $translations);
    }

    public function getDefaultMetricsDocumentation()
    {
        $documentation = array(
            'nb_visits'            => 'General_ColumnNbVisitsDocumentation',
            'nb_uniq_visitors'     => 'General_ColumnNbUniqVisitorsDocumentation',
            'nb_actions'           => 'General_ColumnNbActionsDocumentation',
            'nb_actions_per_visit' => 'General_ColumnActionsPerVisitDocumentation',
            'avg_time_on_site'     => 'General_ColumnAvgTimeOnSiteDocumentation',
            'bounce_rate'          => 'General_ColumnBounceRateDocumentation',
            'conversion_rate'      => 'General_ColumnConversionRateDocumentation',
            'avg_time_on_page'     => 'General_ColumnAverageTimeOnPageDocumentation',
            'nb_hits'              => 'General_ColumnPageviewsDocumentation',
            'exit_rate'            => 'General_ColumnExitRateDocumentation'
        );
        return array_map('Piwik_Translate', $documentation);
    }

    public function getSegmentsMetadata($idSites = array(), $_hideImplementationData = true)
    {
        $segments = array();
        Piwik_PostEvent('API.getSegmentsMetadata', $segments, $idSites);

        $segments[] = array(
            'type'           => 'dimension',
            'category'       => Piwik_Translate('General_Visit'),
            'name'           => 'General_VisitorIP',
            'segment'        => 'visitIp',
            'acceptedValues' => '13.54.122.1, etc.',
            'sqlSegment'     => 'log_visit.location_ip',
            'sqlFilter'      => array('Piwik_IP', 'P2N'),
            'permission'     => Piwik::isUserHasAdminAccess($idSites),
        );
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => Piwik_Translate('General_Visit'),
            'name'           => 'General_VisitorID',
            'segment'        => 'visitorId',
            'acceptedValues' => '34c31e04394bdc63 - any 16 Hexadecimal chars ID, which can be fetched using the Tracking API function getVisitorId()',
            'sqlSegment'     => 'log_visit.idvisitor',
            'sqlFilter'      => array('Piwik_Common', 'convertVisitorIdToBin'),
        );
        $segments[] = array(
            'type'       => 'metric',
            'category'   => Piwik_Translate('General_Visit'),
            'name'       => 'General_NbActions',
            'segment'    => 'actions',
            'sqlSegment' => 'log_visit.visit_total_actions',
        );
        $segments[] = array(
            'type'           => 'metric',
            'category'       => Piwik_Translate('General_Visit'),
            'name'           => 'General_NbSearches',
            'segment'        => 'searches',
            'sqlSegment'     => 'log_visit.visit_total_searches',
            'acceptedValues' => 'To select all visits who used internal Site Search, use: &segment=searches>0',
        );
        $segments[] = array(
            'type'       => 'metric',
            'category'   => Piwik_Translate('General_Visit'),
            'name'       => 'General_ColumnVisitDuration',
            'segment'    => 'visitDuration',
            'sqlSegment' => 'log_visit.visit_total_time',
        );
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => Piwik_Translate('General_Visit'),
            'name'           => Piwik_Translate('General_VisitType') ,
            'segment'        => 'visitorType',
            'acceptedValues' => 'new, returning, returningCustomer' . ". " . Piwik_Translate('General_VisitTypeExample', '"&segment=visitorType==returning,visitorType==returningCustomer"'),
            'sqlSegment'     => 'log_visit.visitor_returning',
            'sqlFilter'      => create_function('$type', 'return $type == "new" ? 0 : ($type == "returning" ? 1 : 2);'),
        );
        $segments[] = array(
            'type'       => 'metric',
            'category'   => Piwik_Translate('General_Visit'),
            'name'       => 'General_DaysSinceLastVisit',
            'segment'    => 'daysSinceLastVisit',
            'sqlSegment' => 'log_visit.visitor_days_since_last',
        );
        $segments[] = array(
            'type'       => 'metric',
            'category'   => Piwik_Translate('General_Visit'),
            'name'       => 'General_DaysSinceFirstVisit',
            'segment'    => 'daysSinceFirstVisit',
            'sqlSegment' => 'log_visit.visitor_days_since_first',
        );
        $segments[] = array(
            'type'       => 'metric',
            'category'   => Piwik_Translate('General_Visit'),
            'name'       => 'General_NumberOfVisits',
            'segment'    => 'visitCount',
            'sqlSegment' => 'log_visit.visitor_count_visits',
        );

        $segments[] = array(
            'type'           => 'dimension',
            'category'       => Piwik_Translate('General_Visit'),
            'name'           => 'General_VisitConvertedGoal',
            'segment'        => 'visitConverted',
            'acceptedValues' => '0, 1',
            'sqlSegment'     => 'log_visit.visit_goal_converted',
        );

        $segments[] = array(
            'type'           => 'dimension',
            'category'       => Piwik_Translate('General_Visit'),
            'name'           => Piwik_Translate('General_EcommerceVisitStatusDesc'),
            'segment'        => 'visitEcommerceStatus',
            'acceptedValues' => implode(", ", self::$visitEcommerceStatus)
                   . '. '. Piwik_Translate('General_EcommerceVisitStatusEg', '"&segment=visitEcommerceStatus==ordered,visitEcommerceStatus==orderedThenAbandonedCart"'),
            'sqlSegment'     => 'log_visit.visit_goal_buyer',
            'sqlFilter'      => array('Piwik_API_API', 'getVisitEcommerceStatus'),
        );

        $segments[] = array(
            'type'       => 'metric',
            'category'   => Piwik_Translate('General_Visit'),
            'name'       => 'General_DaysSinceLastEcommerceOrder',
            'segment'    => 'daysSinceLastEcommerceOrder',
            'sqlSegment' => 'log_visit.visitor_days_since_order',
        );

        foreach ($segments as &$segment) {
            $segment['name'] = Piwik_Translate($segment['name']);
            $segment['category'] = Piwik_Translate($segment['category']);

            if ($_hideImplementationData) {
                unset($segment['sqlFilter']);
                unset($segment['sqlSegment']);
            }
        }

        usort($segments, array($this, 'sortSegments'));
        return $segments;
    }

    static protected $visitEcommerceStatus = array(
        Piwik_Tracker_GoalManager::TYPE_BUYER_NONE                  => 'none',
        Piwik_Tracker_GoalManager::TYPE_BUYER_ORDERED               => 'ordered',
        Piwik_Tracker_GoalManager::TYPE_BUYER_OPEN_CART             => 'abandonedCart',
        Piwik_Tracker_GoalManager::TYPE_BUYER_ORDERED_AND_OPEN_CART => 'orderedThenAbandonedCart',
    );

    /**
     * @ignore
     */
    static public function getVisitEcommerceStatusFromId($id)
    {
        if (!isset(self::$visitEcommerceStatus[$id])) {
            throw new Exception("Unexpected ECommerce status value ");
        }
        return self::$visitEcommerceStatus[$id];
    }

    /**
     * @ignore
     */
    static public function getVisitEcommerceStatus($status)
    {
        $id = array_search($status, self::$visitEcommerceStatus);
        if ($id === false) {
            throw new Exception("Invalid 'visitEcommerceStatus' segment value");
        }
        return $id;
    }

    private function sortSegments($row1, $row2)
    {
        $columns = array('type', 'category', 'name', 'segment');
        foreach ($columns as $column) {
            // Keep segments ordered alphabetically inside categories..
            $type = -1;
            if ($column == 'name') $type = 1;
            $compare = $type * strcmp($row1[$column], $row2[$column]);

            // hack so that custom variables "page" are grouped together in the doc
            if ($row1['category'] == Piwik_Translate('CustomVariables_CustomVariables')
                && $row1['category'] == $row2['category']
            ) {
                $compare = strcmp($row1['segment'], $row2['segment']);
                return $compare;
            }
            if ($compare != 0) {
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
    public function getLogoUrl($pathOnly = false)
    {
        $logo = 'themes/default/images/logo.png';
        if (Piwik_Config::getInstance()->branding['use_custom_logo'] == 1
            && file_exists(Piwik_Common::getPathToPiwikRoot() . '/themes/logo.png')
        ) {
            $logo = 'themes/logo.png';
        }
        if (!$pathOnly) {
            return Piwik::getPiwikUrl() . $logo;
        }
        return Piwik_Common::getPathToPiwikRoot() . '/' . $logo;
    }

    /**
     * Returns the url to header logo (~127x50px)
     *
     * @param bool $pathOnly If true, returns path relative to doc root. Otherwise, returns a URL.
     * @return string
     */
    public function getHeaderLogoUrl($pathOnly = false)
    {
        $logo = 'themes/default/images/logo-header.png';
        if (Piwik_Config::getInstance()->branding['use_custom_logo'] == 1
            && file_exists(Piwik_Common::getPathToPiwikRoot() . '/themes/logo-header.png')
        ) {
            $logo = 'themes/logo-header.png';
        }
        if (!$pathOnly) {
            return Piwik::getPiwikUrl() . $logo;
        }
        return Piwik_Common::getPathToPiwikRoot() . '/' . $logo;
    }

    /**
     * Returns the URL to application SVG Logo
     *
     * @ignore
     * @param bool $pathOnly If true, returns path relative to doc root. Otherwise, returns a URL.
     * @return string
     */
    public function getSVGLogoUrl($pathOnly = false)
    {
        $logo = 'themes/default/images/logo.svg';
        if (Piwik_Config::getInstance()->branding['use_custom_logo'] == 1
            && file_exists(Piwik_Common::getPathToPiwikRoot() . '/themes/logo.svg')
        ) {
            $logo = 'themes/logo.svg';
        }
        if (!$pathOnly) {
            return Piwik::getPiwikUrl() . $logo;
        }
        return Piwik_Common::getPathToPiwikRoot() . '/' . $logo;
    }

    /**
     * Returns whether there is an SVG Logo available.
     * @ignore
     * @return bool
     */
    public function hasSVGLogo()
    {
        if (Piwik_Config::getInstance()->branding['use_custom_logo'] == 0) {
            /* We always have our application logo */
            return true;
        } else if (Piwik_Config::getInstance()->branding['use_custom_logo'] == 1
            && file_exists(Piwik_Common::getPathToPiwikRoot() . '/themes/logo.svg')
        ) {
            return true;
        }

        return false;
    }

    /**
     * Loads reports metadata, then return the requested one,
     * matching optional API parameters.
     */
    public function getMetadata($idSite, $apiModule, $apiAction, $apiParameters = array(), $language = false,
                                $period = false, $date = false, $hideMetricsDoc = false, $showSubtableReports = false)
    {
        Piwik_Translate::getInstance()->reloadLanguage($language);
        $reportsMetadata = $this->getReportMetadata($idSite, $period, $date, $hideMetricsDoc, $showSubtableReports);

        foreach ($reportsMetadata as $report) {
            // See ArchiveProcessing/Period.php - unique visitors are not processed for period != day
            if (($period && $period != 'day') && !($apiModule == 'VisitsSummary' && $apiAction == 'get')) {
                unset($report['metrics']['nb_uniq_visitors']);
            }
            if ($report['module'] == $apiModule
                && $report['action'] == $apiAction
            ) {
                // No custom parameters
                if (empty($apiParameters)
                    && empty($report['parameters'])
                ) {
                    return array($report);
                }
                if (empty($report['parameters'])) {
                    continue;
                }
                $diff = array_diff($report['parameters'], $apiParameters);
                if (empty($diff)) {
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
    public function getReportMetadata($idSites = '', $period = false, $date = false, $hideMetricsDoc = false,
                                      $showSubtableReports = false)
    {
        $idSites = Piwik_Site::getIdSitesFromIdSitesString($idSites);
        if (!empty($idSites)) {
            Piwik::checkUserHasViewAccess($idSites);
        }

        $parameters = array('idSites' => $idSites, 'period' => $period, 'date' => $date);

        $availableReports = array();
        Piwik_PostEvent('API.getReportMetadata', $availableReports, $parameters);
        foreach ($availableReports as &$availableReport) {
            if (!isset($availableReport['metrics'])) {
                $availableReport['metrics'] = $this->getDefaultMetrics();
            }
            if (!isset($availableReport['processedMetrics'])) {
                $availableReport['processedMetrics'] = $this->getDefaultProcessedMetrics();
            }

            if ($hideMetricsDoc) // remove metric documentation if it's not wanted
            {
                unset($availableReport['metricsDocumentation']);
            } else if (!isset($availableReport['metricsDocumentation'])) {
                // set metric documentation to default if it's not set
                $availableReport['metricsDocumentation'] = $this->getDefaultMetricsDocumentation();
            }
        }

        // Some plugins need to add custom metrics after all plugins hooked in
        Piwik_PostEvent('API.getReportMetadata.end', $availableReports, $parameters);
        // Oh this is not pretty! Until we have event listeners order parameter...
        Piwik_PostEvent('API.getReportMetadata.end.end', $availableReports, $parameters);

        // Sort results to ensure consistent order
        usort($availableReports, array($this, 'sort'));

        // Add the magic API.get report metadata aggregating all plugins API.get API calls automatically
        $this->addApiGetMetdata($availableReports);

        $knownMetrics = array_merge($this->getDefaultMetrics(), $this->getDefaultProcessedMetrics());
        foreach ($availableReports as &$availableReport) {
            // Ensure all metrics have a translation
            $metrics = $availableReport['metrics'];
            $cleanedMetrics = array();
            foreach ($metrics as $metricId => $metricTranslation) {
                // When simply the column name was given, ie 'metric' => array( 'nb_visits' )
                // $metricTranslation is in this case nb_visits. We look for a known translation.
                if (is_numeric($metricId)
                    && isset($knownMetrics[$metricTranslation])
                ) {
                    $metricId = $metricTranslation;
                    $metricTranslation = $knownMetrics[$metricTranslation];
                }
                $cleanedMetrics[$metricId] = $metricTranslation;
            }
            $availableReport['metrics'] = $cleanedMetrics;

            // if hide/show columns specified, hide/show metrics & docs
            $availableReport['metrics'] = $this->hideShowMetrics($availableReport['metrics']);
            if (isset($availableReport['processedMetrics'])) {
                $availableReport['processedMetrics'] = $this->hideShowMetrics($availableReport['processedMetrics']);
            }
            if (isset($availableReport['metricsDocumentation'])) {
                $availableReport['metricsDocumentation'] =
                    $this->hideShowMetrics($availableReport['metricsDocumentation']);
            }
            
            // Remove array elements that are false (to clean up API output)
            foreach ($availableReport as $attributeName => $attributeValue) {
                if (empty($attributeValue)) {
                    unset($availableReport[$attributeName]);
                }
            }
            // when there are per goal metrics, don't display conversion_rate since it can differ from per goal sum
            if (isset($availableReport['metricsGoal'])) {
                unset($availableReport['processedMetrics']['conversion_rate']);
                unset($availableReport['metricsGoal']['conversion_rate']);
            }

            // Processing a uniqueId for each report,
            // can be used by UIs as a key to match a given report
            $uniqueId = $availableReport['module'] . '_' . $availableReport['action'];
            if (!empty($availableReport['parameters'])) {
                foreach ($availableReport['parameters'] as $key => $value) {
                    $uniqueId .= '_' . $key . '--' . $value;
                }
            }
            $availableReport['uniqueId'] = $uniqueId;

            // Order is used to order reports internally, but not meant to be used outside
            unset($availableReport['order']);
        }

        // remove subtable reports
        if (!$showSubtableReports) {
            foreach ($availableReports as $idx => $report) {
                if (isset($report['isSubtableReport']) && $report['isSubtableReport']) {
                    unset($availableReports[$idx]);
                }
            }
        }

        return array_values($availableReports); // make sure array has contiguous key values
    }


    /**
     * Add the metadata for the API.get report
     * In other plugins, this would hook on 'API.getReportMetadata'
     */
    private function addApiGetMetdata(&$availableReports)
    {
        $metadata = array(
            'category'             => Piwik_Translate('General_API'),
            'name'                 => Piwik_Translate('General_MainMetrics'),
            'module'               => 'API',
            'action'               => 'get',
            'metrics'              => array(),
            'processedMetrics'     => array(),
            'metricsDocumentation' => array(),
            'order'                => 1
        );

        $indexesToMerge = array('metrics', 'processedMetrics', 'metricsDocumentation');

        foreach ($availableReports as $report) {
            if ($report['action'] == 'get') {
                foreach ($indexesToMerge as $index) {
                    if (isset($report[$index])
                        && is_array($report[$index])
                    ) {
                        $metadata[$index] = array_merge($metadata[$index], $report[$index]);
                    }
                }
            }
        }

        $availableReports[] = $metadata;
    }

    public function getProcessedReport($idSite, $period, $date, $apiModule, $apiAction, $segment = false,
                                       $apiParameters = false, $idGoal = false, $language = false,
                                       $showTimer = true, $hideMetricsDoc = false, $idSubtable = false, $showRawMetrics = false)
    {
        $timer = new Piwik_Timer();
        if ($apiParameters === false) {
            $apiParameters = array();
        }
        if (!empty($idGoal)
            && empty($apiParameters['idGoal'])
        ) {
            $apiParameters['idGoal'] = $idGoal;
        }
        // Is this report found in the Metadata available reports?
        $reportMetadata = $this->getMetadata($idSite, $apiModule, $apiAction, $apiParameters, $language,
            $period, $date, $hideMetricsDoc, $showSubtableReports = true);
        if (empty($reportMetadata)) {
            throw new Exception("Requested report $apiModule.$apiAction for Website id=$idSite not found in the list of available reports. \n");
        }
        $reportMetadata = reset($reportMetadata);

        // Generate Api call URL passing custom parameters
        $parameters = array_merge($apiParameters, array(
                                                       'method'     => $apiModule . '.' . $apiAction,
                                                       'idSite'     => $idSite,
                                                       'period'     => $period,
                                                       'date'       => $date,
                                                       'format'     => 'original',
                                                       'serialize'  => '0',
                                                       'language'   => $language,
                                                       'idSubtable' => $idSubtable,
                                                  ));
        if (!empty($segment)) $parameters['segment'] = $segment;

        $url = Piwik_Url::getQueryStringFromParameters($parameters);
        $request = new Piwik_API_Request($url);
        try {
            /** @var Piwik_DataTable */
            $dataTable = $request->process();
        } catch (Exception $e) {
            throw new Exception("API returned an error: " . $e->getMessage() . "\n");
        }

        list($newReport, $columns, $rowsMetadata) = $this->handleTableReport($idSite, $dataTable, $reportMetadata, isset($reportMetadata['dimension']), $showRawMetrics);
        foreach ($columns as $columnId => &$name) {
            $name = ucfirst($name);
        }
        $website = new Piwik_Site($idSite);
//    	$segment = new Piwik_Segment($segment, $idSite);

        $period = Piwik_Period::advancedFactory($period, $date);
        $period = $period->getLocalizedLongString();

        $return = array(
            'website'        => $website->getName(),
            'prettyDate'     => $period,
//    			'prettySegment' => $segment->getPrettyString(),
            'metadata'       => $reportMetadata,
            'columns'        => $columns,
            'reportData'     => $newReport,
            'reportMetadata' => $rowsMetadata,
        );
        if ($showTimer) {
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
    private function handleTableReport($idSite, $dataTable, &$reportMetadata, $hasDimension, $showRawMetrics = false)
    {
        $columns = $reportMetadata['metrics'];

        if ($hasDimension) {
            $columns = array_merge(
                array('label' => $reportMetadata['dimension']),
                $columns
            );

            if (isset($reportMetadata['processedMetrics'])) {
                $processedMetricsAdded = $this->getDefaultProcessedMetrics();
                foreach ($processedMetricsAdded as $processedMetricId => $processedMetricTranslation) {
                    // this processed metric can be displayed for this report
                    if (isset($reportMetadata['processedMetrics'][$processedMetricId])) {
                        $columns[$processedMetricId] = $processedMetricTranslation;
                    }
                }
            }

            // Display the global Goal metrics
            if (isset($reportMetadata['metricsGoal'])) {
                $metricsGoalDisplay = array('revenue');
                // Add processed metrics to be displayed for this report
                foreach ($metricsGoalDisplay as $goalMetricId) {
                    if (isset($reportMetadata['metricsGoal'][$goalMetricId])) {
                        $columns[$goalMetricId] = $reportMetadata['metricsGoal'][$goalMetricId];
                    }
                }
            }

            if (isset($reportMetadata['processedMetrics'])) {
                // Add processed metrics
                $dataTable->filter('AddColumnsProcessedMetrics', array($deleteRowsWithNoVisit = false));
            }
        }

        $columns = $this->hideShowMetrics($columns);

        // $dataTable is an instance of Piwik_DataTable_Array when multiple periods requested
        if ($dataTable instanceof Piwik_DataTable_Array) {
            // Need a new Piwik_DataTable_Array to store the 'human readable' values
            $newReport = new Piwik_DataTable_Array();
            $newReport->setKeyName("prettyDate");

            // Need a new Piwik_DataTable_Array to store report metadata
            $rowsMetadata = new Piwik_DataTable_Array();
            $rowsMetadata->setKeyName("prettyDate");

            // Process each Piwik_DataTable_Simple entry
            foreach ($dataTable->getArray() as $label => $simpleDataTable) {
                $this->removeEmptyColumns($columns, $reportMetadata, $simpleDataTable);

                list($enhancedSimpleDataTable, $rowMetadata) = $this->handleSimpleDataTable($idSite, $simpleDataTable, $columns, $hasDimension, $showRawMetrics);
                $enhancedSimpleDataTable->metadata = $simpleDataTable->metadata;

                $period = $simpleDataTable->metadata['period']->getLocalizedLongString();
                $newReport->addTable($enhancedSimpleDataTable, $period);
                $rowsMetadata->addTable($rowMetadata, $period);
            }
        } else {
            $this->removeEmptyColumns($columns, $reportMetadata, $dataTable);
            list($newReport, $rowsMetadata) = $this->handleSimpleDataTable($idSite, $dataTable, $columns, $hasDimension, $showRawMetrics);
        }

        return array(
            $newReport,
            $columns,
            $rowsMetadata
        );
    }

    /**
     * Removes metrics from the list of columns and the report meta data if they are marked empty
     * in the data table meta data.
     */
    private function removeEmptyColumns(&$columns, &$reportMetadata, $dataTable)
    {
        $emptyColumns = $dataTable->getMetadata(Piwik_DataTable::EMPTY_COLUMNS_METADATA_NAME);

        if (!is_array($emptyColumns)) {
            return;
        }

        $columns = $this->hideShowMetrics($columns, $emptyColumns);

        if (isset($reportMetadata['metrics'])) {
            $reportMetadata['metrics'] = $this->hideShowMetrics($reportMetadata['metrics'], $emptyColumns);
        }

        if (isset($reportMetadata['metricsDocumentation'])) {
            $reportMetadata['metricsDocumentation'] = $this->hideShowMetrics($reportMetadata['metricsDocumentation'], $emptyColumns);
        }
    }

    /**
     * Removes column names from an array based on the values in the hideColumns,
     * showColumns query parameters. This is a hack that provides the ColumnDelete
     * filter functionality in processed reports.
     *
     * @param array $columns List of metrics shown in a processed report.
     * @param array $emptyColumns Empty columns from the data table meta data.
     * @return array Filtered list of metrics.
     */
    private function hideShowMetrics($columns, $emptyColumns = array())
    {
        if (!is_array($columns)) {
            return $columns;
        }

        // remove columns if hideColumns query parameters exist
        $columnsToRemove = Piwik_Common::getRequestVar('hideColumns', '');
        if ($columnsToRemove != '') {
            $columnsToRemove = explode(',', $columnsToRemove);
            foreach ($columnsToRemove as $name) {
                // if a column to remove is in the column list, remove it
                if (isset($columns[$name])) {
                    unset($columns[$name]);
                }
            }
        }

        // remove columns if showColumns query parameters exist
        $columnsToKeep = Piwik_Common::getRequestVar('showColumns', '');
        if ($columnsToKeep != '') {
            $columnsToKeep = explode(',', $columnsToKeep);
            $columnsToKeep[] = 'label';

            foreach ($columns as $name => $ignore) {
                // if the current column should not be kept, remove it
                $idx = array_search($name, $columnsToKeep);
                if ($idx === false) // if $name is not in $columnsToKeep
                {
                    unset($columns[$name]);
                }
            }
        }

        // remove empty columns
        if (is_array($emptyColumns)) {
            foreach ($emptyColumns as $column) {
                if (isset($columns[$column])) {
                    unset($columns[$column]);
                }
            }
        }

        return $columns;
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
     * @param bool $returnRawMetrics If set to true, the original metrics will be returned
     *
     * @return array Piwik_DataTable $enhancedDataTable filtered metrics with human readable format & Piwik_DataTable_Simple $rowsMetadata
     */
    private function handleSimpleDataTable($idSite, $simpleDataTable, $metadataColumns, $hasDimension, $returnRawMetrics = false)
    {
        // new DataTable to store metadata
        $rowsMetadata = new Piwik_DataTable();

        // new DataTable to store 'human readable' values
        if ($hasDimension) {
            $enhancedDataTable = new Piwik_DataTable();
        } else {
            $enhancedDataTable = new Piwik_DataTable_Simple();
        }

        // add missing metrics
        foreach ($simpleDataTable->getRows() as $row) {
            $rowMetrics = $row->getColumns();
            foreach ($metadataColumns as $id => $name) {
                if (!isset($rowMetrics[$id])) {
                    $row->addColumn($id, 0);
                }
            }
        }

        foreach ($simpleDataTable->getRows() as $row) {
            $enhancedRow = new Piwik_DataTable_Row();
            $enhancedDataTable->addRow($enhancedRow);
            $rowMetrics = $row->getColumns();
            foreach ($rowMetrics as $columnName => $columnValue) {
                // filter metrics according to metadata definition
                if (isset($metadataColumns[$columnName])) {
                    // generate 'human readable' metric values
                    $prettyValue = Piwik::getPrettyValue($idSite, $columnName, $columnValue, $htmlAllowed = false);
                    $enhancedRow->addColumn($columnName, $prettyValue);
                } // For example the Maps Widget requires the raw metrics to do advanced datavis
                elseif ($returnRawMetrics) {
                    $enhancedRow->addColumn($columnName, $columnValue);
                }
            }

            // If report has a dimension, extract metadata into a distinct DataTable
            if ($hasDimension) {
                $rowMetadata = $row->getMetadata();
                $idSubDataTable = $row->getIdSubDataTable();

                // Create a row metadata only if there are metadata to insert
                if (count($rowMetadata) > 0 || !is_null($idSubDataTable)) {
                    $metadataRow = new Piwik_DataTable_Row();
                    $rowsMetadata->addRow($metadataRow);

                    foreach ($rowMetadata as $metadataKey => $metadataValue) {
                        $metadataRow->addColumn($metadataKey, $metadataValue);
                    }

                    if (!is_null($idSubDataTable)) {
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
        if (is_null($order)) {
            $order = array(
                Piwik_Translate('General_MultiSitesSummary'),
                Piwik_Translate('VisitsSummary_VisitsSummary'),
                Piwik_Translate('Goals_Ecommerce'),
                Piwik_Translate('Actions_Actions'),
                Piwik_Translate('Actions_SubmenuSitesearch'),
                Piwik_Translate('Referers_Referers'),
                Piwik_Translate('Goals_Goals'),
                Piwik_Translate('General_Visitors'),
                Piwik_Translate('DevicesDetection_DevicesDetection'),
                Piwik_Translate('UserSettings_VisitorSettings'),
            );
        }
        return ($category = strcmp(array_search($a['category'], $order), array_search($b['category'], $order))) == 0
            ? (@$a['order'] < @$b['order'] ? -1 : 1)
            : $category;
    }

    /**
     * Get a combined report of the *.get API methods.
     */
    public function get($idSite, $period, $date, $segment = false, $columns = false)
    {
        $columns = Piwik::getArrayFromApiParameter($columns);

        // build columns map for faster checks later on
        $columnsMap = array();
        foreach ($columns as $column) {
            $columnsMap[$column] = true;
        }

        // find out which columns belong to which plugin
        $columnsByPlugin = array();
        $meta = Piwik_API_API::getInstance()->getReportMetadata($idSite, $period, $date);
        foreach ($meta as $reportMeta) {
            // scan all *.get reports
            if ($reportMeta['action'] == 'get'
                && !isset($reportMeta['parameters'])
                && $reportMeta['module'] != 'API'
                && !empty($reportMeta['metrics'])
            ) {
                $plugin = $reportMeta['module'];
                foreach ($reportMeta['metrics'] as $column => $columnTranslation) {
                    // a metric from this report has been requested
                    if (isset($columnsMap[$column])
                        // or by default, return all metrics
                        || empty($columnsMap)
                    ) {
                        $columnsByPlugin[$plugin][] = $column;
                    }
                }
            }
        }
        krsort($columnsByPlugin);

        $mergedDataTable = false;
        $params = compact('idSite', 'period', 'date', 'segment', 'idGoal');
        foreach ($columnsByPlugin as $plugin => $columns) {
            // load the data
            $className = 'Piwik_' . $plugin . '_API';
            $params['columns'] = implode(',', $columns);
            $dataTable = Piwik_API_Proxy::getInstance()->call($className, 'get', $params);
            // make sure the table has all columns
            $array = ($dataTable instanceof Piwik_DataTable_Array ? $dataTable->getArray() : array($dataTable));
            foreach ($array as $table) {
                // we don't support idSites=all&date=DATE1,DATE2
                if ($table instanceof Piwik_DataTable) {
                    $firstRow = $table->getFirstRow();
                    if (!$firstRow) {
                        $firstRow = new Piwik_DataTable_Row;
                        $table->addRow($firstRow);
                    }
                    foreach ($columns as $column) {
                        if ($firstRow->getColumn($column) === false) {
                            $firstRow->setColumn($column, 0);
                        }
                    }
                }
            }

            // merge reports
            if ($mergedDataTable === false) {
                $mergedDataTable = $dataTable;
            } else {
                $this->mergeDataTables($mergedDataTable, $dataTable);
            }
        }
        return $mergedDataTable;
    }


    /**
     * Merge the columns of two data tables.
     * Manipulates the first table.
     */
    private function mergeDataTables($table1, $table2)
    {
        // handle table arrays
        if ($table1 instanceof Piwik_DataTable_Array && $table2 instanceof Piwik_DataTable_Array) {
            $subTables2 = $table2->getArray();
            foreach ($table1->getArray() as $index => $subTable1) {
                $subTable2 = $subTables2[$index];
                $this->mergeDataTables($subTable1, $subTable2);
            }
            return;
        }

        $firstRow1 = $table1->getFirstRow();
        $firstRow2 = $table2->getFirstRow();
        if ($firstRow2 instanceof Piwik_DataTable_Row) {
            foreach ($firstRow2->getColumns() as $metric => $value) {
                $firstRow1->setColumn($metric, $value);
            }
        }
    }


    /**
     * Given an API report to query (eg. "Referers.getKeywords", and a Label (eg. "free%20software"),
     * this function will query the API for the previous days/weeks/etc. and will return
     * a ready to use data structure containing the metrics for the requested Label, along with enriched information (min/max values, etc.)
     *
     * @return array
     */
    public function getRowEvolution($idSite, $period, $date, $apiModule, $apiAction, $label = false, $segment = false, $column = false, $language = false, $idGoal = false, $legendAppendMetric = true, $labelUseAbsoluteUrl = true)
    {
        // validation of requested $period & $date
          if ($period == 'range') {
            // load days in the range
            $period = 'day';
        }

        if (!Piwik_Archive::isMultiplePeriod($date, $period)) {
            throw new Exception("Row evolutions can not be processed with this combination of \'date\' and \'period\' parameters.");
        }

        $label = Piwik_API_ResponseBuilder::unsanitizeLabelParameter($label);
        if ($label) {
            $labels = explode(',', $label);
            $labels = array_unique($labels);
        } else {
            $labels = array();
        }

        $dataTable = $this->loadRowEvolutionDataFromAPI($idSite, $period, $date, $apiModule, $apiAction, $labels, $segment, $idGoal);

        if (empty($labels)) {
            // if no labels specified, use all possible labels as list
            foreach ($dataTable->getArray() as $table) {
                $labels = array_merge($labels, $table->getColumn('label'));
            }
            $labels = array_values(array_unique($labels));
            
            // if the filter_limit query param is set, treat it as a request to limit
            // the number of labels used
            $limit = Piwik_Common::getRequestVar('filter_limit', false);
            if ($limit != false
                && $limit >= 0
            ) {
                $labels = array_slice($labels, 0, $limit);
            }
            
            // set label index metadata
            $labelsToIndex = array_flip($labels);
            foreach ($dataTable->getArray() as $table) {
                foreach ($table->getRows() as $row) {
                    $label = $row->getColumn('label');
                    if (isset($labelsToIndex[$label])) {
                        $row->setMetadata('label_index', $labelsToIndex[$label]);
                    }
                }
            }
        }

        if (count($labels) != 1) {
            $data = $this->getMultiRowEvolution(
                $dataTable,
                $idSite,
                $period,
                $date,
                $apiModule,
                $apiAction,
                $labels,
                $segment,
                $column,
                $language,
                $idGoal,
                $legendAppendMetric,
                $labelUseAbsoluteUrl
            );
        } else {
            $data = $this->getSingleRowEvolution(
                $dataTable,
                $idSite,
                $period,
                $date,
                $apiModule,
                $apiAction,
                $labels[0],
                $segment,
                $language,
                $idGoal,
                $labelUseAbsoluteUrl
            );
        }
        return $data;
    }

    /**
     * Get row evolution for a single label
     * @return array containing  report data, metadata, label, logo
     */
    private function getSingleRowEvolution($dataTable, $idSite, $period, $date, $apiModule, $apiAction, $label, $segment, $language = false, $idGoal = false, $labelUseAbsoluteUrl = true)
    {
        $metadata = $this->getRowEvolutionMetaData($idSite, $period, $date, $apiModule, $apiAction, $language, $idGoal);
        $metricNames = array_keys($metadata['metrics']);
        
        $logo = $actualLabel = false;
        $urlFound = false;
        foreach ($dataTable->getArray() as $date => $subTable) {
            /** @var $subTable Piwik_DataTable */
            $subTable->applyQueuedFilters();
            if ($subTable->getRowsCount() > 0) {
                /** @var $row Piwik_DataTable_Row */
                $row = $subTable->getFirstRow();

                if (!$actualLabel) {
                    $logo = $row->getMetadata('logo');

                    $actualLabel = $this->getRowUrlForEvolutionLabel($row, $apiModule, $apiAction, $labelUseAbsoluteUrl);
                    $urlFound = $actualLabel !== false;
                    if (empty($actualLabel)) {
                        $actualLabel = $row->getColumn('label');
                    }

                }

                // remove all columns that are not in the available metrics.
                // this removes the label as well (which is desired for two reasons: (1) it was passed
                // in the request, (2) it would cause the evolution graph to show the label in the legend).
                foreach ($row->getColumns() as $column => $value) {
                    if (!in_array($column, $metricNames)) {
                        $row->deleteColumn($column);
                    }
                }

                $row->deleteMetadata();
            }
        }

        $this->enhanceRowEvolutionMetaData($metadata, $dataTable);

        // if we have a recursive label and no url, use the path
        if (!$urlFound) {
            $actualLabel = str_replace(Piwik_API_DataTableManipulator_LabelFilter::SEPARATOR_RECURSIVE_LABEL, ' - ', $label);
        }

        $return = array(
            'label'      => Piwik_DataTable_Filter_SafeDecodeLabel::safeDecodeLabel($actualLabel),
            'reportData' => $dataTable,
            'metadata'   => $metadata
        );
        if (!empty($logo)) {
            $return['logo'] = $logo;
        }
        return $return;
    }

    private function getRowUrlForEvolutionLabel($row, $apiModule, $apiAction, $labelUseAbsoluteUrl)
    {
        $url = $row->getMetadata('url');
        if ($url
            && ($apiModule == 'Actions'
                || ($apiModule == 'Referers'
                    && $apiAction == 'getWebsites'))
            && $labelUseAbsoluteUrl
        ) {
            $actualLabel = preg_replace(';^http(s)?://(www.)?;i', '', $url);
            return $actualLabel;
        }
        return false;
    }

    /**
     * @param $idSite
     * @param $period
     * @param $date
     * @param $apiModule
     * @param $apiAction
     * @param $label
     * @param $segment
     * @param $idGoal
     * @throws Exception
     * @return Piwik_DataTable_Array|Piwik_DataTable
     */
    private function loadRowEvolutionDataFromAPI($idSite, $period, $date, $apiModule, $apiAction, $label = false, $segment = false, $idGoal = false)
    {
        if (!is_array($label)) {
            $label = array($label);
        }
        $label = array_map('rawurlencode', $label);

        $parameters = array(
            'method'                   => $apiModule . '.' . $apiAction,
            'label'                    => $label,
            'idSite'                   => $idSite,
            'period'                   => $period,
            'date'                     => $date,
            'format'                   => 'original',
            'serialize'                => '0',
            'segment'                  => $segment,
            'idGoal'                   => $idGoal,
            
            // data for row evolution should NOT be limited
            'filter_limit'             => -1,

            // if more than one label is used, we add metadata to ensure we know which
            // row corresponds with which label (since the labels can change, and rows
            // can be sorted in a different order)
            'labelFilterAddLabelIndex' => count($label) > 1 ? 1 : 0,
        );

        // add "processed metrics" like actions per visit or bounce rate
        // note: some reports should not be filtered with AddColumnProcessedMetrics
        // specifically, reports without the Piwik_Archive::INDEX_NB_VISITS metric such as Goals.getVisitsUntilConversion & Goal.getDaysToConversion
        // this is because the AddColumnProcessedMetrics filter removes all datable rows lacking this metric
        if
        (
            $apiModule != 'Actions'
            &&
            ($apiModule != 'Goals' || ($apiAction != 'getVisitsUntilConversion' && $apiAction != 'getDaysToConversion'))
            && !empty($label)
        ) {
            $parameters['filter_add_columns_when_show_all_columns'] = '1';
        }

        $url = Piwik_Url::getQueryStringFromParameters($parameters);

        $request = new Piwik_API_Request($url);

        try {
            $dataTable = $request->process();
        } catch (Exception $e) {
            throw new Exception("API returned an error: " . $e->getMessage() . "\n");
        }

        return $dataTable;
    }

    /**
     * For a given API report, returns a simpler version
     * of the metadata (will return only the metrics and the dimension name)
     * @param $idSite
     * @param $period
     * @param $date
     * @param $apiModule
     * @param $apiAction
     * @param $language
     * @param $idGoal
     * @throws Exception
     * @return array
     */
    private function getRowEvolutionMetaData($idSite, $period, $date, $apiModule, $apiAction, $language, $idGoal = false)
    {
        $apiParameters = array();
        if (!empty($idGoal) && $idGoal > 0) {
            $apiParameters = array('idGoal' => $idGoal);
        }
        $reportMetadata = $this->getMetadata($idSite, $apiModule, $apiAction, $apiParameters, $language, $period, $date, $hideMetricsDoc = false, $showSubtableReports = true);

        if (empty($reportMetadata)) {
            throw new Exception("Requested report $apiModule.$apiAction for Website id=$idSite "
                . "not found in the list of available reports. \n");
        }

        $reportMetadata = reset($reportMetadata);

        $metrics = $reportMetadata['metrics'];
        if (isset($reportMetadata['processedMetrics']) && is_array($reportMetadata['processedMetrics'])) {
            $metrics = $metrics + $reportMetadata['processedMetrics'];
        }

        $dimension = $reportMetadata['dimension'];

        return compact('metrics', 'dimension');
    }

    /**
     * Given the Row evolution dataTable, and the associated metadata,
     * enriches the metadata with min/max values, and % change between the first period and the last one
     * @param array $metadata
     * @param Piwik_DataTable_Array $dataTable
     */
    private function enhanceRowEvolutionMetaData(&$metadata, $dataTable)
    {
        // prepare result array for metrics
        $metricsResult = array();
        foreach ($metadata['metrics'] as $metric => $name) {
            $metricsResult[$metric] = array('name' => $name);

            if (!empty($metadata['logos'][$metric])) {
                $metricsResult[$metric]['logo'] = $metadata['logos'][$metric];
            }
        }
        unset($metadata['logos']);

        $subDataTables = $dataTable->getArray();
        $firstDataTable = reset($subDataTables);
        $firstDataTableRow = $firstDataTable->getFirstRow();
        $lastDataTable = end($subDataTables);
        $lastDataTableRow = $lastDataTable->getFirstRow();

        // Process min/max values
        $firstNonZeroFound = array();
        foreach ($subDataTables as $subDataTable) {
            // $subDataTable is the report for one period, it has only one row
            $firstRow = $subDataTable->getFirstRow();
            foreach ($metadata['metrics'] as $metric => $label) {
                $value = $firstRow ? floatval($firstRow->getColumn($metric)) : 0;
                if ($value > 0) {
                    $firstNonZeroFound[$metric] = true;
                } else if (!isset($firstNonZeroFound[$metric])) {
                    continue;
                }
                if (!isset($metricsResult[$metric]['min'])
                    || $metricsResult[$metric]['min'] > $value
                ) {
                    $metricsResult[$metric]['min'] = $value;
                }
                if (!isset($metricsResult[$metric]['max'])
                    || $metricsResult[$metric]['max'] < $value
                ) {
                    $metricsResult[$metric]['max'] = $value;
                }
            }
        }

        // Process % change between first/last values
        foreach ($metadata['metrics'] as $metric => $label) {
            $first = $firstDataTableRow ? floatval($firstDataTableRow->getColumn($metric)) : 0;
            $last = $lastDataTableRow ? floatval($lastDataTableRow->getColumn($metric)) : 0;

            // do not calculate evolution if the first value is 0 (to avoid divide-by-zero)
            if ($first == 0) {
                continue;
            }

            $change = Piwik_DataTable_Filter_CalculateEvolutionFilter::calculate($last, $first, $quotientPrecision = 0);
            $change = Piwik_DataTable_Filter_CalculateEvolutionFilter::prependPlusSignToNumber($change);
            $metricsResult[$metric]['change'] = $change;
        }

        $metadata['metrics'] = $metricsResult;
    }

    /** Get row evolution for a multiple labels */
    private function getMultiRowEvolution($dataTable, $idSite, $period, $date, $apiModule, $apiAction, $labels, $segment, $column, $language = false, $idGoal = false, $legendAppendMetric = true, $labelUseAbsoluteUrl = true)
    {
        $actualLabels = $logos = array();

        $metadata = $this->getRowEvolutionMetaData($idSite, $period, $date, $apiModule, $apiAction, $language, $idGoal);

        if (!isset($metadata['metrics'][$column])) {
            // invalid column => use the first one that's available
            $metrics = array_keys($metadata['metrics']);
            $column = reset($metrics);
        }
        
        // get the processed label and logo (if any) for every requested label
        $actualLabels = $logos = array();
        foreach ($labels as $labelIdx => $label) {
            foreach ($dataTable->getArray() as $table) {
                $labelRow = $this->getRowEvolutionRowFromLabelIdx($table, $labelIdx);

                if ($labelRow) {
                    $actualLabels[$labelIdx] = $this->getRowUrlForEvolutionLabel(
                        $labelRow, $apiModule, $apiAction, $labelUseAbsoluteUrl);

                    $logos[$labelIdx] = $labelRow->getMetadata('logo');

                    if (!empty($actualLabels[$labelIdx])) {
                        break;
                    }
                }
            }

            if (empty($actualLabels[$labelIdx])) {
                $actualLabels[$labelIdx] = $this->cleanOriginalLabel($label);
            }
        }

        // convert rows to be array($column.'_'.$labelIdx => $value) as opposed to
        // array('label' => $label, 'column' => $value).
        $dataTableMulti = $dataTable->getEmptyClone();
        foreach ($dataTable->getArray() as $tableLabel => $table) {
            $newRow = new Piwik_DataTable_Row();

            foreach ($labels as $labelIdx => $label) {
                $row = $this->getRowEvolutionRowFromLabelIdx($table, $labelIdx);
                
                $value = 0;
                if ($row) {
                    $value = $row->getColumn($column);
                    $value = floatVal(str_replace(',', '.', $value));
                }
                
                if ($value == '') {
                    $value = 0;
                }
                
                $newLabel = $column . '_' . (int)$labelIdx;
                $newRow->addColumn($newLabel, $value);
            }
            
            $newTable = $table->getEmptyClone();
            if (!empty($labels)) { // only add a row if the row has data (no labels === no data)
                $newTable->addRow($newRow);
            }
            
            $dataTableMulti->addTable($newTable, $tableLabel);
        }

        // the available metrics for the report are returned as metadata / columns
        $metadata['columns'] = $metadata['metrics'];

        // metadata / metrics should document the rows that are compared
        // this way, UI code can be reused
        $metadata['metrics'] = array();
        foreach ($actualLabels as $labelIndex => $label) {
            if ($legendAppendMetric) {
                $label .= ' (' . $metadata['columns'][$column] . ')';
            }
            $metricName = $column . '_' . $labelIndex;
            $metadata['metrics'][$metricName] = Piwik_DataTable_Filter_SafeDecodeLabel::safeDecodeLabel($label);

            if (!empty($logos[$labelIndex])) {
                $metadata['logos'][$metricName] = $logos[$labelIndex];
            }
        }

        $this->enhanceRowEvolutionMetaData($metadata, $dataTableMulti);

        return array(
            'column'     => $column,
            'reportData' => $dataTableMulti,
            'metadata'   => $metadata
        );
    }
    
    /**
     * Returns the row in a datatable by its label_index metadata.
     * 
     * @param Piwik_DataTable $table
     * @param int $labelIdx
     * @return Piwik_DataTable_Row|false
     */
    private function getRowEvolutionRowFromLabelIdx($table, $labelIdx)
    {
        $labelIdx = (int)$labelIdx;
        foreach ($table->getRows() as $row)
        {
            if ($row->getMetadata('label_index') === $labelIdx)
            {
                return $row;
            }
        }
        return false;
    }

    /**
     * Returns a prettier, more comprehensible version of a row evolution label
     * for display.
     */
    private function cleanOriginalLabel($label)
    {
        return str_replace(Piwik_API_DataTableManipulator_LabelFilter::SEPARATOR_RECURSIVE_LABEL, ' - ', $label);
    }

    /**
     * Performs multiple API requests at once and returns every result.
     *
     * @param array $urls The array of API requests.
     */
    public function getBulkRequest($urls)
    {
        if (empty($urls)) {
            return array();
        }

        $urls = array_map('urldecode', $urls);
        $urls = array_map(array('Piwik_Common','unsanitizeInputValue'), $urls);

        $result = array();
        foreach ($urls as $url) {
            $req = new Piwik_API_Request($url . '&format=php&serialize=0');
            $result[] = $req->process();
        }
        return $result;
    }

    /**
     * Given a segment, will return a list of the most used values for this particular segment.
     * @param $segmentName
     * @param $idSite
     * @throws Exception
     */
    public function getSuggestedValuesForSegment($segmentName, $idSite)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $maxSuggestionsToReturn = 30;
        $segmentsMetadata = $this->getSegmentsMetadata($idSite, $_hideImplementationData = false);

        $segmentFound = false;
        foreach($segmentsMetadata as $segmentMetadata) {
            if($segmentMetadata['segment'] == $segmentName) {
                $segmentFound = $segmentMetadata;
                break;
            }
        }
        if(empty($segmentFound)) {
            throw new Exception("Requested segment not found.");
        }

        $startDate = Piwik_Date::now()->subDay(60)->toString();
        $requestLastVisits = "method=Live.getLastVisitsDetails
            &idSite=$idSite
            &period=range
            &date=$startDate,today
            &format=original
            &serialize=0
            &flat=1";

        // Select non empty fields only
        // Note: this optimization has only a very minor impact
        $requestLastVisits.= "&segment=$segmentName".urlencode('!=');

        // By default Live fetches all actions for all visitors, but we'd rather do this only when required
        if($this->doesSegmentNeedActionsData($segmentName)) {
            $requestLastVisits .= "&filter_limit=500";
        } else {
            $requestLastVisits .= "&doNotFetchActions=1";
            $requestLastVisits .= "&filter_limit=1000";
        }

        $request = new Piwik_API_Request($requestLastVisits);
        $table = $request->process();
        if(empty($table)) {
            throw new Exception("There was no data to suggest for $segmentName");
        }

        // Cleanup data to return the top suggested (non empty) labels for this segment
        $values = $table->getColumn($segmentName);

        // Select also flattened keys (custom variables "page" scope, page URLs for one visit, page titles for one visit)
        $valuesBis = $table->getColumnsStartingWith($segmentName . Piwik_DataTable_Filter_ColumnDelete::APPEND_TO_COLUMN_NAME_TO_KEEP);
        $values = array_merge($values, $valuesBis);

        // remove false values (while keeping zeros)
        $values = array_filter( $values, 'strlen' );

        // we have a list of all values. let's show the most frequently used first.
        $values = array_count_values( $values );
        arsort($values);
        $values = array_keys($values);

        $values = array_map( array('Piwik_Common', 'unsanitizeInputValue'), $values);

        $values = array_slice($values, 0, $maxSuggestionsToReturn);
        return $values;
    }

    /**
     * @param $segmentName
     * @return bool
     */
    protected function doesSegmentNeedActionsData($segmentName)
    {
        $segmentsNeedActionsInfo = array('visitConvertedGoalId',
                                         'pageUrl', 'pageTitle', 'siteSearchKeyword',
                                         'entryPageTitle', 'entryPageUrl', 'exitPageTitle', 'exitPageUrl');
        $isCustomVariablePage = stripos($segmentName, 'customVariablePage') !== false;
        $doesSegmentNeedActionsInfo = in_array($segmentName, $segmentsNeedActionsInfo) || $isCustomVariablePage;
        return $doesSegmentNeedActionsInfo;
    }

}
