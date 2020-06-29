<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomJsTracker;

use Piwik\Container\StaticContainer;

class Tasks extends \Piwik\Plugin\Tasks
{
    public function schedule()
    {
        $this->hourly('updateTracker');
    }

    public function updateTracker()
    {
        $updater = StaticContainer::get('Piwik\Plugins\CustomJsTracker\TrackerUpdater');
        $updater->update();
    }
}
