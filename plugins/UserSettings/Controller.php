<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserSettings;

use Piwik\Plugins\UserSettings\Reports\GetBrowser;
use Piwik\Plugins\UserSettings\Reports\GetBrowserType;
use Piwik\Plugins\UserSettings\Reports\GetConfiguration;
use Piwik\Plugins\UserSettings\Reports\GetLanguage;
use Piwik\Plugins\UserSettings\Reports\GetMobileVsDesktop;
use Piwik\Plugins\UserSettings\Reports\GetOS;
use Piwik\Plugins\UserSettings\Reports\GetPlugin;
use Piwik\Plugins\UserSettings\Reports\GetResolution;
use Piwik\View;

/**
 *
 */
class Controller extends \Piwik\Plugin\Controller
{
    public function index()
    {
        $view = new View('@UserSettings/index');

        $view->dataTablePlugin = $this->renderReport(new GetPlugin());
        $view->dataTableResolution = $this->renderReport(new GetResolution());
        $view->dataTableConfiguration = $this->renderReport(new GetConfiguration());
        $view->dataTableOS = $this->renderReport(new GetOS());
        $view->dataTableBrowser = $this->renderReport(new GetBrowser());
        $view->dataTableBrowserType = $this->renderReport(new GetBrowserType());
        $view->dataTableMobileVsDesktop = $this->renderReport(new GetMobileVsDesktop());
        $view->dataTableBrowserLanguage = $this->renderReport(new GetLanguage());

        return $view->render();
    }
}
