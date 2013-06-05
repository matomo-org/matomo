<?php

class Piwik_Provider_Archiving {


    /**
     * @param $archiveProcessing
     */
    public function archiveDay($archiveProcessing)
    {
        $recordName = 'Provider_hostnameExt';
        $labelSQL = "log_visit.location_provider";
        $metricsByProvider = $archiveProcessing->getArrayInterestForLabel($labelSQL);
        $tableProvider = $archiveProcessing->getDataTableFromArray($metricsByProvider);
        $columnToSortByBeforeTruncation = Piwik_Archive::INDEX_NB_VISITS;
        $maximumRowsInDataTable = Piwik_Config::getInstance()->General['datatable_archiving_maximum_rows_standard'];
        $archiveProcessing->insertBlobRecord($recordName, $tableProvider->getSerialized($maximumRowsInDataTable, null, $columnToSortByBeforeTruncation));
    }

    /**
     * @param $archiveProcessing
     */
    public function archivePeriod($archiveProcessing)
    {
        $maximumRowsInDataTable = Piwik_Config::getInstance()->General['datatable_archiving_maximum_rows_standard'];
        $dataTableToSum = array('Provider_hostnameExt');
        $archiveProcessing->archiveDataTable($dataTableToSum, null, $maximumRowsInDataTable);
    }

}