<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package DevicesDetection
 */

namespace Piwik\Plugins\DevicesDetection;

use Exception;
use Piwik\ArchiveProcessor;

use Piwik\Common;
use Piwik\Config;
use Piwik\Db;
use Piwik\Menu\MenuMain;
use Piwik\Piwik;
use Piwik\WidgetsList;
use UserAgentParserEnhanced;

require_once PIWIK_INCLUDE_PATH . "/plugins/DevicesDetection/UserAgentParserEnhanced/UserAgentParserEnhanced.php";
require_once PIWIK_INCLUDE_PATH . '/plugins/DevicesDetection/functions.php';

class DevicesDetection extends \Piwik\Plugin
{
    /**
     * @see Piwik_Plugin::getInformation
     */
    public function getInformation()
    {
        return array(
            'description'     => "[Beta Plugin] " . Piwik::translate("DevicesDetection_PluginDescription"),
            'author'          => 'Piwik PRO',
            'author_homepage' => 'http://piwik.pro',
            'version'         => '1.14',
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
            'DevicesDetection.getBrowserFamilies' => Piwik::translate('DevicesDetection_BrowsersFamily'),
            'DevicesDetection.getBrowserVersions' => Piwik::translate('DevicesDetection_BrowserVersions')
        );
    }

    protected function getRawMetadataDeviceType()
    {
        $deviceTypeList = implode(", ", UserAgentParserEnhanced::$deviceTypes);

        $deviceTypeLabelToCode = function ($type) use ($deviceTypeList) {
            $index = array_search(strtolower(trim(urldecode($type))), UserAgentParserEnhanced::$deviceTypes);
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
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'ArchiveProcessor.Day.compute'             => 'archiveDay',
            'ArchiveProcessor.Period.compute'          => 'archivePeriod',
            'Menu.Reporting.addItems'                  => 'addMenu',
            'Tracker.newVisitorInformation'            => 'parseMobileVisitData',
            'WidgetsList.addWidgets'                   => 'addWidgets',
            'API.getReportMetadata'                    => 'getReportMetadata',
            'API.getSegmentsMetadata'                  => 'getSegmentsMetadata',
            'Visualization.getReportDisplayProperties' => 'getReportDisplayProperties',
        );
    }

    /**
     * Defines API reports.
     * Also used to define Widgets, and Segment(s)
     *
     * @return array Category, Report Name, API Module, API action, Translated column name, & optional segment info
     *
     */
    protected function getRawMetadataReports()
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
                'DevicesDetection_BrowsersFamily',
                'DevicesDetection',
                'getBrowserFamilies',
                'DevicesDetection_BrowsersFamily',
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

