<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\API;

use Piwik\API\Proxy;
use Piwik\API\Request;
use Piwik\Columns\Dimension;
use Piwik\Config;
use Piwik\DataTable;
use Piwik\DataTable\Filter\ColumnDelete;
use Piwik\DataTable\Row;
use Piwik\Date;
use Piwik\IP;
use Piwik\Metrics;
use Piwik\Period;
use Piwik\Period\Range;
use Piwik\Piwik;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugins\CoreAdminHome\CustomLogo;
use Piwik\SegmentExpression;
use Piwik\Translate;
use Piwik\Version;

require_once PIWIK_INCLUDE_PATH . '/core/Config.php';

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
 * @method static \Piwik\Plugins\API\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Get Piwik version
     * @return string
     */
    public function getPiwikVersion()
    {
        Piwik::checkUserHasSomeViewAccess();
        return Version::VERSION;
    }

    /**
     * Returns the most accurate IP address availble for the current user, in
     * IPv4 format. This could be the proxy client's IP address.
     *
     * @return string IP address in presentation format.
     */
    public function getIpFromHeader()
    {
        Piwik::checkUserHasSomeViewAccess();
        return IP::getIpFromHeader();
    }

    /**
     * Returns the section [APISettings] if defined in config.ini.php
     * @return array
     */
    public function getSettings()
    {
        return Config::getInstance()->APISettings;
    }

    /**
     * Default translations for many core metrics.
     * This is used for exports with translated labels. The exports contain columns that
     * are not visible in the UI and not present in the API meta data. These columns are
     * translated here.
     * @return array
     */
    public static function getDefaultMetricTranslations()
    {
        return Metrics::getDefaultMetricTranslations();
    }

    public function getSegmentsMetadata($idSites = array(), $_hideImplementationData = true)
    {
        $segments = array();

        foreach (Dimension::getAllDimensions() as $dimension) {
            foreach ($dimension->getSegments() as $segment) {
                $segments[] = $segment->toArray();
            }
        }

        /**
         * Triggered when gathering all available segment dimensions.
         *
         * This event can be used to make new segment dimensions available.
         *
         * **Example**
         *
         *     public function getSegmentsMetadata(&$segments, $idSites)
         *     {
         *         $segments[] = array(
         *             'type'           => 'dimension',
         *             'category'       => Piwik::translate('General_Visit'),
         *             'name'           => 'General_VisitorIP',
         *             'segment'        => 'visitIp',
         *             'acceptedValues' => '13.54.122.1, etc.',
         *             'sqlSegment'     => 'log_visit.location_ip',
         *             'sqlFilter'      => array('Piwik\IP', 'P2N'),
         *             'permission'     => $isAuthenticatedWithViewAccess,
         *         );
         *     }
         *
         * @param array &$dimensions The list of available segment dimensions. Append to this list to add
         *                           new segments. Each element in this list must contain the
         *                           following information:
         *
         *                           - **type**: Either `'metric'` or `'dimension'`. `'metric'` means
         *                                       the value is a numeric and `'dimension'` means it is
         *                                       a string. Also, `'metric'` values will be displayed
         *                                       under **Visit (metrics)** in the Segment Editor.
         *                           - **category**: The segment category name. This can be an existing
         *                                           segment category visible in the segment editor.
         *                           - **name**: The pretty name of the segment. Can be a translation token.
         *                           - **segment**: The segment name, eg, `'visitIp'` or `'searches'`.
         *                           - **acceptedValues**: A string describing one or two exacmple values, eg
         *                                                 `'13.54.122.1, etc.'`.
         *                           - **sqlSegment**: The table column this segment will segment by.
         *                                             For example, `'log_visit.location_ip'` for the
         *                                             **visitIp** segment.
         *                           - **sqlFilter**: A PHP callback to apply to segment values before
         *                                            they are used in SQL.
         *                           - **permission**: True if the current user has view access to this
         *                                             segment, false if otherwise.
         * @param array $idSites The list of site IDs we're getting the available segments
         *                       for. Some segments (such as Goal segments) depend on the
         *                       site.
         */
        Piwik::postEvent('API.getSegmentDimensionMetadata', array(&$segments, $idSites));

        $isAuthenticatedWithViewAccess = Piwik::isUserHasViewAccess($idSites) && !Piwik::isUserIsAnonymous();

        $segments[] = array(
            'type'           => 'dimension',
            'category'       => Piwik::translate('General_Visit'),
            'name'           => 'General_UserId',
            'segment'        => 'userId',
            'acceptedValues' => 'any non empty unique string identifying the user (such as an email address or a username).',
            'sqlSegment'     => 'log_visit.idvisitor',
            'sqlFilterValue' => array('Piwik\Common', 'convertUserIdToVisitorIdBin'),
            'sqlFilter'      => array($this, 'checkSegmentMatchTypeIsValidForUser'),
            'permission'     => $isAuthenticatedWithViewAccess,

            // TODO specify that this segment is not compatible with some operators
//            'unsupportedOperators' = array(MATCH_CONTAINS, MATCH_DOES_NOT_CONTAIN),
        );

        $segments[] = array(
            'type'           => 'dimension',
            'category'       => Piwik::translate('General_Visit'),
            'name'           => 'General_VisitorID',
            'segment'        => 'visitorId',
            'acceptedValues' => '34c31e04394bdc63 - any 16 Hexadecimal chars ID, which can be fetched using the Tracking API function getVisitorId()',
            'sqlSegment'     => 'log_visit.idvisitor',
            'sqlFilterValue' => array('Piwik\Common', 'convertVisitorIdToBin'),
            'permission'     => $isAuthenticatedWithViewAccess,
        );

        $segments[] = array(
            'type'           => 'dimension',
            'category'       => Piwik::translate('General_Visit'),
            'name'           => Piwik::translate('General_Visit') . " ID",
            'segment'        => 'visitId',
            'acceptedValues' => 'Any integer. ',
            'sqlSegment'     => 'log_visit.idvisit',
            'permission'     => $isAuthenticatedWithViewAccess,
        );

        $segments[] = array(
            'type'           => 'metric',
            'category'       => Piwik::translate('General_Visit'),
            'name'           => 'General_VisitorIP',
            'segment'        => 'visitIp',
            'acceptedValues' => '13.54.122.1. </code>Select IP ranges with notation: <code>visitIp>13.54.122.0;visitIp<13.54.122.255',
            'sqlSegment'     => 'log_visit.location_ip',
            'sqlFilterValue' => array('Piwik\IP', 'P2N'),
            'permission'     => $isAuthenticatedWithViewAccess,
        );

        foreach ($segments as &$segment) {
            $segment['name'] = Piwik::translate($segment['name']);
            $segment['category'] = Piwik::translate($segment['category']);

            if ($_hideImplementationData) {
                unset($segment['sqlFilter']);
                unset($segment['sqlFilterValue']);
                unset($segment['sqlSegment']);
            }
        }

        usort($segments, array($this, 'sortSegments'));
        return $segments;
    }

    private function sortSegments($row1, $row2)
    {
        $customVarCategory = Piwik::translate('CustomVariables_CustomVariables');

        $columns = array('type', 'category', 'name', 'segment');
        foreach ($columns as $column) {
            // Keep segments ordered alphabetically inside categories..
            $type = -1;
            if ($column == 'name') $type = 1;

            $compare = $type * strcmp($row1[$column], $row2[$column]);

            // hack so that custom variables "page" are grouped together in the doc
            if ($row1['category'] == $customVarCategory
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
     * Throw an exception if the User ID segment is used with an un-supported match type,
     *
     * @ignore
     * @param $value
     * @param $sqlSegment
     * @param $matchType
     * @param $name
     * @return $value
     * @throws \Exception
     */
    public function checkSegmentMatchTypeIsValidForUser($value, $sqlSegment, $matchType, $name)
    {
        $acceptedMatches = array(
            SegmentExpression::MATCH_EQUAL,
            SegmentExpression::MATCH_IS_NOT_NULL_NOR_EMPTY,
            SegmentExpression::MATCH_IS_NULL_OR_EMPTY,
            SegmentExpression::MATCH_NOT_EQUAL,
        );
        if (in_array($matchType, $acceptedMatches)) {
            return $value;
        }
        $message = "Invalid Segment match type: try using 'userId' segment with one of the following match types: %s.";
        throw new \Exception(sprintf($message, implode(", ", $acceptedMatches)));
    }

    /**
     * Returns the url to application logo (~280x110px)
     *
     * @param bool $pathOnly If true, returns path relative to doc root. Otherwise, returns a URL.
     * @return string
     */
    public function getLogoUrl($pathOnly = false)
    {
        $logo = new CustomLogo();
        return $logo->getLogoUrl($pathOnly);
    }

    /**
     * Returns the url to header logo (~127x50px)
     *
     * @param bool $pathOnly If true, returns path relative to doc root. Otherwise, returns a URL.
     * @return string
     */
    public function getHeaderLogoUrl($pathOnly = false)
    {
        $logo = new CustomLogo();
        return $logo->getHeaderLogoUrl($pathOnly);
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
        $logo = new CustomLogo();
        return $logo->getSVGLogoUrl($pathOnly);
    }

    /**
     * Returns whether there is an SVG Logo available.
     * @ignore
     * @return bool
     */
    public function hasSVGLogo()
    {
        $logo = new CustomLogo();
        return $logo->hasSVGLogo();
    }

    /**
     * Loads reports metadata, then return the requested one,
     * matching optional API parameters.
     */
    public function getMetadata($idSite, $apiModule, $apiAction, $apiParameters = array(), $language = false,
                                $period = false, $date = false, $hideMetricsDoc = false, $showSubtableReports = false)
    {
        Translate::reloadLanguage($language);
        $reporter = new ProcessedReport();
        $metadata = $reporter->getMetadata($idSite, $apiModule, $apiAction, $apiParameters, $language, $period, $date, $hideMetricsDoc, $showSubtableReports);
        return $metadata;
    }

    /**
     * Triggers a hook to ask plugins for available Reports.
     * Returns metadata information about each report (category, name, dimension, metrics, etc.)
     *
     * @param string $idSites Comma separated list of website Ids
     * @param bool|string $period
     * @param bool|Date $date
     * @param bool $hideMetricsDoc
     * @param bool $showSubtableReports
     * @return array
     */
    public function getReportMetadata($idSites = '', $period = false, $date = false, $hideMetricsDoc = false,
                                      $showSubtableReports = false)
    {
        $reporter = new ProcessedReport();
        $metadata = $reporter->getReportMetadata($idSites, $period, $date, $hideMetricsDoc, $showSubtableReports);
        return $metadata;
    }

    public function getProcessedReport($idSite, $period, $date, $apiModule, $apiAction, $segment = false,
                                       $apiParameters = false, $idGoal = false, $language = false,
                                       $showTimer = true, $hideMetricsDoc = false, $idSubtable = false, $showRawMetrics = false)
    {
        $reporter = new ProcessedReport();
        $processed = $reporter->getProcessedReport($idSite, $period, $date, $apiModule, $apiAction, $segment,
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
        $meta = \Piwik\Plugins\API\API::getInstance()->getReportMetadata($idSite, $period, $date);
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
            $className = Request::getClassNameAPI($plugin);
            $params['columns'] = implode(',', $columns);
            $dataTable = Proxy::getInstance()->call($className, 'get', $params);

            // make sure the table has all columns
            $array = ($dataTable instanceof DataTable\Map ? $dataTable->getDataTables() : array($dataTable));
            foreach ($array as $table) {
                // we don't support idSites=all&date=DATE1,DATE2
                if ($table instanceof DataTable) {
                    $firstRow = $table->getFirstRow();
                    if (!$firstRow) {
                        $firstRow = new Row;
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
        if ($table1 instanceof DataTable\Map && $table2 instanceof DataTable\Map) {
            $subTables2 = $table2->getDataTables();
            foreach ($table1->getDataTables() as $index => $subTable1) {
                if (!array_key_exists($index, $subTables2)) {
                    // occurs when archiving starts on dayN and continues into dayN+1, see https://github.com/piwik/piwik/issues/5168#issuecomment-50959925
                    continue;
                }
                $subTable2 = $subTables2[$index];
                $this->mergeDataTables($subTable1, $subTable2);
            }
            return;
        }

        $firstRow1 = $table1->getFirstRow();
        $firstRow2 = $table2->getFirstRow();
        if ($firstRow2 instanceof Row) {
            foreach ($firstRow2->getColumns() as $metric => $value) {
                $firstRow1->setColumn($metric, $value);
            }
        }
    }

    /**
     * Given an API report to query (eg. "Referrers.getKeywords", and a Label (eg. "free%20software"),
     * this function will query the API for the previous days/weeks/etc. and will return
     * a ready to use data structure containing the metrics for the requested Label, along with enriched information (min/max values, etc.)
     *
     * @param int $idSite
     * @param string $period
     * @param Date $date
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
        $rowEvolution = new RowEvolution();
        return $rowEvolution->getRowEvolution($idSite, $period, $date, $apiModule, $apiAction, $label, $segment, $column,
            $language, $idGoal, $legendAppendMetric, $labelUseAbsoluteUrl);
    }

    public function getLastDate($date, $period)
    {
        $lastDate = Range::getLastDate($date, $period);

        return array_shift($lastDate);
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
        $urls = array_map(array('Piwik\Common', 'unsanitizeInputValue'), $urls);

        $result = array();
        foreach ($urls as $url) {
            $req = new Request($url . '&format=php&serialize=0');
            $result[] = $req->process();
        }
        return $result;
    }

    /**
     * Given a segment, will return a list of the most used values for this particular segment.
     * @param $segmentName
     * @param $idSite
     * @throws \Exception
     * @return array
     */
    public function getSuggestedValuesForSegment($segmentName, $idSite)
    {
        if (empty(Config::getInstance()->General['enable_segment_suggested_values'])) {
            return array();
        }
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
            throw new \Exception("Requested segment not found.");
        }

        // if segment has suggested values callback then return result from it instead
        if (isset($segmentFound['suggestedValuesCallback'])) {
            return call_user_func($segmentFound['suggestedValuesCallback'], $idSite, $maxSuggestionsToReturn);
        }

        // if period=range is disabled, do not proceed
        if (!Period\Factory::isPeriodEnabledForAPI('range')) {
            return array();
        }

        $startDate = Date::now()->subDay(60)->toString();
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
            $requestLastVisits .= "&filter_limit=400";
        } else {
            $requestLastVisits .= "&doNotFetchActions=1";
            $requestLastVisits .= "&filter_limit=800";
        }

        $request = new Request($requestLastVisits);
        $table = $request->process();
        if (empty($table)) {
            throw new \Exception("There was no data to suggest for $segmentName");
        }

        // Cleanup data to return the top suggested (non empty) labels for this segment
        $values = $table->getColumn($segmentName);

        // Select also flattened keys (custom variables "page" scope, page URLs for one visit, page titles for one visit)
        $valuesBis = $table->getColumnsStartingWith($segmentName . ColumnDelete::APPEND_TO_COLUMN_NAME_TO_KEEP);
        $values = array_merge($values, $valuesBis);

        $values = $this->getMostFrequentValues($values);

        $values = array_slice($values, 0, $maxSuggestionsToReturn);

        $values = array_map(array('Piwik\Common', 'unsanitizeInputValue'), $values);

        return $values;
    }

    /**
     * @param $segmentName
     * @return bool
     */
    protected function doesSegmentNeedActionsData($segmentName)
    {
        // If you update this, also update flattenVisitorDetailsArray
        $segmentsNeedActionsInfo = array('visitConvertedGoalId',
                                         'pageUrl', 'pageTitle', 'siteSearchKeyword',
                                         'entryPageTitle', 'entryPageUrl', 'exitPageTitle', 'exitPageUrl');
        $isCustomVariablePage = stripos($segmentName, 'customVariablePage') !== false;
        $isEventSegment = stripos($segmentName, 'event') !== false;
        $isContentSegment = stripos($segmentName, 'content') !== false;
        $doesSegmentNeedActionsInfo = in_array($segmentName, $segmentsNeedActionsInfo) || $isCustomVariablePage || $isEventSegment || $isContentSegment;
        return $doesSegmentNeedActionsInfo;
    }

    /**
     * @param $values
     * @param $value
     * @return array
     */
    private function getMostFrequentValues($values)
    {
        // remove false values (while keeping zeros)
        $values = array_filter($values, 'strlen');

        // array_count_values requires strings or integer, convert floats to string (mysqli)
        foreach ($values as &$value) {
            if (is_numeric($value)) {
                $value = (string)round($value, 3);
            }
        }
        // we have a list of all values. let's show the most frequently used first.
        $values = array_count_values($values);

        arsort($values);
        $values = array_keys($values);
        return $values;
    }
}

/**
 */
class Plugin extends \Piwik\Plugin
{
    public function __construct()
    {
        // this class is named 'Plugin', manually set the 'API' plugin
        parent::__construct($pluginName = 'API');
    }

    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles'
        );
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/API/stylesheets/listAllAPI.less";
    }
}
