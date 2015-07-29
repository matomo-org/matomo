<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\MultiSites;

use Exception;
use Piwik\API\Request;
use Piwik\Archive;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Period\Range;
use Piwik\Piwik;
use Piwik\Plugins\Goals\Archiver;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\Plugins\SitesManager\Model as ModelSitesManager;
use Piwik\Scheduler\Scheduler;
use Piwik\SettingsPiwik;
use Piwik\Site;

/**
 * The MultiSites API lets you request the key metrics (visits, page views, revenue) for all Websites in Piwik.
 * @method static \Piwik\Plugins\MultiSites\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    const METRIC_TRANSLATION_KEY = 'translation';
    const METRIC_EVOLUTION_COL_NAME_KEY = 'evolution_column_name';
    const METRIC_RECORD_NAME_KEY = 'record_name';
    const METRIC_COL_NAME_KEY = 'metric_column_name';
    const METRIC_IS_ECOMMERCE_KEY = 'is_ecommerce';

    const NB_VISITS_METRIC = 'nb_visits';
    const NB_ACTIONS_METRIC = 'nb_actions';
    const NB_PAGEVIEWS_LABEL = 'nb_pageviews';
    const NB_PAGEVIEWS_METRIC = 'Actions_nb_pageviews';
    const GOAL_REVENUE_METRIC = 'revenue';
    const GOAL_CONVERSION_METRIC = 'nb_conversions';
    const ECOMMERCE_ORDERS_METRIC = 'orders';
    const ECOMMERCE_REVENUE_METRIC = 'ecommerce_revenue';

    private static $baseMetrics = array(
        self::NB_VISITS_METRIC   => array(
            self::METRIC_TRANSLATION_KEY        => 'General_ColumnNbVisits',
            self::METRIC_EVOLUTION_COL_NAME_KEY => 'visits_evolution',
            self::METRIC_RECORD_NAME_KEY        => self::NB_VISITS_METRIC,
            self::METRIC_COL_NAME_KEY           => self::NB_VISITS_METRIC,
            self::METRIC_IS_ECOMMERCE_KEY       => false,
        ),
        self::NB_ACTIONS_METRIC  => array(
            self::METRIC_TRANSLATION_KEY        => 'General_ColumnNbActions',
            self::METRIC_EVOLUTION_COL_NAME_KEY => 'actions_evolution',
            self::METRIC_RECORD_NAME_KEY        => self::NB_ACTIONS_METRIC,
            self::METRIC_COL_NAME_KEY           => self::NB_ACTIONS_METRIC,
            self::METRIC_IS_ECOMMERCE_KEY       => false,
        )
    );

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
     * DataTable\Map, but it will still be merged.
     *
     * @param string $period The period type to get data for.
     * @param string $date The date(s) to get data for.
     * @param bool|string $segment The segments to get data for.
     * @param bool|string $_restrictSitesToLogin Hack used to enforce we restrict the returned data to the specified username
     *                                        Only used when a scheduled task is running
     * @param bool|string $enhanced When true, return additional goal & ecommerce metrics
     * @param bool|string $pattern If specified, only the website which names (or site ID) match the pattern will be returned using SitesManager.getPatternMatchSites
     * @param array $showColumns If specified, only the requested columns will be fetched
     * @return DataTable
     */
    public function getAll($period, $date, $segment = false, $_restrictSitesToLogin = false, $enhanced = false, $pattern = false, $showColumns = array())
    {
        Piwik::checkUserHasSomeViewAccess();

        $sites = $this->getSitesIdFromPattern($pattern, $_restrictSitesToLogin);

        if (!empty($showColumns) && !is_array($showColumns)) {
            $showColumns = explode(',', $showColumns);
        }

        if (empty($sites)) {
            return new DataTable();
        }

        return $this->buildDataTable(
            $sites,
            $period,
            $date,
            $segment,
            $_restrictSitesToLogin,
            $enhanced,
            $multipleWebsitesRequested = true,
            $showColumns
        );
    }

    /**
     * Fetches the list of sites which names match the string pattern
     *
     * @param string $pattern
     * @param bool   $_restrictSitesToLogin
     * @return array|string
     */
    private function getSitesIdFromPattern($pattern, $_restrictSitesToLogin)
    {
        // First clear cache
        Site::clearCache();

        if (empty($pattern)) {

            /** @var Scheduler $scheduler */
            $scheduler = StaticContainer::getContainer()->get('Piwik\Scheduler\Scheduler');
            // Then, warm the cache with only the data we should have access to
            if (Piwik::hasUserSuperUserAccess()
                // Hack: when this API function is called as a Scheduled Task, Super User status is enforced.
                // This means this function would return ALL websites in all cases.
                // Instead, we make sure that only the right set of data is returned
                && !$scheduler->isRunningTask()
            ) {
                APISitesManager::getInstance()->getAllSites();
            } else {
                APISitesManager::getInstance()->getSitesWithAtLeastViewAccess($limit = false, $_restrictSitesToLogin);
            }

        } else {
            $sites = Request::processRequest('SitesManager.getPatternMatchSites',
                array('pattern'   => $pattern,
                      // added because caller could overwrite these
                      'limit'       => SettingsPiwik::getWebsitesCountToDisplay(),
                      'showColumns' => '',
                      'hideColumns' => '',
                      'serialize'   => 0,
                      'format'      => 'original'));

            if (!empty($sites)) {
                Site::setSitesFromArray($sites);
            }
        }

        // Both calls above have called Site::setSitesFromArray. We now get these sites:
        $sitesToProblablyAdd = Site::getSites();

        return $sitesToProblablyAdd;
    }

    /**
     * Same as getAll but for a unique Piwik site
     * @see Piwik\Plugins\MultiSites\API::getAll()
     *
     * @param int $idSite Id of the Piwik site
     * @param string $period The period type to get data for.
     * @param string $date The date(s) to get data for.
     * @param bool|string $segment The segments to get data for.
     * @param bool|string $_restrictSitesToLogin Hack used to enforce we restrict the returned data to the specified username
     *                                        Only used when a scheduled task is running
     * @param bool|string $enhanced When true, return additional goal & ecommerce metrics
     * @return DataTable
     */
    public function getOne($idSite, $period, $date, $segment = false, $_restrictSitesToLogin = false, $enhanced = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $sites = $this->getSiteFromId($idSite);

        return $this->buildDataTable(
            $sites,
            $period,
            $date,
            $segment,
            $_restrictSitesToLogin,
            $enhanced,
            $multipleWebsitesRequested = false,
            $showColumns = array()
        );
    }

    private function getSiteFromId($idSite)
    {
        $idSite = (int) $idSite;
        $sites = array(APISitesManager::getInstance()->getSiteFromId($idSite));

        return $sites;
    }

    private function buildDataTable($sitesToProblablyAdd, $period, $date, $segment, $_restrictSitesToLogin, $enhanced, $multipleWebsitesRequested, $showColumns)
    {
        $idSites = array();
        if (!empty($sitesToProblablyAdd)) {
            foreach ($sitesToProblablyAdd as $site) {
                $idSites[] = $site['idsite'];
            }
        }

        // build the archive type used to query archive data
        $archive = Archive::build(
            $idSites,
            $period,
            $date,
            $segment,
            $_restrictSitesToLogin
        );

        // determine what data will be displayed
        $fieldsToGet = array();
        $columnNameRewrites = array();
        $apiECommerceMetrics = array();
        $apiMetrics = API::getApiMetrics($enhanced);
        foreach ($apiMetrics as $metricName => $metricSettings) {
            if (!empty($showColumns) && !in_array($metricName, $showColumns)) {
                unset($apiMetrics[$metricName]);
                continue;
            }
            $fieldsToGet[] = $metricSettings[self::METRIC_RECORD_NAME_KEY];
            $columnNameRewrites[$metricSettings[self::METRIC_RECORD_NAME_KEY]] = $metricName;

            if ($metricSettings[self::METRIC_IS_ECOMMERCE_KEY]) {
                $apiECommerceMetrics[$metricName] = $metricSettings;
            }
        }

        $dataTable = $archive->getDataTableFromNumericAndMergeChildren($fieldsToGet);

        $this->populateLabel($dataTable);
        $totalMetrics = $this->preformatApiMetricsForTotalsCalculation($apiMetrics);
        $this->setMetricsTotalsMetadata($dataTable, $totalMetrics);

        // if the period isn't a range & a lastN/previousN date isn't used, we get the same
        // data for the last period to show the evolution of visits/actions/revenue
        list($strLastDate, $lastPeriod) = Range::getLastDate($date, $period);

        if ($strLastDate !== false) {

            if ($lastPeriod !== false) {
                // NOTE: no easy way to set last period date metadata when range of dates is requested.
                //       will be easier if DataTable\Map::metadata is removed, and metadata that is
                //       put there is put directly in DataTable::metadata.
                $dataTable->setMetadata(self::getLastPeriodMetadataName('date'), $lastPeriod);
            }

            $pastArchive = Archive::build($idSites, $period, $strLastDate, $segment, $_restrictSitesToLogin);
            $pastData = $pastArchive->getDataTableFromNumericAndMergeChildren($fieldsToGet);

            $this->populateLabel($pastData); // labels are needed to calculate evolution
            $this->calculateEvolutionPercentages($dataTable, $pastData, $apiMetrics);
            $this->setPastTotalVisitsMetadata($dataTable, $pastData);

            if ($dataTable instanceof DataTable) {
                // needed for MultiSites\Dashboard
                $dataTable->setMetadata('pastData', $pastData);
            }
        }

        // move the site id to a metadata column 
        $dataTable->queueFilter('MetadataCallbackAddMetadata', array('idsite', 'group', array('\Piwik\Site', 'getGroupFor'), array()));
        $dataTable->queueFilter('MetadataCallbackAddMetadata', array('idsite', 'main_url', array('\Piwik\Site', 'getMainUrlFor'), array()));

        // set the label of each row to the site name
        if ($multipleWebsitesRequested) {
            $dataTable->queueFilter('ColumnCallbackReplace', array('label', '\Piwik\Site::getNameFor'));
        } else {
            $dataTable->queueFilter('ColumnDelete', array('label'));
        }

        // replace record names with user friendly metric names
        $dataTable->queueFilter('ReplaceColumnNames', array($columnNameRewrites));

        // filter rows without visits
        // note: if only one website is queried and there are no visits, we can not remove the row otherwise
        // ResponseBuilder throws 'Call to a member function getColumns() on a non-object'
        if ($multipleWebsitesRequested
            // We don't delete the 0 visits row, if "Enhanced" mode is on.
            && !$enhanced
        ) {
            $dataTable->filter(
                'ColumnCallbackDeleteRow',
                array(
                     self::NB_VISITS_METRIC,
                     function ($value) {
                         return $value == 0;
                     }
                )
            );
        }

        if ($multipleWebsitesRequested && $dataTable->getRowsCount() === 1 && $dataTable instanceof DataTable\Simple) {
            $simpleTable = $dataTable;
            $dataTable   = $simpleTable->getEmptyClone();
            $dataTable->addRow($simpleTable->getFirstRow());
            unset($simpleTable);
        }

        return $dataTable;
    }

    /**
     * Performs a binary filter of two
     * DataTables in order to correctly calculate evolution metrics.
     *
     * @param DataTable|DataTable\Map $currentData
     * @param DataTable|DataTable\Map $pastData
     * @param array $apiMetrics The array of string fields to calculate evolution
     *                          metrics for.
     * @throws Exception
     */
    private function calculateEvolutionPercentages($currentData, $pastData, $apiMetrics)
    {
        if (get_class($currentData) != get_class($pastData)) { // sanity check for regressions
            throw new Exception("Expected \$pastData to be of type " . get_class($currentData) . " - got "
                . get_class($pastData) . ".");
        }

        if ($currentData instanceof DataTable\Map) {
            $pastArray = $pastData->getDataTables();
            foreach ($currentData->getDataTables() as $subTable) {
                $this->calculateEvolutionPercentages($subTable, current($pastArray), $apiMetrics);
                next($pastArray);
            }
        } else {
            $extraProcessedMetrics = $currentData->getMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME);
            foreach ($apiMetrics as $metricSettings) {
                $evolutionMetricClass = $this->isEcommerceEvolutionMetric($metricSettings)
                                      ? "Piwik\\Plugins\\MultiSites\\Columns\\Metrics\\EcommerceOnlyEvolutionMetric"
                                      : "Piwik\\Plugins\\CoreHome\\Columns\\Metrics\\EvolutionMetric";

                $extraProcessedMetrics[] = new $evolutionMetricClass(
                    $metricSettings[self::METRIC_RECORD_NAME_KEY],
                    $pastData,
                    $metricSettings[self::METRIC_EVOLUTION_COL_NAME_KEY],
                    $quotientPrecision = 1
                );
            }
            $currentData->setMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME, $extraProcessedMetrics);
        }
    }

    /**
     * @ignore
     */
    public static function getApiMetrics($enhanced)
    {
        $metrics = self::$baseMetrics;

        if (Common::isActionsPluginEnabled()) {
            $metrics[self::NB_PAGEVIEWS_LABEL] = array(
                self::METRIC_TRANSLATION_KEY        => 'General_ColumnPageviews',
                self::METRIC_EVOLUTION_COL_NAME_KEY => 'pageviews_evolution',
                self::METRIC_RECORD_NAME_KEY        => self::NB_PAGEVIEWS_METRIC,
                self::METRIC_COL_NAME_KEY           => self::NB_PAGEVIEWS_LABEL,
                self::METRIC_IS_ECOMMERCE_KEY       => false,
            );
        }

        if (Common::isGoalPluginEnabled()) {
            // goal revenue metric
            $metrics[self::GOAL_REVENUE_METRIC] = array(
                self::METRIC_TRANSLATION_KEY        => 'General_ColumnRevenue',
                self::METRIC_EVOLUTION_COL_NAME_KEY => self::GOAL_REVENUE_METRIC . '_evolution',
                self::METRIC_RECORD_NAME_KEY        => Archiver::getRecordName(self::GOAL_REVENUE_METRIC),
                self::METRIC_COL_NAME_KEY           => self::GOAL_REVENUE_METRIC,
                self::METRIC_IS_ECOMMERCE_KEY       => false,
            );

            if ($enhanced) {
                // number of goal conversions metric
                $metrics[self::GOAL_CONVERSION_METRIC] = array(
                    self::METRIC_TRANSLATION_KEY        => 'Goals_ColumnConversions',
                    self::METRIC_EVOLUTION_COL_NAME_KEY => self::GOAL_CONVERSION_METRIC . '_evolution',
                    self::METRIC_RECORD_NAME_KEY        => Archiver::getRecordName(self::GOAL_CONVERSION_METRIC),
                    self::METRIC_COL_NAME_KEY           => self::GOAL_CONVERSION_METRIC,
                    self::METRIC_IS_ECOMMERCE_KEY       => false,
                );

                // number of orders
                $metrics[self::ECOMMERCE_ORDERS_METRIC] = array(
                    self::METRIC_TRANSLATION_KEY        => 'General_EcommerceOrders',
                    self::METRIC_EVOLUTION_COL_NAME_KEY => self::ECOMMERCE_ORDERS_METRIC . '_evolution',
                    self::METRIC_RECORD_NAME_KEY        => Archiver::getRecordName(self::GOAL_CONVERSION_METRIC, 0),
                    self::METRIC_COL_NAME_KEY           => self::ECOMMERCE_ORDERS_METRIC,
                    self::METRIC_IS_ECOMMERCE_KEY       => true,
                );

                // eCommerce revenue
                $metrics[self::ECOMMERCE_REVENUE_METRIC] = array(
                    self::METRIC_TRANSLATION_KEY        => 'General_ProductRevenue',
                    self::METRIC_EVOLUTION_COL_NAME_KEY => self::ECOMMERCE_REVENUE_METRIC . '_evolution',
                    self::METRIC_RECORD_NAME_KEY        => Archiver::getRecordName(self::GOAL_REVENUE_METRIC, 0),
                    self::METRIC_COL_NAME_KEY           => self::ECOMMERCE_REVENUE_METRIC,
                    self::METRIC_IS_ECOMMERCE_KEY       => true,
                );
            }
        }

        return $metrics;
    }

    private function preformatApiMetricsForTotalsCalculation($apiMetrics)
    {
        $metrics = array();
        foreach ($apiMetrics as $label => $metricsInfo) {
            $totalMetadataName = self::getTotalMetadataName($label);
            $metrics[$totalMetadataName] = $metricsInfo[self::METRIC_RECORD_NAME_KEY];
        }

        return $metrics;
    }

    /**
     * Sets the total visits, actions & revenue for a DataTable returned by
     * $this->buildDataTable.
     *
     * @param DataTable $dataTable
     * @param array $apiMetrics Metrics info.
     * @return array Array of three values: total visits, total actions, total revenue
     */
    private function setMetricsTotalsMetadata($dataTable, $apiMetrics)
    {
        if ($dataTable instanceof DataTable\Map) {
            foreach ($dataTable->getDataTables() as $table) {
                $this->setMetricsTotalsMetadata($table, $apiMetrics);
            }
        } else {
            $totals = array();
            foreach ($apiMetrics as $label => $recordName) {
                $totals[$label] = 0;
            }

            foreach ($dataTable->getRows() as $row) {
                foreach ($apiMetrics as $totalMetadataName => $recordName) {
                    $totals[$totalMetadataName] += $row->getColumn($recordName);
                }
            }

            $dataTable->setMetadataValues($totals);
        }
    }

    /**
     * Sets the number of total visits in tha pastTable on the dataTable as metadata.
     *
     * @param DataTable $dataTable
     * @param DataTable $pastTable
     */
    private function setPastTotalVisitsMetadata($dataTable, $pastTable)
    {
        if ($pastTable instanceof DataTable) {
            $total  = 0;
            $metric = 'nb_visits';

            foreach ($pastTable->getRows() as $row) {
                $total += $row->getColumn($metric);
            }

            $dataTable->setMetadata(self::getTotalMetadataName($metric . '_lastdate'), $total);
        }
    }

    private static function getTotalMetadataName($name)
    {
        return 'total_' . $name;
    }

    private static function getLastPeriodMetadataName($name)
    {
        return 'last_period_' . $name;
    }

    private function populateLabel($dataTable)
    {
        $dataTable->filter(function (DataTable $table) {
            foreach ($table->getRowsWithoutSummaryRow() as $row) {
                $row->setColumn('label', $row->getMetadata('idsite'));
            }
        });
        // make sure label column is always first column
        $dataTable->queueFilter(function (DataTable $table) {
            foreach ($table->getRowsWithoutSummaryRow() as $row) {
                $row->setColumns(array_merge(array('label' => $row->getColumn('label')), $row->getColumns()));
            }
        });
    }

    private function isEcommerceEvolutionMetric($metricSettings)
    {
        return in_array($metricSettings[self::METRIC_EVOLUTION_COL_NAME_KEY], array(
            self::GOAL_REVENUE_METRIC . '_evolution',
            self::ECOMMERCE_ORDERS_METRIC . '_evolution',
            self::ECOMMERCE_REVENUE_METRIC . '_evolution'
        ));
    }
}
