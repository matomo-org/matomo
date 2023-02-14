<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\DevicesDetection;

use DeviceDetector\Parser\Device\AbstractDeviceParser;
use Piwik\Archive;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Piwik;
use DeviceDetector\Parser\Client\Browser AS BrowserParser;

/**
 * The DevicesDetection API lets you access reports on your visitors devices, brands, models, Operating system, Browsers.
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
     * @return DataTable
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
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     * @return DataTable
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
                            ['label' => $requiredRow, $key => 0]
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Gets datatable displaying number of visits by device manufacturer name
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     * @return DataTable
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
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     * @return DataTable
     */
    public function getModel($idSite, $period, $date, $segment = false)
    {
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
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     * @return DataTable
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
     * @param DataTable\DataTableInterface $dataTable
     * @param DataTable\DataTableInterface $dataTable2
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

        } else if (!$dataTable->getRowsCount() && $dataTable2->getRowsCount()) {
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
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     * @return DataTable
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
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     * @return DataTable
     */
    public function getBrowsers($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable('DevicesDetection_browsers', $idSite, $period, $date, $segment);
        $availableBrowsers = BrowserParser::getAvailableBrowsers();
        $dataTable->filter('AddSegmentValue', [function($label) use ($availableBrowsers) {
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
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     * @return DataTable
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
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     * @return DataTable
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
