<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserSettings;

use Piwik\Archive;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Piwik;
use Piwik\Plugins\DevicesDetection\Archiver AS DDArchiver;
use Piwik\Plugins\CoreHome\Columns\Metrics\VisitsPercent;

/**
 * @see plugins/UserSettings/functions.php
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/UserSettings/functions.php';

/**
 * The UserSettings API lets you access reports about some of your Visitors technical settings:
 * plugins supported in their browser, Screen resolution and Screen types (normal, widescreen, dual screen or mobile).
 *
 * @method static \Piwik\Plugins\UserSettings\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    protected function getDataTable($name, $idSite, $period, $date, $segment)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $archive = Archive::build($idSite, $period, $date, $segment);
        $dataTable = $archive->getDataTable($name);
        $dataTable->filter('Sort', array(Metrics::INDEX_NB_VISITS));
        $dataTable->queueFilter('ReplaceColumnNames');
        $dataTable->queueFilter('ReplaceSummaryRowLabel');
        return $dataTable;
    }

    /**
     * @deprecated since 2.10.0   See {@link Piwik\Plugins\Resolution\API} for new implementation.
     */
    public function getResolution($idSite, $period, $date, $segment = false)
    {
        return \Piwik\Plugins\Resolution\API::getInstance()->getResolution($idSite, $period, $date, $segment);
    }

    /**
     * @deprecated since 2.10.0   See {@link Piwik\Plugins\Resolution\API} for new implementation.
     */
    public function getConfiguration($idSite, $period, $date, $segment = false)
    {
        return \Piwik\Plugins\Resolution\API::getInstance()->getConfiguration($idSite, $period, $date, $segment);
    }

    protected function getDevicesDetectorApi()
    {
        return \Piwik\Plugins\DevicesDetection\API::getInstance();
    }

    /**
     * @deprecated since 2.10.0   See {@link Piwik\Plugins\DevicesDetector\API} for new implementation.
     */
    public function getOS($idSite, $period, $date, $segment = false, $addShortLabel = true)
    {
        return $this->getDevicesDetectorApi()->getOsVersions($idSite, $period, $date, $segment);
    }

    /**
     * @deprecated since 2.10.0   See {@link Piwik\Plugins\DevicesDetector\API} for new implementation.
     */
    public function getOSFamily($idSite, $period, $date, $segment = false)
    {
        return $this->getDevicesDetectorApi()->getOsFamilies($idSite, $period, $date, $segment);
    }

    /**
     * Gets a DataTable displaying number of visits by device type.
     * @deprecated since 2.10.0   See {@link Piwik\Plugins\DevicesDetector\API} for new implementation.
     */
    public function getMobileVsDesktop($idSite, $period, $date, $segment = false)
    {
        return $this->getDevicesDetectorApi()->getType($idSite, $period, $date, $segment);
    }

    /**
     * @deprecated since 2.10.0   See {@link Piwik\Plugins\DevicesDetector\API} for new implementation.
     */
    public function getBrowserVersion($idSite, $period, $date, $segment = false)
    {
        return $this->getDevicesDetectorApi()->getBrowserVersions($idSite, $period, $date, $segment);
    }

    /**
     * @deprecated since 2.10.0   See {@link Piwik\Plugins\DevicesDetector\API} for new implementation.
     */
    public function getBrowser($idSite, $period, $date, $segment = false)
    {
        return $this->getDevicesDetectorApi()->getBrowsers($idSite, $period, $date, $segment);
    }

    /**
     * @deprecated since 2.9.0   See {@link Piwik\Plugins\DevicesDetector\API} for new implementation.
     */
    public function getBrowserType($idSite, $period, $date, $segment = false)
    {
        return $this->getDevicesDetectorApi()->getBrowserEngines($idSite, $period, $date, $segment);
    }

    public function getPlugin($idSite, $period, $date, $segment = false)
    {
        // fetch all archive data required
        $dataTable = $this->getDataTable(Archiver::PLUGIN_RECORD_NAME, $idSite, $period, $date, $segment);
        $browserTypes = $this->getDataTable(DDArchiver::BROWSER_ENGINE_RECORD_NAME, $idSite, $period, $date, $segment);
        $archive = Archive::build($idSite, $period, $date, $segment);
        $visitsSums = $archive->getDataTableFromNumeric('nb_visits');

        // check whether given tables are arrays
        if ($dataTable instanceof DataTable\Map) {
            $dataTableMap = $dataTable->getDataTables();
            $browserTypesArray = $browserTypes->getDataTables();
            $visitSumsArray = $visitsSums->getDataTables();
        } else {
            $dataTableMap = array($dataTable);
            $browserTypesArray = array($browserTypes);
            $visitSumsArray = array($visitsSums);
        }

        // walk through the results and calculate the percentage
        foreach ($dataTableMap as $key => $table) {
            // Calculate percentage, but ignore IE users because plugin detection doesn't work on IE
            $ieVisits = 0;

            $ieStats = $browserTypesArray[$key]->getRowFromLabel('Trident');
            if ($ieStats !== false) {
                $ieVisits = $ieStats->getColumn(Metrics::INDEX_NB_VISITS);
            }

            // get according visitsSum
            $visits = $visitSumsArray[$key];
            if ($visits->getRowsCount() == 0) {
                $visitsSumTotal = 0;
            } else {
                $visitsSumTotal = (float) $visits->getFirstRow()->getColumn('nb_visits');
            }

            $visitsSum = $visitsSumTotal - $ieVisits;

            $extraProcessedMetrics = $table->getMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME);
            $extraProcessedMetrics[] = new VisitsPercent($visitsSum);
            $table->setMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME, $extraProcessedMetrics);
        }

        $dataTable->queueFilter('ColumnCallbackAddMetadata', array('label', 'logo', __NAMESPACE__ . '\getPluginsLogo'));
        $dataTable->queueFilter('ColumnCallbackReplace', array('label', 'ucfirst'));

        return $dataTable;
    }

    public function getLanguage($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable(Archiver::LANGUAGE_RECORD_NAME, $idSite, $period, $date, $segment);
        $dataTable->filter('GroupBy', array('label', __NAMESPACE__ . '\groupByLangCallback'));
        $dataTable->filter('ColumnCallbackReplace', array('label', __NAMESPACE__ . '\languageTranslate'));

        return $dataTable;
    }

    public function getLanguageCode($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable(Archiver::LANGUAGE_RECORD_NAME, $idSite, $period, $date, $segment);
        $dataTable->filter('ColumnCallbackReplace', array('label', __NAMESPACE__ . '\languageTranslateWithCode'));

        return $dataTable;
    }
}
