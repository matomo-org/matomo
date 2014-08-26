<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Contents;

use Piwik\Archive;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Metrics;
use Piwik\Piwik;

/**
 * API for plugin Contents
 *
 * @method static \Piwik\Plugins\Contents\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    public function getContentNames($idSite, $period, $date, $segment = false, $idSubtable = false, $secondaryDimension = false)
    {
        return $this->getDataTable(__FUNCTION__, $idSite, $period, $date, $segment, false, $idSubtable, $secondaryDimension);
    }

    public function getContentPieces($idSite, $period, $date, $segment = false, $idSubtable = false, $secondaryDimension = false)
    {
        return $this->getDataTable(__FUNCTION__, $idSite, $period, $date, $segment, false, $idSubtable, $secondaryDimension);
    }

    private function getDataTable($name, $idSite, $period, $date, $segment, $expanded, $idSubtable, $secondaryDimension)
    {
        Piwik::checkUserHasViewAccess($idSite);
        Dimensions::checkSecondaryDimension($name, $secondaryDimension);
        $recordName = Dimensions::getRecordNameForAction($name, $secondaryDimension);
        $dataTable  = Archive::getDataTableFromArchive($recordName, $idSite, $period, $date, $segment, $expanded, $idSubtable);
        $this->filterDataTable($dataTable);
        return $dataTable;
    }

    /**
     * @param DataTable $dataTable
     */
    private function filterDataTable($dataTable)
    {
        $dataTable->filter('Sort', array(Metrics::INDEX_NB_VISITS));

        $dataTable->queueFilter('ReplaceColumnNames');
        $dataTable->queueFilter('ReplaceSummaryRowLabel');

        // Content interaction rate = interactions / impressions
        $dataTable->queueFilter('ColumnCallbackAddColumnPercentage', array('interaction_rate', 'nb_interactions', 'nb_impressions', $precision = 2));
    }
}
