<?php

/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_DevicesDetection
 */
class Piwik_DevicesDetection_Controller extends Piwik_Controller
{

    /** The set of related reports displayed under the 'Operating Systems' header. */
    private $osRelatedReports = null;
    private $browserRelatedReports = null;

    public function __construct()
    {
        parent::__construct();
        $this->osRelatedReports = array(
            'DevicesDetection.getOsFamilies' => Piwik_Translate('DeviceDetection_OperatingSystemFamilies'),
            'DevicesDetection.getOsVersions' => Piwik_Translate('DeviceDetection_OperatingSystemVersions')
        );
        $this->browserRelatedReports = array(
            'DevicesDetection.getBrowserFamilies' => Piwik_Translate('DevicesDetection_BrowsersFamily'),
            'DevicesDetection.getBrowserVersions' => Piwik_Translate('DevicesDetection_BrowserVersions')
        );
    }

    public function index($fetch = false)
    {
        $view = Piwik_View::factory('index');
        $view->deviceTypes = $view->deviceModels = $view->deviceBrands = $view->osReport = $view->browserReport = "blank";
        $view->deviceTypes = $this->getType(true);
        $view->deviceBrands = $this->getBrand(true);
        $view->deviceModels = $this->getModel(true);
        $view->osReport = $this->getOsFamilies(true);
        $view->browserReport = $this->getBrowserFamilies(true);
        echo $view->render();
    }

    public function getType($fetch = false)
    {
        $view = $this->getStandardDataTableUserSettings(
                __FUNCTION__, 'DevicesDetection.getType'
        );

        $view->setColumnTranslation('label', Piwik_Translate("DevicesDetection_dataTableLabelTypes"));
        return $this->renderView($view, $fetch);
    }

    public function getBrand($fetch = false)
    {
        $view = $this->getStandardDataTableUserSettings(
                __FUNCTION__, 'DevicesDetection.getBrand'
        );

        $view->setColumnTranslation('label', Piwik_Translate("DevicesDetection_dataTableLabelBrands"));
        return $this->renderView($view, $fetch);
    }

    public function getModel($fetch = false)
    {
        $view = $this->getStandardDataTableUserSettings(
                __FUNCTION__, 'DevicesDetection.getModel'
        );

        $view->setColumnTranslation('label', Piwik_Translate("DevicesDetection_dataTableLabelModels"));

        return $this->renderView($view, $fetch);
    }

    public function getOsFamilies($fetch = false)
    {
        $view = $this->getStandardDataTableUserSettings(
                __FUNCTION__, 'DevicesDetection.getOsFamilies'
        );

        $view->setColumnTranslation('label', Piwik_Translate("DevicesDetection_dataTableLabelSystemFamily"));
        $view->addRelatedReports(Piwik_Translate('DeviceDetection_OperatingSystemFamilies'), $this->osRelatedReports);
        return $this->renderView($view, $fetch);
    }

    public function getOsVersions($fetch = false)
    {
        $view = $this->getStandardDataTableUserSettings(
                __FUNCTION__, 'DevicesDetection.getOsVersions'
        );

        $view->setColumnTranslation('label', Piwik_Translate("DevicesDetection_dataTableLabelSystemVersion"));
        $view->addRelatedReports(Piwik_Translate('DeviceDetection_OperatingSystemVersions'), $this->osRelatedReports);
        return $this->renderView($view, $fetch);
    }

    public function getBrowserFamilies($fetch = false)
    {
        $view = $this->getStandardDataTableUserSettings(
                __FUNCTION__, 'DevicesDetection.getBrowserFamilies'
        );

        $view->setColumnTranslation('label', Piwik_Translate("DevicesDetection_dataTableLabelBrowserFamily"));
        $view->addRelatedReports(Piwik_Translate('DevicesDetection_BrowsersFamily'), $this->browserRelatedReports);
        return $this->renderView($view, $fetch);
    }

    public function getBrowserVersions($fetch = false)
    {
        $view = $this->getStandardDataTableUserSettings(
                __FUNCTION__, 'DevicesDetection.getBrowserVersions'
        );

        $view->setColumnTranslation('label', Piwik_Translate("DevicesDetection_dataTableLabelBrowserVersion"));
        $view->addRelatedReports(Piwik_Translate('DevicesDetection_BrowserVersions'), $this->browserRelatedReports);
        return $this->renderView($view, $fetch);
    }

    protected function getStandardDataTableUserSettings($currentControllerAction, $APItoCall, $defaultDatatableType = null)
    {
        $view = Piwik_ViewDataTable::factory($defaultDatatableType);
        $view->init($this->pluginName, $currentControllerAction, $APItoCall);
        $view->disableSearchBox();
        $view->disableExcludeLowPopulation();
        $this->setPeriodVariablesView($view);
        $this->setMetricsVariablesView($view);
        return $view;
    }

    /**
     * You may manually call this controller action to force re-processing of past user agents
     */
    public function refreshParsedUserAgents()
    {
        $q = "SELECT idvisit, config_debug_ua FROM " . Piwik_Common::prefixTable("log_visit");
        $res = Piwik_FetchAll($q);
        foreach ($res as $rec) {
            $UAParser = new UserAgentParserEnhanced($rec['config_debug_ua']);
            $UAParser->parse();
            echo "Processing idvisit = " . $rec['idvisit'] . "<br/>";
            echo "UserAgent string: " . $rec['config_debug_ua'] . "<br/> Decoded values:";
            $uaDetails = $this->getArray($UAParser);
            var_dump($uaDetails);
            echo "<hr/>";
            $this->updateVisit($rec['idvisit'], $uaDetails);
            unset($UAParser);
        }
        echo "Please remember to truncate your archives !";
    }

    private function getArray(UserAgentParserEnhanced $UAParser)
    {
        $UADetails['config_browser_name'] = $UAParser->getBrowser("short_name");
        $UADetails['config_browser_version'] = $UAParser->getBrowser("version");
        $UADetails['config_os'] = $UAParser->getOs("short_name");
        $UADetails['config_os_version'] = $UAParser->getOs("version");
        $UADetails['config_device_type'] = $UAParser->getDevice();
        $UADetails['config_device_model'] = $UAParser->getModel();
        $UADetails['config_device_brand'] = $UAParser->getBrand();
        return $UADetails;
    }

    private function updateVisit($idVisit, $uaDetails)
    {
        $q = "UPDATE " . Piwik_Common::prefixTable("log_visit") . " SET " .
            "config_browser_name = '" . $uaDetails['config_browser_name'] . "' ," .
            "config_browser_version = '" . $uaDetails['config_browser_version'] . "' ," .
            "config_os = '" . $uaDetails['config_os'] . "' ," .
            "config_os_version = '" . $uaDetails['config_os_version'] . "' ," .
            "config_device_type =  " . (isset($uaDetails['config_device_type']) ? "'" . $uaDetails['config_device_type'] . "'" : "NULL") . " ," .
            "config_device_model = " . (isset($uaDetails['config_device_model']) ? "'" . $uaDetails['config_device_model'] . "'" : "NULL") . " ," .
            "config_device_brand = " . (isset($uaDetails['config_device_brand']) ? "'" . $uaDetails['config_device_brand'] . "'" : "NULL") . "
                    WHERE idvisit = " . $idVisit;
        Piwik_Query($q);
    }

}