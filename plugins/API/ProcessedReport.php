<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\API;

use Exception;
use Piwik\API\Request;
use Piwik\Archive\DataTableFactory;
use Piwik\CacheId;
use Piwik\Cache as PiwikCache;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\DataTable\Simple;
use Piwik\Date;
use Piwik\Metrics;
use Piwik\Metrics\Formatter;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\Plugin\ReportsProvider;
use Piwik\Site;
use Piwik\Timer;
use Piwik\Url;

class ProcessedReport
{
    /**
     * @var ReportsProvider
     */
    private $reportsProvider;

    public function __construct(ReportsProvider $reportsProvider)
    {
        $this->reportsProvider = $reportsProvider;
    }

    /**
     * Loads reports metadata, then return the requested one,
     * matching optional API parameters.
     */
    public function getMetadata($idSite, $apiModule, $apiAction, $apiParameters = array(), $language = false,
                                $period = false, $date = false, $hideMetricsDoc = false, $showSubtableReports = false)
    {
        $reportsMetadata = $this->getReportMetadata($idSite, $period, $date, $hideMetricsDoc, $showSubtableReports);

        $entityNames = StaticContainer::get('entities.idNames');
        foreach ($entityNames as $entityName) {
            if ($entityName === 'idGoal' || $entityName === 'idDimension') {
                continue; // idGoal and idDimension is passed directly but for other entities we need to "workaround" and
                // check for eg idFoo from GET/POST because we cannot add parameters to API dynamically
            }
            $idEntity = Common::getRequestVar($entityName, 0, 'int');
            if ($idEntity > 0) {
                $apiParameters[$entityName] = $idEntity;
            }
        }

        foreach ($reportsMetadata as $report) {
            // See ArchiveProcessor/Aggregator.php - unique visitors are not processed for period != day
            // todo: should use SettingsPiwik::isUniqueVisitorsEnabled instead
            if (($period && $period != 'day') && !($apiModule == 'VisitsSummary' && $apiAction == 'get')) {
                unset($report['metrics']['nb_uniq_visitors']);
                unset($report['metrics']['nb_users']);
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
     * Verfies whether the given report exists for the given site.
     *
     * @param int $idSite
     * @param string $apiMethodUniqueId  For example 'MultiSites_getAll'
     *
     * @return bool
     */
    public function isValidReportForSite($idSite, $apiMethodUniqueId)
    {
        $report = $this->getReportMetadataByUniqueId($idSite, $apiMethodUniqueId);

        return !empty($report);
    }

    /**
     * Verfies whether the given metric belongs to the given report.
     *
     * @param int $idSite
     * @param string $metric     For example 'nb_visits'
     * @param string $apiMethodUniqueId  For example 'MultiSites_getAll'
     *
     * @return bool
     */
    public function isValidMetricForReport($metric, $idSite, $apiMethodUniqueId)
    {
        $translation = $this->translateMetric($metric, $idSite, $apiMethodUniqueId);

        return !empty($translation);
    }

    public function getReportMetadataByUniqueId($idSite, $apiMethodUniqueId)
    {
        $metadata = $this->getReportMetadata($idSite);

        foreach ($metadata as $report) {
            if ($report['uniqueId'] == $apiMethodUniqueId) {
                return $report;
            }
        }
    }

    /**
     * Translates the given metric in case the report exists and in case the metric actually belongs to the report.
     *
     * @param string $metric     For example 'nb_visits'
     * @param int    $idSite
     * @param string $apiMethodUniqueId  For example 'MultiSites_getAll'
     *
     * @return null|string
     */
    public function translateMetric($metric, $idSite, $apiMethodUniqueId)
    {
        $report = $this->getReportMetadataByUniqueId($idSite, $apiMethodUniqueId);

        if (empty($report)) {
            return;
        }

        $properties = array('metrics', 'processedMetrics', 'processedMetricsGoal');

        foreach ($properties as $prop) {
            if (!empty($report[$prop]) && is_array($report[$prop]) && array_key_exists($metric, $report[$prop])) {
                return $report[$prop][$metric];
            }
        }
    }

    /**
     * Triggers a hook to ask plugins for available Reports.
     * Returns metadata information about each report (category, name, dimension, metrics, etc.)
     *
     * @param int $idSite
     * @param bool|string $period
     * @param bool|Date $date
     * @param bool $hideMetricsDoc
     * @param bool $showSubtableReports
     * @return array
     */
    public function getReportMetadata($idSite, $period = false, $date = false, $hideMetricsDoc = false, $showSubtableReports = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        // as they cache key contains a lot of information there would be an even better cache result by caching parts of
        // this huge method separately but that makes it also more complicated. leaving it like this for now.
        $key   = $this->buildReportMetadataCacheKey($idSite, $period, $date, $hideMetricsDoc, $showSubtableReports);
        $key   = CacheId::pluginAware($key);
        $cache = PiwikCache::getTransientCache();

        if ($cache->contains($key)) {
            return $cache->fetch($key);
        }

        $parameters = array('idSite' => $idSite, 'period' => $period, 'date' => $date);

        $availableReports = array();

        foreach ($this->reportsProvider->getAllReports() as $report) {
            $report->configureReportMetadata($availableReports, $parameters);
        }

        foreach ($availableReports as &$availableReport) {
            if ($hideMetricsDoc) {
                unset($availableReport['metricsDocumentation']);
            }
        }

        /**
         * Triggered after all available reports are collected.
         *
         * This event can be used to modify the report metadata of reports in other plugins. You
         * could, for example, add custom metrics to every report or remove reports from the list
         * of available reports.
         *
         * @param array &$availableReports List of all report metadata. Read the {@hook API.getReportMetadata}
         *                                 docs to see what this array contains.
         * @param array $parameters Contains the values of the sites and period we are
         *                          getting reports for. Some report depend on this data.
         *                          For example, Goals reports depend on the site IDs being
         *                          request. Contains the following information:
         *
         *                          - **idSite**: The site ID we are getting reports for.
         *                          - **period**: The period type, eg, `'day'`, `'week'`, `'month'`,
         *                                        `'year'`, `'range'`.
         *                          - **date**: A string date within the period or a date range, eg,
         *                                      `'2013-01-01'` or `'2012-01-01,2013-01-01'`.
         */
        Piwik::postEvent('API.getReportMetadata.end', array(&$availableReports, $parameters));

        // Sort results to ensure consistent order
        usort($availableReports, array($this, 'sortReports'));

        $knownMetrics = array_merge(Metrics::getDefaultMetrics(), Metrics::getDefaultProcessedMetrics());
        $columnsToKeep   = $this->getColumnsToKeep();
        $columnsToRemove = $this->getColumnsToRemove();

        foreach ($availableReports as &$availableReport) {
            $availableReport['category']    = Piwik::translate($availableReport['category']);
            $availableReport['subcategory'] = Piwik::translate($availableReport['subcategory']);

            // Ensure all metrics have a translation
            $metrics = $availableReport['metrics'];
            $cleanedMetrics = array();
            // TODO we can remove this once we remove the getReportMetadata event, leaving it here for backwards compatibility
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
            $availableReport['metrics'] = $this->hideShowMetricsWithParams($availableReport['metrics'], $columnsToRemove, $columnsToKeep);
            if (isset($availableReport['processedMetrics'])) {
                $availableReport['processedMetrics'] = $this->hideShowMetricsWithParams($availableReport['processedMetrics'], $columnsToRemove, $columnsToKeep);
            }
            if (isset($availableReport['metricsDocumentation'])) {
                $availableReport['metricsDocumentation'] =
                    $this->hideShowMetricsWithParams($availableReport['metricsDocumentation'], $columnsToRemove, $columnsToKeep);
            }
            if (isset($availableReport['metricTypes'])) {
                $availableReport['metricTypes'] = $this->hideShowMetricsWithParams($availableReport['metricTypes'], $columnsToRemove, $columnsToKeep);
            }

            // Remove array elements that are false (to clean up API output)
            foreach ($availableReport as $attributeName => $attributeValue) {
                if (empty($attributeValue)) {
                    unset($availableReport[$attributeName]);
                }
            }
            // when there are per goal metrics, don't display conversion_rate since it can differ from per goal sum
            // (but only if filter_update_columns_when_show_all_goals is not in the request, if it is then we assume
            // the caller wants this information)
            // TODO we should remove this once we remove the getReportMetadata event, leaving it here for backwards compatibility
            $requestingGoalMetrics = Common::getRequestVar('filter_update_columns_when_show_all_goals', false);
            if (isset($availableReport['metricsGoal'])
                && !$requestingGoalMetrics
            ) {
                unset($availableReport['processedMetrics']['conversion_rate']);
                unset($availableReport['metricsGoal']['conversion_rate']);
            }

            // Processing a uniqueId for each report,
            // can be used by UIs as a key to match a given report
            $uniqueId = $availableReport['module'] . '_' . $availableReport['action'];
            if (!empty($availableReport['parameters'])) {
                foreach ($availableReport['parameters'] as $key => $value) {
                    $value = urlencode($value);
                    $value = str_replace('%', '', $value);
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

        $actualReports = array_values($availableReports);
        $cache->save($key, $actualReports);

        return $actualReports; // make sure array has contiguous key values
    }

    /**
     * API metadata are sorted by category/name,
     * with a little tweak to replicate the standard Piwik category ordering
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    public function sortReports($a, $b)
    {
        return $this->reportsProvider->compareCategories($a['category'], $a['subcategory'], $a['order'], $b['category'], $b['subcategory'], $b['order']);
    }

    public function getProcessedReport($idSite, $period, $date, $apiModule, $apiAction, $segment = false,
                                       $apiParameters = false, $idGoal = false, $language = false,
                                       $showTimer = true, $hideMetricsDoc = false, $idSubtable = false, $showRawMetrics = false,
                                       $formatMetrics = null, $idDimension = false)
    {
        $timer = new Timer();
        if (empty($apiParameters)) {
            $apiParameters = array();
        }

        if (!empty($idGoal)
            && empty($apiParameters['idGoal'])
        ) {
            $apiParameters['idGoal'] = $idGoal;
        }

        if (!empty($idDimension)
            && empty($apiParameters['idDimension'])
        ) {
            $apiParameters['idDimension'] = (int) $idDimension;
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

        if (!empty($segment)) {
            $parameters['segment'] = $segment;
        }

        if (!empty($reportMetadata['processedMetrics'])
            && !empty($reportMetadata['metrics']['nb_visits'])
            && @$reportMetadata['category'] != Piwik::translate('Goals_Ecommerce')
            && $apiModule !== 'MultiSites'
        ) {
            $deleteRowsWithNoVisits = empty($reportMetadata['constantRowsCount']) ? '1' : '0';
            $parameters['filter_add_columns_when_show_all_columns'] = $deleteRowsWithNoVisits;
        }

        $url = Url::getQueryStringFromParameters($parameters);
        $request = new Request($url);
        try {
            /** @var DataTable */
            $dataTable = $request->process();
        } catch (Exception $e) {
            throw new Exception("API returned an error: " . $e->getMessage() . " at " . basename($e->getFile()) . ":" . $e->getLine() . "\n");
        }

        list($newReport, $columns, $rowsMetadata, $totals) = $this->handleTableReport($idSite, $dataTable, $reportMetadata, $showRawMetrics, $formatMetrics);

        if (function_exists('mb_substr')) {
            foreach ($columns as &$name) {
                if (substr($name, 0, 1) === mb_substr($name, 0, 1)) {
                    $name = ucfirst($name);
                }
            }
        }

        $website = new Site($idSite);

        $period = Period\Factory::build($period, $date);
        $period = $period->getLocalizedLongString();

        $return = array(
            'website'        => $website->getName(),
            'prettyDate'     => $period,
            'metadata'       => $reportMetadata,
            'columns'        => $columns,
            'reportData'     => $newReport,
            'reportMetadata' => $rowsMetadata,
            'reportTotal'    => $totals
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
     * - extract row metadata to a separate Simple|Set : $rowsMetadata
     * - translate metric names to a separate array : $columns
     *
     * @param int $idSite enables monetary value formatting based on site currency
     * @param \Piwik\DataTable\Map|\Piwik\DataTable\Simple $dataTable
     * @param array $reportMetadata
     * @param bool $showRawMetrics
     * @param bool|null $formatMetrics
     * @return array Simple|Set $newReport with human readable format & array $columns list of translated column names & Simple|Set $rowsMetadata
     */
    private function handleTableReport($idSite, $dataTable, &$reportMetadata, $showRawMetrics = false, $formatMetrics = null)
    {
        $hasDimension = isset($reportMetadata['dimension']);
        $columns = @$reportMetadata['metrics'] ?: array();

        if ($hasDimension) {

            $columns = array_merge(
                array('label' => $reportMetadata['dimension']),
                $columns
            );
        }

        if (isset($reportMetadata['processedMetrics']) && is_array($reportMetadata['processedMetrics'])) {
            $processedMetricsAdded = Metrics::getDefaultProcessedMetrics();
            foreach ($reportMetadata['processedMetrics'] as $processedMetricId => $processedMetricTranslation) {
                // this processed metric can be displayed for this report

                if ($processedMetricTranslation && $processedMetricId !== $processedMetricTranslation) {
                    $columns[$processedMetricId] = $processedMetricTranslation;
                } elseif (isset($processedMetricsAdded[$processedMetricId])) {
                    // for instance in case 'nb_visits' => 'nb_visits' we will translate it
                    $columns[$processedMetricId] = $processedMetricsAdded[$processedMetricId];
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

        $columns = $this->hideShowMetrics($columns);
        $totals  = array();

        // $dataTable is an instance of Set when multiple periods requested
        if ($dataTable instanceof DataTable\Map) {
            // Need a new Set to store the 'human readable' values
            $newReport = new DataTable\Map();
            $newReport->setKeyName("prettyDate");

            // Need a new Set to store report metadata
            $rowsMetadata = new DataTable\Map();
            $rowsMetadata->setKeyName("prettyDate");

            // Process each Simple entry
            foreach ($dataTable->getDataTables() as $simpleDataTable) {
                $this->removeEmptyColumns($columns, $reportMetadata, $simpleDataTable);

                list($enhancedSimpleDataTable, $rowMetadata) = $this->handleSimpleDataTable($idSite, $simpleDataTable, $columns, $hasDimension, $showRawMetrics, $formatMetrics);
                $enhancedSimpleDataTable->setAllTableMetadata($simpleDataTable->getAllTableMetadata());

                $period = $simpleDataTable->getMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX)->getLocalizedLongString();
                $newReport->addTable($enhancedSimpleDataTable, $period);
                $rowsMetadata->addTable($rowMetadata, $period);

                $totals = $this->aggregateReportTotalValues($simpleDataTable, $totals);
            }
        } else {
            $this->removeEmptyColumns($columns, $reportMetadata, $dataTable);
            list($newReport, $rowsMetadata) = $this->handleSimpleDataTable($idSite, $dataTable, $columns, $hasDimension, $showRawMetrics, $formatMetrics);

            $totals = $this->aggregateReportTotalValues($dataTable, $totals);
        }

        return array(
            $newReport,
            $columns,
            $rowsMetadata,
            $totals
        );
    }

    /**
     * Removes metrics from the list of columns and the report meta data if they are marked empty
     * in the data table meta data.
     */
    private function removeEmptyColumns(&$columns, &$reportMetadata, $dataTable)
    {
        $emptyColumns = $dataTable->getMetadata(DataTable::EMPTY_COLUMNS_METADATA_NAME);

        if (!is_array($emptyColumns)) {
            return;
        }

        $columnsToRemove = $this->getColumnsToRemove();
        $columnsToKeep   = $this->getColumnsToKeep();

        $columns = $this->hideShowMetricsWithParams($columns, $columnsToRemove, $columnsToKeep, $emptyColumns);

        if (isset($reportMetadata['metrics'])) {
            $reportMetadata['metrics'] = $this->hideShowMetricsWithParams($reportMetadata['metrics'], $columnsToRemove, $columnsToKeep, $emptyColumns);
        }

        if (isset($reportMetadata['metricsDocumentation'])) {
            $reportMetadata['metricsDocumentation'] = $this->hideShowMetricsWithParams($reportMetadata['metricsDocumentation'], $columnsToRemove, $columnsToKeep, $emptyColumns);
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
        $columnsToRemove = $this->getColumnsToRemove();

        // remove columns if showColumns query parameters exist
        $columnsToKeep = $this->getColumnsToKeep();

        return $this->hideShowMetricsWithParams($columns, $columnsToRemove, $columnsToKeep, $emptyColumns);
    }

    private function hideShowMetricsWithParams($columns, $columnsToRemove, $columnsToKeep, $emptyColumns = array())
    {
        if (!is_array($columns)) {
            return $columns;
        }

        if (null !== $columnsToRemove) {
            foreach ($columnsToRemove as $name) {
                // if a column to remove is in the column list, remove it
                if (isset($columns[$name])) {
                    unset($columns[$name]);
                }
            }
        }

        if (null !== $columnsToKeep) {
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
     * - extract row metadata to a separate Simple $rowsMetadata
     *
     * @param int $idSite enables monetary value formatting based on site currency
     * @param DataTable $simpleDataTable
     * @param array $metadataColumns
     * @param boolean $hasDimension
     * @param bool $returnRawMetrics If set to true, the original metrics will be returned
     * @param bool|null $formatMetrics
     * @return array DataTable $enhancedDataTable filtered metrics with human readable format & Simple $rowsMetadata
     */
    private function handleSimpleDataTable($idSite, $simpleDataTable, $metadataColumns, $hasDimension, $returnRawMetrics = false, $formatMetrics = null, $keepMetadata = false)
    {
        $comparisonColumns = $this->getComparisonColumns($metadataColumns);

        // new DataTable to store metadata
        $rowsMetadata = new DataTable();

        // new DataTable to store 'human readable' values
        if ($hasDimension) {
            $enhancedDataTable = new DataTable();
        } else {
            $enhancedDataTable = new Simple();
        }

        $formatter = new Formatter();

        $hasNonEmptyRowData = false;

        foreach ($simpleDataTable->getRows() as $row) {
            $rowMetrics = $row->getColumns();

            // add missing metrics
            foreach ($metadataColumns as $id => $name) {
                if (!isset($rowMetrics[$id])) {
                    $row->setColumn($id, 0);
                    $rowMetrics[$id] = 0;
                }
            }

            $c = [];
            if ($keepMetadata) {
                $c[Row::METADATA] = $row->getMetadata();
            }
            $enhancedRow = new Row($c);
            $enhancedDataTable->addRow($enhancedRow);

            foreach ($rowMetrics as $columnName => $columnValue) {
                // filter metrics according to metadata definition
                if (isset($metadataColumns[$columnName])
                    || preg_match('/^goal_[0-9]+_/', $columnName)
                ) {
                    // generate 'human readable' metric values

                    // if we handle MultiSites.getAll we do not always have the same idSite but different ones for
                    // each site, see https://github.com/piwik/piwik/issues/5006
                    $idSiteForRow = $idSite;
                    $idSiteMetadata = $row->getMetadata('idsite');
                    if ($idSiteMetadata && is_numeric($idSiteMetadata)) {
                        $idSiteForRow = (int) $idSiteMetadata;
                    }

                    // format metrics manually here to maintain API.getProcessedReport BC if format_metrics query parameter is
                    // not supplied. TODO: should be removed for 3.0. should only rely on format_metrics query parameter.
                    if ($formatMetrics === null
                        || $formatMetrics == 'bc'
                    ) {
                        $prettyValue = self::getPrettyValue($formatter, $idSiteForRow, $columnName, $columnValue, $htmlAllowed = false);
                    } else {
                        $prettyValue = $columnValue;
                    }
                    $enhancedRow->addColumn($columnName, $prettyValue);
                } // For example the Maps Widget requires the raw metrics to do advanced datavis
                else if ($returnRawMetrics) {
                    if (!isset($columnValue)) {
                        $columnValue = 0;
                    }
                    $enhancedRow->addColumn($columnName, $columnValue);
                }
            }

            /** @var DataTable $comparisons */
            $comparisons = $row->getComparisons();

            if (!empty($comparisons)
                && $comparisons->getRowsCount() > 0
            ) {
                list($newComparisons, $ignore) = $this->handleSimpleDataTable($idSite, $comparisons, $comparisonColumns, true, $returnRawMetrics, $formatMetrics, $keepMetadata = true);
                $enhancedRow->setComparisons($newComparisons);
            }

            // If report has a dimension, extract metadata into a distinct DataTable
            if ($hasDimension) {
                $rowMetadata = $row->getMetadata();
                $idSubDataTable = $row->getIdSubDataTable();

                unset($rowMetadata[Row::COMPARISONS_METADATA_NAME]);

                // always add a metadata row - even if empty, so the number of rows and metadata are equal and can be matched directly
                $metadataRow = new Row();
                $rowsMetadata->addRow($metadataRow);

                if (count($rowMetadata) > 0 || !is_null($idSubDataTable)) {
                    $hasNonEmptyRowData = true;

                    foreach ($rowMetadata as $metadataKey => $metadataValue) {
                        $metadataRow->addColumn($metadataKey, $metadataValue);
                    }

                    if (!is_null($idSubDataTable)) {
                        $metadataRow->addColumn('idsubdatatable', $idSubDataTable);
                    }
                }
            }
        }

        // reset $rowsMetadata to empty DataTable if no row had metadata
        if ($hasNonEmptyRowData === false) {
            $rowsMetadata = new DataTable();
        }

        return array(
            $enhancedDataTable,
            $rowsMetadata
        );
    }

    private function aggregateReportTotalValues($simpleDataTable, $totals)
    {
        $metadataTotals = $simpleDataTable->getMetadata('totals');

        if (empty($metadataTotals)) {

            return $totals;
        }

        $simpleTotals = $this->hideShowMetrics($metadataTotals);

        return $this->calculateTotals($simpleTotals, $totals);
    }

    private function calculateTotals($simpleTotals, $totals)
    {
        foreach ($simpleTotals as $metric => $value) {
            if (0 === strpos($metric, 'avg_') || '_rate' === substr($metric, -5) || '_evolution' === substr($metric, -10)) {
                continue; // skip average, rate and evolution metrics
            }

            if (!is_numeric($value) && !is_array($value)) {
                continue;
            }

            if (is_array($value)) {
                $currentValue = array_key_exists($metric, $totals) ? $totals[$metric] : [];
                $newValue = $this->calculateTotals($value, $currentValue);
                if (!empty($newValue)) {
                    $totals[$metric] = $newValue;
                }
            }

            if (!array_key_exists($metric, $totals)) {
                $totals[$metric] = $value;
            } else if(0 === strpos($metric, 'min_')) {
                $totals[$metric] = min($totals[$metric], $value);
            } else if(0 === strpos($metric, 'max_')) {
                $totals[$metric] = max($totals[$metric], $value);
            } else if($value) {
                $totals[$metric] += $value;
            }
        }

        return $totals;
    }

    private function getColumnsToRemove()
    {
        $columnsToRemove = Common::getRequestVar('hideColumns', '');

        if ($columnsToRemove != '') {
            return explode(',', $columnsToRemove);
        }

        return null;
    }

    private function getColumnsToKeep()
    {
        $columnsToKeep = Common::getRequestVar('showColumns', '');

        if ($columnsToKeep != '') {
            $columnsToKeep = explode(',', $columnsToKeep);
            $columnsToKeep[] = 'label';

            return $columnsToKeep;
        }

        return null;
    }

    private function buildReportMetadataCacheKey($idSite, $period, $date, $hideMetricsDoc, $showSubtableReports)
    {
        if (isset($_GET) && isset($_POST) && is_array($_GET) && is_array($_POST)) {
            $request = $_GET + $_POST;
        } elseif (isset($_GET) && is_array($_GET)) {
            $request = $_GET;
        } elseif (isset($_POST) && is_array($_POST)) {
            $request = $_POST;
        } else {
            $request = array();
        }

        $key = '';
        foreach ($request as $k => $v) {
            if (is_array($v)) {
                $key .= $k . $this->getImplodedArray($v) . ',';
            } else {
                $key .= $k . $v . ',';
            }
        }

        $key .= $idSite . 'x' . ($period === false ? 0 : $period) . 'x' . ($date === false ? 0 : $date);
        $key .= (int)$hideMetricsDoc . (int)$showSubtableReports . Piwik::getCurrentUserLogin();
        return 'reportMetadata' . md5($key);
    }

    /**
     * @param $v
     * @return string
     */
    private function getImplodedArray($v)
    {
        return implode(', ', array_map(function ($entry) {
            if (is_array($entry)) {
                return implode(":", $entry);
            }
            return $entry;
        }, $v));
    }

    /**
     * Prettifies a metric value based on the column name.
     *
     * @param int $idSite The ID of the site the metric is for (used if the column value is an amount of money).
     * @param string $columnName The metric name.
     * @param mixed $value The metric value.
     * @param bool $isHtml If true, replaces all spaces with `'&nbsp;'`.
     * @return string
     */
    public static function getPrettyValue(Formatter $formatter, $idSite, $columnName, $value)
    {
        if (!is_numeric($value)) {
            return $value;
        }

        if (strpos($columnName, '_change') !== false) { // comparison change columns are formatted by DataComparisonFilter
            return $value == '0' ? '+0%' : $value;
        }

        // Display time in human readable
		if (strpos($columnName, 'time_generation') !== false) {
			return $formatter->getPrettyTimeFromSeconds($value, true);
		} 
		if (strpos($columnName, 'time') !== false) {
            return $formatter->getPrettyTimeFromSeconds($value);
        }

        // Add revenue symbol to revenues
        $isMoneyMetric = strpos($columnName, 'revenue') !== false || strpos($columnName, 'price') !== false;
        if ($isMoneyMetric && strpos($columnName, 'evolution') === false) {
            return $formatter->getPrettyMoney($value, $idSite);
        }

        // Add % symbol to rates
        if (strpos($columnName, '_rate') !== false) {
            if (strpos($value, "%") === false) {
                return (100 * $value) . "%";
            }
        }

        return $value;
    }

    private function getComparisonColumns(array $metadataColumns)
    {
        $result = $metadataColumns;
        foreach ($metadataColumns as $columnName => $columnTranslation) {
            $result[$columnName . '_change'] = Piwik::translate('General_ChangeInX', lcfirst($columnName));
        }
        return $result;
    }
}
