<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\UserSettings;

use Piwik\Common;
use Piwik\DataAccess\LogAggregator;
use Piwik\DataArray;
use Piwik\DataTable;
use Piwik\Metrics;

require_once PIWIK_INCLUDE_PATH . '/plugins/UserSettings/functions.php';

/**
 * Archiver for UserSettings Plugin
 *
 * @see PluginsArchiver
 */
class Archiver extends \Piwik\Plugin\Archiver
{
    const LANGUAGE_RECORD_NAME = 'UserSettings_language';
    const PLUGIN_RECORD_NAME = 'UserSettings_plugin';
    const SCREEN_TYPE_RECORD_NAME = 'UserSettings_wideScreen';
    const RESOLUTION_RECORD_NAME = 'UserSettings_resolution';
    const BROWSER_RECORD_NAME = 'UserSettings_browser';
    const BROWSER_TYPE_RECORD_NAME = 'UserSettings_browserType';
    const OS_RECORD_NAME = 'UserSettings_os';
    const CONFIGURATION_RECORD_NAME = 'UserSettings_configuration';

    const LANGUAGE_DIMENSION = "log_visit.location_browser_lang";
    const RESOLUTION_DIMENSION = "log_visit.config_resolution";
    const BROWSER_VERSION_DIMENSION = "CONCAT(log_visit.config_browser_name, ';', log_visit.config_browser_version)";
    const OS_DIMENSION = "log_visit.config_os";
    const CONFIGURATION_DIMENSION = "CONCAT(log_visit.config_os, ';', log_visit.config_browser_name, ';', log_visit.config_resolution)";

    /**
     * Daily archive of User Settings report. Processes reports for Visits by Resolution,
     * by Browser, Browser family, etc. Some reports are built from the logs, some reports
     * are superset of an existing report (eg. Browser family is built from the Browser report)
     */
    public function aggregateDayReport()
    {
        $this->aggregateByConfiguration();
        $this->aggregateByOs();
        $this->aggregateByBrowser();
        $this->aggregateByResolutionAndScreenType();
        $this->aggregateByPlugin();
        $this->aggregateByLanguage();
    }

    /**
     * Period archiving: simply sums up daily archives
     */
    public function aggregateMultipleReports()
    {
        $dataTableRecords = array(
            self::CONFIGURATION_RECORD_NAME,
            self::OS_RECORD_NAME,
            self::BROWSER_RECORD_NAME,
            self::BROWSER_TYPE_RECORD_NAME,
            self::RESOLUTION_RECORD_NAME,
            self::SCREEN_TYPE_RECORD_NAME,
            self::PLUGIN_RECORD_NAME,
            self::LANGUAGE_RECORD_NAME,
        );
        $this->getProcessor()->aggregateDataTableRecords($dataTableRecords, $this->maximumRows);
    }

    protected function aggregateByConfiguration()
    {
        $metrics = $this->getLogAggregator()->getMetricsFromVisitByDimension(self::CONFIGURATION_DIMENSION)->asDataTable();
        $this->insertTable(self::CONFIGURATION_RECORD_NAME, $metrics);
    }

    protected function aggregateByOs()
    {
        $metrics = $this->getLogAggregator()->getMetricsFromVisitByDimension(self::OS_DIMENSION)->asDataTable();
        $this->insertTable(self::OS_RECORD_NAME, $metrics);
    }

    protected function aggregateByBrowser()
    {
        $tableBrowser = $this->aggregateByBrowserVersion();
        $this->aggregateByBrowserType($tableBrowser);
    }

    protected function aggregateByBrowserVersion()
    {
        $tableBrowser = $this->getLogAggregator()->getMetricsFromVisitByDimension(self::BROWSER_VERSION_DIMENSION)->asDataTable();
        $this->insertTable(self::BROWSER_RECORD_NAME, $tableBrowser);
        return $tableBrowser;
    }

