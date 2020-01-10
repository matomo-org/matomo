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
use Piwik\Plugins\Live\Visitor;

class ExampleTracker extends \Piwik\Plugin
{
    public function getListHooksRegistered()
    {
        return [
            'Tracker.getTrackerConfigs' => 'getTrackerConfigs',
            'Live.getAllVisitorDetails' => 'getAllVisitorDetails',
        ];
    }

    public function isTrackerPlugin()
    {
        return true;
    }

    public function getAllVisitorDetails(&$visitor, $visitorRawData)
    {
        $visitor['myCustomVisitParam'] = isset($visitorRawData['example_visit_dimension']) ? $visitorRawData['example_visit_dimension'] : 'no-value';
    }

    public function getTrackerConfigs(&$configs)
    {
        $configs['ExampleTracker'] = [
            'randomValue' => Common::getRandomInt(0, 100),
            'myCustomVisitParam' => 500,
        ];
    }
}
