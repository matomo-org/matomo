<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DevicesDetection;

use DeviceDetector\Parser\Device\AbstractDeviceParser;
use Exception;
use Piwik\Archive;
use Piwik\Container\StaticContainer;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Piwik;
use DeviceDetector\Parser\Client\Browser as BrowserParser;

/**
 * The DevicesDetection API exposes reports about visitor devices, including types, brands, models,
 * operating system families/versions, browsers, and browser engines.
 * It returns report DataTables and enriches them with labels, logos, and segments.
 *
 * @method static \Piwik\Plugins\DevicesDetection\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * @param string $name
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param string $segment
     * @return DataTable|DataTable\Map
     */
    protected function getDataTable($name, $idSite, $period, $date, $segment)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $archive = Archive::build($idSite, $period, $date, $segment);
        $dataTable = $archive->getDataTable($name);
        $dataTable->queueFilter('ReplaceColumnNames');
        $dataTable->queueFilter('ReplaceSummaryRowLabel');
        return $dataTable;
    }

    /**
     * Gets datatable displaying number of visits by device type (eg. desktop, smartphone, tablet)
     * @param int $idSite Site ID to query.
     * @param string $period Period identifier enabled for the API. Built-ins include 'day', 'week', 'month', 'year',
     *                       and 'range'; plugin-defined period identifiers may also be enabled. Required, not null.
     * @param \Piwik\Date|string $date Date selector. Single dates can be 'YYYY-MM-DD', 'YYYY-MM-DD HH:MM:SS', a unix
     *                                 timestamp, or a strtotime-compatible string without a comma. Keywords supported
     *                                 are 'now', 'today', 'tomorrow', 'yesterday', 'yesterdaySameTime',
     *                                 'last week'/'last-week', 'last month'/'last-month', 'last year'/'last-year'.
     *                                 Multiple-period selectors include 'lastN' or 'previousN' (N optional integer),
     *                                 or a range 'YYYY-MM-DD,YYYY-MM-DD' or
     *                                 'last week|month|year, today|now|yesterday|last week|month|year'. When $period
     *                                 is 'range', the date must be a range or a last/previous selector.
     *                                 Timezone: with a single site, 'now'/'today'/'yesterday'/'yesterdaySameTime' and
     *                                 'last week/month/year' use the site timezone; otherwise they use UTC. Other
     *                                 formats (including 'tomorrow') are parsed in UTC. In ranges, the end date uses
     *                                 that timezone; the start date is parsed in UTC.
     * @param string|false $segment Segment definition string to filter visits, or false for no segment.
     * @return DataTable|DataTable\Map Report of visits by device type, or a map when multiple sites/periods are queried.
     */
    public function getType($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable('DevicesDetection_types', $idSite, $period, $date, $segment);
        // ensure all device types are in the list
        $this->ensureDefaultRowsInTable($dataTable);

        $mapping = AbstractDeviceParser::getAvailableDeviceTypeNames();
        $dataTable->filter('AddSegmentByLabelMapping', ['deviceType', $mapping]);
        $dataTable->filter('ColumnCallbackAddMetadata', ['label', 'logo', __NAMESPACE__ . '\getDeviceTypeLogo']);
        $dataTable->filter('GroupBy', ['label', __NAMESPACE__ . '\getDeviceTypeLabel']);
        return $dataTable;
    }

    protected function ensureDefaultRowsInTable($dataTable)
    {
        $requiredRows = array_fill(0, count(AbstractDeviceParser::getAvailableDeviceTypes()), Metrics::INDEX_NB_VISITS);

        $dataTables = [$dataTable];

        if (!($dataTable instanceof DataTable\Map)) {
            foreach ($dataTables as $table) {
                if ($table->getRowsCount() == 0) {
                    continue;
                }
                foreach ($requiredRows as $requiredRow => $key) {
                    $row = $table->getRowFromLabel($requiredRow);
                    if (empty($row)) {
                        $table->addRowsFromSimpleArray([
                            ['label' => $requiredRow, $key => 0],
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Gets datatable displaying number of visits by device manufacturer name
     * @param int $idSite Site ID to query.
     * @param string $period Period identifier enabled for the API. Built-ins include 'day', 'week', 'month', 'year',
     *                       and 'range'; plugin-defined period identifiers may also be enabled. Required, not null.
     * @param \Piwik\Date|string $date Date selector. Single dates can be 'YYYY-MM-DD', 'YYYY-MM-DD HH:MM:SS', a unix
     *                                 timestamp, or a strtotime-compatible string without a comma. Keywords supported
     *                                 are 'now', 'today', 'tomorrow', 'yesterday', 'yesterdaySameTime',
     *                                 'last week'/'last-week', 'last month'/'last-month', 'last year'/'last-year'.
     *                                 Multiple-period selectors include 'lastN' or 'previousN' (N optional integer),
     *                                 or a range 'YYYY-MM-DD,YYYY-MM-DD' or
     *                                 'last week|month|year, today|now|yesterday|last week|month|year'. When $period
     *                                 is 'range', the date must be a range or a last/previous selector.
     *                                 Timezone: with a single site, 'now'/'today'/'yesterday'/'yesterdaySameTime' and
     *                                 'last week/month/year' use the site timezone; otherwise they use UTC. Other
     *                                 formats (including 'tomorrow') are parsed in UTC. In ranges, the end date uses
     *                                 that timezone; the start date is parsed in UTC.
     * @param string|false $segment Segment definition string to filter visits, or false for no segment.
     * @return DataTable|DataTable\Map Report of visits by device brand, or a map when multiple sites/periods are queried.
     */
    public function getBrand($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable('DevicesDetection_brands', $idSite, $period, $date, $segment);
        $dataTable->filter('GroupBy', ['label', __NAMESPACE__ . '\getDeviceBrandLabel']);
        $dataTable->filter('ColumnCallbackAddMetadata', ['label', 'logo', __NAMESPACE__ . '\getBrandLogo']);
        $dataTable->filter('AddSegmentByLabel', ['deviceBrand']);
        return $dataTable;
    }

    /**
     * Gets datatable displaying number of visits by device model
     * @param int $idSite Site ID to query.
     * @param string $period Period identifier enabled for the API. Built-ins include 'day', 'week', 'month', 'year',
     *                       and 'range'; plugin-defined period identifiers may also be enabled. Required, not null.
     * @param \Piwik\Date|string $date Date selector. Single dates can be 'YYYY-MM-DD', 'YYYY-MM-DD HH:MM:SS', a unix
     *                                 timestamp, or a strtotime-compatible string without a comma. Keywords supported
     *                                 are 'now', 'today', 'tomorrow', 'yesterday', 'yesterdaySameTime',
     *                                 'last week'/'last-week', 'last month'/'last-month', 'last year'/'last-year'.
     *                                 Multiple-period selectors include 'lastN' or 'previousN' (N optional integer),
     *                                 or a range 'YYYY-MM-DD,YYYY-MM-DD' or
     *                                 'last week|month|year, today|now|yesterday|last week|month|year'. When $period
     *                                 is 'range', the date must be a range or a last/previous selector.
     *                                 Timezone: with a single site, 'now'/'today'/'yesterday'/'yesterdaySameTime' and
     *                                 'last week/month/year' use the site timezone; otherwise they use UTC. Other
     *                                 formats (including 'tomorrow') are parsed in UTC. In ranges, the end date uses
     *                                 that timezone; the start date is parsed in UTC.
     * @param string|false $segment Segment definition string to filter visits, or false for no segment.
     * @return DataTable|DataTable\Map Report of visits by device model, or a map when multiple sites/periods are queried.
     * @throws Exception When device model reporting is disabled by compliance policy.
     */
    public function getModel($idSite, $period, $date, $segment = false)
    {
        $translator = StaticContainer::get('Piwik\Translation\Translator');
        if (DevicesDetection::isDeviceModelDetectionDisabledByCompliancePolicy($idSite)) {
            throw new Exception($translator->translate('DevicesDetection_DeviceModelReportDisabledByCompliancePolicy'));
        }

        $dataTable = $this->getDataTable('DevicesDetection_models', $idSite, $period, $date, $segment);

        $dataTable->filter(function (DataTable $table) {
            foreach ($table->getRowsWithoutSummaryRow() as $row) {
                $label = $row->getColumn('label');

                if (strpos($label, ';') !== false) {
                    list($brand, $model) = explode(';', $label, 2);
                    $brand = getDeviceBrandLabel($brand);
                } else {
                    $brand = '';
                    $model = $label;
                }

                $segment = sprintf('deviceBrand==%s;deviceModel==%s', urlencode($brand), urlencode($model));

                $row->setMetadata('segment', $segment);
            }
        });

        $dataTable->filter('GroupBy', ['label', __NAMESPACE__ . '\getModelName']);
        return $dataTable;
    }

    /**
     * Gets datatable displaying number of visits by OS family (eg. Windows, Android, Linux)
     * @param int $idSite Site ID to query.
     * @param string $period Period identifier enabled for the API. Built-ins include 'day', 'week', 'month', 'year',
     *                       and 'range'; plugin-defined period identifiers may also be enabled. Required, not null.
     * @param \Piwik\Date|string $date Date selector. Single dates can be 'YYYY-MM-DD', 'YYYY-MM-DD HH:MM:SS', a unix
     *                                 timestamp, or a strtotime-compatible string without a comma. Keywords supported
     *                                 are 'now', 'today', 'tomorrow', 'yesterday', 'yesterdaySameTime',
     *                                 'last week'/'last-week', 'last month'/'last-month', 'last year'/'last-year'.
     *                                 Multiple-period selectors include 'lastN' or 'previousN' (N optional integer),
     *                                 or a range 'YYYY-MM-DD,YYYY-MM-DD' or
     *                                 'last week|month|year, today|now|yesterday|last week|month|year'. When $period
     *                                 is 'range', the date must be a range or a last/previous selector.
     *                                 Timezone: with a single site, 'now'/'today'/'yesterday'/'yesterdaySameTime' and
     *                                 'last week/month/year' use the site timezone; otherwise they use UTC. Other
     *                                 formats (including 'tomorrow') are parsed in UTC. In ranges, the end date uses
     *                                 that timezone; the start date is parsed in UTC.
     * @param string|false $segment Segment definition string to filter visits, or false for no segment.
     * @return DataTable\DataTableInterface Report of visits by OS family.
     */
    public function getOsFamilies($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable('DevicesDetection_os', $idSite, $period, $date, $segment);

        // handle legacy archives
        if ($dataTable instanceof DataTable\Map || !$dataTable->getRowsCount()) {
            $versionDataTable = $this->getDataTable('DevicesDetection_osVersions', $idSite, $period, $date, $segment);
            $dataTable = $this->mergeDataTables($dataTable, $versionDataTable);
        }

        $dataTable->filter('GroupBy', ['label', __NAMESPACE__ . '\getOSFamilyFullName']);
        $dataTable->filter('ColumnCallbackAddMetadata', ['label', 'logo', __NAMESPACE__ . '\getOsFamilyLogo']);
        return $dataTable;
    }


    /**
     * That methods handles the fallback to version datatables to calculate those without versions.
     *
     * Unlike DevicesDetection plugin now, the UserSettings plugin did not store archives holding the os and browser data without
     * their version number. The "version-less" reports were always generated out of the "version-containing" archives .
     * For big archives (month/year) that meant that some of the data was truncated, due to the datatable entry limit.
     * To avoid that data loss / inaccuracy in the future, DevicesDetection plugin will also store archives without the version.
     * For data archived before DevicesDetection plugin was enabled, those archives do not exist, so we try to calculate
     * them here from the "version-containing" reports if possible.
     *
     * @param DataTable|DataTable\Map $dataTable
     * @param DataTable|DataTable\Map $dataTable2
     * @return DataTable\DataTableInterface
     */
    protected function mergeDataTables(DataTable\DataTableInterface $dataTable, DataTable\DataTableInterface $dataTable2)
    {
        if ($dataTable instanceof DataTable\Map) {
            $dataTables = $dataTable->getDataTables();

            foreach ($dataTables as $label => $table) {
                $versionDataTables = $dataTable2->getDataTables();

                if (!array_key_exists($label, $versionDataTables)) {
                    continue;
                }
                $newDataTable = $this->mergeDataTables($table, $versionDataTables[$label]);
                $dataTable->addTable($newDataTable, $label);
            }
        } elseif (!$dataTable->getRowsCount() && $dataTable2->getRowsCount()) {
            $dataTable2->filter('GroupBy', ['label', function ($label) {
                if (preg_match("/(.+) [0-9]+(?:\.[0-9]+)?$/", $label, $matches)) {
                    return $matches[1]; // should match for browsers
                }
                if (strpos($label, ';')) {
                    return substr($label, 0, 3); // should match for os
                }
                return $label;
            }]);
            return $dataTable2;
        }

        return $dataTable;
    }

    /**
     * Gets datatable displaying number of visits by OS version (eg. Android 4.0, Windows 7)
     * @param int $idSite Site ID to query.
     * @param string $period Period identifier enabled for the API. Built-ins include 'day', 'week', 'month', 'year',
     *                       and 'range'; plugin-defined period identifiers may also be enabled. Required, not null.
     * @param \Piwik\Date|string $date Date selector. Single dates can be 'YYYY-MM-DD', 'YYYY-MM-DD HH:MM:SS', a unix
     *                                 timestamp, or a strtotime-compatible string without a comma. Keywords supported
     *                                 are 'now', 'today', 'tomorrow', 'yesterday', 'yesterdaySameTime',
     *                                 'last week'/'last-week', 'last month'/'last-month', 'last year'/'last-year'.
     *                                 Multiple-period selectors include 'lastN' or 'previousN' (N optional integer),
     *                                 or a range 'YYYY-MM-DD,YYYY-MM-DD' or
     *                                 'last week|month|year, today|now|yesterday|last week|month|year'. When $period
     *                                 is 'range', the date must be a range or a last/previous selector.
     *                                 Timezone: with a single site, 'now'/'today'/'yesterday'/'yesterdaySameTime' and
     *                                 'last week/month/year' use the site timezone; otherwise they use UTC. Other
     *                                 formats (including 'tomorrow') are parsed in UTC. In ranges, the end date uses
     *                                 that timezone; the start date is parsed in UTC.
     * @param string|false $segment Segment definition string to filter visits, or false for no segment.
     * @return DataTable|DataTable\Map Report of visits by OS version, or a map when multiple sites/periods are queried.
     */
    public function getOsVersions($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable('DevicesDetection_osVersions', $idSite, $period, $date, $segment);

        $segments = ['operatingSystemCode', 'operatingSystemVersion'];
        $dataTable->filter('AddSegmentByLabel', [$segments, Archiver::BROWSER_SEPARATOR]);
        $dataTable->filter('ColumnCallbackAddMetadata', ['label', 'logo', __NAMESPACE__ . '\getOsLogo']);
        // use GroupBy filter to avoid duplicate rows if old (UserSettings) and new (DevicesDetection) reports were combined
        $dataTable->filter('GroupBy', ['label', __NAMESPACE__ . '\getOsFullName']);
        return $dataTable;
    }

    /**
     * Gets datatable displaying number of visits by Browser (Without version)
     * @param int $idSite Site ID to query.
     * @param string $period Period identifier enabled for the API. Built-ins include 'day', 'week', 'month', 'year',
     *                       and 'range'; plugin-defined period identifiers may also be enabled. Required, not null.
     * @param \Piwik\Date|string $date Date selector. Single dates can be 'YYYY-MM-DD', 'YYYY-MM-DD HH:MM:SS', a unix
     *                                 timestamp, or a strtotime-compatible string without a comma. Keywords supported
     *                                 are 'now', 'today', 'tomorrow', 'yesterday', 'yesterdaySameTime',
     *                                 'last week'/'last-week', 'last month'/'last-month', 'last year'/'last-year'.
     *                                 Multiple-period selectors include 'lastN' or 'previousN' (N optional integer),
     *                                 or a range 'YYYY-MM-DD,YYYY-MM-DD' or
     *                                 'last week|month|year, today|now|yesterday|last week|month|year'. When $period
     *                                 is 'range', the date must be a range or a last/previous selector.
     *                                 Timezone: with a single site, 'now'/'today'/'yesterday'/'yesterdaySameTime' and
     *                                 'last week/month/year' use the site timezone; otherwise they use UTC. Other
     *                                 formats (including 'tomorrow') are parsed in UTC. In ranges, the end date uses
     *                                 that timezone; the start date is parsed in UTC.
     * @param string|false $segment Segment definition string to filter visits, or false for no segment.
     * @return DataTable\DataTableInterface Report of visits by browser family.
     */
    public function getBrowsers($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable('DevicesDetection_browsers', $idSite, $period, $date, $segment);
        $availableBrowsers = BrowserParser::getAvailableBrowsers();
        $dataTable->filter('AddSegmentValue', [function ($label) use ($availableBrowsers) {
            if (!array_key_exists($label, $availableBrowsers) && $label !== 'UNK') {
                return false;
            }
            return $label;
        }]);

        // handle legacy archives
        if ($dataTable instanceof DataTable\Map || !$dataTable->getRowsCount()) {
            $versionDataTable = $this->getDataTable('DevicesDetection_browserVersions', $idSite, $period, $date, $segment);
            $dataTable = $this->mergeDataTables($dataTable, $versionDataTable);
        }

        $dataTable->filter('GroupBy', ['label', __NAMESPACE__ . '\getBrowserName']);
        $dataTable->filter('ColumnCallbackAddMetadata', ['label', 'logo', __NAMESPACE__ . '\getBrowserFamilyLogo']);
        return $dataTable;
    }

    /**
     * Gets datatable displaying number of visits by Browser version (eg. Firefox 20, Safari 6.0)
     * @param int $idSite Site ID to query.
     * @param string $period Period identifier enabled for the API. Built-ins include 'day', 'week', 'month', 'year',
     *                       and 'range'; plugin-defined period identifiers may also be enabled. Required, not null.
     * @param \Piwik\Date|string $date Date selector. Single dates can be 'YYYY-MM-DD', 'YYYY-MM-DD HH:MM:SS', a unix
     *                                 timestamp, or a strtotime-compatible string without a comma. Keywords supported
     *                                 are 'now', 'today', 'tomorrow', 'yesterday', 'yesterdaySameTime',
     *                                 'last week'/'last-week', 'last month'/'last-month', 'last year'/'last-year'.
     *                                 Multiple-period selectors include 'lastN' or 'previousN' (N optional integer),
     *                                 or a range 'YYYY-MM-DD,YYYY-MM-DD' or
     *                                 'last week|month|year, today|now|yesterday|last week|month|year'. When $period
     *                                 is 'range', the date must be a range or a last/previous selector.
     *                                 Timezone: with a single site, 'now'/'today'/'yesterday'/'yesterdaySameTime' and
     *                                 'last week/month/year' use the site timezone; otherwise they use UTC. Other
     *                                 formats (including 'tomorrow') are parsed in UTC. In ranges, the end date uses
     *                                 that timezone; the start date is parsed in UTC.
     * @param string|false $segment Segment definition string to filter visits, or false for no segment.
     * @return DataTable|DataTable\Map Report of visits by browser version, or a map when multiple sites/periods are queried.
     */
    public function getBrowserVersions($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable('DevicesDetection_browserVersions', $idSite, $period, $date, $segment);

        $segments = ['browserCode', 'browserVersion'];
        $dataTable->filter('AddSegmentByLabel', [$segments, Archiver::BROWSER_SEPARATOR]);
        $dataTable->filter('ColumnCallbackAddMetadata', ['label', 'logo', __NAMESPACE__ . '\getBrowserLogo']);
        $dataTable->filter('ColumnCallbackReplace', ['label', __NAMESPACE__ . '\getBrowserNameWithVersion']);
        return $dataTable;
    }

    /**
     * Gets datatable displaying number of visits by Browser engine (eg. Trident, Gecko, Blink,...)
     * @param int $idSite Site ID to query.
     * @param string $period Period identifier enabled for the API. Built-ins include 'day', 'week', 'month', 'year',
     *                       and 'range'; plugin-defined period identifiers may also be enabled. Required, not null.
     * @param \Piwik\Date|string $date Date selector. Single dates can be 'YYYY-MM-DD', 'YYYY-MM-DD HH:MM:SS', a unix
     *                                 timestamp, or a strtotime-compatible string without a comma. Keywords supported
     *                                 are 'now', 'today', 'tomorrow', 'yesterday', 'yesterdaySameTime',
     *                                 'last week'/'last-week', 'last month'/'last-month', 'last year'/'last-year'.
     *                                 Multiple-period selectors include 'lastN' or 'previousN' (N optional integer),
     *                                 or a range 'YYYY-MM-DD,YYYY-MM-DD' or
     *                                 'last week|month|year, today|now|yesterday|last week|month|year'. When $period
     *                                 is 'range', the date must be a range or a last/previous selector.
     *                                 Timezone: if exactly one site is requested, relative keywords are evaluated in
     *                                 that site's timezone; otherwise they are evaluated in UTC. For range strings,
     *                                 the relative end date uses that timezone; start dates are parsed as absolute
     *                                 dates.
     * @param string|false $segment Segment definition string to filter visits, or false for no segment.
     * @return DataTable|DataTable\Map Report of visits by browser engine, or a map when multiple sites/periods are queried.
     */
    public function getBrowserEngines($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable('DevicesDetection_browserEngines', $idSite, $period, $date, $segment);
        $dataTable->filter('AddSegmentValue');
        // use GroupBy filter to avoid duplicate rows if old (UserSettings) and new (DevicesDetection) reports were combined
        $dataTable->filter('GroupBy', ['label',  __NAMESPACE__ . '\getBrowserEngineName']);
        return $dataTable;
    }
}
