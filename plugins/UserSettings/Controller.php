<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package UserSettings
 */
namespace Piwik\Plugins\UserSettings;

use Piwik\View;
use Piwik\ViewDataTable\Factory;

/**
 *
 * @package UserSettings
 */
class Controller extends \Piwik\Plugin\Controller
{
    public function index()
    {
        $view = new View('@UserSettings/index');

        $view->dataTablePlugin = $this->getPlugin(true);
        $view->dataTableResolution = $this->getResolution(true);
        $view->dataTableConfiguration = $this->getConfiguration(true);
        $view->dataTableOS = $this->getOS(true);
        $view->dataTableBrowser = $this->getBrowser(true);
        $view->dataTableBrowserType = $this->getBrowserType(true);
        $view->dataTableMobileVsDesktop = $this->getMobileVsDesktop(true);
        $view->dataTableBrowserLanguage = $this->getLanguage(true);

        echo $view->render();
    }

    public function getResolution($fetch = false)
    {
        return Factory::renderReport($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getConfiguration($fetch = false)
    {
        return Factory::renderReport($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getOS($fetch = false)
    {
        return Factory::renderReport($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getOSFamily($fetch = false)
    {
        return Factory::renderReport($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getMobileVsDesktop($fetch = false)
    {
        return Factory::renderReport($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getBrowserVersion($fetch = false)
    {
        return Factory::renderReport($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getBrowser($fetch = false)
    {
        return Factory::renderReport($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getBrowserType($fetch = false)
    {
        return Factory::renderReport($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getWideScreen($fetch = false)
    {
        return Factory::renderReport($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getPlugin($fetch = false)
    {
        return Factory::renderReport($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getLanguage($fetch = false)
    {
        return Factory::renderReport($this->pluginName, __FUNCTION__, $fetch);
    }
}
