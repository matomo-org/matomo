<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicePlugins;

use Piwik\Archive;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Piwik;
use Piwik\Plugins\DevicesDetection\Archiver AS DDArchiver;
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
        $browserTypes = $this->getDataTable(DDArchiver::BROWSER_ENGINE_RECORD_NAME, $idSite, $period, $date, $segment);
        $archive = Archive::build($idSite, $period, $date, $segment);
        $visitsSums = $archive->getDataTableFromNumeric('nb_visits');

        // check whether given tables are arrays
        if ($dataTable instanceof DataTable\Map) {
            $dataTableMap = $dataTable->getDataTables();
            $browserTypesArray = $browserTypes->getDataTables();
            $visitSumsArray = $visitsSums->getDataTables();
        } else {
            $dataTableMap = array($dataTable);
            $browserTypesArray = array($browserTypes);
            $visitSumsArray = array($visitsSums);
        }

        // walk through the results and calculate the percentage
        foreach ($dataTableMap as $key => $table) {
            // Calculate percentage, but ignore IE users because plugin detection doesn't work on IE
            $ieVisits = 0;

            $ieStats = $browserTypesArray[$key]->getRowFromLabel('Trident');
            if ($ieStats !== false) {
                $ieVisits = $ieStats->getColumn(Metrics::INDEX_NB_VISITS);
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
            $extraProcessedMetrics[] = new VisitsPercent($visitsSum);
            $table->setMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME, $extraProcessedMetrics);
        }

        $dataTable->queueFilter('ColumnCallbackAddMetadata', array('label', 'logo', __NAMESPACE__ . '\getPluginsLogo'));
        $dataTable->queueFilter('ColumnCallbackReplace', array('label', 'ucfirst'));

        return $dataTable;
    }
}
