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
            'Tracker.Cache.getSiteAttributes' => 'getTrackerCacheSiteAttributes',
        ];
    }

    public function isTrackerPlugin()
    {
        return true;
    }

    public function getTrackerCacheSiteAttributes(&$content, $idSite)
    {
        $content['trackerConfigs']['ExampleTracker'] = [
            'myCustomVisitParam' => 500 + (int)$idSite,
        ];
    }

    public function getAllVisitorDetails(&$visitor, $visitorRawData)
    {
        $visitor['myCustomVisitParam'] = isset($visitorRawData['example_visit_dimension']) ? $visitorRawData['example_visit_dimension'] : 'no-value';
    }

    public function getTrackerConfigs(&$configs, $params)
    {
        $configs['ExampleTracker'] = [
            'randomValue' => Common::getRandomInt(0, 100),
        ];
    }
}
