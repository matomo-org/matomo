<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicesDetection;

use DeviceDetector\DeviceDetector;
use Piwik\Common;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Plugin\ControllerAdmin;
use Piwik\View;

class Controller extends \Piwik\Plugin\Controller
{
    public function index()
    {
        $view = new View('@DevicesDetection/index');
        $view->deviceTypes = $view->deviceModels = $view->deviceBrands = $view->osReport = $view->browserReport = "blank";
        $view->deviceTypes = $this->getType(true);
        $view->deviceBrands = $this->getBrand(true);
        $view->deviceModels = $this->getModel(true);
        $view->osReport = $this->getOsFamilies(true);
        $view->browserReport = $this->getBrowserFamilies(true);
        return $view->render();
    }

    public function getType()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getBrand()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getModel()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getOsFamilies()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getOsVersions()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getBrowserFamilies()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getBrowserVersions()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function deviceDetection()
    {
        Piwik::checkUserHasSomeAdminAccess();

        $view = new View('@DevicesDetection/detection');
        $this->setBasicVariablesView($view);
        ControllerAdmin::setBasicVariablesAdminView($view);

        $userAgent = Common::getRequestVar('ua', $_SERVER['HTTP_USER_AGENT'], 'string');

        $uaParser = new DeviceDetector($userAgent);
        $uaParser->parse();

        $view->userAgent           = $userAgent;
        $view->browser_name        = $uaParser->getClient('name');
        $view->browser_short_name  = $uaParser->getClient('short_name');
        $view->browser_version     = $uaParser->getClient('version');
        $view->browser_logo        = getBrowserLogoExtended($uaParser->getClient('short_name'));
        $view->browser_family      = \DeviceDetector\Parser\Client\Browser::getBrowserFamily($uaParser->getClient('short_name'));
        $view->browser_family_logo = getBrowserFamilyLogoExtended($view->browser_family);
        $view->os_name             = $uaParser->getOs('name');
        $view->os_logo             = getOsLogoExtended($uaParser->getOs('short_name'));
        $view->os_short_name       = $uaParser->getOs('short_name');
        $view->os_family           = \DeviceDetector\Parser\OperatingSystem::getOsFamily($uaParser->getOs('short_name'));
        $view->os_family_logo      = getOsFamilyLogoExtended($view->os_family);
        $view->os_version          = $uaParser->getOs('version');
        $view->device_type         = getDeviceTypeLabel($uaParser->getDeviceName());
        $view->device_type_logo    = getDeviceTypeLogo($uaParser->getDeviceName());
        $view->device_model        = $uaParser->getModel();
        $view->device_brand        = getDeviceBrandLabel($uaParser->getBrand());
        $view->device_brand_logo   = getBrandLogo($uaParser->getBrand());

        return $view->render();
    }

    public function showList()
    {
        Piwik::checkUserHasSomeAdminAccess();

        $view = new View('@DevicesDetection/list');

        $type = Common::getRequestVar('type', 'brands', 'string');

        $list = array();

        switch ($type) {
            case 'brands':
                $availableBrands = \DeviceDetector\Parser\Device\DeviceParserAbstract::$deviceBrands;

                foreach ($availableBrands AS $short => $name) {
                    if ($name != 'Unknown') {
                        $list[$name] = getBrandLogo($name);
                    }
                }
                break;

            case 'browsers':
                $availableBrowsers = \DeviceDetector\Parser\Client\Browser::getAvailableBrowsers();

                foreach ($availableBrowsers AS $short => $name) {
                    $list[$name] = getBrowserLogoExtended($short);
                }
                break;

            case 'browserfamilies':
                $availableBrowserFamilies = \DeviceDetector\Parser\Client\Browser::getAvailableBrowserFamilies();

                foreach ($availableBrowserFamilies AS $name => $browsers) {
                    $list[$name] = getBrowserFamilyLogoExtended($name);
                }
                break;

            case 'os':
                $availableOSs = \DeviceDetector\Parser\OperatingSystem::getAvailableOperatingSystems();

                foreach ($availableOSs AS $short => $name) {
                    $list[$name] = getOsLogoExtended($short);
                }
                break;

            case 'osfamilies':
                $osFamilies = \DeviceDetector\Parser\OperatingSystem::getAvailableOperatingSystemFamilies();

                foreach ($osFamilies AS $name => $oss) {
                    $list[$name] = getOsFamilyLogoExtended($name);
                }
                break;

            case 'devicetypes':
                $deviceTypes = \DeviceDetector\Parser\Device\DeviceParserAbstract::getAvailableDeviceTypes();

                foreach ($deviceTypes AS $name => $id) {
                    $list[$name] = getDeviceTypeLogo($name);
                }
                break;
        }

        $view->itemList = $list;

        return $view->render();
    }
}
