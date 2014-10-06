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

/**
 * @see plugins/UserSettings/functions.php
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/UserSettings/functions.php';

/**
 * The UserSettings API lets you access reports about your Visitors technical settings: browsers, browser types (rendering engine),
 * operating systems, plugins supported in their browser, Screen resolution and Screen types (normal, widescreen, dual screen or mobile).
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

    public function getResolution($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable(Archiver::RESOLUTION_RECORD_NAME, $idSite, $period, $date, $segment);
        return $dataTable;
    }

    public function getConfiguration($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable(Archiver::CONFIGURATION_RECORD_NAME, $idSite, $period, $date, $segment);
        $dataTable->queueFilter('ColumnCallbackReplace', array('label', __NAMESPACE__ . '\getConfigurationLabel'));
        return $dataTable;
    }

    public function getOS($idSite, $period, $date, $segment = false, $addShortLabel = true)
    {
        $dataTable = $this->getDataTable(Archiver::OS_RECORD_NAME, $idSite, $period, $date, $segment);
        // these filters are applied directly so other API methods can use GroupBy on the result of this method
        $dataTable->filter('ColumnCallbackAddMetadata', array('label', 'logo', __NAMESPACE__ . '\getOSLogo'));
        if ($addShortLabel) {
            $dataTable->filter(
                'ColumnCallbackAddMetadata', array('label', 'shortLabel', __NAMESPACE__ . '\getOSShortLabel'));
        }
        $dataTable->filter('ColumnCallbackReplace', array('label', __NAMESPACE__ . '\getOSLabel'));
        return $dataTable;
    }

    /**
     * Gets a DataTable displaying number of visits by operating system family. The operating
     * system families are listed in vendor piwik/device-detector.
     */
    public function getOSFamily($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getOS($idSite, $period, $date, $segment, $addShortLabel = false);
        $dataTable->filter('GroupBy', array('label', __NAMESPACE__ . '\getOSFamily'));
        $dataTable->queueFilter('ColumnCallbackReplace', array('label', array('\\Piwik\\Piwik','translate')));
        return $dataTable;
    }

    /**
     * Gets a DataTable displaying number of visits by device type (mobile vs. desktop).
     */
    public function getMobileVsDesktop($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getOS($idSite, $period, $date, $segment, $addShortLabel = false);
        $dataTable->filter('GroupBy', array('label', __NAMESPACE__ . '\getDeviceTypeFromOS'));
        $this->ensureDefaultRowsInTable($dataTable);

        // set the logo metadata
        $dataTable->queueFilter('MetadataCallbackReplace',
            array('logo', __NAMESPACE__ . '\getDeviceTypeImg', null, array('label')));

        // translate the labels
        $dataTable->queueFilter('ColumnCallbackReplace', array('label', array('\\Piwik\\Piwik','translate')));

        return $dataTable;
    }

    protected function ensureDefaultRowsInTable($dataTable)
    {
        $requiredRows = array(
            'General_Desktop' => Metrics::INDEX_NB_VISITS,
            'General_Mobile'  => Metrics::INDEX_NB_VISITS
        );

        $dataTables = array($dataTable);

        if (!($dataTable instanceof DataTable\Map)) {
            foreach ($dataTables as $table) {
                if ($table->getRowsCount() == 0) {
                    continue;
                }
                foreach ($requiredRows as $requiredRow => $key) {
                    $row = $table->getRowFromLabel($requiredRow);
                    if (empty($row)) {
                        $table->addRowsFromSimpleArray(array(
                                                            array('label' => $requiredRow, $key => 0)
                                                       ));
                    }
                }
            }
        }
    }

    public function getBrowserVersion($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getBrowserTable($idSite, $period, $date, $segment);
        $dataTable->filter('ColumnCallbackAddMetadata', array('label', 'shortLabel', __NAMESPACE__ . '\getBrowserShortLabel'));
        return $dataTable;
    }

    protected function getBrowserTable($idSite, $period, $date, $segment)
    {
        $dataTable = $this->getDataTable(Archiver::BROWSER_RECORD_NAME, $idSite, $period, $date, $segment);
        $dataTable->filter('ColumnCallbackAddMetadata', array('label', 'logo', __NAMESPACE__ . '\getBrowsersLogo'));
        $dataTable->filter('ColumnCallbackReplace', array('label', __NAMESPACE__ . '\getBrowserLabel'));
        return $dataTable;
    }

    /**
     * Gets a DataTable displaying number of visits by browser (ie, Firefox, Chrome, etc.).
     * The browser version is not included in this report.
     */
    public function getBrowser($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getBrowserTable($idSite, $period, $date, $segment);
        $dataTable->filter('GroupBy', array('label', __NAMESPACE__ . '\getBrowserFromBrowserVersion'));
        return $dataTable;
    }

    public function getBrowserType($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable(Archiver::BROWSER_TYPE_RECORD_NAME, $idSite, $period, $date, $segment);
        $dataTable->queueFilter('ColumnCallbackAddMetadata', array('label', 'shortLabel', 'ucfirst'));
        $dataTable->queueFilter('ColumnCallbackReplace', array('label', __NAMESPACE__ . '\getBrowserTypeLabel'));
        return $dataTable;
    }

    public function getWideScreen($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable(Archiver::SCREEN_TYPE_RECORD_NAME, $idSite, $period, $date, $segment);
        $dataTable->queueFilter('ColumnCallbackAddMetadata', array('label', 'logo', __NAMESPACE__ . '\getScreensLogo'));
        $dataTable->queueFilter('ColumnCallbackReplace', array('label', 'ucfirst'));
        return $dataTable;
    }

    public function getPlugin($idSite, $period, $date, $segment = false)
    {
        // fetch all archive data required
        $dataTable = $this->getDataTable(Archiver::PLUGIN_RECORD_NAME, $idSite, $period, $date, $segment);
        $browserTypes = $this->getDataTable(Archiver::BROWSER_TYPE_RECORD_NAME, $idSite, $period, $date, $segment);
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
            // get according browserType table
            foreach ($browserTypesArray as $k => $browsers) {
                if ($k == $key) {
                    $browserType = $browsers;
                }
            }

            // get according visitsSum
            foreach ($visitSumsArray as $k => $visits) {
                if ($k == $key) {
                    if (is_object($visits)) {
                        if ($visits->getRowsCount() == 0) {
                            $visitsSumTotal = 0;
                        } else {
                            $visitsSumTotal = (float)$visits->getFirstRow()->getColumn('nb_visits');
                        }
                    } else {
                        $visitsSumTotal = (float)$visits;
                    }
                }
            }

            // Calculate percentage, but ignore IE users because plugin detection doesn't work on IE
            $ieVisits = 0;

            $ieStats = $browserType->getRowFromLabel('ie');
            if ($ieStats !== false) {
                $ieVisits = $ieStats->getColumn(Metrics::INDEX_NB_VISITS);
            }

            $visitsSum = $visitsSumTotal - $ieVisits;

            // When Truncate filter is applied, it will call AddSummaryRow which tries to sum all rows.
            // We tell the object to skip the column nb_visits_percentage when aggregating (since it's not correct to sum % values)
            $columnAggregationOps = $table->getMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME);
            $columnAggregationOps['nb_visits_percentage'] = 'skip';
            $table->setMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME, $columnAggregationOps);

            // The filter must be applied now so that the new column can
            // be sorted by the generic filters (applied right after this loop exits)
            $table->filter('ColumnCallbackAddColumnPercentage', array('nb_visits_percentage', Metrics::INDEX_NB_VISITS, $visitsSum, 1));
            $table->filter('RangeCheck', array('nb_visits_percentage'));
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
