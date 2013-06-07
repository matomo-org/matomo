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
    const TYPE_RECORD_NAME = 'DevicesDetection_types';
    const BRAND_RECORD_NAME = 'DevicesDetection_brands';
    const MODEL_RECORD_NAME = 'DevicesDetection_models';
    const OS_RECORD_NAME = 'DevicesDetection_os';
    const OS_VERSION_RECORD_NAME = 'DevicesDetection_osVersions';
    const BROWSER_RECORD_NAME = 'DevicesDetection_browsers';
    const BROWSER_VERSION_RECORD_NAME = 'DevicesDetection_browserVersions';

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
        $labelSQL = "log_visit.config_device_type";
        $this->aggregateByLabel( $labelSQL, self::TYPE_RECORD_NAME);
    }

    private function aggregateByLabel( $labelSQL, $recordName)
    {
        $metricsByLabel = $this->getProcessor()->getMetricsForLabel($labelSQL);
        $tableBrand = $this->getProcessor()->getDataTableFromArray($metricsByLabel);

        $this->getProcessor()->insertBlobRecord($recordName, $tableBrand->getSerialized($this->maximumRowsInDataTable, null, Piwik_Archive::INDEX_NB_VISITS));
    }

    private function archiveDayDeviceBrands()
    {
        $this->aggregateByLabel( "log_visit.config_device_brand", self::BRAND_RECORD_NAME);
    }

    private function archiveDayDeviceModels()
    {
        $this->aggregateByLabel( "log_visit.config_device_model", self::MODEL_RECORD_NAME);
    }

    private function archiveDayOs()
    {
        $this->aggregateByLabel( "log_visit.config_os", self::OS_RECORD_NAME);
    }

    private function archiveDayOsVersions()
    {
        $this->aggregateByLabel( "CONCAT(log_visit.config_os, ';', log_visit.config_os_version)", self::OS_VERSION_RECORD_NAME);
    }

    private function archiveDayBrowserFamilies()
    {
        $this->aggregateByLabel( "log_visit.config_browser_name", self::BROWSER_RECORD_NAME);
    }

    private function archiveDayBrowsersVersions()
    {
        $this->aggregateByLabel( "CONCAT(log_visit.config_browser_name, ';', log_visit.config_browser_version)", self::BROWSER_VERSION_RECORD_NAME);
    }

    public function archivePeriod()
    {
        $maximumRowsInSubDataTable = $this->maximumRowsInDataTable;
        $dataTablesToSum = array(
            self::TYPE_RECORD_NAME,
            self::BRAND_RECORD_NAME,
            self::MODEL_RECORD_NAME,
            self::OS_RECORD_NAME,
            self::OS_VERSION_RECORD_NAME,
            self::BROWSER_RECORD_NAME,
            self::BROWSER_VERSION_RECORD_NAME
        );
        foreach ($dataTablesToSum as $dt) {
            $this->getProcessor()->archiveDataTable(
                $dt, null, $this->maximumRowsInDataTable, $maximumRowsInSubDataTable, $columnToSort = "nb_visits");
        }
    }
}