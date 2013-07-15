<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_UserSettings
 */

/**
 *
 * @package Piwik_UserSettings
 */
class Piwik_UserSettings_Controller extends Piwik_Controller
{
    public function index()
    {
        $view = new Piwik_View('@UserSettings/index');

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
        return Piwik_ViewDataTable::render($this->pluginName, __FUNCTION__, $fetch);
    }
    
    public function getConfiguration($fetch = false)
    {
        return Piwik_ViewDataTable::render($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getOS($fetch = false)
    {
        return Piwik_ViewDataTable::render($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getOSFamily($fetch = false)
    {
        return Piwik_ViewDataTable::render($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getMobileVsDesktop($fetch = false)
    {
        return Piwik_ViewDataTable::render($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getBrowserVersion($fetch = false)
    {
        return Piwik_ViewDataTable::render($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getBrowser($fetch = false)
    {
        return Piwik_ViewDataTable::render($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getBrowserType($fetch = false)
    {
        return Piwik_ViewDataTable::render($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getWideScreen($fetch = false)
    {
        return Piwik_ViewDataTable::render($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getPlugin($fetch = false)
    {
        return Piwik_ViewDataTable::render($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getLanguage($fetch = false)
    {
        return Piwik_ViewDataTable::render($this->pluginName, __FUNCTION__, $fetch);
    }
}
