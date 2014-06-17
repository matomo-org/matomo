<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\DevicesDetection;

use DeviceDetector\Parser\Device\DeviceParserAbstract AS DeviceParser;
use Exception;
use Piwik\ArchiveProcessor;
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
        $deviceTypeList = implode(", ", DeviceParser::getAvailableDeviceTypeNames());

        $deviceTypeLabelToCode = function ($type) use ($deviceTypeList) {
            $index = array_search(strtolower(trim(urldecode($type))), DeviceParser::getAvailableDeviceTypeNames());
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
            'API.getReportMetadata'           => 'getReportMetadata',
            'API.getSegmentDimensionMetadata' => 'getSegmentsMetadata',
            'ViewDataTable.configure'         => 'configureViewDataTable',
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
        try {
            $q1 = "ALTER TABLE `" . Common::prefixTable("log_visit") . "`
                ADD `config_os_version` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `config_os` ,
                ADD `config_device_type` TINYINT( 100 ) NULL DEFAULT NULL AFTER `config_browser_version` ,
                ADD `config_device_brand` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `config_device_type` ,
                ADD `config_device_model` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `config_device_brand`";
            Db::exec($q1);

        } catch (Exception $e) {
            if (!Db::get()->isErrNo($e, '1060')) {
                throw $e;
            }
        }
    }

    public function configureViewDataTable(ViewDataTable $view)
    {
        switch ($view->requestConfig->apiMethodToRequestDataTable) {
            case 'DevicesDetection.getType':
                $this->configureViewForGetType($view);
                break;
            case 'DevicesDetection.getBrand':
                $this->configureViewForGetBrand($view);
                break;
            case 'DevicesDetection.getModel':
                $this->configureViewForGetModel($view);
                break;
            case 'DevicesDetection.getOsFamilies':
                $this->configureViewForGetOsFamilies($view);
                break;
            case 'DevicesDetection.getOsVersions':
                $this->configureViewForGetOsVersions($view);
                break;
            case 'DevicesDetection.getBrowserFamilies':
                $this->configureViewForGetBrowserFamilies($view);
                break;
            case 'DevicesDetection.getBrowserVersions':
                $this->configureViewForGetBrowserVersions($view);
                break;
        }
    }

    private function configureViewForGetType(ViewDataTable $view)
    {
        $view->config->show_search = false;
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', Piwik::translate("DevicesDetection_dataTableLabelTypes"));
    }

    private function configureViewForGetBrand(ViewDataTable $view)
    {
        $view->config->show_search = false;
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', Piwik::translate("DevicesDetection_dataTableLabelBrands"));
    }

    private function configureViewForGetModel(ViewDataTable $view)
    {
        $view->config->show_search = false;
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', Piwik::translate("DevicesDetection_dataTableLabelModels"));
    }

    private function configureViewForGetOsFamilies(ViewDataTable $view)
    {
        $view->config->title = Piwik::translate('DevicesDetection_OperatingSystemFamilies');
        $view->config->show_search = false;
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', Piwik::translate("UserSettings_OperatingSystemFamily"));
        $view->config->addRelatedReports($this->getOsRelatedReports());
    }

    private function configureViewForGetOsVersions(ViewDataTable $view)
    {
        $view->config->title = Piwik::translate('DevicesDetection_OperatingSystemVersions');
        $view->config->show_search = false;
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', Piwik::translate("DevicesDetection_dataTableLabelSystemVersion"));
        $view->config->addRelatedReports($this->getOsRelatedReports());
    }

    private function configureViewForGetBrowserFamilies(ViewDataTable $view)
    {
        $view->config->title = Piwik::translate('UserSettings_BrowserFamilies');
        $view->config->show_search = false;
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', Piwik::translate("DevicesDetection_dataTableLabelBrowserFamily"));
        $view->config->addRelatedReports($this->getBrowserRelatedReports());
    }

    private function configureViewForGetBrowserVersions(ViewDataTable $view)
    {
        $view->config->show_search = false;
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', Piwik::translate("UserSettings_ColumnBrowserVersion"));
        $view->config->addRelatedReports($this->getBrowserRelatedReports());
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
            'DevicesDetection.getBrowserFamilies' => Piwik::translate('UserSettings_BrowserFamilies'),
            'DevicesDetection.getBrowserVersions' => Piwik::translate('DevicesDetection_BrowserVersions')
        );
    }
}
