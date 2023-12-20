<?php
/**
* Matomo - free/libre analytics platform
*
* @link https://matomo.org
* @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
*/

namespace Piwik\Tests\Framework\Mock\Tracker;

use Piwik\Tracker;

class ScheduledTasksRunner extends Tracker\ScheduledTasksRunner
{
    public $shouldRun = true;
    public $ranScheduledTasks = false;

    public function shouldRun(Tracker $tracker)
    {
        return $this->shouldRun;
    }

    public function runScheduledTasks()
    {
        $this->ranScheduledTasks = true;
    }
}
