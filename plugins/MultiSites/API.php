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
use Piwik\DataTable;
use Piwik\Period\Range;
use Piwik\Piwik;
use Piwik\Plugins\Goals\Archiver;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\Plugins\SitesManager\Model as ModelSitesManager;
use Piwik\Site;
use Piwik\TaskScheduler;

/**
 * The MultiSites API lets you request the key metrics (visits, page views, revenue) for all Websites in Piwik.
 * @method static \Piwik\Plugins\MultiSites\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    const METRIC_TRANSLATION_KEY = 'translation';
    const METRIC_EVOLUTION_COL_NAME_KEY = 'evolution_column_name';
    const METRIC_RECORD_NAME_KEY = 'record_name';
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
            self::METRIC_IS_ECOMMERCE_KEY       => false,
        ),
        self::NB_ACTIONS_METRIC  => array(
            self::METRIC_TRANSLATION_KEY        => 'General_ColumnNbActions',
            self::METRIC_EVOLUTION_COL_NAME_KEY => 'actions_evolution',
            self::METRIC_RECORD_NAME_KEY        => self::NB_ACTIONS_METRIC,
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
     * @return DataTable
     */
    public function getAll($period, $date, $segment = false, $_restrictSitesToLogin = false, $enhanced = false, $pattern = false)
    {
        Piwik::checkUserHasSomeViewAccess();

        $idSites = $this->getSitesIdFromPattern($pattern);

        if (empty($idSites)) {
            return new DataTable();
        }
        return $this->buildDataTable(
            $idSites,
            $period,
            $date,
            $segment,
            $_restrictSitesToLogin,
            $enhanced,
            $multipleWebsitesRequested = true
        );
    }

    /**
     * Fetches the list of sites which names match the string pattern
     *
     * @param $pattern
     * @return array|string
     */
    private function getSitesIdFromPattern($pattern)
    {
        $idSites = 'all';
        if (empty($pattern)) {
            return $idSites;
        }
        $idSites = array();
        $sites = Request::processRequest('SitesManager.getPatternMatchSites',
            array('pattern'   => $pattern,
                  // added because caller could overwrite these
                  'serialize' => 0,
                  'format'    => 'original'));
        if (!empty($sites)) {
            foreach ($sites as $site) {
                $idSites[] = $site['idsite'];
            }
        }
        return $idSites;
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
        return $this->buildDataTable(
            $idSite,
            $period,
            $date,
            $segment,
            $_restrictSitesToLogin,
            $enhanced,
            $multipleWebsitesRequested = false
        );
    }

    private function buildDataTable($idSitesOrIdSite, $period, $date, $segment, $_restrictSitesToLogin, $enhanced, $multipleWebsitesRequested)
    {
        $allWebsitesRequested = ($idSitesOrIdSite == 'all');
        if ($allWebsitesRequested) {
            // First clear cache
            Site::clearCache();
            // Then, warm the cache with only the data we should have access to
            if (Piwik::hasUserSuperUserAccess()
                // Hack: when this API function is called as a Scheduled Task, Super User status is enforced.
                // This means this function would return ALL websites in all cases.
                // Instead, we make sure that only the right set of data is returned
                && !TaskScheduler::isTaskBeingExecuted()
            ) {
                APISitesManager::getInstance()->getAllSites();
            } else {
                APISitesManager::getInstance()->getSitesWithAtLeastViewAccess($limit = false, $_restrictSitesToLogin);
            }
            // Both calls above have called Site::setSitesFromArray. We now get these sites:
            $sitesToProblablyAdd = Site::getSites();
        } else if (is_array($idSitesOrIdSite)) {
            $model = new ModelSitesManager();
            $sitesToProblablyAdd = $model->getSitesFromIds($idSitesOrIdSite);
        } else {
            $sitesToProblablyAdd = array(APISitesManager::getInstance()->getSiteFromId($idSitesOrIdSite));
        }

        // build the archive type used to query archive data
        $archive = Archive::build(
            $idSitesOrIdSite,
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
            $fieldsToGet[] = $metricSettings[self::METRIC_RECORD_NAME_KEY];
            $columnNameRewrites[$metricSettings[self::METRIC_RECORD_NAME_KEY]] = $metricName;

            if ($metricSettings[self::METRIC_IS_ECOMMERCE_KEY]) {
                $apiECommerceMetrics[$metricName] = $metricSettings;
            }
        }

        // get the data
        // $dataTable instanceOf Set
        $dataTable = $archive->getDataTableFromNumeric($fieldsToGet);

        $dataTable = $this->mergeDataTableMapAndPopulateLabel($idSitesOrIdSite, $multipleWebsitesRequested, $dataTable);

        if ($dataTable instanceof DataTable\Map) {
            foreach ($dataTable->getDataTables() as $table) {
                $this->addMissingWebsites($table, $fieldsToGet, $sitesToProblablyAdd);
            }
        } else {
            $this->addMissingWebsites($dataTable, $fieldsToGet, $sitesToProblablyAdd);
        }

        // calculate total visits/actions/revenue
        $this->setMetricsTotalsMetadata($dataTable, $apiMetrics);

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

            $pastArchive = Archive::build($idSitesOrIdSite, $period, $strLastDate, $segment, $_restrictSitesToLogin);

            $pastData = $pastArchive->getDataTableFromNumeric($fieldsToGet);

            $pastData = $this->mergeDataTableMapAndPopulateLabel($idSitesOrIdSite, $multipleWebsitesRequested, $pastData);

            // use past data to calculate evolution percentages
            $this->calculateEvolutionPercentages($dataTable, $pastData, $apiMetrics);
            Common::destroy($pastData);
        }

        // remove eCommerce related metrics on non eCommerce Piwik sites
        // note: this is not optimal in terms of performance: those metrics should not be retrieved in the first place
        if ($enhanced) {
            if ($dataTable instanceof DataTable\Map) {
                foreach ($dataTable->getDataTables() as $table) {
                    $this->removeEcommerceRelatedMetricsOnNonEcommercePiwikSites($table, $apiECommerceMetrics);
                }
            } else {
                $this->removeEcommerceRelatedMetricsOnNonEcommercePiwikSites($dataTable, $apiECommerceMetrics);
            }
        }

        // move the site id to a metadata column
        $dataTable->filter('ColumnCallbackAddMetadata', array('label', 'group', array('\Piwik\Site', 'getGroupFor'), array()));
        $dataTable->filter('ColumnCallbackAddMetadata', array('label', 'main_url', array('\Piwik\Site', 'getMainUrlFor'), array()));
        $dataTable->filter('ColumnCallbackAddMetadata', array('label', 'idsite'));

        // set the label of each row to the site name
        if ($multipleWebsitesRequested) {
            $dataTable->filter('ColumnCallbackReplace', array('label', '\Piwik\Site::getNameFor'));
        } else {
            $dataTable->filter('ColumnDelete', array('label'));
        }

        Site::clearCache();

        // replace record names with user friendly metric names
        $dataTable->filter('ReplaceColumnNames', array($columnNameRewrites));

        // Ensures data set sorted, for Metadata output
        $dataTable->filter('Sort', array(self::NB_VISITS_METRIC, 'desc', $naturalSort = false));

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
            foreach ($apiMetrics as $metricSettings) {
                $currentData->filter(
                    'CalculateEvolutionFilter',
                    array(
                         $pastData,
                         $metricSettings[self::METRIC_EVOLUTION_COL_NAME_KEY],
                         $metricSettings[self::METRIC_RECORD_NAME_KEY],
                         $quotientPrecision = 1)
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

        if (Common::isActionsPluginEnabled()) {
            $metrics[self::NB_PAGEVIEWS_LABEL] = array(
                self::METRIC_TRANSLATION_KEY        => 'General_ColumnPageviews',
                self::METRIC_EVOLUTION_COL_NAME_KEY => 'pageviews_evolution',
                self::METRIC_RECORD_NAME_KEY        => self::NB_PAGEVIEWS_METRIC,
                self::METRIC_IS_ECOMMERCE_KEY       => false,
            );
        }

        if (Common::isGoalPluginEnabled()) {
            // goal revenue metric
            $metrics[self::GOAL_REVENUE_METRIC] = array(
                self::METRIC_TRANSLATION_KEY        => 'General_ColumnRevenue',
                self::METRIC_EVOLUTION_COL_NAME_KEY => self::GOAL_REVENUE_METRIC . '_evolution',
                self::METRIC_RECORD_NAME_KEY        => Archiver::getRecordName(self::GOAL_REVENUE_METRIC),
                self::METRIC_IS_ECOMMERCE_KEY       => false,
            );

            if ($enhanced) {
                // number of goal conversions metric
                $metrics[self::GOAL_CONVERSION_METRIC] = array(
                    self::METRIC_TRANSLATION_KEY        => 'Goals_ColumnConversions',
                    self::METRIC_EVOLUTION_COL_NAME_KEY => self::GOAL_CONVERSION_METRIC . '_evolution',
                    self::METRIC_RECORD_NAME_KEY        => Archiver::getRecordName(self::GOAL_CONVERSION_METRIC),
                    self::METRIC_IS_ECOMMERCE_KEY       => false,
                );

                // number of orders
                $metrics[self::ECOMMERCE_ORDERS_METRIC] = array(
                    self::METRIC_TRANSLATION_KEY        => 'General_EcommerceOrders',
                    self::METRIC_EVOLUTION_COL_NAME_KEY => self::ECOMMERCE_ORDERS_METRIC . '_evolution',
                    self::METRIC_RECORD_NAME_KEY        => Archiver::getRecordName(self::GOAL_CONVERSION_METRIC, 0),
                    self::METRIC_IS_ECOMMERCE_KEY       => true,
                );

                // eCommerce revenue
                $metrics[self::ECOMMERCE_REVENUE_METRIC] = array(
                    self::METRIC_TRANSLATION_KEY        => 'General_ProductRevenue',
                    self::METRIC_EVOLUTION_COL_NAME_KEY => self::ECOMMERCE_REVENUE_METRIC . '_evolution',
                    self::METRIC_RECORD_NAME_KEY        => Archiver::getRecordName(self::GOAL_REVENUE_METRIC, 0),
                    self::METRIC_IS_ECOMMERCE_KEY       => true,
                );
            }
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
            foreach ($apiMetrics as $label => $metricInfo) {
                $totalMetadataName = self::getTotalMetadataName($label);
                $totals[$totalMetadataName] = 0;
            }

            foreach ($dataTable->getRows() as $row) {
                foreach ($apiMetrics as $label => $metricInfo) {
                    $totalMetadataName = self::getTotalMetadataName($label);
                    $totals[$totalMetadataName] += $row->getColumn($metricInfo[self::METRIC_RECORD_NAME_KEY]);
                }
            }

            foreach ($totals as $name => $value) {
                $dataTable->setMetadata($name, $value);
            }
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

    /**
     * @param DataTable|DataTable\Map $dataTable
     * @param $fieldsToGet
     * @param $sitesToProblablyAdd
     */
    private function addMissingWebsites($dataTable, $fieldsToGet, $sitesToProblablyAdd)
    {
        $siteIdsInDataTable = array();
        foreach ($dataTable->getRows() as $row) {
            /** @var DataTable\Row $row */
            $siteIdsInDataTable[] = $row->getColumn('label');
        }

        foreach ($sitesToProblablyAdd as $site) {
            if (!in_array($site['idsite'], $siteIdsInDataTable)) {
                $siteRow = array_combine($fieldsToGet, array_pad(array(), count($fieldsToGet), 0));
                $siteRow['label'] = (int) $site['idsite'];
                $dataTable->addRowFromSimpleArray($siteRow);
            }
        }
    }

    private function removeEcommerceRelatedMetricsOnNonEcommercePiwikSites($dataTable, $apiECommerceMetrics)
    {
        // $dataTableRows instanceOf Row[]
        $dataTableRows = $dataTable->getRows();

        foreach ($dataTableRows as $dataTableRow) {
            $siteId = $dataTableRow->getColumn('label');
            if (!Site::isEcommerceEnabledFor($siteId)) {
                foreach ($apiECommerceMetrics as $metricSettings) {
                    $dataTableRow->deleteColumn($metricSettings[self::METRIC_RECORD_NAME_KEY]);
                    $dataTableRow->deleteColumn($metricSettings[self::METRIC_EVOLUTION_COL_NAME_KEY]);
                }
            }
        }
    }

    private function mergeDataTableMapAndPopulateLabel($idSitesOrIdSite, $multipleWebsitesRequested, $dataTable)
    {
        // get rid of the DataTable\Map that is created by the IndexedBySite archive type
        if ($dataTable instanceof DataTable\Map && $multipleWebsitesRequested) {

            return $dataTable->mergeChildren();

        } else {

            if (!$dataTable instanceof DataTable\Map && $dataTable->getRowsCount() > 0) {

                $firstSite = is_array($idSitesOrIdSite) ? reset($idSitesOrIdSite) : $idSitesOrIdSite;

                $firstDataTableRow = $dataTable->getFirstRow();

                $firstDataTableRow->setColumn('label', $firstSite);
            }
        }

        return $dataTable;
    }
}

