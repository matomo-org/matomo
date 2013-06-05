<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_DevicesDetection
 */

class Piwik_DevicesDetection_Archiving
{
    /**
     * @param $archiveProcessing
     */
    public function archiveDay($archiveProcessing)
    {
        $this->maximumRowsInDataTable = Piwik_Config::getInstance()->General['datatable_archiving_maximum_rows_standard'];
        $this->columnToSortByBeforeTruncation = Piwik_Archive::INDEX_NB_VISITS;

        $this->archiveDayDeviceTypes($archiveProcessing);
        $this->archiveDayDeviceBrands($archiveProcessing);
        $this->archiveDayDeviceModels($archiveProcessing);
        $this->archiveDayOs($archiveProcessing);
        $this->archiveDayOsVersions($archiveProcessing);
        $this->archiveDayBrowserFamilies($archiveProcessing);
        $this->archiveDayBrowsersVersions($archiveProcessing);
    }

    private function archiveDayDeviceTypes($archiveProcessing)
    {
        $recordName = 'DevicesDetection_types';
        $labelSQL = "log_visit.config_device_type";
        $this->aggregateByLabel($archiveProcessing, $labelSQL, $recordName);
    }

    /**
     * @param $archiveProcessing
     * @param $labelSQL
     * @param $recordName
     */
    private function aggregateByLabel($archiveProcessing, $labelSQL, $recordName)
    {
        $metricsByLabel = $archiveProcessing->getArrayInterestForLabel($labelSQL);
        $tableBrand = $archiveProcessing->getDataTableFromArray($metricsByLabel);

        $archiveProcessing->insertBlobRecord($recordName, $tableBrand->getSerialized($this->maximumRowsInDataTable, null, $this->columnToSortByBeforeTruncation));
        destroy($tableBrand);
    }

    private function archiveDayDeviceBrands($archiveProcessing)
    {
        $recordName = 'DevicesDetection_brands';
        $labelSQL = "log_visit.config_device_brand";
        $this->aggregateByLabel($archiveProcessing, $labelSQL, $recordName);
    }

    private function archiveDayDeviceModels($archiveProcessing)
    {
        $recordName = 'DevicesDetection_models';
        $labelSQL = "log_visit.config_device_model";
        $this->aggregateByLabel($archiveProcessing, $labelSQL, $recordName);
    }

    private function archiveDayOs($archiveProcessing)
    {
        $recordName = 'DevicesDetection_os';
        $labelSQL = "log_visit.config_os";
        $this->aggregateByLabel($archiveProcessing, $labelSQL, $recordName);

    }

    private function archiveDayOsVersions($archiveProcessing)
    {
        $recordName = 'DevicesDetection_osVersions';
        $labelSQL = "CONCAT(log_visit.config_os, ';', log_visit.config_os_version)";
        $this->aggregateByLabel($archiveProcessing, $labelSQL, $recordName);

    }

    private function archiveDayBrowserFamilies($archiveProcessing)
    {
        $recordName = 'DevicesDetection_browsers';
        $labelSQL = "log_visit.config_browser_name";
        $this->aggregateByLabel($archiveProcessing, $labelSQL, $recordName);

    }

    private function archiveDayBrowsersVersions($archiveProcessing)
    {
        $recordName = 'DevicesDetection_browserVersions';
        $labelSQL = "CONCAT(log_visit.config_browser_name, ';', log_visit.config_browser_version)";

        $this->aggregateByLabel($archiveProcessing, $labelSQL, $recordName);
    }

    /**
     * @param $archiveProcessing
     */
    public function archivePeriod($archiveProcessing)
    {
        $this->maximumRowsInDataTableLevelZero = Piwik_Config::getInstance()->General['datatable_archiving_maximum_rows_standard'];
        $this->maximumRowsInSubDataTable = $this->maximumRowsInDataTableLevelZero;
        $dataTablesToSum = array(
            'DevicesDetection_types',
            'DevicesDetection_brands',
            'DevicesDetection_models',
            'DevicesDetection_os',
            'DevicesDetection_osVersions',
            'DevicesDetection_browsers',
            'DevicesDetection_browserVersions'
        );
        foreach ($dataTablesToSum as $dt) {
            $archiveProcessing->archiveDataTable(
                $dt, null, $this->maximumRowsInDataTableLevelZero, $this->maximumRowsInSubDataTable, $columnToSort = "nb_visits");
        }
    }

}