    protected function aggregateByBrowserType(DataTable $tableBrowser)
    {
        $tableBrowser->filter('GroupBy', array('label', __NAMESPACE__ . '\getBrowserFamily'));
        $this->insertTable(self::BROWSER_TYPE_RECORD_NAME, $tableBrowser);
    }

    protected function aggregateByResolutionAndScreenType()
    {
        $resolutions = $this->aggregateByResolution();
        $this->aggregateByScreenType($resolutions);
    }

    protected function aggregateByResolution()
    {
        $table = $this->getLogAggregator()->getMetricsFromVisitByDimension(self::RESOLUTION_DIMENSION)->asDataTable();
        $table->filter('ColumnCallbackDeleteRow', array('label', function ($value) {
            return strlen($value) <= 5;
        }));
        $this->insertTable(self::RESOLUTION_RECORD_NAME, $table);
        return $table;
    }

    protected function aggregateByScreenType(DataTable $resolutions)
    {
        $resolutions->filter('GroupBy', array('label', __NAMESPACE__ . '\getScreenTypeFromResolution'));
        $this->insertTable(self::SCREEN_TYPE_RECORD_NAME, $resolutions);
    }

    protected function aggregateByPlugin()
    {
        $selects = array(
            "sum(case log_visit.config_pdf when 1 then 1 else 0 end) as pdf",
            "sum(case log_visit.config_flash when 1 then 1 else 0 end) as flash",
            "sum(case log_visit.config_java when 1 then 1 else 0 end) as java",
            "sum(case log_visit.config_director when 1 then 1 else 0 end) as director",
            "sum(case log_visit.config_quicktime when 1 then 1 else 0 end) as quicktime",
            "sum(case log_visit.config_realplayer when 1 then 1 else 0 end) as realplayer",
            "sum(case log_visit.config_windowsmedia when 1 then 1 else 0 end) as windowsmedia",
            "sum(case log_visit.config_gears when 1 then 1 else 0 end) as gears",
            "sum(case log_visit.config_silverlight when 1 then 1 else 0 end) as silverlight",
            "sum(case log_visit.config_cookie when 1 then 1 else 0 end) as cookie"
        );

        $query = $this->getLogAggregator()->queryVisitsByDimension(array(), false, $selects, $metrics = array());
        $data = $query->fetch();
        $cleanRow = LogAggregator::makeArrayOneColumn($data, Metrics::INDEX_NB_VISITS);
        $table = DataTable::makeFromIndexedArray($cleanRow);
        $this->insertTable(self::PLUGIN_RECORD_NAME, $table);
    }

    protected function aggregateByLanguage()
    {
        $query = $this->getLogAggregator()->queryVisitsByDimension(array("label" => self::LANGUAGE_DIMENSION));
        $languageCodes = array_keys(Common::getLanguagesList());
        $countryCodes = Common::getCountriesList($includeInternalCodes = true);
        $metricsByLanguage = new DataArray();

        while ($row = $query->fetch()) {
            $langCode = Common::extractLanguageCodeFromBrowserLanguage($row['label'], $languageCodes);
            $countryCode = Common::extractCountryCodeFromBrowserLanguage($row['label'], $countryCodes, $enableLanguageToCountryGuess = true);

            if ($countryCode == 'xx' || $countryCode == $langCode) {
                $metricsByLanguage->sumMetricsVisits($langCode, $row);
            } else {
                $metricsByLanguage->sumMetricsVisits($langCode . '-' . $countryCode, $row);
            }
        }

        $report = $metricsByLanguage->asDataTable();
        $this->insertTable(self::LANGUAGE_RECORD_NAME, $report);
    }


    protected function insertTable($recordName, DataTable $table)
    {
        $report = $table->getSerialized($this->maximumRows, null, Metrics::INDEX_NB_VISITS);
        return $this->getProcessor()->insertBlobRecord($recordName, $report);
    }

}

