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

class Piwik_DevicesDetection_Archiver extends Piwik_PluginsArchiver
{
    public function __construct($processor)
    {
        parent::__construct($processor);
        $this->maximumRowsInDataTable = Piwik_Config::getInstance()->General['datatable_archiving_maximum_rows_standard'];
    }

    public function archiveDay()
    {
        $this->archiveDayDeviceTypes();
        $this->archiveDayDeviceBrands();
        $this->archiveDayDeviceModels();
        $this->archiveDayOs();
        $this->archiveDayOsVersions();
        $this->archiveDayBrowserFamilies();
        $this->archiveDayBrowsersVersions();
    }

    private function archiveDayDeviceTypes()
    {
        $recordName = 'DevicesDetection_types';
        $labelSQL = "log_visit.config_device_type";
        $this->aggregateByLabel( $labelSQL, $recordName);
    }

    private function aggregateByLabel( $labelSQL, $recordName)
    {
        $metricsByLabel = $this->getProcessor()->getMetricsForLabel($labelSQL);
        $tableBrand = $this->getProcessor()->getDataTableFromArray($metricsByLabel);

        $this->getProcessor()->insertBlobRecord($recordName, $tableBrand->getSerialized($this->maximumRowsInDataTable, null, Piwik_Archive::INDEX_NB_VISITS));
    }

    private function archiveDayDeviceBrands()
    {
        $recordName = 'DevicesDetection_brands';
        $labelSQL = "log_visit.config_device_brand";
        $this->aggregateByLabel( $labelSQL, $recordName);
    }

    private function archiveDayDeviceModels()
    {
        $recordName = 'DevicesDetection_models';
        $labelSQL = "log_visit.config_device_model";
        $this->aggregateByLabel( $labelSQL, $recordName);
    }

    private function archiveDayOs()
    {
        $recordName = 'DevicesDetection_os';
        $labelSQL = "log_visit.config_os";
        $this->aggregateByLabel( $labelSQL, $recordName);
    }

    private function archiveDayOsVersions()
    {
        $recordName = 'DevicesDetection_osVersions';
        $labelSQL = "CONCAT(log_visit.config_os, ';', log_visit.config_os_version)";
        $this->aggregateByLabel( $labelSQL, $recordName);
    }

    private function archiveDayBrowserFamilies()
    {
        $recordName = 'DevicesDetection_browsers';
        $labelSQL = "log_visit.config_browser_name";
        $this->aggregateByLabel( $labelSQL, $recordName);
    }

    private function archiveDayBrowsersVersions()
    {
        $recordName = 'DevicesDetection_browserVersions';
        $labelSQL = "CONCAT(log_visit.config_browser_name, ';', log_visit.config_browser_version)";
        $this->aggregateByLabel( $labelSQL, $recordName);
    }

    public function archivePeriod()
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
            $this->getProcessor()->archiveDataTable(
                $dt, null, $this->maximumRowsInDataTable, $maximumRowsInSubDataTable, $columnToSort = "nb_visits");
        }
    }
}