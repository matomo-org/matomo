<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\UserLanguage;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\DataArray;
use Piwik\DataTable;
use Piwik\Intl\Data\Provider\RegionDataProvider;
use Piwik\Metrics;

require_once PIWIK_INCLUDE_PATH . '/plugins/UserLanguage/functions.php';

/**
 * Archiver for UserLanguage Plugin
 *
 * @see PluginsArchiver
 */
class Archiver extends \Piwik\Plugin\Archiver
{
    const LANGUAGE_RECORD_NAME = 'UserLanguage_language';

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
        $columnsAggregationOperation = null;
        $this->getProcessor()->aggregateDataTableRecords(
            $dataTableRecords,
            $this->maximumRows,
            $maximumRowsInSubDataTable = null,
            $columnToSortByBeforeTruncation = null,
            $columnsAggregationOperation,
            $columnsToRenameAfterAggregation = null,
            $countRowsRecursive = array());
    }

    protected function aggregateByLanguage()
    {
        /** @var RegionDataProvider $regionDataProvider */
        $regionDataProvider = StaticContainer::get('Piwik\Intl\Data\Provider\RegionDataProvider');

        $query = $this->getLogAggregator()->queryVisitsByDimension(array("label" => self::LANGUAGE_DIMENSION));
        $countryCodes = $regionDataProvider->getCountryList($includeInternalCodes = true);
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

