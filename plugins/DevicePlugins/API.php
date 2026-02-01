<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DevicePlugins;

use Piwik\Archive;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Piwik;
use Piwik\Plugins\DevicesDetection\Archiver as DDArchiver;
use Piwik\Plugins\CoreHome\Columns\Metrics\VisitsPercent;

/**
 * @see plugins/DevicePlugins/functions.php
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/DevicePlugins/functions.php';

/**
 * The DevicePlugins API exposes reports about device plugins detected in visitors' browsers.
 * It focuses on plugin usage counts and visit percentages, derived from plugin labels and
 * related browser version data used to compute percentages.
 *
 * @method static \Piwik\Plugins\DevicePlugins\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    protected function getDataTable($name, $idSite, $period, $date, $segment)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $archive = Archive::build($idSite, $period, $date, $segment);
        $dataTable = $archive->getDataTable($name);
        $dataTable->queueFilter('ReplaceColumnNames');
        $dataTable->queueFilter('ReplaceSummaryRowLabel');
        return $dataTable;
    }

    /**
     * Returns the browser plugin report with visit percentage metrics, excluding IE visitors
     * from the percentage denominator where plugin detection is unreliable.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     *
     * @param string $period           The period to process, processes data for the period containing the specified date.
     *                                 Allowed values: "day", "week", "month", "year", "range".
     *
     * @param string $date             The date or date range to process.
     *                                 'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                                 or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     *
     * @param string|false $segment    (Optional) Custom segment to filter the report.
     *                                 Example: "referrerName==twitter.com"
     *                                 Supports AND (;) and OR (,) operators.
     *                                 [See documentation:](https://developer.matomo.org/api-reference/reporting-api-segmentation)
     *
     *  @return \Piwik\DataTable|\Piwik\DataTable\Map Plugin report data table.
     */
    public function getPlugin($idSite, $period, $date, $segment = false)
    {
        // fetch all archive data required
        $dataTable = $this->getDataTable(Archiver::PLUGIN_RECORD_NAME, $idSite, $period, $date, $segment);
        $browserVersions = $this->getDataTable(DDArchiver::BROWSER_VERSION_RECORD_NAME, $idSite, $period, $date, $segment);
        $archive = Archive::build($idSite, $period, $date, $segment);
        $visitsSums = $archive->getDataTableFromNumeric('nb_visits');

        // check whether given tables are arrays
        if ($dataTable instanceof DataTable\Map) {
            $dataTableMap = $dataTable->getDataTables();
            $browserVersionsArray = $browserVersions->getDataTables();
            $visitSumsArray = $visitsSums->getDataTables();
        } else {
            $dataTableMap = array($dataTable);
            $browserVersionsArray = array($browserVersions);
            $visitSumsArray = array($visitsSums);
        }

        // walk through the results and calculate the percentage
        foreach ($dataTableMap as $key => $table) {
            // Calculate percentage, but ignore IE users because plugin detection doesn't work on IE
            $ieVisits = 0;

            $browserVersionsToExclude = array(
                'IE;10.0',
                'IE;9.0',
                'IE;8.0',
                'IE;7.0',
                'IE;6.0',
            );
            foreach ($browserVersionsToExclude as $browserVersionToExclude) {
                $ieStats = $browserVersionsArray[$key]->getRowFromLabel($browserVersionToExclude);
                if ($ieStats !== false) {
                    $ieVisits += $ieStats->getColumn(Metrics::INDEX_NB_VISITS);
                }
            }

            // get according visitsSum
            $visits = $visitSumsArray[$key];
            if ($visits->getRowsCount() == 0) {
                $visitsSumTotal = 0;
            } else {
                $visitsSumTotal = (float) $visits->getFirstRow()->getColumn('nb_visits');
            }

            $visitsSum = $visitsSumTotal - $ieVisits;

            $extraProcessedMetrics = $table->getMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME);
            $extraProcessedMetrics = is_array($extraProcessedMetrics) ? $extraProcessedMetrics : [];
            $extraProcessedMetrics[] = new VisitsPercent($visitsSum);
            $table->setMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME, $extraProcessedMetrics);
        }

        $dataTable->queueFilter('ColumnCallbackAddMetadata', array('label', 'logo', __NAMESPACE__ . '\getPluginsLogo'));
        $dataTable->queueFilter('ColumnCallbackReplace', array('label', 'ucfirst'));
        $dataTable->queueFilter('RangeCheck', array('nb_visits_percentage', 0, 1));

        return $dataTable;
    }
}
