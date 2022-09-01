<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
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
