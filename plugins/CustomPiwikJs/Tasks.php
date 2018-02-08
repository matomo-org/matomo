<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomPiwikJs;

use Piwik\Container\StaticContainer;

class Tasks extends \Piwik\Plugin\Tasks
{
    public function schedule()
    {
        $this->hourly('updateTracker');
    }

    public function updateTracker()
    {
        $updater = StaticContainer::get('Piwik\Plugins\CustomPiwikJs\TrackerUpdater');
        $updater->update();
    }
}
