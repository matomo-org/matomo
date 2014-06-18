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

        $view->dataTablePlugin = $this->renderReport('getPlugin');
        $view->dataTableResolution = $this->renderReport('getResolution');
        $view->dataTableConfiguration = $this->renderReport('getConfiguration');
        $view->dataTableOS = $this->renderReport('getOS');
        $view->dataTableBrowser = $this->renderReport('getBrowser');
        $view->dataTableBrowserType = $this->renderReport('getBrowserType');
        $view->dataTableMobileVsDesktop = $this->renderReport('getMobileVsDesktop');
        $view->dataTableBrowserLanguage = $this->renderReport('getLanguage');

        return $view->render();
    }
}