    public function addWidgets()
    {
        foreach ($this->getRawMetadataReports() as $report) {
            list($category, $name, $controllerName, $controllerAction) = $report;
            if ($category == false)
                continue;
            WidgetsList::add($category, $name, $controllerName, $controllerAction);
        }
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
                'sqlFilter'      => isset($sqlFilter) ? $sqlFilter : false,
            );
        }
    }

    public function getReportMetadata(&$reports)
    {
        $i = 0;
        foreach ($this->getRawMetadataReports() as $report) {
            list($category, $name, $apiModule, $apiAction, $columnName) = $report;
            if ($category == false)
                continue;

            $report = array(
                'category'  => Piwik::translate($category),
                'name'      => Piwik::translate($name),
                'module'    => $apiModule,
                'action'    => $apiAction,
                'dimension' => Piwik::translate($columnName),
                'order'     => $i++
            );

            $translation = $name . 'Documentation';
            $translated = Piwik::translate($translation, '<br />');
            if ($translated != $translation) {
                $report['documentation'] = $translated;
            }

            $reports[] = $report;
        }
    }

    public function install()
    {
// we catch the exception
        try {
            $q1 = "ALTER TABLE `" . Common::prefixTable("log_visit") . "`
                ADD `config_os_version` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `config_os` ,
                ADD `config_device_type` TINYINT( 100 ) NULL DEFAULT NULL AFTER `config_browser_version` ,
                ADD `config_device_brand` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `config_device_type` ,
                ADD `config_device_model` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `config_device_brand`";
            Db::exec($q1);
            // conditionaly add this column
            if (@Config::getInstance()->Debug['store_user_agent_in_visit']) {
                $q2 = "ALTER TABLE `" . Common::prefixTable("log_visit") . "`
                ADD `config_debug_ua` VARCHAR( 512 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `config_device_model`";
                Db::exec($q2);
            }
        } catch (Exception $e) {
            if (!Db::get()->isErrNo($e, '1060')) {
                throw $e;
            }
        }
    }

    public function parseMobileVisitData(&$visitorInfo, $extraInfo)
    {
        $userAgent = $extraInfo['UserAgent'];

        $UAParser = new UserAgentParserEnhanced($userAgent);
        $UAParser->parse();
        $deviceInfo['config_browser_name'] = $UAParser->getBrowser("short_name");
        $deviceInfo['config_browser_version'] = $UAParser->getBrowser("version");
        $deviceInfo['config_os'] = $UAParser->getOs("short_name");
        $deviceInfo['config_os_version'] = $UAParser->getOs("version");
        $deviceInfo['config_device_type'] = $UAParser->getDevice();
        $deviceInfo['config_device_model'] = $UAParser->getModel();
        $deviceInfo['config_device_brand'] = $UAParser->getBrand();

        if (@Config::getInstance()->Debug['store_user_agent_in_visit']) {
            $deviceInfo['config_debug_ua'] = $userAgent;
        }

        $visitorInfo = array_merge($visitorInfo, $deviceInfo);
        Common::printDebug("Device Detection:");
        Common::printDebug($deviceInfo);
    }

    public function archiveDay(ArchiveProcessor\Day $archiveProcessor)
    {
        $archiving = new Archiver($archiveProcessor);
        if ($archiving->shouldArchive()) {
            $archiving->archiveDay();
        }
    }

    public function archivePeriod(ArchiveProcessor\Period $archiveProcessor)
    {
        $archiving = new Archiver($archiveProcessor);
        if ($archiving->shouldArchive()) {
            $archiving->archivePeriod();
        }
    }

    public function addMenu()
    {
        MenuMain::getInstance()->add('General_Visitors', 'DevicesDetection_submenu', array('module' => 'DevicesDetection', 'action' => 'index'));
    }

    public function getReportDisplayProperties(&$properties)
    {
        $properties['DevicesDetection.getType'] = $this->getDisplayPropertiesForGetType();
        $properties['DevicesDetection.getBrand'] = $this->getDisplayPropertiesForGetBrand();
        $properties['DevicesDetection.getModel'] = $this->getDisplayPropertiesForGetModel();
        $properties['DevicesDetection.getOsFamilies'] = $this->getDisplayPropertiesForGetOsFamilies();
        $properties['DevicesDetection.getOsVersions'] = $this->getDisplayPropertiesForGetOsVersions();
        $properties['DevicesDetection.getBrowserFamilies'] = $this->getDisplayPropertiesForGetBrowserFamilies();
        $properties['DevicesDetection.getBrowserVersions'] = $this->getDisplayPropertiesForGetBrowserVersions();
    }

    private function getDisplayPropertiesForGetType()
    {
        return array(
            'show_search'                 => false,
            'show_exclude_low_population' => false,
            'translations'                => array('label' => Piwik::translate("DevicesDetection_dataTableLabelTypes"))
        );
    }

    private function getDisplayPropertiesForGetBrand()
    {
        return array(
            'show_search'                 => false,
            'show_exclude_low_population' => false,
            'translations'                => array('label' => Piwik::translate("DevicesDetection_dataTableLabelBrands"))
        );
    }

    private function getDisplayPropertiesForGetModel()
    {
        return array(
            'show_search'                 => false,
            'show_exclude_low_population' => false,
            'translations'                => array('label' => Piwik::translate("DevicesDetection_dataTableLabelModels"))
        );
    }

    private function getDisplayPropertiesForGetOsFamilies()
    {
        return array(
            'show_search'                 => false,
            'show_exclude_low_population' => false,
            'translations'                => array('label' => Piwik::translate("DevicesDetection_dataTableLabelSystemFamily")),
            'title'                       => Piwik::translate('DevicesDetection_OperatingSystemFamilies'),
            'related_reports'             => $this->getOsRelatedReports()
        );
    }

    private function getDisplayPropertiesForGetOsVersions()
    {
        return array(
            'show_search'                 => false,
            'show_exclude_low_population' => false,
            'translations'                => array('label' => Piwik::translate("DevicesDetection_dataTableLabelSystemVersion")),
            'title'                       => Piwik::translate('DevicesDetection_OperatingSystemVersions'),
            'related_reports'             => $this->getOsRelatedReports()
        );
    }

    private function getDisplayPropertiesForGetBrowserFamilies()
    {
        return array(
            'show_search'                 => false,
            'show_exclude_low_population' => false,
            'translations'                => array('label' => Piwik::translate("DevicesDetection_dataTableLabelBrowserFamily")),
            'title'                       => Piwik::translate('DevicesDetection_BrowsersFamily'),
            'related_reports'             => $this->getBrowserRelatedReports()
        );
    }

    private function getDisplayPropertiesForGetBrowserVersions()
    {
        return array(
            'show_search'                 => false,
            'show_exclude_low_population' => false,
            'translations'                => array('label' => Piwik::translate("DevicesDetection_dataTableLabelBrowserVersion")),
            'related_reports'             => $this->getBrowserRelatedReports()
        );
    }

    private function getOsRelatedReports()
    {
        return array(
            'DevicesDetection.getOsFamilies' => Piwik::translate('DevicesDetection_OperatingSystemFamilies'),
            'DevicesDetection.getOsVersions' => Piwik::translate('DevicesDetection_OperatingSystemVersions')
        );
    }

    private function getBrowserRelatedReports()
    {
        return array(
            'DevicesDetection.getBrowserFamilies' => Piwik::translate('DevicesDetection_BrowsersFamily'),
            'DevicesDetection.getBrowserVersions' => Piwik::translate('DevicesDetection_BrowserVersions')
        );
    }
}
