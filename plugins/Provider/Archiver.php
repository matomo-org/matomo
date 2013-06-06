<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_Provider
 */
class Piwik_Provider_Archiver extends Piwik_PluginsArchiver
{
    public function archiveDay()
    {
        $recordName = 'Provider_hostnameExt';
        $labelSQL = "log_visit.location_provider";
        $metricsByProvider = $this->getProcessor()->getMetricsForLabel($labelSQL);
        $tableProvider = $this->getProcessor()->getDataTableFromArray($metricsByProvider);
        $columnToSortByBeforeTruncation = Piwik_Archive::INDEX_NB_VISITS;
        $maximumRowsInDataTable = Piwik_Config::getInstance()->General['datatable_archiving_maximum_rows_standard'];
        $this->getProcessor()->insertBlobRecord($recordName, $tableProvider->getSerialized($maximumRowsInDataTable, null, $columnToSortByBeforeTruncation));
    }

    public function archivePeriod()
    {
        $maximumRowsInDataTable = Piwik_Config::getInstance()->General['datatable_archiving_maximum_rows_standard'];
        $dataTableToSum = array('Provider_hostnameExt');
        $this->getProcessor()->archiveDataTable($dataTableToSum, null, $maximumRowsInDataTable);
    }
}