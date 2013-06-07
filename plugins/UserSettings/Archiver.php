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

class Piwik_UserSettings_Archiver extends Piwik_PluginsArchiver
{
    const LANGUAGE_RECORD_NAME = 'UserSettings_language';
    const PLUGIN_RECORD_NAME = 'UserSettings_plugin';
    const SCREEN_TYPE_RECORD_NAME = 'UserSettings_wideScreen';
    const RESOLUTION_RECORD_NAME = 'UserSettings_resolution';
    const BROWSER_RECORD_NAME = 'UserSettings_browser';
    const BROWSER_TYPE_RECORD_NAME = 'UserSettings_browserType';
    const OS_RECORD_NAME = 'UserSettings_os';
    const CONFIGURATION_RECORD_NAME = 'UserSettings_configuration';

    public function __construct($processor)
    {
        parent::__construct($processor);
        $this->maximumRowsInDataTable = Piwik_Config::getInstance()->General['datatable_archiving_maximum_rows_standard'];
        $this->columnToSortByBeforeTruncation = Piwik_Archive::INDEX_NB_VISITS;
    }

    public function archiveDay()
    {
        $this->aggregateByConfiguration();
        $this->aggregateByOs();
        $this->aggregateByBrowser();
        $this->aggregateByResolutionAndScreenType();
        $this->aggregateByPlugin();
        $this->aggregateByLanguage();
    }

    protected function aggregateByConfiguration()
    {
        $labelSQL = "CONCAT(log_visit.config_os, ';', log_visit.config_browser_name, ';', log_visit.config_resolution)";
        $metrics = $this->getProcessor()->getMetricsForLabel($labelSQL);
        $table = $this->getProcessor()->getDataTableFromArray($metrics);
        $this->getProcessor()->insertBlobRecord(self::CONFIGURATION_RECORD_NAME, $table->getSerialized($this->maximumRowsInDataTable, null, $this->columnToSortByBeforeTruncation));
    }

    protected function aggregateByOs()
    {
        $metrics = $this->getProcessor()->getMetricsForLabel("log_visit.config_os");
        $table = $this->getProcessor()->getDataTableFromArray($metrics);
        $this->getProcessor()->insertBlobRecord(self::OS_RECORD_NAME, $table->getSerialized($this->maximumRowsInDataTable, null, $this->columnToSortByBeforeTruncation));
    }

    protected function aggregateByBrowser()
    {
        $tableBrowser = $this->aggregateByBrowserVersion();
        $this->aggregateByBrowserType($tableBrowser);
    }

    protected function aggregateByBrowserVersion()
    {
        $labelSQL = "CONCAT(log_visit.config_browser_name, ';', log_visit.config_browser_version)";
        $metrics = $this->getProcessor()->getMetricsForLabel($labelSQL);
        $tableBrowser = $this->getProcessor()->getDataTableFromArray($metrics);

        $this->getProcessor()->insertBlobRecord(self::BROWSER_RECORD_NAME, $tableBrowser->getSerialized($this->maximumRowsInDataTable, null, $this->columnToSortByBeforeTruncation));
        return $tableBrowser;
    }

    protected function aggregateByBrowserType(Piwik_DataTable $tableBrowser)
    {
        $tableBrowser->filter('GroupBy', array('label', 'Piwik_getBrowserFamily'));
        $this->getProcessor()->insertBlobRecord(self::BROWSER_TYPE_RECORD_NAME, $tableBrowser->getSerialized());
    }

    protected function aggregateByResolutionAndScreenType()
    {
        $resolutions = $this->aggregateByResolution();
        $this->aggregateByScreenType($resolutions);
    }

    protected function aggregateByResolution()
    {
        $metrics = $this->getProcessor()->getMetricsForLabel("log_visit.config_resolution");
        $table = $this->getProcessor()->getDataTableFromArray($metrics);
        $table->filter('ColumnCallbackDeleteRow', array('label', 'Piwik_UserSettings_keepStrlenGreater'));
        $this->getProcessor()->insertBlobRecord(self::RESOLUTION_RECORD_NAME, $table->getSerialized($this->maximumRowsInDataTable, null, $this->columnToSortByBeforeTruncation));
        return $table;
    }

    protected function aggregateByScreenType(Piwik_DataTable $resolutions)
    {
        $resolutions->filter('GroupBy', array('label', 'Piwik_getScreenTypeFromResolution'));
        $this->getProcessor()->insertBlobRecord(self::SCREEN_TYPE_RECORD_NAME, $resolutions->getSerialized());
    }

    protected function aggregateByPlugin()
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

        $data = $this->getProcessor()->queryVisitsSimple($toSelect);
        $table =  $this->getProcessor()->getSimpleDataTableFromRow($data, Piwik_Archive::INDEX_NB_VISITS);
        $this->getProcessor()->insertBlobRecord(self::PLUGIN_RECORD_NAME, $table->getSerialized());
    }

    protected function aggregateByLanguage()
    {
        $query = $this->getProcessor()->queryVisitsByDimension("log_visit.location_browser_lang");
        $languageCodes = array_keys(Piwik_Common::getLanguagesList());
        $metricsByLanguage = array();
        while ($row = $query->fetch()) {
            $code = Piwik_Common::extractLanguageCodeFromBrowserLanguage($row['label'], $languageCodes);

            if (!isset($metricsByLanguage[$code])) {
                $metricsByLanguage[$code] = $this->getProcessor()->makeEmptyRow();
            }
            $this->getProcessor()->sumMetrics($row, $metricsByLanguage[$code]);
        }

        $tableLanguage = $this->getProcessor()->getDataTableFromArray($metricsByLanguage);
        $this->getProcessor()->insertBlobRecord(self::LANGUAGE_RECORD_NAME, $tableLanguage->getSerialized($this->maximumRowsInDataTable, null, $this->columnToSortByBeforeTruncation));
    }

    public function archivePeriod()
    {
        $dataTableToSum = array(
            self::CONFIGURATION_RECORD_NAME,
            self::OS_RECORD_NAME,
            self::BROWSER_RECORD_NAME,
            self::BROWSER_TYPE_RECORD_NAME,
            self::RESOLUTION_RECORD_NAME,
            self::SCREEN_TYPE_RECORD_NAME,
            self::PLUGIN_RECORD_NAME,
            self::LANGUAGE_RECORD_NAME,
        );
        $this->getProcessor()->archiveDataTable($dataTableToSum, null, $this->maximumRowsInDataTable);
    }
}

