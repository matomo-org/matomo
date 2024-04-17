<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Scheduler;

use Piwik\Plugins\ScheduledReports\ScheduledReports;
use Piwik\Scheduler\Task;

/**
 * @group Scheduler
 */
class TaskTest extends \PHPUnit\Framework\TestCase
{
    public function testGetClassName()
    {
        $scheduledTask = new Task(new ScheduledReports(), null, null, null);
        $this->assertEquals('Piwik\Plugins\ScheduledReports\ScheduledReports', $scheduledTask->getClassName());
    }

    /**
     * Dataprovider for testGetTaskName
     */
    public function getTaskNameTestCases()
    {
        return array(
            array('CoreAdminHome.purgeOutdatedArchives', 'CoreAdminHome', 'purgeOutdatedArchives', null),
            array('CoreAdminHome.purgeOutdatedArchives_previous30', 'CoreAdminHome', 'purgeOutdatedArchives', 'previous30'),
            array('ScheduledReports.weeklySchedule', 'ScheduledReports', 'weeklySchedule', null),
            array('ScheduledReports.weeklySchedule_1', 'ScheduledReports', 'weeklySchedule', 1),
        );
    }

    /**
     * @dataProvider getTaskNameTestCases
     */
    public function testGetTaskName($expectedTaskName, $className, $methodName, $methodParameter)
    {
        $this->assertEquals($expectedTaskName, Task::getTaskName($className, $methodName, $methodParameter));
    }
}
