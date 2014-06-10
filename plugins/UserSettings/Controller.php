<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserSettings;

use Piwik\View;

/**
 *
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

        return $view->render();
    }

    public function getResolution()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getConfiguration()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getOS()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getOSFamily()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getMobileVsDesktop()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getBrowserVersion()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getBrowser()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getBrowserType()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getWideScreen()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getPlugin()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getLanguage()
    {
        return $this->renderReport(__FUNCTION__);
    }
}
