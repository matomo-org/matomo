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

    const LANGUAGE_DIMENSION = "log_visit.location_browser_lang";

    /**
     * Daily archive of User Settings report. Processes reports for Visits by Resolution,
     * browser plugins, etc. Some reports are built from the logs, some reports are superset of an existing report
     */
    public function aggregateDayReport()
    {
        $this->aggregateByLanguage();
    }

    /**
     * Period archiving: simply sums up daily archives
     */
    public function aggregateMultipleReports()
    {
        $dataTableRecords = array(
            self::LANGUAGE_RECORD_NAME,
        );
        $this->getProcessor()->aggregateDataTableRecords($dataTableRecords, $this->maximumRows);
    }

    protected function aggregateByLanguage()
    {
        $query = $this->getLogAggregator()->queryVisitsByDimension(array("label" => self::LANGUAGE_DIMENSION));
        $countryCodes = Common::getCountriesList($includeInternalCodes = true);
        $metricsByLanguage = new DataArray();

        while ($row = $query->fetch()) {
            $langCode = Common::extractLanguageCodeFromBrowserLanguage($row['label']);
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

