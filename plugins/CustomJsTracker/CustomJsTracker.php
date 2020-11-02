<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomJsTracker;

use Piwik\Container\StaticContainer;
use Piwik\Log;
use Piwik\Plugin;

class CustomJsTracker extends Plugin
{
    public function registerEvents()
    {
        return array(
            'CoreUpdater.update.end' => 'updateTracker',
            'PluginManager.pluginActivated' => 'updateTracker',
            'PluginManager.pluginDeactivated' => 'updateTracker',
            'PluginManager.pluginInstalled' => 'updateTracker',
            'PluginManager.pluginUninstalled' => 'updateTracker',
            'Updater.componentUpdated' => 'updateTracker',
            'Controller.CoreHome.checkForUpdates.end' => 'updateTracker',
            'CustomJsTracker.updateTracker' => 'updateTracker'
        );
    }

    public function updateTracker()
    {
        try {
            if (Plugin\Manager::getInstance()->isPluginActivated('CustomJsTracker')) {
                $trackerUpdater = StaticContainer::get('Piwik\Plugins\CustomJsTracker\TrackerUpdater');
                $trackerUpdater->update();
            }
        } catch (\Exception $e) {
            Log::error('There was an error while updating the javascript tracker: ' . $e->getMessage());
        }
    }
}
