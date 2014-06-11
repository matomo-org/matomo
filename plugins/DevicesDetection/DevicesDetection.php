<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\DevicesDetection;

use DeviceDetector;
use Exception;
use Piwik\ArchiveProcessor;
use Piwik\CacheFile;
use Piwik\Common;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;

require_once PIWIK_INCLUDE_PATH . '/plugins/DevicesDetection/functions.php';

class DevicesDetection extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::getInformation
     */
    public function getInformation()
    {
        return array(
            'description'     => "[Beta Plugin] " . Piwik::translate("DevicesDetection_PluginDescription"),
            'authors'          => array(array('name' => 'Piwik PRO', 'homepage' => 'http://piwik.pro')),
            'version'         => '1.14',
            'license'          => 'GPL v3+',
            'license_homepage' => 'http://www.gnu.org/licenses/gpl.html'
        );
    }

    /** The set of related reports displayed under the 'Operating Systems' header. */
    private $osRelatedReports = null;
    private $browserRelatedReports = null;

    public function __construct()
    {
        parent::__construct();
        $this->osRelatedReports = array(
            'DevicesDetection.getOsFamilies' => Piwik::translate('DevicesDetection_OperatingSystemFamilies'),
            'DevicesDetection.getOsVersions' => Piwik::translate('DevicesDetection_OperatingSystemVersions')
        );
        $this->browserRelatedReports = array(
            'DevicesDetection.getBrowserFamilies' => Piwik::translate('UserSettings_BrowserFamilies'),
            'DevicesDetection.getBrowserVersions' => Piwik::translate('DevicesDetection_BrowserVersions')
        );
    }

    protected function getRawMetadataDeviceType()
    {
        $deviceTypeList = implode(", ", DeviceDetector::$deviceTypes);

        $deviceTypeLabelToCode = function ($type) use ($deviceTypeList) {
            $index = array_search(strtolower(trim(urldecode($type))), DeviceDetector::$deviceTypes);
            if ($index === false) {
                throw new Exception("deviceType segment must be one of: $deviceTypeList");
            }
            return $index;
        };

        return array(
            'DevicesDetection_DevicesDetection',
            'DevicesDetection_DeviceType',
            'DevicesDetection',
            'getType',
            'DevicesDetection_DeviceType',

            // Segment
            'deviceType',
            'log_visit.config_device_type',
            $deviceTypeList,
            $deviceTypeLabelToCode
        );
    }

    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'API.getSegmentDimensionMetadata' => 'getSegmentsMetadata',
        );
    }

    /**
     * Defines API reports.
     * Also used to define Widgets, and Segment(s)
     *
     * @return array Category, Report Name, API Module, API action, Translated column name, & optional segment info
     */
    public function getRawMetadataReports()
    {

        $report = array(
            // device type report (tablet, desktop, mobile...)
            $this->getRawMetadataDeviceType(),

            // device brands report
            array(
                'DevicesDetection_DevicesDetection',
                'DevicesDetection_DeviceBrand',
                'DevicesDetection',
                'getBrand',
                'DevicesDetection_DeviceBrand',
            ),
            // device model report
            array(
                'DevicesDetection_DevicesDetection',
                'DevicesDetection_DeviceModel',
                'DevicesDetection',
                'getModel',
                'DevicesDetection_DeviceModel',
            ),
            // device OS family report
            array(
                'DevicesDetection_DevicesDetection',
                'DevicesDetection_OperatingSystemFamilies',
                'DevicesDetection',
                'getOsFamilies',
                'DevicesDetection_OperatingSystemFamilies',
            ),
            // device OS version report
            array(
                'DevicesDetection_DevicesDetection',
                'DevicesDetection_OperatingSystemVersions',
                'DevicesDetection',
                'getOsVersions',
                'DevicesDetection_OperatingSystemVersions',
            ),
            // Browser family report
            array(
                'DevicesDetection_DevicesDetection',
                'UserSettings_BrowserFamilies',
                'DevicesDetection',
                'getBrowserFamilies',
                'UserSettings_BrowserFamilies',
            ),
            // Browser versions report
            array(
                'DevicesDetection_DevicesDetection',
                'DevicesDetection_BrowserVersions',
                'DevicesDetection',
                'getBrowserVersions',
                'DevicesDetection_BrowserVersions',
            ),
        );
        return $report;
    }

    /**
     * Get segments meta data
     */
    public function getSegmentsMetadata(&$segments)
    {
        // Note: only one field segmented so far: deviceType
        foreach ($this->getRawMetadataReports() as $report) {
            @list($category, $name, $apiModule, $apiAction, $columnName, $segment, $sqlSegment, $acceptedValues, $sqlFilter) = $report;

            if (empty($segment)) continue;

            $segments[] = array(
                'type'           => 'dimension',
                'category'       => Piwik::translate('General_Visit'),
                'name'           => $columnName,
                'segment'        => $segment,
                'acceptedValues' => $acceptedValues,
                'sqlSegment'     => $sqlSegment,
                'sqlFilter'      => isset($sqlFilter) ? $sqlFilter : false
            );
        }
    }

}
