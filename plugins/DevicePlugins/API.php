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
 * The DevicePlugins API lets you access reports about device plugins such as browser plugins.
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

    public function getPlugin($idSite, $period, $date, $segment = false)
    {
        // fetch all archive data required
        $dataTable = $this->getDataTable(Archiver::PLUGIN_RECORD_NAME, $idSite, $period, $date, $segment);
        $browserVersions = $this->getDataTable(DDArchiver::BROWSER_VERSION_RECORD_NAME, $idSite, $period, $date, $segment);
        $archive = Archive::build($idSite, $period, $date, $segment);
        $visitsSums = $archive->getDataTableFromNumeric('nb_visits');

        if ($dataTable instanceof DataTable\Map) {
            $dataTable->multiFilter(
                [
                    $browserVersions instanceof DataTable\Map ? $browserVersions : null,
                    $visitsSums instanceof DataTable\Map ? $visitsSums : null,
                ],
                function (DataTable $pluginTable, ?DataTable $browserVersionsTable, ?DataTable $visitsTable): void {
                    $this->addVisitsPercentProcessedMetric($pluginTable, $browserVersionsTable, $visitsTable);
                }
            );
        } else {
            $this->addVisitsPercentProcessedMetric(
                $dataTable,
                $browserVersions instanceof DataTable ? $browserVersions : null,
                $visitsSums instanceof DataTable ? $visitsSums : null
            );
        }

        $dataTable->queueFilter('ColumnCallbackAddMetadata', ['label', 'logo', __NAMESPACE__ . '\getPluginsLogo']);
        $dataTable->queueFilter('ColumnCallbackReplace', ['label', 'ucfirst']);
        $dataTable->queueFilter('RangeCheck', ['nb_visits_percentage', 0, 1]);

        return $dataTable;
    }

    private function addVisitsPercentProcessedMetric(
        DataTable $table,
        ?DataTable $browserVersions,
        ?DataTable $visits
    ): void {
        // Calculate percentage, but ignore IE users because plugin detection doesn't work on IE
        $ieVisits = 0;

        $browserVersionsToExclude = [
            'IE;10.0',
            'IE;9.0',
            'IE;8.0',
            'IE;7.0',
            'IE;6.0',
        ];
        foreach ($browserVersionsToExclude as $browserVersionToExclude) {
            $ieStats = $browserVersions ? $browserVersions->getRowFromLabel($browserVersionToExclude) : false;
            if ($ieStats !== false) {
                $ieVisits += $ieStats->getColumn(Metrics::INDEX_NB_VISITS);
            }
        }

        if (!$visits || $visits->getRowsCount() == 0) {
            $visitsSumTotal = 0;
        } else {
            $visitsSumTotal = (float)$visits->getFirstRow()->getColumn('nb_visits');
        }

        $visitsSum = $visitsSumTotal - $ieVisits;

        $extraProcessedMetrics   = $table->getMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME);
        $extraProcessedMetrics   = is_array($extraProcessedMetrics) ? $extraProcessedMetrics : [];
        $extraProcessedMetrics[] = new VisitsPercent($visitsSum);
        $table->setMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME, $extraProcessedMetrics);
    }
}
