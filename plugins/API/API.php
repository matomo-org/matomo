<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\API;

use Piwik\API\Proxy;
use Piwik\API\Request;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Cache;
use Piwik\CacheId;
use Piwik\Category\CategoryList;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\DataTable;
use Piwik\DataTable\Filter\ColumnDelete;
use Piwik\Date;
use Piwik\Http\BadRequestException;
use Piwik\IP;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\Plugin\SettingsProvider;
use Piwik\Plugins\API\DataTable\MergeDataTables;
use Piwik\Plugins\CorePluginsAdmin\SettingsMetadata;
use Piwik\Segment;
use Piwik\Site;
use Piwik\Translation\Translator;
use Piwik\Measurable\Type\TypeManager;
use Piwik\Version;
use Piwik\Widget\WidgetsList;

require_once PIWIK_INCLUDE_PATH . '/core/Config.php';

/**
 * This API is the <a href='https://matomo.org/docs/analytics-api/metadata/' rel='noreferrer' target='_blank'>Metadata API</a>: it gives information about all other available APIs methods, as well as providing
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
 * The Metadata API is for example used by the Matomo Mobile App to automatically display all Matomo reports, with translated report & columns names and nicely formatted values.
 * More information on the <a href='https://matomo.org/docs/analytics-api/metadata/' rel='noreferrer' target='_blank'>Metadata API documentation page</a>
 *
 * @phpstan-import-type ProcessedReportData from ProcessedReport
 *
 * @method static \Piwik\Plugins\API\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * @var SettingsProvider
     */
    private $settingsProvider;

    /**
     * @var ProcessedReport
     */
    private $processedReport;

    /**
     * For Testing purpose only
     * @var int
     */
    public static $_autoSuggestLookBack = 60; // phpcs:ignore PSR2.Classes.PropertyDeclaration.Underscore

    public function __construct(SettingsProvider $settingsProvider, ProcessedReport $processedReport)
    {
        $this->settingsProvider = $settingsProvider;
        $this->processedReport = $processedReport;
    }

    /**
     * Returns the current Matomo version.
     *
     * @return string Matomo's version string.
     */
    public function getMatomoVersion(): string
    {
        Piwik::checkUserHasSomeViewAccess();
        return Version::VERSION;
    }

    /**
     * Returns information about the PHP runtime version.
     *
     * @return array{version:string, major:int, minor:int, release:int, versionId:int, extra:string}
     */
    public function getPhpVersion(): array
    {
        Piwik::checkUserHasSuperUserAccess();
        return [
            'version' => PHP_VERSION,
            'major' => PHP_MAJOR_VERSION,
            'minor' => PHP_MINOR_VERSION,
            'release' => PHP_RELEASE_VERSION,
            'versionId' => PHP_VERSION_ID,
            'extra' => PHP_EXTRA_VERSION,
        ];
    }

    /**
     * Returns the current Matomo version.
     *
     * @return string Matomo's version string.
     * @deprecated Deprecated but we keep it for historical reasons to not break BC
     */
    public function getPiwikVersion(): string
    {
        return $this->getMatomoVersion();
    }

    /**
     * Returns the most accurate IP address available for the current user, in
     * IPv4 format. This could be the proxy client's IP address.
     *
     * @return string IP address in presentation format.
     */
    public function getIpFromHeader(): string
    {
        Piwik::checkUserHasSomeViewAccess();
        return IP::getIpFromHeader();
    }

    /**
     * Returns the `[APISettings]` section from `config.ini.php`.
     *
     * @return array<string, mixed>
     * @deprecated May be removed in one of the next major releases
     */
    public function getSettings()
    {
        return Config::getInstance()->APISettings;
    }

    /**
     * Returns all available measurable types.
     * Marked as internal so it won't appear on the API page.
     * @return list<array{id:string, name:string, description:string, longDescription:string, howToSetupUrl:string, settings:list<array<string, scalar>>}>
     * @internal
     */
    public function getAvailableMeasurableTypes()
    {
        Piwik::checkUserHasSomeViewAccess();

        $typeManager = new TypeManager();
        $types = $typeManager->getAllTypes();

        $available = array();
        foreach ($types as $type) {
            $measurableSettings = $this->settingsProvider->getAllMeasurableSettings($idSite = 0, $type->getId());

            $settingsMetadata = new SettingsMetadata();

            $available[] = array(
                'id' => $type->getId(),
                'name' => Piwik::translate($type->getName()),
                'description' => Piwik::translate($type->getDescription()),
                'longDescription' => Piwik::translate($type->getLongDescription()),
                'howToSetupUrl' => $type->getHowToSetupUrl(),
                'settings' => $settingsMetadata->formatSettings($measurableSettings),
            );
        }

        return $available;
    }

    /**
     * Returns metadata for all available segments.
     *
     * @param int[]|int|string $idSites One or more site IDs. If empty, returns metadata visible to the current user.
     * @param bool $_hideImplementationData Whether internal implementation details should be omitted.
     * @param bool $_showAllSegments Whether to include segments that are normally hidden.
     * @return array<int, array<string, mixed>>
     */
    public function getSegmentsMetadata($idSites = array(), $_hideImplementationData = true, $_showAllSegments = false): array
    {
        if (empty($idSites)) {
            Piwik::checkUserHasSomeViewAccess();
        } else {
            $idSites = Site::getIdSitesFromIdSitesString($idSites, false, true);
            Piwik::checkUserHasViewAccess($idSites);
        }

        $isNotAnonymous = !Piwik::isUserIsAnonymous();

        $sites   = (is_array($idSites) ? implode('.', $idSites) : (int) $idSites);
        $cache   = Cache::getTransientCache();
        $cacheKey = 'API.getSegmentsMetadata' . $sites . '_' . (int) $_hideImplementationData . '_' . (int) $isNotAnonymous . '_' . (int) $_showAllSegments;
        $cacheKey = CacheId::pluginAware($cacheKey);

        if ($cache->contains($cacheKey)) {
            return $cache->fetch($cacheKey);
        }

        $metadata = new SegmentMetadata();
        $segments = $metadata->getSegmentsMetadata($idSites, $_hideImplementationData, $isNotAnonymous, $_showAllSegments);

        $cache->save($cacheKey, $segments);

        return $segments;
    }

    /**
     * Extracts suggested segment values from a visitor log result table.
     *
     * @param string $segmentName Segment name to read values for.
     * @param DataTable $table Visitor log data.
     * @return array<int, mixed>
     */
    protected function getSegmentValuesFromVisitorLog($segmentName, $table)
    {
        // Cleanup data to return the top suggested (non empty) labels for this segment
        $values = $table->getColumn($segmentName);

        // Select also flattened keys (custom variables "page" scope, page URLs for one visit, page titles for one visit)
        $valuesBis = $table->getColumnsStartingWith($segmentName . ColumnDelete::APPEND_TO_COLUMN_NAME_TO_KEEP);
        $values = array_merge($values, $valuesBis);

        // Select values from the action details if needed for this particular segment
        if (empty(array_filter($values)) && $this->doesSegmentNeedActionsData($segmentName)) {
            foreach ($table->getRows() as $row) {
                foreach ($row->getColumn('actionDetails') as $actionRow) {
                    if (isset($actionRow[$segmentName])) {
                        $values[] = $actionRow[$segmentName];
                    }
                }
            }
        }

        return $values;
    }

    /**
     * Returns metadata for a specific API method.
     *
     * @param int|string $idSite Site ID to use when loading metadata.
     * @param string $apiModule API module name.
     * @param string $apiAction API method name without the module prefix.
     * @param array<string, mixed> $apiParameters Additional API parameters used to resolve metadata variants.
     * @param string|false $language Optional language code used to localize the response.
     * @param string|false $period Optional period used to resolve period-dependent metadata.
     * @param string|false $date Optional date or date range used to resolve metadata.
     * @param bool $hideMetricsDoc Whether metric documentation should be omitted.
     * @param bool $showSubtableReports Whether subtable reports should be included.
     * @return array<int, array<string, mixed>>
     */
    public function getMetadata(
        $idSite,
        string $apiModule,
        string $apiAction,
        $apiParameters = [],
        $language = false,
        $period = false,
        $date = false,
        $hideMetricsDoc = false,
        $showSubtableReports = false
    ) {
        Piwik::checkUserHasViewAccess($idSite);

        if ($language) {
            /** @var Translator $translator */
            $translator = StaticContainer::get('Piwik\Translation\Translator');
            $translator->setCurrentLanguage($language);
        }

        return $this->processedReport->getMetadata($idSite, $apiModule, $apiAction, $apiParameters, $language, $period, $date, $hideMetricsDoc, $showSubtableReports);
    }

    /**
     * Returns metadata for all available reports.
     *
     * @param int[]|int|string $idSites Deprecated fallback for specifying one or more site IDs.
     * @param string|false $period Optional period used to resolve report metadata.
     * @param string|false $date Optional date or date range used to resolve report metadata.
     * @param bool $hideMetricsDoc Whether metric documentation should be omitted.
     * @param bool $showSubtableReports Whether subtable reports should be included.
     * @param int|string|false $idSite Preferred site ID parameter.
     * @return array<int, array<string, mixed>>
     */
    public function getReportMetadata(
        $idSites = '',
        $period = false,
        $date = false,
        $hideMetricsDoc = false,
        $showSubtableReports = false,
        $idSite = false
    ) {
        if (empty($idSite) && !empty($idSites)) {
            if (is_array($idSites)) {
                $idSite = array_shift($idSites);
            } else {
                $idSite = $idSites;
            }
        } elseif (empty($idSite) && empty($idSites)) {
            throw new \Exception('Calling API.getReportMetadata without any idSite is no longer supported since Matomo 3.0.0. Please specify at least one idSite via the "idSite" parameter.');
        }

        Piwik::checkUserHasViewAccess($idSite);

        $metadata = $this->processedReport->getReportMetadata($idSite, $period, $date, $hideMetricsDoc, $showSubtableReports);
        return $metadata;
    }

    /**
     * Returns a processed report with metadata, formatting, and processed metrics applied.
     *
     * @param int|string $idSite Site ID to query.
     * @param string $period Report period.
     * @param string $date Date or date range to query.
     * @param string $apiModule API module name.
     * @param string $apiAction API method name without the module prefix.
     * @param string|false $segment Optional segment expression.
     * @param array<string, mixed>|false $apiParameters Additional API parameters forwarded to the target report.
     * @param int|string|false $idGoal Optional goal ID.
     * @param string|false $language Optional language code for the response.
     * @param bool $showTimer Whether processing time information should be included.
     * @param bool $hideMetricsDoc Whether metric documentation should be omitted.
     * @param int|string|false $idSubtable Optional subtable ID to load.
     * @param bool $showRawMetrics Whether raw metrics should be included alongside formatted metrics.
     * @param string|null $format_metrics Optional metrics formatting mode.
     * @param int|string|false $idDimension Optional dimension ID.
     * @return array<string, mixed>
     * @phpstan-return ProcessedReportData
     */
    public function getProcessedReport(
        $idSite,
        $period,
        $date,
        $apiModule,
        $apiAction,
        $segment = false,
        $apiParameters = false,
        $idGoal = false,
        $language = false,
        $showTimer = true,
        $hideMetricsDoc = false,
        $idSubtable = false,
        $showRawMetrics = false,
        $format_metrics = null,
        $idDimension = false
    ) {
        Piwik::checkUserHasViewAccess($idSite);

        $processed = $this->processedReport->getProcessedReport(
            $idSite,
            $period,
            $date,
            $apiModule,
            $apiAction,
            $segment,
            $apiParameters,
            $idGoal,
            $language,
            $showTimer,
            $hideMetricsDoc,
            $idSubtable,
            $showRawMetrics,
            $format_metrics,
            $idDimension
        );

        return $processed;
    }

    /**
     * Returns page metadata for the Matomo UI, including the widgets shown on each page.
     *
     * @param int|string $idSite Site ID used for the access check.
     * @return array<int, array<string, mixed>>
     */
    public function getReportPagesMetadata($idSite)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $widgetsList  = WidgetsList::get();
        $categoryList = CategoryList::get();
        $metadata     = new WidgetMetadata();

        return $metadata->getPagesMetadata($categoryList, $widgetsList);
    }

    /**
     * Returns metadata for all widgets that can be displayed in the UI.
     *
     * @param int|string $idSite Site ID used for the access check.
     * @return array<int, array<string, mixed>>
     */
    public function getWidgetMetadata($idSite)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $widgetsList  = WidgetsList::get();
        $categoryList = CategoryList::get();
        $metadata     = new WidgetMetadata();

        return $metadata->getWidgetMetadata($categoryList, $widgetsList);
    }

    /**
     * Returns a combined report built from the `*.get` API methods of other plugins.
     *
     * @param int|string $idSite Site ID to query.
     * @param string $period Report period.
     * @param string $date Date or date range to query.
     * @param string|false $segment Optional segment expression.
     * @param string[]|string|false $columns Optional metric names to keep in the combined result.
     * @return DataTable
     */
    public function get($idSite, $period, $date, $segment = false, $columns = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

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
            if (
                $reportMeta['action'] == 'get'
                && !isset($reportMeta['parameters'])
                && $reportMeta['module'] != 'API'
                && !empty($reportMeta['metrics'])
            ) {
                $plugin = $reportMeta['module'];
                $allMetrics = array_merge($reportMeta['metrics'], (@$reportMeta['processedMetrics'] ?: []));
                foreach ($allMetrics as $column => $columnTranslation) {
                    // a metric from this report has been requested
                    if (
                        isset($columnsMap[$column])
                        // or by default, return all metrics
                        || empty($columnsMap)
                    ) {
                        $columnsByPlugin[$plugin][] = $column;
                    }
                }
            }
        }
        krsort($columnsByPlugin);

        $mergedDataTable = null;
        $params = compact('idSite', 'period', 'date', 'segment');
        foreach ($columnsByPlugin as $plugin => $columns) {
            // load the data
            $className = Request::getClassNameAPI($plugin);
            $params['columns'] = implode(',', $columns);
            $dataTable = Proxy::getInstance()->call($className, 'get', $params);

            $dataTable->filter(function (DataTable $table) {
                $table->clearQueuedFilters();
            });

            // merge reports
            if ($mergedDataTable === null) {
                $mergedDataTable = $dataTable;
            } else {
                $merger = new MergeDataTables(true);
                $merger->mergeDataTables($mergedDataTable, $dataTable);
            }
        }

        if (
            !empty($columnsMap)
            && !empty($mergedDataTable)
        ) {
            $mergedDataTable->queueFilter('ColumnDelete', array(false, array_keys($columnsMap)));
        }

        return $mergedDataTable ?? new DataTable();
    }

    /**
     * Returns an evolution series for a specific report row or metric label.
     *
     * @param int|string $idSite Site ID to query.
     * @param string $period Period to calculate the evolution for.
     * @param string $date Date or date range to query.
     * @param string $apiModule API module name.
     * @param string $apiAction API method name without the module prefix.
     * @param string|false $label Optional row label to track.
     * @param string|false $segment Optional segment expression.
     * @param string|false $column Optional metric column to use.
     * @param string|false $language Optional language code for the response.
     * @param int|string|false $idGoal Optional goal ID.
     * @param bool|string $legendAppendMetric Whether to append the metric name to the legend.
     * @param bool|string $labelUseAbsoluteUrl Whether labels that are URLs should be normalized to absolute URLs.
     * @param int|string|false $idDimension Optional dimension ID.
     * @param string|false $labelSeries Optional custom series label.
     * @param int|string|false $showGoalMetricsForGoal Optional goal ID whose goal metrics should be included.
     * @return array<string, mixed>
     */
    public function getRowEvolution($idSite, $period, $date, $apiModule, $apiAction, $label = false, $segment = false, $column = false, $language = false, $idGoal = false, $legendAppendMetric = true, $labelUseAbsoluteUrl = true, $idDimension = false, $labelSeries = false, $showGoalMetricsForGoal = false)
    {
        // check if site exists
        $idSite = (int) $idSite;
        $site = new Site($idSite);

        Piwik::checkUserHasViewAccess($idSite);

        $apiParameters = array();
        $entityNames = StaticContainer::get('entities.idNames');
        foreach ($entityNames as $entityName) {
            if ($entityName === 'idGoal' && is_numeric($idGoal)) {
                $apiParameters['idGoal'] = $idGoal;
            } elseif ($entityName === 'idDimension' && $idDimension) {
                $apiParameters['idDimension'] = $idDimension;
            } else {
                // ideally it would get the value from API params but dynamic params is not possible yet in API. If this
                // method is called eg in Request::processRequest, it could in theory pick up a param from the original request
                // and not from the API request within the original request.
                $idEntity = Common::getRequestVar($entityName, 0, 'int');
                if ($idEntity > 0) {
                    $apiParameters[$entityName] = $idEntity;
                }
            }
        }

        $rowEvolution = new RowEvolution();
        return $rowEvolution->getRowEvolution(
            $idSite,
            $period,
            $date,
            $apiModule,
            $apiAction,
            $label,
            $segment,
            $column,
            $language,
            $apiParameters,
            $legendAppendMetric,
            $labelUseAbsoluteUrl,
            $labelSeries,
            $showGoalMetricsForGoal
        );
    }

    /**
     * Performs multiple API requests at once and returns every result.
     *
     * @param string[] $urls API query strings to execute.
     * @return array<int, mixed>
     * @unsanitized
     */
    public function getBulkRequest($urls)
    {
        if (empty($urls) || !is_array($urls)) {
            return [];
        }

        $limit = BulkRequestLimit::getCurrentLimit();
        if ($limit > -1 && count($urls) > $limit) {
            throw new BadRequestException(Piwik::translate('General_MaximumNumberOfBulkRequestUrlsIs', [$limit]));
        }

        $request = \Piwik\Request::fromRequest();
        $queryParameters = $request->getParameters();
        unset($queryParameters['urls']);

        $result = [];
        foreach ($urls as $url) {
            $params = \Piwik\Request::fromQueryString($url)->getParameters();
            $params['format'] = 'json';

            $params += $queryParameters;

            $method = $params['method'] ?? '';

            if (
                !empty($method) && is_string($method) &&
                preg_replace('/[^\w\.]+/', '', Common::sanitizeInputValue($method)) === 'API.getBulkRequest'
            ) {
                continue;
            }

            $req = new Request($params);
            $result[] = json_decode($req->process(), true);
        }
        return $result;
    }

    /**
     * Returns whether a plugin is currently activated.
     *
     * @param string $pluginName Plugin name to check.
     */
    public function isPluginActivated($pluginName): bool
    {
        Piwik::checkUserHasSomeViewAccess();
        return \Piwik\Plugin\Manager::getInstance()->isPluginActivated($pluginName);
    }

    /**
     * Returns suggested values for a segment based on recent data or a segment-specific callback.
     *
     * @param string $segmentName Segment name to suggest values for.
     * @param int|string $idSite Site ID to query, or `'all'` for all sites where supported.
     * @return array<int, mixed>
     */
    public function getSuggestedValuesForSegment($segmentName, $idSite)
    {
        if (empty(Config::getInstance()->General['enable_segment_suggested_values'])) {
            return array();
        }

        Piwik::checkUserHasViewAccess($idSite);

        $maxSuggestionsToReturn = 30;
        $segment = $this->findSegment($segmentName, $idSite);

        // if segment has suggested values callback then return result from it instead
        $suggestedValuesCallbackRequiresTable = false;

        if (!empty($segment['suggestedValuesApi']) && is_string($segment['suggestedValuesApi']) && !Rules::isBrowserTriggerEnabled()) {
            if ($idSite === 'all') {
                $now = Date::now()->setTimezone(\Piwik\Plugins\SitesManager\API::getInstance()->getDefaultTimezone());
            } else {
                $now = Date::now()->setTimezone(Site::getTimezoneFor($idSite));
            }
            if (self::$_autoSuggestLookBack != 60) {
                // in Auto suggest tests we need to assume now is in 2018...
                // we do - 20 to make sure the year is still correct otherwise could end up being 2017-12-31 and the recorded visits are over several days in the tests we make sure to select the last day a visit was recorded
                $now = $now->subDay(self::$_autoSuggestLookBack - 20);
            }
            // we want to avoid launching the archiver should browser archiving be enabled as this can be very slow... we then rather
            // use the live api.
            $period = 'year';
            $date = $now->toString();
            if ($now->toString('m') == '01') {
                if (Rules::isArchivingDisabledFor([$idSite], new Segment('', [$idSite]), 'range')) {
                    $date = $now->subYear(1)->toString(); // use previous year data to avoid using range
                } else {
                    $period = 'range';
                    $date = $now->subMonth(1)->toString() . ',' . $now->addDay(1)->toString();
                }
            }

            $apiParts = explode('.', $segment['suggestedValuesApi']);
            $meta = $this->getMetadata($idSite, $apiParts[0], $apiParts[1]);
            $flat = !empty($meta[0]['actionToLoadSubTables']) && $meta[0]['actionToLoadSubTables'] == $apiParts[1];

            $table          = Request::processRequest($segment['suggestedValuesApi'], [
                'idSite' => $idSite,
                'period' => $period,
                'date' => $date,
                'segment' => '',
                'filter_offset' => 0,
                'flat' => (int) $flat,
                'filter_limit' => $maxSuggestionsToReturn,
            ]);

            if ($table && $table instanceof DataTable && $table->getRowsCount()) {
                $values = [];
                foreach ($table->getRowsWithoutSummaryRow() as $row) {
                    /** @var string|null|false $segment */
                    $segment = $row->getMetadata('segment');
                    $remove = [
                        $segmentName . Segment\SegmentExpression::MATCH_EQUAL,
                        $segmentName . Segment\SegmentExpression::MATCH_STARTS_WITH,
                    ];
                    // we don't look at row columns since this could include rows that won't work eg Other summary rows. etc
                    // and it is generally not reliable.
                    if (!empty($segment) && preg_match('/^' . implode('|', $remove) . '/', $segment)) {
                        $values[] = urldecode(urldecode(str_replace($remove, '', $segment)));
                    }
                }

                $values = array_slice($values, 0, $maxSuggestionsToReturn);
                $values     = array_map(['Piwik\Common', 'unsanitizeInputValue'], $values);
                return $values;
            }
        }

        if (isset($segment['suggestedValuesCallback'])) {
            $suggestedValuesCallbackRequiresTable = $this->doesSuggestedValuesCallbackNeedData(
                $segment['suggestedValuesCallback']
            );

            if (!$suggestedValuesCallbackRequiresTable) {
                return call_user_func($segment['suggestedValuesCallback'], $idSite, $maxSuggestionsToReturn);
            }
        }

        // if period=range is disabled, do not proceed
        if (!Period\Factory::isPeriodEnabledForAPI('range')) {
            return [];
        }

        if (!empty($segment['unionOfSegments'])) {
            $values = [];
            foreach ($segment['unionOfSegments'] as $unionSegmentName) {
                $unionSegment = $this->findSegment($unionSegmentName, $idSite, $_showAllSegments = true);

                try {
                    $result = $this->getSuggestedValuesForSegmentName($idSite, $unionSegment, $maxSuggestionsToReturn);
                    if (!empty($result)) {
                        $values = array_merge($result, $values);
                    }
                } catch (\Exception $e) {
                    // we ignore if there was no data found for $unionSegmentName
                }
            }

            if (empty($values)) {
                throw new \Exception("There was no data to suggest for $segmentName");
            }
        } else {
            $values = $this->getSuggestedValuesForSegmentName($idSite, $segment, $maxSuggestionsToReturn);
        }

        if ($segment['needsMostFrequentValues']) {
            $values = $this->getMostFrequentValues($values);
        }
        $values = array_slice($values, 0, $maxSuggestionsToReturn);
        $values = array_map(['Piwik\Common', 'unsanitizeInputValue'], $values);

        return $values;
    }

    /**
     * Returns category/subcategory pairs as "CategoryId.SubcategoryId" for whom comparison features should
     * be disabled.
     *
     * @return string[]
     */
    public function getPagesComparisonsDisabledFor()
    {
        $pages = [];

        /**
         * If your plugin has pages where you'd like comparison features to be disabled, you can add them
         * via this event. Add the pages as "CategoryId.SubcategoryId".
         *
         * **Example**
         *
         * ```
         * public function getPagesComparisonsDisabledFor(&$pages)
         * {
         *     $pages[] = "General_Visitors.MyPlugin_MySubcategory";
         *     $pages[] = "MyPlugin.myControllerAction"; // if your plugin defines a whole page you want comparison disabled for
         * }
         * ```
         *
         * @param string[] &$pages
         */
        Piwik::postEvent('API.getPagesComparisonsDisabledFor', [&$pages]);

        return $pages;
    }

    /**
     * @param string $segmentName
     * @param int|string $idSite
     * @return mixed[]
     */
    private function findSegment($segmentName, $idSite, bool $_showAllSegments = false)
    {
        $segmentsMetadata = $this->getSegmentsMetadata($idSite, $_hideImplementationData = false, $_showAllSegments);

        foreach ($segmentsMetadata as $segmentMetadata) {
            if ($segmentMetadata['segment'] == $segmentName) {
                return $segmentMetadata;
            }
        }

        throw new \Exception("Requested segment $segmentName not found.");
    }

    /**
     * @param int|string $idSite
     * @param array $segment
     * @param int $maxSuggestionsToReturn
     */
    private function getSuggestedValuesForSegmentName($idSite, $segment, $maxSuggestionsToReturn)
    {
        $startDate = Date::now()->subDay(self::$_autoSuggestLookBack)->toString();
        $requestLastVisits = [
            'method' => 'Live.getLastVisitsDetails',
            'idSite' => $idSite,
            'period' => 'range',
            'date' => $startDate . ',today',
            'format' => 'original',
            'serialize' => 0,
            'flat' => 1,
        ];

        $segmentName = $segment['segment'];

        // Select non empty fields only
        // Note: this optimization has only a very minor impact
        $requestLastVisits['segment'] = $segmentName . urlencode('!=');

        // By default Live fetches all actions for all visitors, but we'd rather do this only when required
        if ($this->doesSegmentNeedActionsData($segmentName)) {
            $requestLastVisits['filter_limit'] = 400;
        } else {
            $requestLastVisits['doNotFetchActions'] = 1;
            $requestLastVisits['filter_limit'] = 800;
        }

        $request = new Request($requestLastVisits);
        $table = $request->process();

        if (empty($table)) {
            throw new \Exception("There was no data to suggest for $segmentName");
        }

        if (
            isset($segment['suggestedValuesCallback']) &&
            $this->doesSuggestedValuesCallbackNeedData($segment['suggestedValuesCallback'])
        ) {
            $values = call_user_func($segment['suggestedValuesCallback'], $idSite, $maxSuggestionsToReturn, $table);
        } else {
            $values = $this->getSegmentValuesFromVisitorLog($segmentName, $table);
        }

        return $values;
    }

    /**
     * Returns glossary entries for all reports.
     *
     * @param int|string $idSite Site ID used for the access check.
     * @return array<int, array<string, mixed>>
     */
    public function getGlossaryReports($idSite)
    {
        $glossary = StaticContainer::get('Piwik\Plugins\API\Glossary');
        return $glossary->reportsGlossary($idSite);
    }

    /**
     * Returns glossary entries for all metrics.
     *
     * @param int|string $idSite Site ID used for the access check.
     * @return array<int, array<string, mixed>>
     */
    public function getGlossaryMetrics($idSite)
    {
        $glossary = StaticContainer::get('Piwik\Plugins\API\Glossary');
        return $glossary->metricsGlossary($idSite);
    }

    /**
     * Returns whether the segment requires action details from the visitor log.
     */
    protected function doesSegmentNeedActionsData(string $segmentName): bool
    {
        // If you update this, also update flattenVisitorDetailsArray
        $segmentsNeedActionsInfo = array('visitConvertedGoalId', 'visitConvertedGoalName',
            'pageUrl', 'pageTitle', 'siteSearchKeyword', 'siteSearchCategory', 'siteSearchCount',
            'entryPageTitle', 'entryPageUrl', 'exitPageTitle', 'exitPageUrl',
            'outlinkUrl', 'downloadUrl', 'eventUrl', 'orderId', 'revenueOrder', 'revenueAbandonedCart', 'productViewName', 'productViewSku', 'productViewPrice',
            'productViewCategory1', 'productViewCategory2', 'productViewCategory3', 'productViewCategory4', 'productViewCategory5',
        );
        $isCustomVariablePage = stripos($segmentName, 'customVariablePage') !== false;
        $isEventSegment = stripos($segmentName, 'event') !== false;
        $isContentSegment = stripos($segmentName, 'content') !== false;
        $doesSegmentNeedActionsInfo = in_array($segmentName, $segmentsNeedActionsInfo) || $isCustomVariablePage || $isEventSegment || $isContentSegment;
        return $doesSegmentNeedActionsInfo;
    }

    /**
     * Sorts values by frequency, preserving deterministic ordering for ties.
     *
     * @param array<int, mixed> $values Raw values to rank.
     * @return array<int, int|string>
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

        // Sort this list by converting and sorting the array with custom method, so the result doesn't differ between PHP versions
        $sortArray = [];

        foreach ($values as $val => $count) {
            $sortArray[] = [
                'value' => $val,
                'count' => $count,
            ];
        }

        usort($sortArray, function ($a, $b) {
            if ($a['count'] == $b['count']) {
                return strcmp($a['value'], $b['value']);
            }

            return $a['count'] > $b['count'] ? -1 : 1;
        });

        return array_column($sortArray, 'value');
    }

    /**
     * @param callable|string $suggestedValuesCallback
     */
    private function doesSuggestedValuesCallbackNeedData($suggestedValuesCallback): bool
    {
        if (
            is_string($suggestedValuesCallback)
            && strpos($suggestedValuesCallback, '::') !== false
        ) {
            $suggestedValuesCallback = explode('::', $suggestedValuesCallback);
        }

        if (is_array($suggestedValuesCallback)) {
            $methodMetadata = new \ReflectionMethod($suggestedValuesCallback[0], $suggestedValuesCallback[1]);
        } else {
            $methodMetadata = new \ReflectionFunction($suggestedValuesCallback);
        }

        return $methodMetadata->getNumberOfParameters() >= 3;
    }
}


// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses
class Plugin extends \Piwik\Plugin
{
    public function __construct()
    {
        // this class is named 'Plugin', manually set the 'API' plugin
        parent::__construct($pluginName = 'API');
    }

    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'Template.jsGlobalVariables' => 'getJsGlobalVariables',
            'Platform.initialized' => 'detectIsApiRequest',
        );
    }

    public function detectIsApiRequest(): void
    {
        Request::setIsRootRequestApiRequest(Request::getMethodIfApiRequest($request = null));
    }

    /**
     * @param list<string> $stylesheets
     */
    public function getStylesheetFiles(&$stylesheets): void
    {
        $stylesheets[] = "plugins/API/stylesheets/listAllAPI.less";
        $stylesheets[] = "plugins/API/stylesheets/glossary.less";
    }

    /**
     * @param string $out
     */
    public function getJsGlobalVariables(&$out): void
    {
        $bulkRequestLimit = BulkRequestLimit::getCurrentLimit();
        $out .= "piwik.apiBulkRequestLimit = $bulkRequestLimit;\n";

        // Do not perform page comparison check for glossary widget
        // This is performed here and not in Comparison.store.ts, as the widget might be used like on glossary.matomo.org
        // where url parameters are hidden in the request and javascript can't access the current module and action
        if (Piwik::getModule() === 'API' && Piwik::getAction() === 'glossary' && \Piwik\Request::fromRequest()->getBoolParameter('widget', false)) {
            $out .= "piwik.isPagesComparisonApiDisabled = true;\n";
        }
    }

    /**
     * @param list<string> $translations
     */
    public function getClientSideTranslationKeys(&$translations): void
    {
        $translations[] = 'API_Glossary';
        $translations[] = 'API_LearnAboutCommonlyUsedTerms2';
    }
}
