<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_UserSettings
 */

require_once PIWIK_INCLUDE_PATH . '/plugins/UserSettings/functions.php';

class Piwik_UserSettings_Archiving
{
    const LANGUAGE_RECORD_NAME = 'UserSettings_language';
    const PLUGIN_RECORD_NAME = 'UserSettings_plugin';
    const SCREEN_TYPES_RECORD_NAME = 'UserSettings_wideScreen';
    const RESOLUTION_RECORD_NAME = 'UserSettings_resolution';
    const BROWSER_RECORD_NAME = 'UserSettings_browser';
    const BROWSER_TYPE_RECORD_NAME = 'UserSettings_browserType';
    const OS_RECORD_NAME = 'UserSettings_os';
    const CONFIGURATION_RECORD_NAME = 'UserSettings_configuration';

    public function __construct()
    {
        $this->maximumRowsInDataTable = Piwik_Config::getInstance()->General['datatable_archiving_maximum_rows_standard'];
        $this->columnToSortByBeforeTruncation = Piwik_Archive::INDEX_NB_VISITS;
    }

    public function archiveDay($archiveProcessing)
    {
        $this->aggregateByConfiguration($archiveProcessing);
        $this->aggregateByOs($archiveProcessing);
        $this->aggregateByBrowser($archiveProcessing);
        $this->aggregateByResolutionAndScreenType($archiveProcessing);
        $this->aggregateByPlugin($archiveProcessing);
        $this->aggregateByLanguage($archiveProcessing);
    }

    protected function aggregateByConfiguration($archiveProcessing)
    {
        $labelSQL = "CONCAT(log_visit.config_os, ';', log_visit.config_browser_name, ';', log_visit.config_resolution)";
        $metrics = $archiveProcessing->getMetricsForLabel($labelSQL);
        $table = $archiveProcessing->getDataTableFromArray($metrics);
        $archiveProcessing->insertBlobRecord(self::CONFIGURATION_RECORD_NAME, $table->getSerialized($this->maximumRowsInDataTable, null, $this->columnToSortByBeforeTruncation));
    }

    protected function aggregateByOs($archiveProcessing)
    {
        $metrics = $archiveProcessing->getMetricsForLabel("log_visit.config_os");
        $table = $archiveProcessing->getDataTableFromArray($metrics);
        $archiveProcessing->insertBlobRecord(self::OS_RECORD_NAME, $table->getSerialized($this->maximumRowsInDataTable, null, $this->columnToSortByBeforeTruncation));
    }

    protected function aggregateByBrowser($archiveProcessing)
    {
        $tableBrowser = $this->aggregateByBrowserVersion($archiveProcessing);
        $this->aggregateByBrowserType($archiveProcessing, $tableBrowser);
    }

    protected function aggregateByBrowserVersion($archiveProcessing)
    {
        $labelSQL = "CONCAT(log_visit.config_browser_name, ';', log_visit.config_browser_version)";
        $metrics = $archiveProcessing->getMetricsForLabel($labelSQL);
        $tableBrowser = $archiveProcessing->getDataTableFromArray($metrics);

        $archiveProcessing->insertBlobRecord(self::BROWSER_RECORD_NAME, $tableBrowser->getSerialized($this->maximumRowsInDataTable, null, $this->columnToSortByBeforeTruncation));
        return $tableBrowser;
    }

    protected function aggregateByBrowserType($archiveProcessing, $tableBrowser)
    {
        $tableBrowser->filter('GroupBy', array('label', 'Piwik_getBrowserFamily'));
        $archiveProcessing->insertBlobRecord(self::BROWSER_TYPE_RECORD_NAME, $tableBrowser->getSerialized());
    }

    protected function aggregateByResolutionAndScreenType($archiveProcessing)
    {
        $resolutions = $this->aggregateByResolution($archiveProcessing);
        $this->aggregateByScreenType($archiveProcessing, $resolutions);
    }

    protected function aggregateByResolution($archiveProcessing)
    {
        $metrics = $archiveProcessing->getMetricsForLabel("log_visit.config_resolution");
        $table = $archiveProcessing->getDataTableFromArray($metrics);
        $table->filter('ColumnCallbackDeleteRow', array('label', 'Piwik_UserSettings_keepStrlenGreater'));
        $archiveProcessing->insertBlobRecord(self::RESOLUTION_RECORD_NAME, $table->getSerialized($this->maximumRowsInDataTable, null, $this->columnToSortByBeforeTruncation));
        return $table;
    }

    protected function aggregateByScreenType($archiveProcessing, Piwik_DataTable $resolutions)
    {
        $resolutions->filter('GroupBy', array('label', 'Piwik_getScreenTypeFromResolution'));
        $archiveProcessing->insertBlobRecord(self::SCREEN_TYPES_RECORD_NAME, $resolutions->getSerialized());
    }

    protected function aggregateByPlugin($archiveProcessing)
    {
        $toSelect = "sum(case log_visit.config_pdf when 1 then 1 else 0 end) as pdf,
				sum(case log_visit.config_flash when 1 then 1 else 0 end) as flash,
				sum(case log_visit.config_java when 1 then 1 else 0 end) as java,
				sum(case log_visit.config_director when 1 then 1 else 0 end) as director,
				sum(case log_visit.config_quicktime when 1 then 1 else 0 end) as quicktime,
				sum(case log_visit.config_realplayer when 1 then 1 else 0 end) as realplayer,
				sum(case log_visit.config_windowsmedia when 1 then 1 else 0 end) as windowsmedia,
				sum(case log_visit.config_gears when 1 then 1 else 0 end) as gears,
				sum(case log_visit.config_silverlight when 1 then 1 else 0 end) as silverlight,
				sum(case log_visit.config_cookie when 1 then 1 else 0 end) as cookie	";
        $table = $archiveProcessing->getSimpleDataTableFromSelect($toSelect, Piwik_Archive::INDEX_NB_VISITS);
        $archiveProcessing->insertBlobRecord(self::PLUGIN_RECORD_NAME, $table->getSerialized());
    }

    protected function aggregateByLanguage($archiveProcessing)
    {
        $query = $archiveProcessing->queryVisitsByDimension("log_visit.location_browser_lang");
        $languageCodes = array_keys(Piwik_Common::getLanguagesList());
        $metricsByLanguage = array();
        while ($row = $query->fetch()) {
            $code = Piwik_Common::extractLanguageCodeFromBrowserLanguage($row['label'], $languageCodes);

            if (!isset($metricsByLanguage[$code])) {
                $metricsByLanguage[$code] = $archiveProcessing->makeEmptyRow();
            }
            $archiveProcessing->sumMetrics($row, $metricsByLanguage[$code]);
        }

        $tableLanguage = $archiveProcessing->getDataTableFromArray($metricsByLanguage);
        $archiveProcessing->insertBlobRecord(self::LANGUAGE_RECORD_NAME, $tableLanguage->getSerialized($this->maximumRowsInDataTable, null, $this->columnToSortByBeforeTruncation));
    }

    public function archivePeriod($archiveProcessing)
    {
        $dataTableToSum = array(
            self::CONFIGURATION_RECORD_NAME,
            self::OS_RECORD_NAME,
            self::BROWSER_RECORD_NAME,
            self::BROWSER_TYPE_RECORD_NAME,
            self::RESOLUTION_RECORD_NAME,
            self::SCREEN_TYPES_RECORD_NAME,
            self::PLUGIN_RECORD_NAME,
            self::LANGUAGE_RECORD_NAME,
        );
        $archiveProcessing->archiveDataTable($dataTableToSum, null, $this->maximumRowsInDataTable);
    }
}