<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ExampleTracker;

use Piwik\Common;

class ExampleTracker extends \Piwik\Plugin
{
    public function getListHooksRegistered()
    {
        return [
            'Tracker.getTrackerConfigs' => 'getTrackerConfigs',
        ];
    }

    public function isTrackerPlugin()
    {
        return true;
    }

    public function getTrackerConfigs(&$configs)
    {
        $configs['ExampleTracker'] = [
            'randomValue' => Common::getRandomInt(0, 100),
        ];
    }
}
