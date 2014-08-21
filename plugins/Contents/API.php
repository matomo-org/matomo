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

    protected $mappingApiToRecord = array(
        'getContents' => Archiver::CONTENTS_NAME_RECORD_NAME
    );

    /**
     * Another example method that returns a data table.
     * @param int    $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     * @return DataTable
     */
    public function getContents($idSite, $period, $date, $segment = false)
    {
        return $this->getDataTable(__FUNCTION__, $idSite, $period, $date, $segment);
    }

    protected function getDataTable($name, $idSite, $period, $date, $segment)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $recordName = $this->getRecordNameForAction($name);
        $dataTable = Archive::getDataTableFromArchive($recordName, $idSite, $period, $date, $segment, false);
        $this->filterDataTable($dataTable);
        return $dataTable;
    }

    protected function getRecordNameForAction($apiMethod, $secondaryDimension = false)
    {
        return $this->mappingApiToRecord[$apiMethod];
    }

    /**
     * @param DataTable $dataTable
     */
    protected function filterDataTable($dataTable)
    {
        $dataTable->filter('Sort', array(Metrics::INDEX_NB_VISITS));
        $dataTable->queueFilter('ReplaceColumnNames');
        $dataTable->queueFilter('ReplaceSummaryRowLabel');
        $dataTable->filter(function (DataTable $table) {
            $row = $table->getRowFromLabel(Archiver::CONTENT_TARGET_NOT_SET);
            if ($row) {
                $row->setColumn('label', Piwik::translate('General_NotDefined', Piwik::translate('Contents_ContentTarget')));
            }
        });

        // Content conversion rate = conversions / impressions
        $dataTable->queueFilter('ColumnCallbackAddColumnPercentage', array('conversion_rate', 'nb_conversions', 'nb_impressions', $precision = 2));
    }
}
