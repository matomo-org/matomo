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
    /**
     * @see Piwik_Plugin::getInformation
     */
    public function getInformation()
    {
        return array(
            'description'     => Piwik_Translate('API_PluginDescription'),
            'author'          => 'Piwik',
            'author_homepage' => 'http://piwik.org/',
            'version'         => Piwik_Version::VERSION,
        );
    }

    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
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

    public function getCssFiles(&$cssFiles)
    {
        $cssFiles[] = "plugins/API/stylesheets/listAllAPI.css";
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
     * Default translations for many core metrics.
     * This is used for exports with translated labels. The exports contain columns that
     * are not visible in the UI and not present in the API meta data. These columns are
     * translated here.
     * @return array
     */
    static public function getDefaultMetricTranslations()
    {
        return Piwik_Metrics::getDefaultMetricTranslations();
    }

    public function getSegmentsMetadata($idSites = array(), $_hideImplementationData = true)
    {
        $segments = array();
        Piwik_PostEvent('API.getSegmentsMetadata', array(&$segments, $idSites));

        $isAuthenticatedWithViewAccess = Piwik::isUserHasViewAccess($idSites) && !Piwik::isUserIsAnonymous();

        $segments[] = array(
            'type'           => 'dimension',
            'category'       => Piwik_Translate('General_Visit'),
            'name'           => 'General_VisitorIP',
            'segment'        => 'visitIp',
            'acceptedValues' => '13.54.122.1, etc.',
            'sqlSegment'     => 'log_visit.location_ip',
            'sqlFilter'      => array('Piwik_IP', 'P2N'),
            'permission'     => $isAuthenticatedWithViewAccess,
        );
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => Piwik_Translate('General_Visit'),
            'name'           => 'General_VisitorID',
            'segment'        => 'visitorId',
            'acceptedValues' => '34c31e04394bdc63 - any 16 Hexadecimal chars ID, which can be fetched using the Tracking API function getVisitorId()',
            'sqlSegment'     => 'log_visit.idvisitor',
            'sqlFilter'      => array('Piwik_Common', 'convertVisitorIdToBin'),
            'permission'     => $isAuthenticatedWithViewAccess,
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
            'name'           => Piwik_Translate('General_VisitType'),
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
                . '. ' . Piwik_Translate('General_EcommerceVisitStatusEg', '"&segment=visitEcommerceStatus==ordered,visitEcommerceStatus==orderedThenAbandonedCart"'),
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
        $logo = 'plugins/Zeitgeist/images/logo.png';
        if (Piwik_Config::getInstance()->branding['use_custom_logo'] == 1
            && file_exists(Piwik_Common::getPathToPiwikRoot() . '/misc/user/logo.png')
        ) {
            $logo = 'misc/user/logo.png';
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
        $logo = 'plugins/Zeitgeist/images/logo-header.png';
        if (Piwik_Config::getInstance()->branding['use_custom_logo'] == 1
            && file_exists(Piwik_Common::getPathToPiwikRoot() . '/misc/user/logo-header.png')
        ) {
            $logo = 'misc/user/logo-header.png';
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
        $logo = 'plugins/Zeitgeist/images/logo.svg';
        if (Piwik_Config::getInstance()->branding['use_custom_logo'] == 1
            && file_exists(Piwik_Common::getPathToPiwikRoot() . '/misc/user/logo.svg')
        ) {
            $logo = 'misc/user/logo.svg';
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
            && file_exists(Piwik_Common::getPathToPiwikRoot() . '/misc/user/logo.svg')
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
        $reporter = new Piwik_API_ProcessedReport();
        $metadata =  $reporter->getMetadata($idSite, $apiModule, $apiAction, $apiParameters, $language, $period, $date, $hideMetricsDoc, $showSubtableReports);
        return $metadata;
    }

    /**
     * Triggers a hook to ask plugins for available Reports.
     * Returns metadata information about each report (category, name, dimension, metrics, etc.)
     *
     * @param string $idSites Comma separated list of website Ids
     * @param bool|string $period
     * @param bool|Piwik_Date $date
     * @param bool $hideMetricsDoc
     * @param bool $showSubtableReports
     * @return array
     */
    public function getReportMetadata($idSites = '', $period = false, $date = false, $hideMetricsDoc = false,
                                      $showSubtableReports = false)
    {
        $reporter = new Piwik_API_ProcessedReport();
        $metadata =  $reporter->getReportMetadata($idSites, $period, $date, $hideMetricsDoc, $showSubtableReports);
        return $metadata;
    }

    public function getProcessedReport($idSite, $period, $date, $apiModule, $apiAction, $segment = false,
                                       $apiParameters = false, $idGoal = false, $language = false,
                                       $showTimer = true, $hideMetricsDoc = false, $idSubtable = false, $showRawMetrics = false)
    {
        $reporter = new Piwik_API_ProcessedReport();
        $processed =  $reporter->getProcessedReport( $idSite, $period, $date, $apiModule, $apiAction, $segment,
            $apiParameters, $idGoal, $language, $showTimer, $hideMetricsDoc, $idSubtable, $showRawMetrics);

        return $processed;
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
     * @param int $idSite
     * @param string $period
     * @param Piwik_Date $date
     * @param string $apiModule
     * @param string $apiAction
     * @param bool|string $label
     * @param bool|string $segment
     * @param bool|string $column
     * @param bool|string $language
     * @param bool|int $idGoal
     * @param bool|string $legendAppendMetric
     * @param bool|string $labelUseAbsoluteUrl
     * @return array
     */
    public function getRowEvolution($idSite, $period, $date, $apiModule, $apiAction, $label = false, $segment = false, $column = false, $language = false, $idGoal = false, $legendAppendMetric = true, $labelUseAbsoluteUrl = true)
    {
        $rowEvolution = new Piwik_API_RowEvolution();
        return $rowEvolution->getRowEvolution($idSite, $period, $date, $apiModule, $apiAction, $label, $segment, $column,
            $language, $idGoal, $legendAppendMetric, $labelUseAbsoluteUrl);
    }

    /**
     * Performs multiple API requests at once and returns every result.
     *
     * @param array $urls The array of API requests.
     * @return array
     */
    public function getBulkRequest($urls)
    {
        if (empty($urls)) {
            return array();
        }

        $urls = array_map('urldecode', $urls);
        $urls = array_map(array('Piwik_Common', 'unsanitizeInputValue'), $urls);

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
     * @return array
     * @throws Exception
     */
    public function getSuggestedValuesForSegment($segmentName, $idSite)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $maxSuggestionsToReturn = 30;
        $segmentsMetadata = $this->getSegmentsMetadata($idSite, $_hideImplementationData = false);

        $segmentFound = false;
        foreach ($segmentsMetadata as $segmentMetadata) {
            if ($segmentMetadata['segment'] == $segmentName) {
                $segmentFound = $segmentMetadata;
                break;
            }
        }
        if (empty($segmentFound)) {
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
        $requestLastVisits .= "&segment=$segmentName" . urlencode('!=');

        // By default Live fetches all actions for all visitors, but we'd rather do this only when required
        if ($this->doesSegmentNeedActionsData($segmentName)) {
            $requestLastVisits .= "&filter_limit=500";
        } else {
            $requestLastVisits .= "&doNotFetchActions=1";
            $requestLastVisits .= "&filter_limit=1000";
        }

        $request = new Piwik_API_Request($requestLastVisits);
        $table = $request->process();
        if (empty($table)) {
            throw new Exception("There was no data to suggest for $segmentName");
        }

        // Cleanup data to return the top suggested (non empty) labels for this segment
        $values = $table->getColumn($segmentName);

        // Select also flattened keys (custom variables "page" scope, page URLs for one visit, page titles for one visit)
        $valuesBis = $table->getColumnsStartingWith($segmentName . Piwik_DataTable_Filter_ColumnDelete::APPEND_TO_COLUMN_NAME_TO_KEEP);
        $values = array_merge($values, $valuesBis);

        // remove false values (while keeping zeros)
        $values = array_filter($values, 'strlen');

        // we have a list of all values. let's show the most frequently used first.
        $values = array_count_values($values);
        arsort($values);
        $values = array_keys($values);

        $values = array_map(array('Piwik_Common', 'unsanitizeInputValue'), $values);

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
