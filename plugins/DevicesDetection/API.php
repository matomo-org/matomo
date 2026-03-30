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
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Piwik;
use DeviceDetector\Parser\Client\Browser as BrowserParser;
use Piwik\Site;

/**
 * The DevicesDetection API lets you access reports about your visitors' device types, brands, models,
 * operating systems, and browsers.
 *
 * @method static \Piwik\Plugins\DevicesDetection\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * @param int|string|int[] $idSite
     * @param string|null|false $segment
     * @return DataTable|DataTable\Map
     */
    protected function getDataTable(string $name, $idSite, string $period, string $date, $segment)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $archive = Archive::build($idSite, $period, $date, $segment);
        $dataTable = $archive->getDataTable($name);
        $dataTable->queueFilter('ReplaceColumnNames');
        $dataTable->queueFilter('ReplaceSummaryRowLabel');
        return $dataTable;
    }

    /**
     * Returns visit information grouped by device type.
     *
     * This report always includes every device type Matomo can detect, even if a type has zero visits.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map The device type report with segment metadata for each device type.
     */
    public function getType($idSite, string $period, string $date, $segment = false)
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

    /**
     * @param DataTable|DataTable\Map $dataTable
     */
    protected function ensureDefaultRowsInTable($dataTable): void
    {
        $requiredRows = array_fill(0, count(AbstractDeviceParser::getAvailableDeviceTypes()), Metrics::INDEX_NB_VISITS);

        $dataTable->filter(function (DataTable $table) use ($requiredRows) {
            if ($table->getRowsCount() == 0) {
                return;
            }
            foreach ($requiredRows as $requiredRow => $key) {
                $row = $table->getRowFromLabel((string)$requiredRow);
                if (empty($row)) {
                    $table->addRowsFromSimpleArray([
                        ['label' => $requiredRow, $key => 0],
                    ]);
                }
            }
        });
    }

    /**
     * Returns visit information grouped by device brand.
     *
     * Most device brand information is only available for non-desktop devices.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map The device brand report with segment metadata for each detected brand.
     */
    public function getBrand($idSite, string $period, string $date, $segment = false)
    {
        $dataTable = $this->getDataTable('DevicesDetection_brands', $idSite, $period, $date, $segment);
        $dataTable->filter('GroupBy', ['label', __NAMESPACE__ . '\getDeviceBrandLabel']);
        $dataTable->filter('ColumnCallbackAddMetadata', ['label', 'logo', __NAMESPACE__ . '\getBrandLogo']);
        $dataTable->filter('AddSegmentByLabel', ['deviceBrand']);
        return $dataTable;
    }

    /**
     * Returns visit information grouped by device model.
     *
     * This report is unavailable when device model detection is disabled by compliance policy.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map The device model report with segment metadata for each brand/model pair.
     */
    public function getModel($idSite, string $period, string $date, $segment = false)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $idSites = Site::getIdSitesFromIdSitesString($idSite);

        // filter sites where model report disabled by compliance
        $idSitesFiltered = array_filter($idSites, function ($idSite) {
            return !DevicesDetection::isDeviceModelDetectionDisabledByCompliancePolicy((int)$idSite);
        });

        // show an error only if none of the requested sites is left
        if (count($idSitesFiltered) === 0 && count($idSites) !== count($idSitesFiltered)) {
            throw new Exception(Piwik::translate('DevicesDetection_DeviceModelReportDisabledByCompliancePolicy'));
        }

        $dataTable = $this->getDataTable('DevicesDetection_models', $idSitesFiltered, $period, $date, $segment);

        $dataTable->filter(function (DataTable $table) {
            foreach ($table->getRowsWithoutSummaryRow() as $row) {
                /** @var string $label */
                $label = $row->getColumn('label');

                if (strpos($label, ';') !== false) {
                    [$brand, $model] = explode(';', $label, 2);
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
     * Returns visit information grouped by operating system family.
     *
     * For legacy archives, this report falls back to operating system version data when needed.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map The operating system family report with grouped family labels and logos.
     */
    public function getOsFamilies($idSite, string $period, string $date, $segment = false)
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
     * @template T of DataTable|DataTable\Map
     *
     * @param T $dataTable
     * @param T $dataTable2
     * @return T
     */
    protected function mergeDataTables(DataTable\DataTableInterface $dataTable, DataTable\DataTableInterface $dataTable2)
    {
        if ($dataTable instanceof DataTable\Map && $dataTable2 instanceof DataTable\Map) {
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
     * Returns visit information grouped by operating system version.
     *
     * Each row includes segment metadata for the operating system code and version.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map The operating system version report with segment metadata and logos.
     */
    public function getOsVersions($idSite, string $period, string $date, $segment = false)
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
     * Returns visit information grouped by browser family without version numbers.
     *
     * For legacy archives, this report falls back to browser version data when needed.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map The browser family report with grouped browser names and logos.
     */
    public function getBrowsers($idSite, string $period, string $date, $segment = false)
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
     * Returns visit information grouped by browser version.
     *
     * Each row includes segment metadata for the browser code and detected version.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map The browser version report with rewritten labels and logos.
     */
    public function getBrowserVersions($idSite, string $period, string $date, $segment = false)
    {
        $dataTable = $this->getDataTable('DevicesDetection_browserVersions', $idSite, $period, $date, $segment);

        $segments = ['browserCode', 'browserVersion'];
        $dataTable->filter('AddSegmentByLabel', [$segments, Archiver::BROWSER_SEPARATOR]);
        $dataTable->filter('ColumnCallbackAddMetadata', ['label', 'logo', __NAMESPACE__ . '\getBrowserLogo']);
        $dataTable->filter('ColumnCallbackReplace', ['label', __NAMESPACE__ . '\getBrowserNameWithVersion']);
        return $dataTable;
    }

    /**
     * Returns visit information grouped by browser engine.
     *
     * Row labels are normalized to the detected browser engine names.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map The browser engine report with grouped engine names.
     */
    public function getBrowserEngines($idSite, string $period, string $date, $segment = false)
    {
        $dataTable = $this->getDataTable('DevicesDetection_browserEngines', $idSite, $period, $date, $segment);
        $dataTable->filter('AddSegmentValue');
        // use GroupBy filter to avoid duplicate rows if old (UserSettings) and new (DevicesDetection) reports were combined
        $dataTable->filter('GroupBy', ['label',  __NAMESPACE__ . '\getBrowserEngineName']);
        return $dataTable;
    }
}
