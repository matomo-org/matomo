<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomPiwikJs;

use Piwik\Container\StaticContainer;
use Piwik\Log;
use Piwik\Plugin;

class CustomPiwikJs extends Plugin
{
    public function getListHooksRegistered()
    {
        return array(
            'CoreUpdater.update.end' => 'updateTracker',
            'CronArchive.end' => 'updateTracker',
            'PluginManager.pluginActivated' => 'updateTracker',
            'PluginManager.pluginDeactivated' => 'updateTracker',
            'PluginManager.pluginInstalled' => 'updateTracker',
            'PluginManager.pluginUninstalled' => 'updateTracker',
            'Updater.componentUpdated' => 'updateTracker',
            'Controller.CoreHome.checkForUpdates.end' => 'updateTracker'
        );
    }

    public function updateTracker()
    {
        try {
            if (Plugin\Manager::getInstance()->isPluginActivated('CustomPiwikJs')) {
                $trackerUpdater = StaticContainer::get('Piwik\Plugins\CustomPiwikJs\TrackerUpdater');
                $trackerUpdater->update();
            }
        } catch (\Exception $e) {
            Log::error('There was an error while updating the javascript tracker: ' . $e->getMessage());
        }
    }
}
