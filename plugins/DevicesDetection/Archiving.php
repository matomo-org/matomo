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
    public function archiveDay($archiveProcessing)
    {
        $this->__construct();
        $this->columnToSortByBeforeTruncation = Piwik_Archive::INDEX_NB_VISITS;

        $this->archiveDayDeviceTypes($archiveProcessing);
        $this->archiveDayDeviceBrands($archiveProcessing);
        $this->archiveDayDeviceModels($archiveProcessing);
        $this->archiveDayOs($archiveProcessing);
        $this->archiveDayOsVersions($archiveProcessing);
        $this->archiveDayBrowserFamilies($archiveProcessing);
        $this->archiveDayBrowsersVersions($archiveProcessing);
    }

    public function __construct()
    {
        return $this->maximumRowsInDataTable = Piwik_Config::getInstance()->General['datatable_archiving_maximum_rows_standard'];
    }

    private function archiveDayDeviceTypes($archiveProcessing)
    {
        $recordName = 'DevicesDetection_types';
        $labelSQL = "log_visit.config_device_type";
        $this->aggregateByLabel($archiveProcessing, $labelSQL, $recordName);
    }

    private function aggregateByLabel($archiveProcessing, $labelSQL, $recordName)
    {
        $metricsByLabel = $archiveProcessing->getMetricsForLabel($labelSQL);
        $tableBrand = $archiveProcessing->getDataTableFromArray($metricsByLabel);

        $archiveProcessing->insertBlobRecord($recordName, $tableBrand->getSerialized($this->maximumRowsInDataTable, null, $this->columnToSortByBeforeTruncation));
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

    public function archivePeriod($archiveProcessing)
    {
        $maximumRowsInSubDataTable = $this->maximumRowsInDataTable;
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
                $dt, null, $this->maximumRowsInDataTable, $maximumRowsInSubDataTable, $columnToSort = "nb_visits");
        }
    }
}