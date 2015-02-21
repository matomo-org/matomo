<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserSettings;

use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugins\Resolution\Reports\GetConfiguration;
use Piwik\Plugins\UserLanguage\Reports\GetLanguage;
use Piwik\Plugins\DevicePlugins\Reports\GetPlugin;
use Piwik\Plugins\Resolution\Reports\GetResolution;
use Piwik\View;

/**
 *
 */
class Controller extends \Piwik\Plugin\Controller
{
    public function index()
    {
        $view = new View('@UserSettings/index');

        $isDeviceDetectionEnabled = PluginManager::getInstance()->isPluginActivated('DevicePlugins');
        if ($isDeviceDetectionEnabled) {
            $view->dataTablePlugin = $this->renderReport(new GetPlugin());
        }

        $isResolutionEnabled = PluginManager::getInstance()->isPluginActivated('Resolution');
        if ($isResolutionEnabled) {
            $view->dataTableResolution = $this->renderReport(new GetResolution());
            $view->dataTableConfiguration = $this->renderReport(new GetConfiguration());
        }

        return $view->render();
    }
}
