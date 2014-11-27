<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserSettings;

use Piwik\Plugins\UserSettings\Reports\GetConfiguration;
use Piwik\Plugins\UserSettings\Reports\GetLanguage;
use Piwik\Plugins\UserSettings\Reports\GetPlugin;
use Piwik\Plugins\UserSettings\Reports\GetResolution;
use Piwik\Plugins\UserSettings\Reports\GetScreenType;
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
        $view->dataTableScreenType = $this->renderReport(new GetScreenType());
        $view->dataTableBrowserLanguage = $this->renderReport(new GetLanguage());

        return $view->render();
    }
}
